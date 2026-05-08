// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'recommendation_result.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$WeatherContextImpl _$$WeatherContextImplFromJson(Map<String, dynamic> json) =>
    _$WeatherContextImpl(
      temperatureCelsius: (json['temperature_celsius'] as num).toDouble(),
      humidityPercent: (json['humidity_percent'] as num).toDouble(),
      condition: json['condition'] as String,
      season: json['season'] as String,
      location: json['location'] as String,
    );

Map<String, dynamic> _$$WeatherContextImplToJson(
        _$WeatherContextImpl instance) =>
    <String, dynamic>{
      'temperature_celsius': instance.temperatureCelsius,
      'humidity_percent': instance.humidityPercent,
      'condition': instance.condition,
      'season': instance.season,
      'location': instance.location,
    };

_$RecommendationResultImpl _$$RecommendationResultImplFromJson(
        Map<String, dynamic> json) =>
    _$RecommendationResultImpl(
      logId: (json['log_id'] as num).toInt(),
      weather: WeatherContext.fromJson(json['weather'] as Map<String, dynamic>),
      recommendations: (json['recommendations'] as List<dynamic>)
          .map((e) => ScoredFragrance.fromJson(e as Map<String, dynamic>))
          .toList(),
    );

Map<String, dynamic> _$$RecommendationResultImplToJson(
        _$RecommendationResultImpl instance) =>
    <String, dynamic>{
      'log_id': instance.logId,
      'weather': instance.weather,
      'recommendations': instance.recommendations,
    };

_$HistoryEntryImpl _$$HistoryEntryImplFromJson(Map<String, dynamic> json) =>
    _$HistoryEntryImpl(
      id: (json['id'] as num).toInt(),
      weather: WeatherContext.fromJson(json['weather'] as Map<String, dynamic>),
      engineResponse: (json['engine_response_payload'] as List<dynamic>)
          .map((e) => ScoredFragrance.fromJson(e as Map<String, dynamic>))
          .toList(),
      chosenFragrance: json['chosen_fragrance'] == null
          ? null
          : ChosenFragrance.fromJson(
              json['chosen_fragrance'] as Map<String, dynamic>),
      createdAt: DateTime.parse(json['created_at'] as String),
    );

Map<String, dynamic> _$$HistoryEntryImplToJson(_$HistoryEntryImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'weather': instance.weather,
      'engine_response_payload': instance.engineResponse,
      'chosen_fragrance': instance.chosenFragrance,
      'created_at': instance.createdAt.toIso8601String(),
    };

_$ChosenFragranceImpl _$$ChosenFragranceImplFromJson(
        Map<String, dynamic> json) =>
    _$ChosenFragranceImpl(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String,
      brand: json['brand'] as String,
    );

Map<String, dynamic> _$$ChosenFragranceImplToJson(
        _$ChosenFragranceImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'brand': instance.brand,
    };
