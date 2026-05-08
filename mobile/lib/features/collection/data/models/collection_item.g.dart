// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'collection_item.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$CollectionItemImpl _$$CollectionItemImplFromJson(Map<String, dynamic> json) =>
    _$CollectionItemImpl(
      fragranceId: (json['fragrance_id'] as num).toInt(),
      fragrance: CollectionFragrance.fromJson(
          json['fragrance'] as Map<String, dynamic>),
      isFavorite: json['is_favorite'] as bool,
      personalNotes: json['personal_notes'] as String?,
      acquiredDate: json['acquired_date'] == null
          ? null
          : DateTime.parse(json['acquired_date'] as String),
      createdAt: DateTime.parse(json['created_at'] as String),
    );

Map<String, dynamic> _$$CollectionItemImplToJson(
        _$CollectionItemImpl instance) =>
    <String, dynamic>{
      'fragrance_id': instance.fragranceId,
      'fragrance': instance.fragrance,
      'is_favorite': instance.isFavorite,
      'personal_notes': instance.personalNotes,
      'acquired_date': instance.acquiredDate?.toIso8601String(),
      'created_at': instance.createdAt.toIso8601String(),
    };

_$CollectionFragranceImpl _$$CollectionFragranceImplFromJson(
        Map<String, dynamic> json) =>
    _$CollectionFragranceImpl(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String,
      brand: json['brand'] as String,
      cachedImagePath: json['cached_image_path'] as String?,
      remoteImageUrl: json['remote_image_url'] as String?,
    );

Map<String, dynamic> _$$CollectionFragranceImplToJson(
        _$CollectionFragranceImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'brand': instance.brand,
      'cached_image_path': instance.cachedImagePath,
      'remote_image_url': instance.remoteImageUrl,
    };
