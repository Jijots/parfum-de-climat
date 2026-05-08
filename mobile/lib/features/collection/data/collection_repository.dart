/// CRUD operations for the user's personal fragrance wardrobe.
/// All methods map to routes under /api/v1/collection:
///   GET    /collection               → [fetchCollection]
///   POST   /collection               → [addToCollection]
///   PUT    /collection/{fragranceId} → [updateItem]
///   DELETE /collection/{fragranceId} → [removeFromCollection]

import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import 'models/collection_item.dart';

class CollectionRepository {
  const CollectionRepository({required ApiClient apiClient})
      : _api = apiClient;

  final ApiClient _api;

  /// Fetch the full wardrobe for the authenticated user.
  Future<List<CollectionItem>> fetchCollection() async {
    final response =
        await _api.get<Map<String, dynamic>>(ApiEndpoints.collection);
    final items = response.data!['data'] as List<dynamic>;
    return items
        .map((e) => CollectionItem.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  /// Add a fragrance to the user's collection.
  /// [fragranceId] must exist in the fragrances table.
  /// Returns the created [CollectionItem].
  /// Throws [ValidationException] if the fragrance is already in the collection.
  Future<CollectionItem> addToCollection({
    required int fragranceId,
    bool isFavorite = false,
    String? personalNotes,
    DateTime? acquiredDate,
  }) async {
    final response = await _api.post<Map<String, dynamic>>(
      ApiEndpoints.collection,
      data: {
        'fragrance_id': fragranceId,
        'is_favorite': isFavorite,
        if (personalNotes != null) 'personal_notes': personalNotes,
        if (acquiredDate != null)
          'acquired_date': acquiredDate.toIso8601String().split('T').first,
      },
    );
    return CollectionItem.fromJson(
        response.data!['item'] as Map<String, dynamic>);
  }

  /// Toggle favourite, update notes or acquired date for an existing item.
  /// Only fields provided (non-null) are sent in the PATCH body.
  Future<CollectionItem> updateItem({
    required int fragranceId,
    bool? isFavorite,
    String? personalNotes,
    DateTime? acquiredDate,
  }) async {
    final response = await _api.patch<Map<String, dynamic>>(
      ApiEndpoints.collectionItem(fragranceId),
      data: {
        if (isFavorite != null) 'is_favorite': isFavorite,
        if (personalNotes != null) 'personal_notes': personalNotes,
        if (acquiredDate != null)
          'acquired_date': acquiredDate.toIso8601String().split('T').first,
      },
    );
    return CollectionItem.fromJson(
        response.data!['item'] as Map<String, dynamic>);
  }

  /// Remove a fragrance from the user's collection.
  Future<void> removeFromCollection(int fragranceId) async {
    await _api.delete<void>(ApiEndpoints.collectionItem(fragranceId));
  }
}
