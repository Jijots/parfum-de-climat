/// Immutable model for a single scored fragrance as returned by the Python
/// engine via POST /api/v1/recommend.
/// Maps 1-to-1 to the engine's [ScoredFragrance] Pydantic model (scorer.py).
/// Field names use camelCase here; [JsonKey] handles the snake_case API keys.

import 'package:freezed_annotation/freezed_annotation.dart';

part 'scored_fragrance.freezed.dart';
part 'scored_fragrance.g.dart';

// ScoredFragrance

@freezed
class ScoredFragrance with _$ScoredFragrance {
  const factory ScoredFragrance({
    /// The fragrance's primary key in our MySQL database.
    @JsonKey(name: 'fragrance_id') required int fragranceId,
    required String name,
    required String brand,

    /// Composite climate-suitability score in [0.0, 1.0].
    /// Derived from temperature (50%), season (35%), humidity (15%)
    /// with sillage penalty and favourite boost applied.
    required double score,

    /// Note names that contributed positively to the score.
    /// Shown in the recommendation card ("Matched notes: Bergamot, Neroli").
    @JsonKey(name: 'matched_notes') required List<String> matchedNotes,

    /// Human-readable explanation for the score (e.g.
    /// "A perfect match — Bergamot and Neroli thrive in 30°C summer heat.").
    required String reasoning,

    @JsonKey(name: 'score_breakdown') required ScoreBreakdown scoreBreakdown,

    /// Relative path to the cached bottle image served by Laravel Storage.
    /// May be null if the import ran with --no-images.
    @JsonKey(name: 'cached_image_path') String? cachedImagePath,
  }) = _ScoredFragrance;

  factory ScoredFragrance.fromJson(Map<String, dynamic> json) =>
      _$ScoredFragranceFromJson(json);
}

// ScoreBreakdown

/// Detailed per-dimension scores for the debug / developer view.
/// Shown only in a "Why this?" expandable panel — not in the main card.
@freezed
class ScoreBreakdown with _$ScoreBreakdown {
  const factory ScoreBreakdown({
    @JsonKey(name: 'base_score') required double baseScore,
    @JsonKey(name: 'sillage_multiplier') required double sillageMultiplier,
    @JsonKey(name: 'favorite_boost') required double favoriteBoost,
    @JsonKey(name: 'final_score') required double finalScore,
  }) = _ScoreBreakdown;

  factory ScoreBreakdown.fromJson(Map<String, dynamic> json) =>
      _$ScoreBreakdownFromJson(json);
}
