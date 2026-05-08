/// An entry in the user's personal fragrance wardrobe.
/// Returned by GET /api/v1/collection and POST /api/v1/collection.
/// Each item has a nested [CollectionFragrance] with just enough data to
/// render the wardrobe list (name, brand, image) without loading the full
/// fragrance detail on every row.

import 'package:freezed_annotation/freezed_annotation.dart';

part 'collection_item.freezed.dart';
part 'collection_item.g.dart';

@freezed
class CollectionItem with _$CollectionItem {
  const factory CollectionItem({
    @JsonKey(name: 'fragrance_id') required int fragranceId,
    required CollectionFragrance fragrance,

    @JsonKey(name: 'is_favorite') required bool isFavorite,

    /// Optional personal note (e.g. "Gift from mum, summer 2024").
    @JsonKey(name: 'personal_notes') String? personalNotes,

    /// Date the bottle was acquired. Null if not recorded.
    @JsonKey(name: 'acquired_date') DateTime? acquiredDate,

    @JsonKey(name: 'created_at') required DateTime createdAt,
  }) = _CollectionItem;

  factory CollectionItem.fromJson(Map<String, dynamic> json) =>
      _$CollectionItemFromJson(json);
}

/// Fragrance sub-object embedded in [CollectionItem].
/// Contains only the fields needed to render the wardrobe list row.
@freezed
class CollectionFragrance with _$CollectionFragrance {
  const factory CollectionFragrance({
    required int id,
    required String name,
    required String brand,

    /// Relative path on Laravel Storage. Prefix with the server origin to form
    /// a full URL: '${ApiEndpoints.storageBaseUrl}/${item.cachedImagePath}'
    @JsonKey(name: 'cached_image_path') String? cachedImagePath,

    /// Original CDN URL as fallback if [cachedImagePath] is null.
    @JsonKey(name: 'remote_image_url') String? remoteImageUrl,
  }) = _CollectionFragrance;

  factory CollectionFragrance.fromJson(Map<String, dynamic> json) =>
      _$CollectionFragranceFromJson(json);
}
