/// Riverpod state management for the recommendation flow.
/// [RecommendationNotifier] drives the core feature:
///   1. User opens the app / taps "Recommend" button
///   2. [fetchRecommendations] requests GPS from [LocationService]
///   3. GPS coordinates are posted to Laravel → engine runs → top 3 returned
///   4. State becomes [AsyncData(RecommendationResult)]
///   5. UI displays the top 3 recommendation cards
///   6. User taps a card → [chooseFragrance] records their selection
/// [historyProvider] is a simple [FutureProvider] for the history screen.

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/providers/core_providers.dart';
import '../../../services/location_service.dart';
import '../data/recommendation_repository.dart';
import '../data/models/recommendation_result.dart';

// Repository provider

final recommendationRepositoryProvider =
    Provider<RecommendationRepository>((ref) {
  return RecommendationRepository(
    apiClient: ref.watch(apiClientProvider),
  );
});

// Notifier

/// Manages the recommendation request lifecycle.
/// State transitions:
///   null (initial)  →  AsyncLoading  →  AsyncData(result)
///                                    ↘  AsyncError
class RecommendationNotifier
    extends AutoDisposeAsyncNotifier<RecommendationResult?> {
  @override
  Future<RecommendationResult?> build() async {
    // Start with no result — the user must trigger a fetch explicitly.
    return null;
  }

  /// Request GPS permission, get current coordinates, and fetch recommendations.
  /// Sets state to [AsyncLoading] while the request is in flight.
  /// On success, state becomes [AsyncData(RecommendationResult)].
  /// On failure, state becomes [AsyncError] with a typed [AppException].
  Future<void> fetchRecommendations() async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      // Step 1: Get current GPS position (throws AppException on failure).
      final locationService = ref.read(locationServiceProvider);
      final position = await locationService.currentPosition();

      // Step 2: Send to Laravel and get scored recommendations.
      final result = await ref
          .read(recommendationRepositoryProvider)
          .fetchRecommendations(
            latitude: position.latitude,
            longitude: position.longitude,
          );

      return result;
    });
  }

  /// Record the user's fragrance choice for the current recommendation log.
  /// This is a fire-and-forget feedback signal — state is not updated.
  Future<void> chooseFragrance(int fragranceId) async {
    final logId = state.valueOrNull?.logId;
    if (logId == null) return;

    await ref
        .read(recommendationRepositoryProvider)
        .chooseFragrance(logId: logId, fragranceId: fragranceId);
  }
}

// Providers

/// autoDispose: the result is screen-scoped. When the user navigates away,
/// the state is cleared so the next visit always triggers a fresh fetch.
final recommendationNotifierProvider =
    AsyncNotifierProvider.autoDispose<RecommendationNotifier, RecommendationResult?>(
  RecommendationNotifier.new,
);

/// Paginated recommendation history.
/// Provide [page] via [historyProvider(2)] for page 2.
final historyProvider =
    FutureProvider.autoDispose.family<List<HistoryEntry>, int>(
  (ref, page) => ref
      .watch(recommendationRepositoryProvider)
      .fetchHistory(page: page),
);
