// coverage:ignore-file
// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'recommendation_result.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

T _$identity<T>(T value) => value;

final _privateConstructorUsedError = UnsupportedError(
    'It seems like you constructed your class using `MyClass._()`. This constructor is only meant to be used by freezed and you are not supposed to need it nor use it.\nPlease check the documentation here for more information: https://github.com/rrousselGit/freezed#adding-getters-and-methods-to-our-models');

WeatherContext _$WeatherContextFromJson(Map<String, dynamic> json) {
  return _WeatherContext.fromJson(json);
}

/// @nodoc
mixin _$WeatherContext {
  @JsonKey(name: 'temperature_celsius')
  double get temperatureCelsius => throw _privateConstructorUsedError;
  @JsonKey(name: 'humidity_percent')
  double get humidityPercent => throw _privateConstructorUsedError;
  String get condition => throw _privateConstructorUsedError;
  String get season => throw _privateConstructorUsedError;
  String get location => throw _privateConstructorUsedError;

  /// Serializes this WeatherContext to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of WeatherContext
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $WeatherContextCopyWith<WeatherContext> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $WeatherContextCopyWith<$Res> {
  factory $WeatherContextCopyWith(
          WeatherContext value, $Res Function(WeatherContext) then) =
      _$WeatherContextCopyWithImpl<$Res, WeatherContext>;
  @useResult
  $Res call(
      {@JsonKey(name: 'temperature_celsius') double temperatureCelsius,
      @JsonKey(name: 'humidity_percent') double humidityPercent,
      String condition,
      String season,
      String location});
}

/// @nodoc
class _$WeatherContextCopyWithImpl<$Res, $Val extends WeatherContext>
    implements $WeatherContextCopyWith<$Res> {
  _$WeatherContextCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of WeatherContext
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? temperatureCelsius = null,
    Object? humidityPercent = null,
    Object? condition = null,
    Object? season = null,
    Object? location = null,
  }) {
    return _then(_value.copyWith(
      temperatureCelsius: null == temperatureCelsius
          ? _value.temperatureCelsius
          : temperatureCelsius // ignore: cast_nullable_to_non_nullable
              as double,
      humidityPercent: null == humidityPercent
          ? _value.humidityPercent
          : humidityPercent // ignore: cast_nullable_to_non_nullable
              as double,
      condition: null == condition
          ? _value.condition
          : condition // ignore: cast_nullable_to_non_nullable
              as String,
      season: null == season
          ? _value.season
          : season // ignore: cast_nullable_to_non_nullable
              as String,
      location: null == location
          ? _value.location
          : location // ignore: cast_nullable_to_non_nullable
              as String,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$WeatherContextImplCopyWith<$Res>
    implements $WeatherContextCopyWith<$Res> {
  factory _$$WeatherContextImplCopyWith(_$WeatherContextImpl value,
          $Res Function(_$WeatherContextImpl) then) =
      __$$WeatherContextImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {@JsonKey(name: 'temperature_celsius') double temperatureCelsius,
      @JsonKey(name: 'humidity_percent') double humidityPercent,
      String condition,
      String season,
      String location});
}

/// @nodoc
class __$$WeatherContextImplCopyWithImpl<$Res>
    extends _$WeatherContextCopyWithImpl<$Res, _$WeatherContextImpl>
    implements _$$WeatherContextImplCopyWith<$Res> {
  __$$WeatherContextImplCopyWithImpl(
      _$WeatherContextImpl _value, $Res Function(_$WeatherContextImpl) _then)
      : super(_value, _then);

  /// Create a copy of WeatherContext
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? temperatureCelsius = null,
    Object? humidityPercent = null,
    Object? condition = null,
    Object? season = null,
    Object? location = null,
  }) {
    return _then(_$WeatherContextImpl(
      temperatureCelsius: null == temperatureCelsius
          ? _value.temperatureCelsius
          : temperatureCelsius // ignore: cast_nullable_to_non_nullable
              as double,
      humidityPercent: null == humidityPercent
          ? _value.humidityPercent
          : humidityPercent // ignore: cast_nullable_to_non_nullable
              as double,
      condition: null == condition
          ? _value.condition
          : condition // ignore: cast_nullable_to_non_nullable
              as String,
      season: null == season
          ? _value.season
          : season // ignore: cast_nullable_to_non_nullable
              as String,
      location: null == location
          ? _value.location
          : location // ignore: cast_nullable_to_non_nullable
              as String,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$WeatherContextImpl implements _WeatherContext {
  const _$WeatherContextImpl(
      {@JsonKey(name: 'temperature_celsius') required this.temperatureCelsius,
      @JsonKey(name: 'humidity_percent') required this.humidityPercent,
      required this.condition,
      required this.season,
      required this.location});

  factory _$WeatherContextImpl.fromJson(Map<String, dynamic> json) =>
      _$$WeatherContextImplFromJson(json);

  @override
  @JsonKey(name: 'temperature_celsius')
  final double temperatureCelsius;
  @override
  @JsonKey(name: 'humidity_percent')
  final double humidityPercent;
  @override
  final String condition;
  @override
  final String season;
  @override
  final String location;

  @override
  String toString() {
    return 'WeatherContext(temperatureCelsius: $temperatureCelsius, humidityPercent: $humidityPercent, condition: $condition, season: $season, location: $location)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$WeatherContextImpl &&
            (identical(other.temperatureCelsius, temperatureCelsius) ||
                other.temperatureCelsius == temperatureCelsius) &&
            (identical(other.humidityPercent, humidityPercent) ||
                other.humidityPercent == humidityPercent) &&
            (identical(other.condition, condition) ||
                other.condition == condition) &&
            (identical(other.season, season) || other.season == season) &&
            (identical(other.location, location) ||
                other.location == location));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(runtimeType, temperatureCelsius,
      humidityPercent, condition, season, location);

  /// Create a copy of WeatherContext
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$WeatherContextImplCopyWith<_$WeatherContextImpl> get copyWith =>
      __$$WeatherContextImplCopyWithImpl<_$WeatherContextImpl>(
          this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$WeatherContextImplToJson(
      this,
    );
  }
}

abstract class _WeatherContext implements WeatherContext {
  const factory _WeatherContext(
      {@JsonKey(name: 'temperature_celsius')
      required final double temperatureCelsius,
      @JsonKey(name: 'humidity_percent') required final double humidityPercent,
      required final String condition,
      required final String season,
      required final String location}) = _$WeatherContextImpl;

  factory _WeatherContext.fromJson(Map<String, dynamic> json) =
      _$WeatherContextImpl.fromJson;

  @override
  @JsonKey(name: 'temperature_celsius')
  double get temperatureCelsius;
  @override
  @JsonKey(name: 'humidity_percent')
  double get humidityPercent;
  @override
  String get condition;
  @override
  String get season;
  @override
  String get location;

  /// Create a copy of WeatherContext
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$WeatherContextImplCopyWith<_$WeatherContextImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

RecommendationResult _$RecommendationResultFromJson(Map<String, dynamic> json) {
  return _RecommendationResult.fromJson(json);
}

/// @nodoc
mixin _$RecommendationResult {
  /// The WeatherLog primary key — passed to PATCH /recommend/{logId}/choose.
  @JsonKey(name: 'log_id')
  int get logId => throw _privateConstructorUsedError;
  WeatherContext get weather => throw _privateConstructorUsedError;

  /// Top 3 fragrances from the Python engine, sorted by score descending.
  List<ScoredFragrance> get recommendations =>
      throw _privateConstructorUsedError;

  /// Serializes this RecommendationResult to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of RecommendationResult
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $RecommendationResultCopyWith<RecommendationResult> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $RecommendationResultCopyWith<$Res> {
  factory $RecommendationResultCopyWith(RecommendationResult value,
          $Res Function(RecommendationResult) then) =
      _$RecommendationResultCopyWithImpl<$Res, RecommendationResult>;
  @useResult
  $Res call(
      {@JsonKey(name: 'log_id') int logId,
      WeatherContext weather,
      List<ScoredFragrance> recommendations});

  $WeatherContextCopyWith<$Res> get weather;
}

/// @nodoc
class _$RecommendationResultCopyWithImpl<$Res,
        $Val extends RecommendationResult>
    implements $RecommendationResultCopyWith<$Res> {
  _$RecommendationResultCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of RecommendationResult
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? logId = null,
    Object? weather = null,
    Object? recommendations = null,
  }) {
    return _then(_value.copyWith(
      logId: null == logId
          ? _value.logId
          : logId // ignore: cast_nullable_to_non_nullable
              as int,
      weather: null == weather
          ? _value.weather
          : weather // ignore: cast_nullable_to_non_nullable
              as WeatherContext,
      recommendations: null == recommendations
          ? _value.recommendations
          : recommendations // ignore: cast_nullable_to_non_nullable
              as List<ScoredFragrance>,
    ) as $Val);
  }

  /// Create a copy of RecommendationResult
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $WeatherContextCopyWith<$Res> get weather {
    return $WeatherContextCopyWith<$Res>(_value.weather, (value) {
      return _then(_value.copyWith(weather: value) as $Val);
    });
  }
}

/// @nodoc
abstract class _$$RecommendationResultImplCopyWith<$Res>
    implements $RecommendationResultCopyWith<$Res> {
  factory _$$RecommendationResultImplCopyWith(_$RecommendationResultImpl value,
          $Res Function(_$RecommendationResultImpl) then) =
      __$$RecommendationResultImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {@JsonKey(name: 'log_id') int logId,
      WeatherContext weather,
      List<ScoredFragrance> recommendations});

  @override
  $WeatherContextCopyWith<$Res> get weather;
}

/// @nodoc
class __$$RecommendationResultImplCopyWithImpl<$Res>
    extends _$RecommendationResultCopyWithImpl<$Res, _$RecommendationResultImpl>
    implements _$$RecommendationResultImplCopyWith<$Res> {
  __$$RecommendationResultImplCopyWithImpl(_$RecommendationResultImpl _value,
      $Res Function(_$RecommendationResultImpl) _then)
      : super(_value, _then);

  /// Create a copy of RecommendationResult
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? logId = null,
    Object? weather = null,
    Object? recommendations = null,
  }) {
    return _then(_$RecommendationResultImpl(
      logId: null == logId
          ? _value.logId
          : logId // ignore: cast_nullable_to_non_nullable
              as int,
      weather: null == weather
          ? _value.weather
          : weather // ignore: cast_nullable_to_non_nullable
              as WeatherContext,
      recommendations: null == recommendations
          ? _value._recommendations
          : recommendations // ignore: cast_nullable_to_non_nullable
              as List<ScoredFragrance>,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$RecommendationResultImpl implements _RecommendationResult {
  const _$RecommendationResultImpl(
      {@JsonKey(name: 'log_id') required this.logId,
      required this.weather,
      required final List<ScoredFragrance> recommendations})
      : _recommendations = recommendations;

  factory _$RecommendationResultImpl.fromJson(Map<String, dynamic> json) =>
      _$$RecommendationResultImplFromJson(json);

  /// The WeatherLog primary key — passed to PATCH /recommend/{logId}/choose.
  @override
  @JsonKey(name: 'log_id')
  final int logId;
  @override
  final WeatherContext weather;

  /// Top 3 fragrances from the Python engine, sorted by score descending.
  final List<ScoredFragrance> _recommendations;

  /// Top 3 fragrances from the Python engine, sorted by score descending.
  @override
  List<ScoredFragrance> get recommendations {
    if (_recommendations is EqualUnmodifiableListView) return _recommendations;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_recommendations);
  }

  @override
  String toString() {
    return 'RecommendationResult(logId: $logId, weather: $weather, recommendations: $recommendations)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$RecommendationResultImpl &&
            (identical(other.logId, logId) || other.logId == logId) &&
            (identical(other.weather, weather) || other.weather == weather) &&
            const DeepCollectionEquality()
                .equals(other._recommendations, _recommendations));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(runtimeType, logId, weather,
      const DeepCollectionEquality().hash(_recommendations));

  /// Create a copy of RecommendationResult
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$RecommendationResultImplCopyWith<_$RecommendationResultImpl>
      get copyWith =>
          __$$RecommendationResultImplCopyWithImpl<_$RecommendationResultImpl>(
              this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$RecommendationResultImplToJson(
      this,
    );
  }
}

abstract class _RecommendationResult implements RecommendationResult {
  const factory _RecommendationResult(
          {@JsonKey(name: 'log_id') required final int logId,
          required final WeatherContext weather,
          required final List<ScoredFragrance> recommendations}) =
      _$RecommendationResultImpl;

  factory _RecommendationResult.fromJson(Map<String, dynamic> json) =
      _$RecommendationResultImpl.fromJson;

  /// The WeatherLog primary key — passed to PATCH /recommend/{logId}/choose.
  @override
  @JsonKey(name: 'log_id')
  int get logId;
  @override
  WeatherContext get weather;

  /// Top 3 fragrances from the Python engine, sorted by score descending.
  @override
  List<ScoredFragrance> get recommendations;

  /// Create a copy of RecommendationResult
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$RecommendationResultImplCopyWith<_$RecommendationResultImpl>
      get copyWith => throw _privateConstructorUsedError;
}

HistoryEntry _$HistoryEntryFromJson(Map<String, dynamic> json) {
  return _HistoryEntry.fromJson(json);
}

/// @nodoc
mixin _$HistoryEntry {
  int get id => throw _privateConstructorUsedError;
  WeatherContext get weather => throw _privateConstructorUsedError;
  @JsonKey(name: 'engine_response_payload')
  List<ScoredFragrance> get engineResponse =>
      throw _privateConstructorUsedError;
  @JsonKey(name: 'chosen_fragrance')
  ChosenFragrance? get chosenFragrance => throw _privateConstructorUsedError;
  @JsonKey(name: 'created_at')
  DateTime get createdAt => throw _privateConstructorUsedError;

  /// Serializes this HistoryEntry to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of HistoryEntry
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $HistoryEntryCopyWith<HistoryEntry> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $HistoryEntryCopyWith<$Res> {
  factory $HistoryEntryCopyWith(
          HistoryEntry value, $Res Function(HistoryEntry) then) =
      _$HistoryEntryCopyWithImpl<$Res, HistoryEntry>;
  @useResult
  $Res call(
      {int id,
      WeatherContext weather,
      @JsonKey(name: 'engine_response_payload')
      List<ScoredFragrance> engineResponse,
      @JsonKey(name: 'chosen_fragrance') ChosenFragrance? chosenFragrance,
      @JsonKey(name: 'created_at') DateTime createdAt});

  $WeatherContextCopyWith<$Res> get weather;
  $ChosenFragranceCopyWith<$Res>? get chosenFragrance;
}

/// @nodoc
class _$HistoryEntryCopyWithImpl<$Res, $Val extends HistoryEntry>
    implements $HistoryEntryCopyWith<$Res> {
  _$HistoryEntryCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of HistoryEntry
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? weather = null,
    Object? engineResponse = null,
    Object? chosenFragrance = freezed,
    Object? createdAt = null,
  }) {
    return _then(_value.copyWith(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as int,
      weather: null == weather
          ? _value.weather
          : weather // ignore: cast_nullable_to_non_nullable
              as WeatherContext,
      engineResponse: null == engineResponse
          ? _value.engineResponse
          : engineResponse // ignore: cast_nullable_to_non_nullable
              as List<ScoredFragrance>,
      chosenFragrance: freezed == chosenFragrance
          ? _value.chosenFragrance
          : chosenFragrance // ignore: cast_nullable_to_non_nullable
              as ChosenFragrance?,
      createdAt: null == createdAt
          ? _value.createdAt
          : createdAt // ignore: cast_nullable_to_non_nullable
              as DateTime,
    ) as $Val);
  }

  /// Create a copy of HistoryEntry
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $WeatherContextCopyWith<$Res> get weather {
    return $WeatherContextCopyWith<$Res>(_value.weather, (value) {
      return _then(_value.copyWith(weather: value) as $Val);
    });
  }

  /// Create a copy of HistoryEntry
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $ChosenFragranceCopyWith<$Res>? get chosenFragrance {
    if (_value.chosenFragrance == null) {
      return null;
    }

    return $ChosenFragranceCopyWith<$Res>(_value.chosenFragrance!, (value) {
      return _then(_value.copyWith(chosenFragrance: value) as $Val);
    });
  }
}

/// @nodoc
abstract class _$$HistoryEntryImplCopyWith<$Res>
    implements $HistoryEntryCopyWith<$Res> {
  factory _$$HistoryEntryImplCopyWith(
          _$HistoryEntryImpl value, $Res Function(_$HistoryEntryImpl) then) =
      __$$HistoryEntryImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {int id,
      WeatherContext weather,
      @JsonKey(name: 'engine_response_payload')
      List<ScoredFragrance> engineResponse,
      @JsonKey(name: 'chosen_fragrance') ChosenFragrance? chosenFragrance,
      @JsonKey(name: 'created_at') DateTime createdAt});

  @override
  $WeatherContextCopyWith<$Res> get weather;
  @override
  $ChosenFragranceCopyWith<$Res>? get chosenFragrance;
}

/// @nodoc
class __$$HistoryEntryImplCopyWithImpl<$Res>
    extends _$HistoryEntryCopyWithImpl<$Res, _$HistoryEntryImpl>
    implements _$$HistoryEntryImplCopyWith<$Res> {
  __$$HistoryEntryImplCopyWithImpl(
      _$HistoryEntryImpl _value, $Res Function(_$HistoryEntryImpl) _then)
      : super(_value, _then);

  /// Create a copy of HistoryEntry
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? weather = null,
    Object? engineResponse = null,
    Object? chosenFragrance = freezed,
    Object? createdAt = null,
  }) {
    return _then(_$HistoryEntryImpl(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as int,
      weather: null == weather
          ? _value.weather
          : weather // ignore: cast_nullable_to_non_nullable
              as WeatherContext,
      engineResponse: null == engineResponse
          ? _value._engineResponse
          : engineResponse // ignore: cast_nullable_to_non_nullable
              as List<ScoredFragrance>,
      chosenFragrance: freezed == chosenFragrance
          ? _value.chosenFragrance
          : chosenFragrance // ignore: cast_nullable_to_non_nullable
              as ChosenFragrance?,
      createdAt: null == createdAt
          ? _value.createdAt
          : createdAt // ignore: cast_nullable_to_non_nullable
              as DateTime,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$HistoryEntryImpl implements _HistoryEntry {
  const _$HistoryEntryImpl(
      {required this.id,
      required this.weather,
      @JsonKey(name: 'engine_response_payload')
      required final List<ScoredFragrance> engineResponse,
      @JsonKey(name: 'chosen_fragrance') this.chosenFragrance,
      @JsonKey(name: 'created_at') required this.createdAt})
      : _engineResponse = engineResponse;

  factory _$HistoryEntryImpl.fromJson(Map<String, dynamic> json) =>
      _$$HistoryEntryImplFromJson(json);

  @override
  final int id;
  @override
  final WeatherContext weather;
  final List<ScoredFragrance> _engineResponse;
  @override
  @JsonKey(name: 'engine_response_payload')
  List<ScoredFragrance> get engineResponse {
    if (_engineResponse is EqualUnmodifiableListView) return _engineResponse;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_engineResponse);
  }

  @override
  @JsonKey(name: 'chosen_fragrance')
  final ChosenFragrance? chosenFragrance;
  @override
  @JsonKey(name: 'created_at')
  final DateTime createdAt;

  @override
  String toString() {
    return 'HistoryEntry(id: $id, weather: $weather, engineResponse: $engineResponse, chosenFragrance: $chosenFragrance, createdAt: $createdAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$HistoryEntryImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.weather, weather) || other.weather == weather) &&
            const DeepCollectionEquality()
                .equals(other._engineResponse, _engineResponse) &&
            (identical(other.chosenFragrance, chosenFragrance) ||
                other.chosenFragrance == chosenFragrance) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType,
      id,
      weather,
      const DeepCollectionEquality().hash(_engineResponse),
      chosenFragrance,
      createdAt);

  /// Create a copy of HistoryEntry
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$HistoryEntryImplCopyWith<_$HistoryEntryImpl> get copyWith =>
      __$$HistoryEntryImplCopyWithImpl<_$HistoryEntryImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$HistoryEntryImplToJson(
      this,
    );
  }
}

abstract class _HistoryEntry implements HistoryEntry {
  const factory _HistoryEntry(
      {required final int id,
      required final WeatherContext weather,
      @JsonKey(name: 'engine_response_payload')
      required final List<ScoredFragrance> engineResponse,
      @JsonKey(name: 'chosen_fragrance') final ChosenFragrance? chosenFragrance,
      @JsonKey(name: 'created_at')
      required final DateTime createdAt}) = _$HistoryEntryImpl;

  factory _HistoryEntry.fromJson(Map<String, dynamic> json) =
      _$HistoryEntryImpl.fromJson;

  @override
  int get id;
  @override
  WeatherContext get weather;
  @override
  @JsonKey(name: 'engine_response_payload')
  List<ScoredFragrance> get engineResponse;
  @override
  @JsonKey(name: 'chosen_fragrance')
  ChosenFragrance? get chosenFragrance;
  @override
  @JsonKey(name: 'created_at')
  DateTime get createdAt;

  /// Create a copy of HistoryEntry
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$HistoryEntryImplCopyWith<_$HistoryEntryImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

ChosenFragrance _$ChosenFragranceFromJson(Map<String, dynamic> json) {
  return _ChosenFragrance.fromJson(json);
}

/// @nodoc
mixin _$ChosenFragrance {
  int get id => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  String get brand => throw _privateConstructorUsedError;

  /// Serializes this ChosenFragrance to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of ChosenFragrance
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $ChosenFragranceCopyWith<ChosenFragrance> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $ChosenFragranceCopyWith<$Res> {
  factory $ChosenFragranceCopyWith(
          ChosenFragrance value, $Res Function(ChosenFragrance) then) =
      _$ChosenFragranceCopyWithImpl<$Res, ChosenFragrance>;
  @useResult
  $Res call({int id, String name, String brand});
}

/// @nodoc
class _$ChosenFragranceCopyWithImpl<$Res, $Val extends ChosenFragrance>
    implements $ChosenFragranceCopyWith<$Res> {
  _$ChosenFragranceCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of ChosenFragrance
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? brand = null,
  }) {
    return _then(_value.copyWith(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as int,
      name: null == name
          ? _value.name
          : name // ignore: cast_nullable_to_non_nullable
              as String,
      brand: null == brand
          ? _value.brand
          : brand // ignore: cast_nullable_to_non_nullable
              as String,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$ChosenFragranceImplCopyWith<$Res>
    implements $ChosenFragranceCopyWith<$Res> {
  factory _$$ChosenFragranceImplCopyWith(_$ChosenFragranceImpl value,
          $Res Function(_$ChosenFragranceImpl) then) =
      __$$ChosenFragranceImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({int id, String name, String brand});
}

/// @nodoc
class __$$ChosenFragranceImplCopyWithImpl<$Res>
    extends _$ChosenFragranceCopyWithImpl<$Res, _$ChosenFragranceImpl>
    implements _$$ChosenFragranceImplCopyWith<$Res> {
  __$$ChosenFragranceImplCopyWithImpl(
      _$ChosenFragranceImpl _value, $Res Function(_$ChosenFragranceImpl) _then)
      : super(_value, _then);

  /// Create a copy of ChosenFragrance
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? brand = null,
  }) {
    return _then(_$ChosenFragranceImpl(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as int,
      name: null == name
          ? _value.name
          : name // ignore: cast_nullable_to_non_nullable
              as String,
      brand: null == brand
          ? _value.brand
          : brand // ignore: cast_nullable_to_non_nullable
              as String,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$ChosenFragranceImpl implements _ChosenFragrance {
  const _$ChosenFragranceImpl(
      {required this.id, required this.name, required this.brand});

  factory _$ChosenFragranceImpl.fromJson(Map<String, dynamic> json) =>
      _$$ChosenFragranceImplFromJson(json);

  @override
  final int id;
  @override
  final String name;
  @override
  final String brand;

  @override
  String toString() {
    return 'ChosenFragrance(id: $id, name: $name, brand: $brand)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$ChosenFragranceImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.brand, brand) || other.brand == brand));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(runtimeType, id, name, brand);

  /// Create a copy of ChosenFragrance
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$ChosenFragranceImplCopyWith<_$ChosenFragranceImpl> get copyWith =>
      __$$ChosenFragranceImplCopyWithImpl<_$ChosenFragranceImpl>(
          this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$ChosenFragranceImplToJson(
      this,
    );
  }
}

abstract class _ChosenFragrance implements ChosenFragrance {
  const factory _ChosenFragrance(
      {required final int id,
      required final String name,
      required final String brand}) = _$ChosenFragranceImpl;

  factory _ChosenFragrance.fromJson(Map<String, dynamic> json) =
      _$ChosenFragranceImpl.fromJson;

  @override
  int get id;
  @override
  String get name;
  @override
  String get brand;

  /// Create a copy of ChosenFragrance
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$ChosenFragranceImplCopyWith<_$ChosenFragranceImpl> get copyWith =>
      throw _privateConstructorUsedError;
}
