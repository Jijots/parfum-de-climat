// coverage:ignore-file
// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'scored_fragrance.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

T _$identity<T>(T value) => value;

final _privateConstructorUsedError = UnsupportedError(
    'It seems like you constructed your class using `MyClass._()`. This constructor is only meant to be used by freezed and you are not supposed to need it nor use it.\nPlease check the documentation here for more information: https://github.com/rrousselGit/freezed#adding-getters-and-methods-to-our-models');

ScoredFragrance _$ScoredFragranceFromJson(Map<String, dynamic> json) {
  return _ScoredFragrance.fromJson(json);
}

/// @nodoc
mixin _$ScoredFragrance {
  /// The fragrance's primary key in our MySQL database.
  @JsonKey(name: 'fragrance_id')
  int get fragranceId => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  String get brand => throw _privateConstructorUsedError;

  /// Composite climate-suitability score in [0.0, 1.0].
  /// Derived from temperature (50%), season (35%), humidity (15%)
  /// with sillage penalty and favourite boost applied.
  double get score => throw _privateConstructorUsedError;

  /// Note names that contributed positively to the score.
  /// Shown in the recommendation card ("Matched notes: Bergamot, Neroli").
  @JsonKey(name: 'matched_notes')
  List<String> get matchedNotes => throw _privateConstructorUsedError;

  /// Human-readable explanation for the score (e.g.
  /// "A perfect match — Bergamot and Neroli thrive in 30°C summer heat.").
  String get reasoning => throw _privateConstructorUsedError;
  @JsonKey(name: 'score_breakdown')
  ScoreBreakdown get scoreBreakdown => throw _privateConstructorUsedError;

  /// Relative path to the cached bottle image served by Laravel Storage.
  /// May be null if the import ran with --no-images.
  @JsonKey(name: 'cached_image_path')
  String? get cachedImagePath => throw _privateConstructorUsedError;

  /// Serializes this ScoredFragrance to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of ScoredFragrance
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $ScoredFragranceCopyWith<ScoredFragrance> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $ScoredFragranceCopyWith<$Res> {
  factory $ScoredFragranceCopyWith(
          ScoredFragrance value, $Res Function(ScoredFragrance) then) =
      _$ScoredFragranceCopyWithImpl<$Res, ScoredFragrance>;
  @useResult
  $Res call(
      {@JsonKey(name: 'fragrance_id') int fragranceId,
      String name,
      String brand,
      double score,
      @JsonKey(name: 'matched_notes') List<String> matchedNotes,
      String reasoning,
      @JsonKey(name: 'score_breakdown') ScoreBreakdown scoreBreakdown,
      @JsonKey(name: 'cached_image_path') String? cachedImagePath});

  $ScoreBreakdownCopyWith<$Res> get scoreBreakdown;
}

/// @nodoc
class _$ScoredFragranceCopyWithImpl<$Res, $Val extends ScoredFragrance>
    implements $ScoredFragranceCopyWith<$Res> {
  _$ScoredFragranceCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of ScoredFragrance
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? fragranceId = null,
    Object? name = null,
    Object? brand = null,
    Object? score = null,
    Object? matchedNotes = null,
    Object? reasoning = null,
    Object? scoreBreakdown = null,
    Object? cachedImagePath = freezed,
  }) {
    return _then(_value.copyWith(
      fragranceId: null == fragranceId
          ? _value.fragranceId
          : fragranceId // ignore: cast_nullable_to_non_nullable
              as int,
      name: null == name
          ? _value.name
          : name // ignore: cast_nullable_to_non_nullable
              as String,
      brand: null == brand
          ? _value.brand
          : brand // ignore: cast_nullable_to_non_nullable
              as String,
      score: null == score
          ? _value.score
          : score // ignore: cast_nullable_to_non_nullable
              as double,
      matchedNotes: null == matchedNotes
          ? _value.matchedNotes
          : matchedNotes // ignore: cast_nullable_to_non_nullable
              as List<String>,
      reasoning: null == reasoning
          ? _value.reasoning
          : reasoning // ignore: cast_nullable_to_non_nullable
              as String,
      scoreBreakdown: null == scoreBreakdown
          ? _value.scoreBreakdown
          : scoreBreakdown // ignore: cast_nullable_to_non_nullable
              as ScoreBreakdown,
      cachedImagePath: freezed == cachedImagePath
          ? _value.cachedImagePath
          : cachedImagePath // ignore: cast_nullable_to_non_nullable
              as String?,
    ) as $Val);
  }

  /// Create a copy of ScoredFragrance
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $ScoreBreakdownCopyWith<$Res> get scoreBreakdown {
    return $ScoreBreakdownCopyWith<$Res>(_value.scoreBreakdown, (value) {
      return _then(_value.copyWith(scoreBreakdown: value) as $Val);
    });
  }
}

/// @nodoc
abstract class _$$ScoredFragranceImplCopyWith<$Res>
    implements $ScoredFragranceCopyWith<$Res> {
  factory _$$ScoredFragranceImplCopyWith(_$ScoredFragranceImpl value,
          $Res Function(_$ScoredFragranceImpl) then) =
      __$$ScoredFragranceImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {@JsonKey(name: 'fragrance_id') int fragranceId,
      String name,
      String brand,
      double score,
      @JsonKey(name: 'matched_notes') List<String> matchedNotes,
      String reasoning,
      @JsonKey(name: 'score_breakdown') ScoreBreakdown scoreBreakdown,
      @JsonKey(name: 'cached_image_path') String? cachedImagePath});

  @override
  $ScoreBreakdownCopyWith<$Res> get scoreBreakdown;
}

/// @nodoc
class __$$ScoredFragranceImplCopyWithImpl<$Res>
    extends _$ScoredFragranceCopyWithImpl<$Res, _$ScoredFragranceImpl>
    implements _$$ScoredFragranceImplCopyWith<$Res> {
  __$$ScoredFragranceImplCopyWithImpl(
      _$ScoredFragranceImpl _value, $Res Function(_$ScoredFragranceImpl) _then)
      : super(_value, _then);

  /// Create a copy of ScoredFragrance
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? fragranceId = null,
    Object? name = null,
    Object? brand = null,
    Object? score = null,
    Object? matchedNotes = null,
    Object? reasoning = null,
    Object? scoreBreakdown = null,
    Object? cachedImagePath = freezed,
  }) {
    return _then(_$ScoredFragranceImpl(
      fragranceId: null == fragranceId
          ? _value.fragranceId
          : fragranceId // ignore: cast_nullable_to_non_nullable
              as int,
      name: null == name
          ? _value.name
          : name // ignore: cast_nullable_to_non_nullable
              as String,
      brand: null == brand
          ? _value.brand
          : brand // ignore: cast_nullable_to_non_nullable
              as String,
      score: null == score
          ? _value.score
          : score // ignore: cast_nullable_to_non_nullable
              as double,
      matchedNotes: null == matchedNotes
          ? _value._matchedNotes
          : matchedNotes // ignore: cast_nullable_to_non_nullable
              as List<String>,
      reasoning: null == reasoning
          ? _value.reasoning
          : reasoning // ignore: cast_nullable_to_non_nullable
              as String,
      scoreBreakdown: null == scoreBreakdown
          ? _value.scoreBreakdown
          : scoreBreakdown // ignore: cast_nullable_to_non_nullable
              as ScoreBreakdown,
      cachedImagePath: freezed == cachedImagePath
          ? _value.cachedImagePath
          : cachedImagePath // ignore: cast_nullable_to_non_nullable
              as String?,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$ScoredFragranceImpl implements _ScoredFragrance {
  const _$ScoredFragranceImpl(
      {@JsonKey(name: 'fragrance_id') required this.fragranceId,
      required this.name,
      required this.brand,
      required this.score,
      @JsonKey(name: 'matched_notes') required final List<String> matchedNotes,
      required this.reasoning,
      @JsonKey(name: 'score_breakdown') required this.scoreBreakdown,
      @JsonKey(name: 'cached_image_path') this.cachedImagePath})
      : _matchedNotes = matchedNotes;

  factory _$ScoredFragranceImpl.fromJson(Map<String, dynamic> json) =>
      _$$ScoredFragranceImplFromJson(json);

  /// The fragrance's primary key in our MySQL database.
  @override
  @JsonKey(name: 'fragrance_id')
  final int fragranceId;
  @override
  final String name;
  @override
  final String brand;

  /// Composite climate-suitability score in [0.0, 1.0].
  /// Derived from temperature (50%), season (35%), humidity (15%)
  /// with sillage penalty and favourite boost applied.
  @override
  final double score;

  /// Note names that contributed positively to the score.
  /// Shown in the recommendation card ("Matched notes: Bergamot, Neroli").
  final List<String> _matchedNotes;

  /// Note names that contributed positively to the score.
  /// Shown in the recommendation card ("Matched notes: Bergamot, Neroli").
  @override
  @JsonKey(name: 'matched_notes')
  List<String> get matchedNotes {
    if (_matchedNotes is EqualUnmodifiableListView) return _matchedNotes;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_matchedNotes);
  }

  /// Human-readable explanation for the score (e.g.
  /// "A perfect match — Bergamot and Neroli thrive in 30°C summer heat.").
  @override
  final String reasoning;
  @override
  @JsonKey(name: 'score_breakdown')
  final ScoreBreakdown scoreBreakdown;

  /// Relative path to the cached bottle image served by Laravel Storage.
  /// May be null if the import ran with --no-images.
  @override
  @JsonKey(name: 'cached_image_path')
  final String? cachedImagePath;

  @override
  String toString() {
    return 'ScoredFragrance(fragranceId: $fragranceId, name: $name, brand: $brand, score: $score, matchedNotes: $matchedNotes, reasoning: $reasoning, scoreBreakdown: $scoreBreakdown, cachedImagePath: $cachedImagePath)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$ScoredFragranceImpl &&
            (identical(other.fragranceId, fragranceId) ||
                other.fragranceId == fragranceId) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.brand, brand) || other.brand == brand) &&
            (identical(other.score, score) || other.score == score) &&
            const DeepCollectionEquality()
                .equals(other._matchedNotes, _matchedNotes) &&
            (identical(other.reasoning, reasoning) ||
                other.reasoning == reasoning) &&
            (identical(other.scoreBreakdown, scoreBreakdown) ||
                other.scoreBreakdown == scoreBreakdown) &&
            (identical(other.cachedImagePath, cachedImagePath) ||
                other.cachedImagePath == cachedImagePath));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType,
      fragranceId,
      name,
      brand,
      score,
      const DeepCollectionEquality().hash(_matchedNotes),
      reasoning,
      scoreBreakdown,
      cachedImagePath);

  /// Create a copy of ScoredFragrance
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$ScoredFragranceImplCopyWith<_$ScoredFragranceImpl> get copyWith =>
      __$$ScoredFragranceImplCopyWithImpl<_$ScoredFragranceImpl>(
          this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$ScoredFragranceImplToJson(
      this,
    );
  }
}

abstract class _ScoredFragrance implements ScoredFragrance {
  const factory _ScoredFragrance(
      {@JsonKey(name: 'fragrance_id') required final int fragranceId,
      required final String name,
      required final String brand,
      required final double score,
      @JsonKey(name: 'matched_notes') required final List<String> matchedNotes,
      required final String reasoning,
      @JsonKey(name: 'score_breakdown')
      required final ScoreBreakdown scoreBreakdown,
      @JsonKey(name: 'cached_image_path')
      final String? cachedImagePath}) = _$ScoredFragranceImpl;

  factory _ScoredFragrance.fromJson(Map<String, dynamic> json) =
      _$ScoredFragranceImpl.fromJson;

  /// The fragrance's primary key in our MySQL database.
  @override
  @JsonKey(name: 'fragrance_id')
  int get fragranceId;
  @override
  String get name;
  @override
  String get brand;

  /// Composite climate-suitability score in [0.0, 1.0].
  /// Derived from temperature (50%), season (35%), humidity (15%)
  /// with sillage penalty and favourite boost applied.
  @override
  double get score;

  /// Note names that contributed positively to the score.
  /// Shown in the recommendation card ("Matched notes: Bergamot, Neroli").
  @override
  @JsonKey(name: 'matched_notes')
  List<String> get matchedNotes;

  /// Human-readable explanation for the score (e.g.
  /// "A perfect match — Bergamot and Neroli thrive in 30°C summer heat.").
  @override
  String get reasoning;
  @override
  @JsonKey(name: 'score_breakdown')
  ScoreBreakdown get scoreBreakdown;

  /// Relative path to the cached bottle image served by Laravel Storage.
  /// May be null if the import ran with --no-images.
  @override
  @JsonKey(name: 'cached_image_path')
  String? get cachedImagePath;

  /// Create a copy of ScoredFragrance
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$ScoredFragranceImplCopyWith<_$ScoredFragranceImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

ScoreBreakdown _$ScoreBreakdownFromJson(Map<String, dynamic> json) {
  return _ScoreBreakdown.fromJson(json);
}

/// @nodoc
mixin _$ScoreBreakdown {
  @JsonKey(name: 'base_score')
  double get baseScore => throw _privateConstructorUsedError;
  @JsonKey(name: 'sillage_multiplier')
  double get sillageMultiplier => throw _privateConstructorUsedError;
  @JsonKey(name: 'favorite_boost')
  double get favoriteBoost => throw _privateConstructorUsedError;
  @JsonKey(name: 'final_score')
  double get finalScore => throw _privateConstructorUsedError;

  /// Serializes this ScoreBreakdown to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of ScoreBreakdown
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $ScoreBreakdownCopyWith<ScoreBreakdown> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $ScoreBreakdownCopyWith<$Res> {
  factory $ScoreBreakdownCopyWith(
          ScoreBreakdown value, $Res Function(ScoreBreakdown) then) =
      _$ScoreBreakdownCopyWithImpl<$Res, ScoreBreakdown>;
  @useResult
  $Res call(
      {@JsonKey(name: 'base_score') double baseScore,
      @JsonKey(name: 'sillage_multiplier') double sillageMultiplier,
      @JsonKey(name: 'favorite_boost') double favoriteBoost,
      @JsonKey(name: 'final_score') double finalScore});
}

/// @nodoc
class _$ScoreBreakdownCopyWithImpl<$Res, $Val extends ScoreBreakdown>
    implements $ScoreBreakdownCopyWith<$Res> {
  _$ScoreBreakdownCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of ScoreBreakdown
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? baseScore = null,
    Object? sillageMultiplier = null,
    Object? favoriteBoost = null,
    Object? finalScore = null,
  }) {
    return _then(_value.copyWith(
      baseScore: null == baseScore
          ? _value.baseScore
          : baseScore // ignore: cast_nullable_to_non_nullable
              as double,
      sillageMultiplier: null == sillageMultiplier
          ? _value.sillageMultiplier
          : sillageMultiplier // ignore: cast_nullable_to_non_nullable
              as double,
      favoriteBoost: null == favoriteBoost
          ? _value.favoriteBoost
          : favoriteBoost // ignore: cast_nullable_to_non_nullable
              as double,
      finalScore: null == finalScore
          ? _value.finalScore
          : finalScore // ignore: cast_nullable_to_non_nullable
              as double,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$ScoreBreakdownImplCopyWith<$Res>
    implements $ScoreBreakdownCopyWith<$Res> {
  factory _$$ScoreBreakdownImplCopyWith(_$ScoreBreakdownImpl value,
          $Res Function(_$ScoreBreakdownImpl) then) =
      __$$ScoreBreakdownImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {@JsonKey(name: 'base_score') double baseScore,
      @JsonKey(name: 'sillage_multiplier') double sillageMultiplier,
      @JsonKey(name: 'favorite_boost') double favoriteBoost,
      @JsonKey(name: 'final_score') double finalScore});
}

/// @nodoc
class __$$ScoreBreakdownImplCopyWithImpl<$Res>
    extends _$ScoreBreakdownCopyWithImpl<$Res, _$ScoreBreakdownImpl>
    implements _$$ScoreBreakdownImplCopyWith<$Res> {
  __$$ScoreBreakdownImplCopyWithImpl(
      _$ScoreBreakdownImpl _value, $Res Function(_$ScoreBreakdownImpl) _then)
      : super(_value, _then);

  /// Create a copy of ScoreBreakdown
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? baseScore = null,
    Object? sillageMultiplier = null,
    Object? favoriteBoost = null,
    Object? finalScore = null,
  }) {
    return _then(_$ScoreBreakdownImpl(
      baseScore: null == baseScore
          ? _value.baseScore
          : baseScore // ignore: cast_nullable_to_non_nullable
              as double,
      sillageMultiplier: null == sillageMultiplier
          ? _value.sillageMultiplier
          : sillageMultiplier // ignore: cast_nullable_to_non_nullable
              as double,
      favoriteBoost: null == favoriteBoost
          ? _value.favoriteBoost
          : favoriteBoost // ignore: cast_nullable_to_non_nullable
              as double,
      finalScore: null == finalScore
          ? _value.finalScore
          : finalScore // ignore: cast_nullable_to_non_nullable
              as double,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$ScoreBreakdownImpl implements _ScoreBreakdown {
  const _$ScoreBreakdownImpl(
      {@JsonKey(name: 'base_score') required this.baseScore,
      @JsonKey(name: 'sillage_multiplier') required this.sillageMultiplier,
      @JsonKey(name: 'favorite_boost') required this.favoriteBoost,
      @JsonKey(name: 'final_score') required this.finalScore});

  factory _$ScoreBreakdownImpl.fromJson(Map<String, dynamic> json) =>
      _$$ScoreBreakdownImplFromJson(json);

  @override
  @JsonKey(name: 'base_score')
  final double baseScore;
  @override
  @JsonKey(name: 'sillage_multiplier')
  final double sillageMultiplier;
  @override
  @JsonKey(name: 'favorite_boost')
  final double favoriteBoost;
  @override
  @JsonKey(name: 'final_score')
  final double finalScore;

  @override
  String toString() {
    return 'ScoreBreakdown(baseScore: $baseScore, sillageMultiplier: $sillageMultiplier, favoriteBoost: $favoriteBoost, finalScore: $finalScore)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$ScoreBreakdownImpl &&
            (identical(other.baseScore, baseScore) ||
                other.baseScore == baseScore) &&
            (identical(other.sillageMultiplier, sillageMultiplier) ||
                other.sillageMultiplier == sillageMultiplier) &&
            (identical(other.favoriteBoost, favoriteBoost) ||
                other.favoriteBoost == favoriteBoost) &&
            (identical(other.finalScore, finalScore) ||
                other.finalScore == finalScore));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType, baseScore, sillageMultiplier, favoriteBoost, finalScore);

  /// Create a copy of ScoreBreakdown
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$ScoreBreakdownImplCopyWith<_$ScoreBreakdownImpl> get copyWith =>
      __$$ScoreBreakdownImplCopyWithImpl<_$ScoreBreakdownImpl>(
          this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$ScoreBreakdownImplToJson(
      this,
    );
  }
}

abstract class _ScoreBreakdown implements ScoreBreakdown {
  const factory _ScoreBreakdown(
          {@JsonKey(name: 'base_score') required final double baseScore,
          @JsonKey(name: 'sillage_multiplier')
          required final double sillageMultiplier,
          @JsonKey(name: 'favorite_boost') required final double favoriteBoost,
          @JsonKey(name: 'final_score') required final double finalScore}) =
      _$ScoreBreakdownImpl;

  factory _ScoreBreakdown.fromJson(Map<String, dynamic> json) =
      _$ScoreBreakdownImpl.fromJson;

  @override
  @JsonKey(name: 'base_score')
  double get baseScore;
  @override
  @JsonKey(name: 'sillage_multiplier')
  double get sillageMultiplier;
  @override
  @JsonKey(name: 'favorite_boost')
  double get favoriteBoost;
  @override
  @JsonKey(name: 'final_score')
  double get finalScore;

  /// Create a copy of ScoreBreakdown
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$ScoreBreakdownImplCopyWith<_$ScoreBreakdownImpl> get copyWith =>
      throw _privateConstructorUsedError;
}
