/// Data layer for fragrance recommendations.
/// Three operations:
///   [fetchRecommendations] — POST /recommend with GPS coordinates.
///   [chooseFragrance]      — PATCH /recommend/{logId}/choose.
///   [fetchHistory]         — GET /recommend/history (paginated).

import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import 'models/recommendation_result.dart';

class RecommendationRepository {
  const RecommendationRepository({required ApiClient apiClient})
      : _api = apiClient;

  final ApiClient _api;

  /// Send GPS coordinates to Laravel, which fetches weather, runs the Python
  /// engine, persists a WeatherLog, and returns the top-3 recommendations.
  /// [latitude] and [longitude] are the device's current GPS coordinates as
  /// returned by [LocationService.currentPosition].
  /// Throws [ServiceUnavailableException] if the engine or OWM is down.
  /// Throws [NetworkException] if the device is offline.
  Future<RecommendationResult> fetchRecommendations({
    required double latitude,
    required double longitude,
  }) async {
    final response = await _api.post<Map<String, dynamic>>(
      ApiEndpoints.recommend,
      data: {'latitude': latitude, 'longitude': longitude},
    );

    return RecommendationResult.fromJson(response.data!);
  }

  /// Record which fragrance the user chose to wear for [logId].
  /// Called after the user taps a recommendation card on the result screen.
  /// Silently ignores errors — this is a feedback signal, not critical path.
  Future<void> chooseFragrance({
    required int logId,
    required int fragranceId,
  }) async {
    try {
      await _api.patch<void>(
        ApiEndpoints.chooseFragrance(logId),
        data: {'fragrance_id': fragranceId},
      );
    } catch (_) {
      // Best-effort: choosing a fragrance is non-critical; log silently.
    }
  }

  /// Fetch the user's recommendation history, paginated newest-first.
  /// [page] is 1-indexed. Each page contains up to 15 entries.
  Future<List<HistoryEntry>> fetchHistory({int page = 1}) async {
    final response = await _api.get<Map<String, dynamic>>(
      ApiEndpoints.recommendHistory,
      queryParameters: {'page': page},
    );

    final body = response.data!;
    final items = body['data'] as List<dynamic>;
    return items
        .map((e) => HistoryEntry.fromJson(e as Map<String, dynamic>))
        .toList();
  }
}
