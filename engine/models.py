"""
models.py — Parfum de Climat Recommendation Engine
===================================================

Pydantic v2 data models that define the strict contract between the Laravel
backend and the Python recommendation engine.

These models serve as the single source of truth for the engine's I/O shape.
Any change here must be reflected in:
  - RecommendationController::recommend() (payload construction)
  - EngineService::run() (response parsing)
  - WeatherLogResource (response serialisation to Flutter)

Data flow:
  Flutter → POST /api/v1/recommend
         → RecommendationController builds EngineRequest
         → EngineService pipes JSON to engine stdin
         → Engine validates with these models
         → Engine writes EngineResponse JSON to stdout
         → Controller logs + returns top 3 to Flutter
"""

from __future__ import annotations

from enum import Enum
from typing import Optional

from pydantic import BaseModel, Field, field_validator, model_validator


# ─────────────────────────────────────────────────────────────────────────────
# Enumerations
# ─────────────────────────────────────────────────────────────────────────────

class Season(str, Enum):
    """
    Meteorological season. Derived server-side from the user's timezone
    and the current calendar date, accounting for Southern Hemisphere inversion.
    """
    SPRING = "spring"
    SUMMER = "summer"
    AUTUMN = "autumn"
    WINTER = "winter"


class Layer(str, Enum):
    """
    Olfactive pyramid layer.
    - TOP   : first impression (0–30 min), most volatile, most weather-reactive
    - HEART : core character (30 min – 4 hrs)
    - BASE  : dry-down (4 hrs+), heaviest molecules, least weather-reactive
    """
    TOP   = "top"
    HEART = "heart"
    BASE  = "base"


class HumidityPreference(str, Enum):
    """
    A note's preferred ambient humidity level.
    'any' means no humidity penalty is applied — the note performs universally.
    """
    LOW    = "low"
    MEDIUM = "medium"
    HIGH   = "high"
    ANY    = "any"


# ─────────────────────────────────────────────────────────────────────────────
# Input Models (Laravel → Engine)
# ─────────────────────────────────────────────────────────────────────────────

class WeatherContext(BaseModel):
    """
    The current weather snapshot at the user's location.
    All fields are required — the engine's scoring is entirely weather-driven.
    """

    temperature_celsius: float = Field(
        ...,
        description="Ambient temperature in °C. Range: -30 to 55 (practical limits).",
        ge=-30,
        le=55,
    )
    humidity_percent: int = Field(
        ...,
        description="Relative humidity as a percentage.",
        ge=0,
        le=100,
    )
    condition: str = Field(
        ...,
        description="Broad weather condition from OpenWeatherMap, e.g. 'Clear', 'Rain', 'Snow'.",
        max_length=100,
    )
    season: Season = Field(
        ...,
        description="Derived meteorological season, hemisphere-corrected.",
    )


class NoteProfile(BaseModel):
    """
    The climate profile of a single olfactive note linked to a fragrance.

    Only notes where note_profile_id IS NOT NULL are included in the engine
    payload — unmapped notes are silently excluded by the Laravel controller.

    The combination of (name, layer, climate_weight) is how the engine
    identifies which notes to weight most heavily in its scoring pass.
    """

    name: str = Field(
        ...,
        description="Canonical note name, e.g. 'Bergamot', 'Oud', 'Vanilla'.",
        max_length=100,
    )
    layer: Layer = Field(
        ...,
        description="Which pyramid layer this note belongs to.",
    )
    note_family: Optional[str] = Field(
        None,
        description="Broad olfactive family, e.g. 'Citrus', 'Woody', 'Gourmand'.",
        max_length=100,
    )

    # ── Climate parameters (from note_climate_profiles table) ────────────────
    min_temp_celsius: Optional[int] = Field(
        None,
        description="Lower ideal temperature bound. None = no lower limit.",
        ge=-20,
        le=50,
    )
    max_temp_celsius: Optional[int] = Field(
        None,
        description="Upper ideal temperature bound. None = no upper limit.",
        ge=-20,
        le=50,
    )
    humidity_preference: HumidityPreference = Field(
        HumidityPreference.ANY,
        description="Preferred humidity level. 'any' = no penalty.",
    )
    ideal_seasons: list[Season] = Field(
        ...,
        description="Seasons this note performs best in.",
        min_length=1,
    )
    climate_weight: float = Field(
        0.50,
        description="Engine influence factor. Range 0.00 (negligible) to 1.00 (dominant).",
        ge=0.0,
        le=1.0,
    )

    @model_validator(mode="after")
    def validate_temp_range(self) -> "NoteProfile":
        """Ensure min_temp <= max_temp when both are specified."""
        if (
            self.min_temp_celsius is not None
            and self.max_temp_celsius is not None
            and self.min_temp_celsius > self.max_temp_celsius
        ):
            raise ValueError(
                f"min_temp_celsius ({self.min_temp_celsius}) must be ≤ "
                f"max_temp_celsius ({self.max_temp_celsius}) for note '{self.name}'."
            )
        return self


class FragranceItem(BaseModel):
    """
    A single fragrance from the user's personal collection, serialised
    with all its mapped note climate profiles ready for scoring.
    """

    fragrance_id: int = Field(..., description="Primary key from the fragrances table.", gt=0)
    name: str = Field(..., description="Fragrance name, e.g. 'Baccarat Rouge 540'.", max_length=255)
    brand: str = Field(..., description="Brand name, e.g. 'Maison Francis Kurkdjian'.", max_length=255)

    is_favorite: bool = Field(
        False,
        description="User has marked this as a favorite. Applies tie-breaking score boost.",
    )
    sillage: Optional[float] = Field(
        None,
        description="Community sillage score (0–10). Used for heat/humidity penalty.",
        ge=0.0,
        le=10.0,
    )

    notes: list[NoteProfile] = Field(
        ...,
        description="All mapped note profiles for this fragrance across all pyramid layers.",
    )

    @field_validator("notes")
    @classmethod
    def notes_must_not_be_empty(cls, v: list[NoteProfile]) -> list[NoteProfile]:
        """
        The Laravel controller should already filter out fragrances with no
        mapped notes, but we enforce it here as a defence-in-depth measure.
        """
        if not v:
            raise ValueError("A fragrance must have at least one mapped note profile to be scored.")
        return v


class EngineRequest(BaseModel):
    """
    The root input model. This is the exact JSON structure the engine
    reads from stdin (subprocess mode) or receives as a POST body
    (FastAPI microservice mode).

    Constructed in: RecommendationController::recommend()
    Serialised via: EngineService::run()
    """

    weather: WeatherContext = Field(
        ...,
        description="Current weather snapshot at the user's GPS location.",
    )
    collection: list[FragranceItem] = Field(
        ...,
        description="User's fragrance collection with all mapped note profiles.",
        min_length=1,
    )


# ─────────────────────────────────────────────────────────────────────────────
# Output Models (Engine → Laravel)
# ─────────────────────────────────────────────────────────────────────────────

class ScoredFragrance(BaseModel):
    """
    The recommendation result for a single fragrance.
    The engine returns a list of these, sorted by score descending.
    Laravel takes the top 3 and returns them to Flutter.
    """

    fragrance_id: int = Field(..., description="Matches FragranceItem.fragrance_id.")
    name: str = Field(..., description="Fragrance name, passed through for Flutter display.")
    brand: str = Field(..., description="Brand name, passed through for Flutter display.")

    score: float = Field(
        ...,
        description=(
            "Composite climate suitability score. "
            "Range: 0.0 (terrible match) to 1.0 (perfect match). "
            "Rounded to 4 decimal places."
        ),
        ge=0.0,
        le=1.0,
    )
    matched_notes: list[str] = Field(
        default_factory=list,
        description=(
            "Notes that scored ≥ 0.65 on the composite note score — "
            "the specific reasons this fragrance ranked well. "
            "Used to generate the reasoning string and displayed in Flutter."
        ),
    )
    reasoning: str = Field(
        ...,
        description=(
            "Human-readable explanation of the score. "
            "Displayed directly on the Flutter recommendation card."
        ),
        max_length=300,
    )

    # ── Score breakdown (for transparency / debugging) ────────────────────────
    score_breakdown: dict[str, float] = Field(
        default_factory=dict,
        description=(
            "Per-dimension score contributions for debugging and admin analytics. "
            "Keys: 'temperature', 'season', 'humidity', 'sillage_multiplier', 'favorite_boost'."
        ),
    )


class EngineResponse(BaseModel):
    """
    The root output model. The engine writes this as a JSON array to stdout.
    Laravel parses it with EngineService::run() and stores it in weather_logs.

    Note: This is serialised as a plain list (not a wrapped object) because
    the Laravel controller accesses it as an array directly.
    """

    results: list[ScoredFragrance] = Field(
        ...,
        description="All fragrances from the collection, scored and sorted descending.",
    )

    def to_list(self) -> list[dict]:
        """
        Serialise to a plain list of dicts for stdout output and DB storage.
        This is the format Laravel's EngineService expects.
        """
        return [item.model_dump() for item in self.results]
