// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'scored_fragrance.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$ScoredFragranceImpl _$$ScoredFragranceImplFromJson(
        Map<String, dynamic> json) =>
    _$ScoredFragranceImpl(
      fragranceId: (json['fragrance_id'] as num).toInt(),
      name: json['name'] as String,
      brand: json['brand'] as String,
      score: (json['score'] as num).toDouble(),
      matchedNotes: (json['matched_notes'] as List<dynamic>)
          .map((e) => e as String)
          .toList(),
      reasoning: json['reasoning'] as String,
      scoreBreakdown: ScoreBreakdown.fromJson(
          json['score_breakdown'] as Map<String, dynamic>),
      cachedImagePath: json['cached_image_path'] as String?,
    );

Map<String, dynamic> _$$ScoredFragranceImplToJson(
        _$ScoredFragranceImpl instance) =>
    <String, dynamic>{
      'fragrance_id': instance.fragranceId,
      'name': instance.name,
      'brand': instance.brand,
      'score': instance.score,
      'matched_notes': instance.matchedNotes,
      'reasoning': instance.reasoning,
      'score_breakdown': instance.scoreBreakdown,
      'cached_image_path': instance.cachedImagePath,
    };

_$ScoreBreakdownImpl _$$ScoreBreakdownImplFromJson(Map<String, dynamic> json) =>
    _$ScoreBreakdownImpl(
      baseScore: (json['base_score'] as num).toDouble(),
      sillageMultiplier: (json['sillage_multiplier'] as num).toDouble(),
      favoriteBoost: (json['favorite_boost'] as num).toDouble(),
      finalScore: (json['final_score'] as num).toDouble(),
    );

Map<String, dynamic> _$$ScoreBreakdownImplToJson(
        _$ScoreBreakdownImpl instance) =>
    <String, dynamic>{
      'base_score': instance.baseScore,
      'sillage_multiplier': instance.sillageMultiplier,
      'favorite_boost': instance.favoriteBoost,
      'final_score': instance.finalScore,
    };
