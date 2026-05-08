/// Riverpod providers for the fragrance browse and detail screens.
/// [fragranceListProvider] — paginated list, re-fetches when [page] changes.
/// [fragranceDetailProvider] — caches detail per [id].
/// [fragranceSearchProvider] — debounced search state.

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/providers/core_providers.dart';
import '../data/fragrance_repository.dart';
import '../data/models/fragrance.dart';

// Repository provider

final fragranceRepositoryProvider = Provider<FragranceRepository>((ref) {
  return FragranceRepository(apiClient: ref.watch(apiClientProvider));
});

// List provider

/// Parameters for the fragrance list (page + optional search query).
typedef FragranceListParams = ({int page, String? search});

/// Paginated fragrance list.
/// Usage: ref.watch(fragranceListProvider((page: 1, search: 'Santal')))
final fragranceListProvider = FutureProvider.autoDispose
    .family<PaginatedFragrances, FragranceListParams>((ref, params) {
  return ref.watch(fragranceRepositoryProvider).list(
        page: params.page,
        search: params.search,
      );
});

// Detail provider

/// Full fragrance detail, cached by [id] for the session.
/// Usage: ref.watch(fragranceDetailProvider(42))
final fragranceDetailProvider =
    FutureProvider.autoDispose.family<Fragrance, int>((ref, id) {
  return ref.watch(fragranceRepositoryProvider).detail(id);
});

// Search state

/// The current search query string entered by the user.
/// Widgets that trigger searches write to this provider:
///   ref.read(fragranceSearchQueryProvider.notifier).state = 'bergamot';
/// The browse screen watches [fragranceListProvider((page: 1, search: query))].
final fragranceSearchQueryProvider = StateProvider.autoDispose<String>(
  (ref) => '',
);
