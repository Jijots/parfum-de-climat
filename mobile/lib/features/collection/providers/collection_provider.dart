/// Riverpod state management for the user's fragrance wardrobe.
/// [CollectionNotifier] holds the full list of [CollectionItem]s.
/// Mutations (add, update, remove) update the list optimistically so the UI
/// reflects changes immediately without a full refetch.

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/providers/core_providers.dart';
import '../data/collection_repository.dart';
import '../data/models/collection_item.dart';

// Repository provider

final collectionRepositoryProvider = Provider<CollectionRepository>((ref) {
  return CollectionRepository(apiClient: ref.watch(apiClientProvider));
});

// Notifier

class CollectionNotifier
    extends AutoDisposeAsyncNotifier<List<CollectionItem>> {
  @override
  Future<List<CollectionItem>> build() =>
      ref.watch(collectionRepositoryProvider).fetchCollection();

  // Mutations

  /// Add [fragranceId] to the wardrobe and prepend it to the local list.
  Future<void> add({
    required int fragranceId,
    bool isFavorite = false,
    String? personalNotes,
    DateTime? acquiredDate,
  }) async {
    final item = await ref.read(collectionRepositoryProvider).addToCollection(
          fragranceId: fragranceId,
          isFavorite: isFavorite,
          personalNotes: personalNotes,
          acquiredDate: acquiredDate,
        );

    // Optimistic update: prepend to current list.
    state = state.whenData((items) => [item, ...items]);
  }

  /// Toggle the favourite flag for [fragranceId].
  Future<void> toggleFavourite(int fragranceId) async {
    final current = state.valueOrNull;
    if (current == null) return;

    final index = current.indexWhere((i) => i.fragranceId == fragranceId);
    if (index == -1) return;

    final updated = await ref.read(collectionRepositoryProvider).updateItem(
          fragranceId: fragranceId,
          isFavorite: !current[index].isFavorite,
        );

    // Replace the single updated item in the list.
    final newList = [...current];
    newList[index] = updated;
    state = AsyncData(newList);
  }

  /// Update notes / acquired date for an existing item.
  /// Named [updateItem] to avoid shadowing [AsyncNotifierBase.update].
  Future<void> updateItem({
    required int fragranceId,
    String? personalNotes,
    DateTime? acquiredDate,
  }) async {
    final updated = await ref.read(collectionRepositoryProvider).updateItem(
          fragranceId: fragranceId,
          personalNotes: personalNotes,
          acquiredDate: acquiredDate,
        );

    state = state.whenData((items) => [
          for (final item in items)
            if (item.fragranceId == fragranceId) updated else item,
        ]);
  }

  /// Remove [fragranceId] from the wardrobe.
  Future<void> remove(int fragranceId) async {
    await ref
        .read(collectionRepositoryProvider)
        .removeFromCollection(fragranceId);

    state = state.whenData(
      (items) => items.where((i) => i.fragranceId != fragranceId).toList(),
    );
  }

  // Convenience getters

  /// True if [fragranceId] is already in the collection.
  bool contains(int fragranceId) =>
      state.valueOrNull?.any((i) => i.fragranceId == fragranceId) ?? false;

  /// Favourite items, sorted alphabetically.
  /// Creates a new list rather than mutating the state list in-place.
  /// Using `..retainWhere` on `state.valueOrNull` directly would corrupt
  /// Riverpod's immutable state.
  List<CollectionItem> get favourites {
    final items = state.valueOrNull ?? [];
    return items
        .where((i) => i.isFavorite)
        .toList()
      ..sort((a, b) => a.fragrance.name.compareTo(b.fragrance.name));
  }
}

// Provider

final collectionNotifierProvider =
    AsyncNotifierProvider.autoDispose<CollectionNotifier, List<CollectionItem>>(
  CollectionNotifier.new,
);
