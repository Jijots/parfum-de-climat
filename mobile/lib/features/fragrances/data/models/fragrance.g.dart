// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'fragrance.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$FragrancePerformanceImpl _$$FragrancePerformanceImplFromJson(
        Map<String, dynamic> json) =>
    _$FragrancePerformanceImpl(
      sillage: (json['sillage'] as num?)?.toDouble(),
      longevity: (json['longevity'] as num?)?.toDouble(),
      rating: (json['rating'] as num?)?.toDouble(),
      votes: (json['votes'] as num?)?.toInt(),
    );

Map<String, dynamic> _$$FragrancePerformanceImplToJson(
        _$FragrancePerformanceImpl instance) =>
    <String, dynamic>{
      'sillage': instance.sillage,
      'longevity': instance.longevity,
      'rating': instance.rating,
      'votes': instance.votes,
    };

_$FragranceNotesImpl _$$FragranceNotesImplFromJson(Map<String, dynamic> json) =>
    _$FragranceNotesImpl(
      top: (json['top'] as List<dynamic>?)?.map((e) => e as String).toList() ??
          const [],
      heart:
          (json['heart'] as List<dynamic>?)?.map((e) => e as String).toList() ??
              const [],
      base:
          (json['base'] as List<dynamic>?)?.map((e) => e as String).toList() ??
              const [],
    );

Map<String, dynamic> _$$FragranceNotesImplToJson(
        _$FragranceNotesImpl instance) =>
    <String, dynamic>{
      'top': instance.top,
      'heart': instance.heart,
      'base': instance.base,
    };

_$FragranceAccordImpl _$$FragranceAccordImplFromJson(
        Map<String, dynamic> json) =>
    _$FragranceAccordImpl(
      accord: json['accord'] as String,
      strength: (json['strength'] as num).toDouble(),
    );

Map<String, dynamic> _$$FragranceAccordImplToJson(
        _$FragranceAccordImpl instance) =>
    <String, dynamic>{
      'accord': instance.accord,
      'strength': instance.strength,
    };

_$FragranceSummaryImpl _$$FragranceSummaryImplFromJson(
        Map<String, dynamic> json) =>
    _$FragranceSummaryImpl(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String,
      brand: json['brand'] as String,
      olfactiveFamily: json['olfactive_family'] as String?,
      genderTarget: json['gender_target'] as String?,
      releaseYear: (json['release_year'] as num?)?.toInt(),
      concentration: json['concentration'] as String?,
      imageUrl: json['image_url'] as String?,
      performance: json['performance'] == null
          ? null
          : FragrancePerformance.fromJson(
              json['performance'] as Map<String, dynamic>),
      hasClimateProfile: json['has_climate_profile'] as bool? ?? false,
      isActive: json['is_active'] as bool? ?? true,
    );

Map<String, dynamic> _$$FragranceSummaryImplToJson(
        _$FragranceSummaryImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'brand': instance.brand,
      'olfactive_family': instance.olfactiveFamily,
      'gender_target': instance.genderTarget,
      'release_year': instance.releaseYear,
      'concentration': instance.concentration,
      'image_url': instance.imageUrl,
      'performance': instance.performance,
      'has_climate_profile': instance.hasClimateProfile,
      'is_active': instance.isActive,
    };

_$FragranceImpl _$$FragranceImplFromJson(Map<String, dynamic> json) =>
    _$FragranceImpl(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String,
      brand: json['brand'] as String,
      description: json['description'] as String?,
      olfactiveFamily: json['olfactive_family'] as String?,
      genderTarget: json['gender_target'] as String?,
      releaseYear: (json['release_year'] as num?)?.toInt(),
      concentration: json['concentration'] as String?,
      imageUrl: json['image_url'] as String?,
      performance: json['performance'] == null
          ? null
          : FragrancePerformance.fromJson(
              json['performance'] as Map<String, dynamic>),
      notes: json['notes'] == null
          ? null
          : FragranceNotes.fromJson(json['notes'] as Map<String, dynamic>),
      accords: (json['accords'] as List<dynamic>?)
              ?.map((e) => FragranceAccord.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const [],
      hasClimateProfile: json['has_climate_profile'] as bool? ?? false,
      isActive: json['is_active'] as bool? ?? true,
    );

Map<String, dynamic> _$$FragranceImplToJson(_$FragranceImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'brand': instance.brand,
      'description': instance.description,
      'olfactive_family': instance.olfactiveFamily,
      'gender_target': instance.genderTarget,
      'release_year': instance.releaseYear,
      'concentration': instance.concentration,
      'image_url': instance.imageUrl,
      'performance': instance.performance,
      'notes': instance.notes,
      'accords': instance.accords,
      'has_climate_profile': instance.hasClimateProfile,
      'is_active': instance.isActive,
    };

_$PaginatedFragrancesImpl _$$PaginatedFragrancesImplFromJson(
        Map<String, dynamic> json) =>
    _$PaginatedFragrancesImpl(
      data: (json['data'] as List<dynamic>)
          .map((e) => FragranceSummary.fromJson(e as Map<String, dynamic>))
          .toList(),
      currentPage: (json['current_page'] as num).toInt(),
      lastPage: (json['last_page'] as num).toInt(),
      total: (json['total'] as num).toInt(),
    );

Map<String, dynamic> _$$PaginatedFragrancesImplToJson(
        _$PaginatedFragrancesImpl instance) =>
    <String, dynamic>{
      'data': instance.data,
      'current_page': instance.currentPage,
      'last_page': instance.lastPage,
      'total': instance.total,
    };
