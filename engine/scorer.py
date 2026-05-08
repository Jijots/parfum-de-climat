"""
scorer.py — Parfum de Climat Recommendation Engine
====================================================

The core climate-to-fragrance matching algorithm.

This module is intentionally dependency-free (pure Python stdlib) so it can
run without importing Pydantic or FastAPI. It operates on the already-validated
dataclass instances produced by models.py.

Algorithm Overview
------------------
Each fragrance in the user's collection is scored independently across three
climate dimensions for each of its mapped notes:

  1. Temperature  (50% weight) — how well the current temp fits the note's range
  2. Season       (35% weight) — whether the current season matches ideal seasons
  3. Humidity     (15% weight) — how well current humidity matches note preference

Each note dimension score is then:
  - Multiplied by the note's climate_weight (0.0–1.0, its influence factor)
  - Multiplied by a layer multiplier (top notes are most weather-reactive)

The note scores are accumulated into a weighted mean, then adjusted by:
  - A sillage penalty for heavy-projecting fragrances in hot+humid conditions
  - A small is_favorite tie-breaking boost (+0.05)

The final score is clamped to [0.0, 1.0] and rounded to 4 decimal places.

Dimension Weights
-----------------
  Temperature : 50%  — the single most decisive factor. A Vanilla fragrance
                        at 35°C is genuinely uncomfortable regardless of season.
  Season      : 35%  — highly important but slightly softer than temperature.
                        A winter fragrance worn on an unseasonably warm autumn
                        day is better than it would be in full summer.
  Humidity    : 15%  — real but subtler. Most people feel humidity less acutely
                        than temperature, and many notes tolerate moderate humidity.

Layer Multipliers
-----------------
  Top   : 1.20 — Top notes are the most volatile and weather-reactive.
                  A citrus top note in cold weather evaporates before it can
                  be appreciated. These notes deserve extra scoring influence.
  Heart : 1.00 — The core character. Standard weight.
  Base  : 0.80 — Base notes are heavy molecules that diffuse slowly regardless
                  of weather. They matter for comfort but are less climate-sensitive.

Sillage Penalty
---------------
High-projection fragrances (sillage > 6.0 on the 0–10 scale) become oppressive
in hot and humid conditions. The penalty is proportional to:
  - How far above 25°C the temperature is
  - How far above 60% the humidity is
  - How excessive the sillage rating is above the threshold

Maximum penalty capped at 30% score reduction (multiplier floor: 0.70).
This prevents legitimate warm-weather fragrances from being unfairly buried.
"""

from __future__ import annotations

import math
from dataclasses import dataclass
from typing import Optional

# Season adjacency for partial-match scoring.
# Each season scores 0.5 when the current season is adjacent to an ideal season.
# Non-adjacent (opposite) seasons score 0.0.
#
#   spring ↔ summer ↔ autumn ↔ winter ↔ spring  (circular)
#   Opposite pairs: spring/autumn, summer/winter
_SEASON_ADJACENCY: dict[str, set[str]] = {
    "spring": {"winter", "summer"},
    "summer": {"spring", "autumn"},
    "autumn": {"summer", "winter"},
    "winter": {"autumn", "spring"},
}

# Humidity level hierarchy for distance-based scoring.
# The distance between 'low' and 'high' is 2 steps.
_HUMIDITY_RANK: dict[str, int] = {
    "low":    0,
    "medium": 1,
    "high":   2,
}

# Dimension contribution weights — must sum to 1.0.
_WEIGHT_TEMPERATURE = 0.50
_WEIGHT_SEASON      = 0.35
_WEIGHT_HUMIDITY    = 0.15

# Layer multipliers — adjusts a note's influence based on pyramid position.
_LAYER_MULTIPLIERS: dict[str, float] = {
    "top":   1.20,
    "heart": 1.00,
    "base":  0.80,
}

# Notes scoring >= this threshold are included in "matched_notes" for reasoning.
_MATCH_THRESHOLD = 0.65

# Sillage threshold above which heat+humidity penalties begin to apply.
_SILLAGE_PENALTY_THRESHOLD = 6.0

# Minimum sillage multiplier — caps the maximum penalty at 30% score reduction.
_SILLAGE_MIN_MULTIPLIER = 0.70

# Score boost applied to fragrances flagged as the user's favorites.
# Intentionally small — just enough to break ties, not distort the ranking.
_FAVORITE_BOOST = 0.05


# ─────────────────────────────────────────────────────────────────────────────
# Dimension Scoring Functions
# ─────────────────────────────────────────────────────────────────────────────

def score_temperature(
    current_temp: float,
    min_temp: Optional[int],
    max_temp: Optional[int],
) -> float:
    """
    Score how well the current temperature fits a note's ideal range.

    Scoring logic:
      - Within range           → 1.0 (perfect)
      - Below minimum          → Linear decay: −0.10 per °C below min
                                  (cold weather makes warm-weather notes disappear
                                   quickly, but don't penalise too aggressively
                                   since a near-season transition is still wearable)
      - Above maximum          → Steeper decay: −0.15 per °C above max
                                  (heavy/gourmand/oriental notes in heat are worse
                                   than light notes in cold — hence the asymmetry)
      - No min + no max (None) → 1.0 (universally applicable note)
      - No lower limit (None)  → Only the upper bound applies
      - No upper limit (None)  → Only the lower bound applies

    Returns:
        float: Score in range [0.0, 1.0].

    Examples:
        >>> score_temperature(32, 18, None)    # 32°C, min 18°C, no max
        1.0
        >>> score_temperature(15, 18, None)    # 3°C below minimum
        0.7
        >>> score_temperature(40, None, 35)    # 5°C above maximum
        0.25
        >>> score_temperature(22, None, None)  # No preference
        1.0
    """
    # No temperature preference → always scores perfectly
    if min_temp is None and max_temp is None:
        return 1.0

    # Too cold: current temp is below the note's minimum
    if min_temp is not None and current_temp < min_temp:
        distance = min_temp - current_temp
        return max(0.0, 1.0 - distance * 0.10)

    # Too hot: current temp exceeds the note's maximum
    if max_temp is not None and current_temp > max_temp:
        distance = current_temp - max_temp
        return max(0.0, 1.0 - distance * 0.15)

    # Within range (or only a one-sided bound that is satisfied)
    return 1.0


def score_season(current_season: str, ideal_seasons: list[str]) -> float:
    """
    Score how well the current season aligns with a note's ideal seasons.

    Scoring logic:
      - Exact match   → 1.0 (current season is in ideal_seasons)
      - Adjacent      → 0.5 (e.g., wearing a spring note in summer — not ideal
                              but the weather is still conducive)
      - Opposite      → 0.0 (e.g., wearing a winter note in summer)

    The circular adjacency map treats seasons as a ring:
        spring → summer → autumn → winter → spring

    Returns:
        float: Score in {0.0, 0.5, 1.0}.

    Examples:
        >>> score_season("summer", ["spring", "summer"])
        1.0
        >>> score_season("autumn", ["spring", "summer"])  # Adjacent to summer
        0.5
        >>> score_season("winter", ["spring", "summer"])  # Opposite of summer
        0.0
    """
    # Exact match
    if current_season in ideal_seasons:
        return 1.0

    # Adjacent season check — partial credit
    adjacent = _SEASON_ADJACENCY.get(current_season, set())
    for ideal in ideal_seasons:
        if ideal in adjacent:
            return 0.5

    # No overlap and no adjacency
    return 0.0


def score_humidity(current_humidity_pct: int, preference: str) -> float:
    """
    Score how well the current humidity matches a note's humidity preference.

    Current humidity is categorised into three bands:
        low    : < 40%
        medium : 40% – 69%
        high   : ≥ 70%

    Scoring logic:
      - 'any' preference          → 1.0 (no penalty)
      - Exact band match          → 1.0
      - One step apart            → 0.60 (e.g., medium note in high humidity)
      - Two steps apart (low↔high)→ 0.20 (e.g., dry-weather note in tropical humidity)

    Returns:
        float: Score in {0.20, 0.60, 1.0}.
    """
    # 'any' means the note is humidity-agnostic — no penalty applied
    if preference == "any":
        return 1.0

    # Categorise the current ambient humidity into a band
    if current_humidity_pct < 40:
        current_band = "low"
    elif current_humidity_pct < 70:
        current_band = "medium"
    else:
        current_band = "high"

    if current_band == preference:
        return 1.0

    # Distance-based penalty: 1 step = −0.40, 2 steps = −0.80
    distance = abs(_HUMIDITY_RANK[current_band] - _HUMIDITY_RANK[preference])
    return max(0.0, 1.0 - distance * 0.40)


def compute_sillage_multiplier(
    sillage: Optional[float],
    temperature: float,
    humidity_percent: int,
) -> float:
    """
    Compute a sillage-based score multiplier for hot and humid conditions.

    Heavy-projecting fragrances (high sillage) can become overwhelming and
    socially intrusive in hot, humid weather. This function penalises them
    proportional to both the sillage excess and the environmental discomfort.

    The penalty is calculated as:
        heat_factor     = how far above 25°C the temperature is (0.0 → 1.0 at 40°C)
        humidity_factor = how far above 60% the humidity is     (0.0 → 1.0 at 100%)
        discomfort      = average of heat_factor and humidity_factor
        sillage_excess  = how much above the threshold the sillage is (normalised)
        penalty         = discomfort × sillage_excess × 0.60

    Multiplier is clamped to [_SILLAGE_MIN_MULTIPLIER, 1.0] so even the
    heaviest fragrance in full tropical heat never drops below 70% of its
    climate score.

    Args:
        sillage:          Fragrance sillage rating (0–10). None = treat as 5.0.
        temperature:      Current temperature in °C.
        humidity_percent: Current relative humidity (0–100).

    Returns:
        float: Multiplier in [0.70, 1.0].

    Examples:
        >>> compute_sillage_multiplier(9.0, 35.0, 85)   # Heavy, hot, humid
        ~0.77
        >>> compute_sillage_multiplier(4.0, 35.0, 85)   # Light, hot, humid
        1.0  (below threshold, no penalty)
        >>> compute_sillage_multiplier(9.0, 15.0, 40)   # Heavy, cold, dry
        1.0  (comfortable conditions, no penalty)
    """
    # Fragrances without sillage data are treated as moderate projectors
    effective_sillage = sillage if sillage is not None else 5.0

    # Below the threshold: no penalty applied at all
    if effective_sillage <= _SILLAGE_PENALTY_THRESHOLD:
        return 1.0

    # Heat factor: normalised excess above 25°C, capped at 1.0 (reached at 40°C)
    heat_factor = max(0.0, min(1.0, (temperature - 25.0) / 15.0))

    # Humidity factor: normalised excess above 60%, capped at 1.0 (reached at 100%)
    humidity_factor = max(0.0, min(1.0, (humidity_percent - 60.0) / 40.0))

    # Environmental discomfort: average of both factors
    discomfort = (heat_factor + humidity_factor) / 2.0

    # Sillage excess: how far above threshold on a 0–1 scale (max at sillage 10)
    sillage_excess = (effective_sillage - _SILLAGE_PENALTY_THRESHOLD) / (
        10.0 - _SILLAGE_PENALTY_THRESHOLD
    )

    # Final penalty magnitude
    penalty = discomfort * sillage_excess * 0.60

    return max(_SILLAGE_MIN_MULTIPLIER, 1.0 - penalty)


# ─────────────────────────────────────────────────────────────────────────────
# Reasoning Generator
# ─────────────────────────────────────────────────────────────────────────────

def generate_reasoning(
    name: str,
    brand: str,
    matched_notes: list[str],
    score: float,
    season: str,
    temperature: float,
    sillage_multiplier: float,
    is_favorite: bool,
) -> str:
    """
    Generate a human-readable explanation of the recommendation score.

    The reasoning is displayed directly on the Flutter recommendation card,
    so it must be clear, specific, and concise (under 300 chars).

    Score tiers:
        ≥ 0.80  → "Excellent match"
        ≥ 0.60  → "Good choice"
        ≥ 0.40  → "Decent option"
        < 0.40  → "Not ideal today"

    Args:
        name:               Fragrance name (for personalization).
        brand:              Brand name.
        matched_notes:      Notes that scored ≥ 0.65 (the "good reasons").
        score:              Final composite score (0.0–1.0).
        season:             Current season string.
        temperature:        Current temperature in °C.
        sillage_multiplier: Sillage penalty multiplier (< 1.0 means penalised).
        is_favorite:        Whether the user's favorite boost was applied.

    Returns:
        str: Reasoning string, ≤ 300 characters.
    """
    # ── Score tier label ──────────────────────────────────────────────────────
    if score >= 0.80:
        tier = "An excellent match"
    elif score >= 0.60:
        tier = "A solid choice"
    elif score >= 0.40:
        tier = "A decent option"
    else:
        tier = "Not ideal today"

    # ── Note mention ──────────────────────────────────────────────────────────
    if matched_notes:
        top_notes = matched_notes[:3]
        if len(top_notes) == 1:
            note_phrase = f"{top_notes[0]} thrives"
        elif len(top_notes) == 2:
            note_phrase = f"{top_notes[0]} and {top_notes[1]} thrive"
        else:
            note_phrase = f"{top_notes[0]}, {top_notes[1]}, and {top_notes[2]} thrive"
        note_mention = f" — {note_phrase} in {season} at {temperature:.0f}°C."
    else:
        note_mention = f" for {season} weather at {temperature:.0f}°C."

    # ── Sillage caveat ────────────────────────────────────────────────────────
    sillage_note = ""
    if sillage_multiplier < 0.90:
        sillage_note = " Note: strong sillage may project heavily in current humidity."

    # ── Favorite note ─────────────────────────────────────────────────────────
    fav_note = " (Your favourite — tie-breaking boost applied.)" if is_favorite else ""

    return f"{tier}{note_mention}{sillage_note}{fav_note}"[:300]


# ─────────────────────────────────────────────────────────────────────────────
# Primary Scoring Function
# ─────────────────────────────────────────────────────────────────────────────

@dataclass
class NoteScoreDetail:
    """Intermediate scoring data for a single note — used for the breakdown."""
    note_name:      str
    layer:          str
    temp_score:     float
    season_score:   float
    humidity_score: float
    composite:      float   # Combined before weight × layer multiplier
    weighted:       float   # After weight × layer multiplier


def score_fragrance(
    fragrance_id: int,
    name: str,
    brand: str,
    is_favorite: bool,
    sillage: Optional[float],
    notes: list[dict],
    weather: dict,
) -> dict:
    """
    Compute the full climate suitability score for a single fragrance.

    This function is the engine's core computation unit. It is called once
    per fragrance per recommendation request.

    Args:
        fragrance_id: DB primary key, passed through to output.
        name:         Fragrance name, passed through.
        brand:        Brand name, passed through.
        is_favorite:  User's favorite flag — applies tie-breaking boost.
        sillage:      Community sillage score (0–10), or None.
        notes:        List of note profile dicts (from NoteProfile.model_dump()).
        weather:      Weather context dict (from WeatherContext.model_dump()).

    Returns:
        dict: A ScoredFragrance-compatible dict with all scoring output fields.

    Algorithm (see module docstring for full rationale):
        1. For each note:
            a. Compute temp_score, season_score, humidity_score
            b. Weighted composite = (temp×0.50 + season×0.35 + humidity×0.15)
               × climate_weight × layer_multiplier
        2. Normalise by total possible weighted score
        3. Apply sillage multiplier
        4. Apply favorite boost
        5. Clamp to [0.0, 1.0] and round to 4dp
    """
    temp        = weather["temperature_celsius"]
    humidity    = weather["humidity_percent"]
    season      = weather["season"]

    accumulated_score  = 0.0
    total_weight       = 0.0
    matched_notes: list[str] = []
    note_details:  list[NoteScoreDetail] = []

    # ── Score each note ───────────────────────────────────────────────────────
    for note in notes:
        layer_mult   = _LAYER_MULTIPLIERS.get(note["layer"], 1.0)
        climate_wt   = float(note["climate_weight"])
        effective_wt = climate_wt * layer_mult

        # Dimension scores
        t_score = score_temperature(temp, note["min_temp_celsius"], note["max_temp_celsius"])
        s_score = score_season(season, [s if isinstance(s, str) else s.value for s in note["ideal_seasons"]])
        h_score = score_humidity(humidity, note["humidity_preference"] if isinstance(note["humidity_preference"], str) else note["humidity_preference"].value)

        # Weighted composite for this note
        composite = (
            t_score * _WEIGHT_TEMPERATURE
            + s_score * _WEIGHT_SEASON
            + h_score * _WEIGHT_HUMIDITY
        )
        weighted = composite * effective_wt

        accumulated_score += weighted
        total_weight      += effective_wt

        # Track matched notes for the reasoning string
        if composite >= _MATCH_THRESHOLD:
            matched_notes.append(note["name"])

        note_details.append(NoteScoreDetail(
            note_name    = note["name"],
            layer        = note["layer"],
            temp_score   = round(t_score, 4),
            season_score = round(s_score, 4),
            humidity_score = round(h_score, 4),
            composite    = round(composite, 4),
            weighted     = round(weighted, 4),
        ))

    # ── Normalise ─────────────────────────────────────────────────────────────
    base_score = accumulated_score / total_weight if total_weight > 0.0 else 0.0

    # ── Apply sillage penalty ─────────────────────────────────────────────────
    sillage_mult = compute_sillage_multiplier(sillage, temp, humidity)
    penalised    = base_score * sillage_mult

    # ── Apply favorite boost ──────────────────────────────────────────────────
    fav_boost  = _FAVORITE_BOOST if is_favorite else 0.0
    final      = min(1.0, penalised + fav_boost)
    final      = round(final, 4)

    # ── Build reasoning string ────────────────────────────────────────────────
    reasoning = generate_reasoning(
        name               = name,
        brand              = brand,
        matched_notes      = matched_notes,
        score              = final,
        season             = season,
        temperature        = temp,
        sillage_multiplier = sillage_mult,
        is_favorite        = is_favorite,
    )

    return {
        "fragrance_id":  fragrance_id,
        "name":          name,
        "brand":         brand,
        "score":         final,
        "matched_notes": matched_notes,
        "reasoning":     reasoning,
        "score_breakdown": {
            "base_score":          round(base_score, 4),
            "sillage_multiplier":  round(sillage_mult, 4),
            "favorite_boost":      fav_boost,
            "notes_scored":        len(notes),
            "notes_matched":       len(matched_notes),
        },
    }


# ─────────────────────────────────────────────────────────────────────────────
# Batch Scorer
# ─────────────────────────────────────────────────────────────────────────────

def score_collection(request_dict: dict) -> list[dict]:
    """
    Score all fragrances in the collection and return them sorted by score.

    This is the main public interface for the recommendation engine.
    It accepts and returns plain Python dicts so it can be called from both
    the subprocess entry point and the FastAPI endpoint without Pydantic
    serialisation overhead.

    Args:
        request_dict: Validated EngineRequest serialised to dict
                      (via EngineRequest.model_dump()).

    Returns:
        list[dict]: All ScoredFragrance dicts, sorted by score descending.
                    Laravel takes the first 3 as the recommendation.
    """
    weather    = request_dict["weather"]
    collection = request_dict["collection"]

    results = [
        score_fragrance(
            fragrance_id = item["fragrance_id"],
            name         = item["name"],
            brand        = item["brand"],
            is_favorite  = item["is_favorite"],
            sillage      = item.get("sillage"),
            notes        = item["notes"],
            weather      = weather,
        )
        for item in collection
    ]

    # Sort descending by score, then by name as a deterministic tiebreaker
    results.sort(key=lambda x: (-x["score"], x["name"]))

    return results
