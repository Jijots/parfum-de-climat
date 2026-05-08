/// Dart mirror of the JSON shape emitted by [FragranceResource] on Laravel.
/// API shapes:
///   GET /api/v1/fragrances        → list of [FragranceSummary] (no notes/accords)
///   GET /api/v1/fragrances/{id}   → single [Fragrance] (full detail, notes + accords)
/// Key design decisions vs. the original draft:
///   • Sillage / longevity / rating / votes are nested under [FragrancePerformance]
///     because [FragranceResource] groups them under a "performance" key.
///   • Image is a single nullable [imageUrl] string (resolved server-side by
///     Fragrance::getImageUrl(), which prefers local cache then remote CDN).
///   • [FragranceNotes] (top/heart/base) is only present on the detail endpoint.
///   • [hasClimateProfile] is computed server-side from mapped_notes_count and
///     indicates whether the Python engine can score this fragrance.

import 'package:freezed_annotation/freezed_annotation.dart';

part 'fragrance.freezed.dart';
part 'fragrance.g.dart';

// FragrancePerformance

/// The nested "performance" object from [FragranceResource].
/// All fields are nullable — a newly imported fragrance may have no community
/// data yet. The UI should show placeholders (e.g., dashes) rather than crash.
@freezed
class FragrancePerformance with _$FragrancePerformance {
  const factory FragrancePerformance({
    /// Projection strength, 1–10. Higher = the scent travels further from skin.
    double? sillage,

    /// Staying power, 1–10. Higher = lasts longer on skin.
    double? longevity,

    /// Community average rating, 0.0–5.0.
    double? rating,

    /// Number of community votes that produced [rating].
    int? votes,
  }) = _FragrancePerformance;

  factory FragrancePerformance.fromJson(Map<String, dynamic> json) =>
      _$FragrancePerformanceFromJson(json);
}

// FragranceNotes

/// The nested "notes" object from [FragranceResource] (detail endpoint only).
/// Each list contains raw note name strings (e.g., "Bergamot", "Vanilla").
/// Only present when the controller eager-loads [notes]; will be null on the
/// list endpoint where notes are not loaded to keep payloads lean.
@freezed
class FragranceNotes with _$FragranceNotes {
  const factory FragranceNotes({
    /// Top notes — first impression, evaporates within 15–30 minutes.
    @Default([]) List<String> top,

    /// Heart notes — the core character of the fragrance.
    @Default([]) List<String> heart,

    /// Base notes — the dry-down, lasting 4–8 hours on skin.
    @Default([]) List<String> base,
  }) = _FragranceNotes;

  factory FragranceNotes.fromJson(Map<String, dynamic> json) =>
      _$FragranceNotesFromJson(json);
}

// FragranceAccord

/// A single accord entry from the "accords" array in [FragranceResource].
/// Accords describe the overall blended character of a fragrance
/// (e.g., "Woody", "Citrus") rather than individual ingredients.
/// [strength] mirrors Fragrantica's accord bar chart representation
/// and is used as a weighting factor by the Python recommendation engine.
@freezed
class FragranceAccord with _$FragranceAccord {
  const factory FragranceAccord({
    /// Accord label, e.g., "Woody", "Citrus", "Spicy", "Powdery".
    required String accord,

    /// Relative dominance of this accord, 0–100.
    required double strength,
  }) = _FragranceAccord;

  factory FragranceAccord.fromJson(Map<String, dynamic> json) =>
      _$FragranceAccordFromJson(json);
}

// FragranceSummary

/// Lightweight model for the fragrance browse list.
/// Notes and accords are intentionally absent — they are only fetched on the
/// detail endpoint to keep list payloads lean. Use [Fragrance] for full detail.
@freezed
class FragranceSummary with _$FragranceSummary {
  const factory FragranceSummary({
    required int id,
    required String name,
    required String brand,

    @JsonKey(name: 'olfactive_family') String? olfactiveFamily,
    @JsonKey(name: 'gender_target') String? genderTarget,
    @JsonKey(name: 'release_year') int? releaseYear,
    String? concentration,

    /// Server-resolved image URL. Prefers local storage cache; falls back to
    /// the remote CDN URL stored at import time; null if neither is available.
    @JsonKey(name: 'image_url') String? imageUrl,

    /// Sillage, longevity, rating, and vote count — grouped as on the server.
    FragrancePerformance? performance,

    /// True when the fragrance has at least one note mapped to a climate profile.
    /// Used in the browse list to render an "unscored" badge so users know the
    /// engine can't recommend this fragrance until an admin maps its notes.
    @JsonKey(name: 'has_climate_profile') @Default(false) bool hasClimateProfile,

    @JsonKey(name: 'is_active') @Default(true) bool isActive,
  }) = _FragranceSummary;

  factory FragranceSummary.fromJson(Map<String, dynamic> json) =>
      _$FragranceSummaryFromJson(json);
}

// Fragrance (full detail)

/// Full fragrance model returned by GET /api/v1/fragrances/{id}.
/// Includes all fields from [FragranceSummary] plus notes (top/heart/base),
/// accords, and the full description. All nullable fields are gracefully
/// handled — the detail screen must never crash on incomplete data.
@freezed
class Fragrance with _$Fragrance {
  const factory Fragrance({
    required int id,
    required String name,
    required String brand,
    String? description,

    @JsonKey(name: 'olfactive_family') String? olfactiveFamily,
    @JsonKey(name: 'gender_target') String? genderTarget,
    @JsonKey(name: 'release_year') int? releaseYear,
    String? concentration,

    /// Server-resolved image URL (same resolution logic as [FragranceSummary]).
    @JsonKey(name: 'image_url') String? imageUrl,

    /// Sillage, longevity, community rating, and vote count.
    FragrancePerformance? performance,

    /// Fragrance pyramid — only populated on the detail endpoint.
    FragranceNotes? notes,

    /// Accords with strength weighting — only populated on the detail endpoint.
    @Default([]) List<FragranceAccord> accords,

    @JsonKey(name: 'has_climate_profile') @Default(false) bool hasClimateProfile,
    @JsonKey(name: 'is_active') @Default(true) bool isActive,
  }) = _Fragrance;

  factory Fragrance.fromJson(Map<String, dynamic> json) =>
      _$FragranceFromJson(json);
}

// PaginatedFragrances

/// Laravel's default pagination envelope for the fragrance browse list.
/// Maps to the JSON produced by [Fragrance::paginate()] after passing through
/// [FragranceResource::collection()].
@freezed
class PaginatedFragrances with _$PaginatedFragrances {
  const factory PaginatedFragrances({
    required List<FragranceSummary> data,
    @JsonKey(name: 'current_page') required int currentPage,
    @JsonKey(name: 'last_page') required int lastPage,
    required int total,
  }) = _PaginatedFragrances;

  factory PaginatedFragrances.fromJson(Map<String, dynamic> json) =>
      _$PaginatedFragrancesFromJson(json);
}
