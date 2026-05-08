/// The full response from POST /api/v1/recommend, combining the weather
/// context with the top-3 scored fragrances.
/// [RecommendationResult] is what the UI binds to — it carries everything
/// needed to render the recommendation screen in one object.

import 'package:freezed_annotation/freezed_annotation.dart';

import 'scored_fragrance.dart';

part 'recommendation_result.freezed.dart';
part 'recommendation_result.g.dart';

// WeatherContext

/// Weather snapshot used to generate this recommendation.
/// Displayed in the header of the recommendation screen.
@freezed
class WeatherContext with _$WeatherContext {
  const factory WeatherContext({
    @JsonKey(name: 'temperature_celsius') required double temperatureCelsius,
    @JsonKey(name: 'humidity_percent') required double humidityPercent,
    required String condition,
    required String season,
    required String location,
  }) = _WeatherContext;

  factory WeatherContext.fromJson(Map<String, dynamic> json) =>
      _$WeatherContextFromJson(json);
}

// RecommendationResult

@freezed
class RecommendationResult with _$RecommendationResult {
  const factory RecommendationResult({
    /// The WeatherLog primary key — passed to PATCH /recommend/{logId}/choose.
    @JsonKey(name: 'log_id') required int logId,

    required WeatherContext weather,

    /// Top 3 fragrances from the Python engine, sorted by score descending.
    required List<ScoredFragrance> recommendations,
  }) = _RecommendationResult;

  factory RecommendationResult.fromJson(Map<String, dynamic> json) =>
      _$RecommendationResultFromJson(json);
}

// HistoryEntry

/// A single entry from GET /api/v1/recommend/history.
/// Includes the weather context, the engine's output, and the chosen
/// fragrance (if the user selected one via the choose endpoint).
@freezed
class HistoryEntry with _$HistoryEntry {
  const factory HistoryEntry({
    required int id,
    required WeatherContext weather,
    @JsonKey(name: 'engine_response_payload')
    required List<ScoredFragrance> engineResponse,
    @JsonKey(name: 'chosen_fragrance') ChosenFragrance? chosenFragrance,
    @JsonKey(name: 'created_at') required DateTime createdAt,
  }) = _HistoryEntry;

  factory HistoryEntry.fromJson(Map<String, dynamic> json) =>
      _$HistoryEntryFromJson(json);
}

/// The fragrance the user chose to wear for a given [HistoryEntry].
@freezed
class ChosenFragrance with _$ChosenFragrance {
  const factory ChosenFragrance({
    required int id,
    required String name,
    required String brand,
  }) = _ChosenFragrance;

  factory ChosenFragrance.fromJson(Map<String, dynamic> json) =>
      _$ChosenFragranceFromJson(json);
}
