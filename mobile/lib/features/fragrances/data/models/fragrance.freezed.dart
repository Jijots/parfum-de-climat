// coverage:ignore-file
// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'fragrance.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

T _$identity<T>(T value) => value;

final _privateConstructorUsedError = UnsupportedError(
    'It seems like you constructed your class using `MyClass._()`. This constructor is only meant to be used by freezed and you are not supposed to need it nor use it.\nPlease check the documentation here for more information: https://github.com/rrousselGit/freezed#adding-getters-and-methods-to-our-models');

FragrancePerformance _$FragrancePerformanceFromJson(Map<String, dynamic> json) {
  return _FragrancePerformance.fromJson(json);
}

/// @nodoc
mixin _$FragrancePerformance {
  /// Projection strength, 1–10. Higher = the scent travels further from skin.
  double? get sillage => throw _privateConstructorUsedError;

  /// Staying power, 1–10. Higher = lasts longer on skin.
  double? get longevity => throw _privateConstructorUsedError;

  /// Community average rating, 0.0–5.0.
  double? get rating => throw _privateConstructorUsedError;

  /// Number of community votes that produced [rating].
  int? get votes => throw _privateConstructorUsedError;

  /// Serializes this FragrancePerformance to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of FragrancePerformance
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $FragrancePerformanceCopyWith<FragrancePerformance> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $FragrancePerformanceCopyWith<$Res> {
  factory $FragrancePerformanceCopyWith(FragrancePerformance value,
          $Res Function(FragrancePerformance) then) =
      _$FragrancePerformanceCopyWithImpl<$Res, FragrancePerformance>;
  @useResult
  $Res call({double? sillage, double? longevity, double? rating, int? votes});
}

/// @nodoc
class _$FragrancePerformanceCopyWithImpl<$Res,
        $Val extends FragrancePerformance>
    implements $FragrancePerformanceCopyWith<$Res> {
  _$FragrancePerformanceCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of FragrancePerformance
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? sillage = freezed,
    Object? longevity = freezed,
    Object? rating = freezed,
    Object? votes = freezed,
  }) {
    return _then(_value.copyWith(
      sillage: freezed == sillage
          ? _value.sillage
          : sillage // ignore: cast_nullable_to_non_nullable
              as double?,
      longevity: freezed == longevity
          ? _value.longevity
          : longevity // ignore: cast_nullable_to_non_nullable
              as double?,
      rating: freezed == rating
          ? _value.rating
          : rating // ignore: cast_nullable_to_non_nullable
              as double?,
      votes: freezed == votes
          ? _value.votes
          : votes // ignore: cast_nullable_to_non_nullable
              as int?,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$FragrancePerformanceImplCopyWith<$Res>
    implements $FragrancePerformanceCopyWith<$Res> {
  factory _$$FragrancePerformanceImplCopyWith(_$FragrancePerformanceImpl value,
          $Res Function(_$FragrancePerformanceImpl) then) =
      __$$FragrancePerformanceImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({double? sillage, double? longevity, double? rating, int? votes});
}

/// @nodoc
class __$$FragrancePerformanceImplCopyWithImpl<$Res>
    extends _$FragrancePerformanceCopyWithImpl<$Res, _$FragrancePerformanceImpl>
    implements _$$FragrancePerformanceImplCopyWith<$Res> {
  __$$FragrancePerformanceImplCopyWithImpl(_$FragrancePerformanceImpl _value,
      $Res Function(_$FragrancePerformanceImpl) _then)
      : super(_value, _then);

  /// Create a copy of FragrancePerformance
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? sillage = freezed,
    Object? longevity = freezed,
    Object? rating = freezed,
    Object? votes = freezed,
  }) {
    return _then(_$FragrancePerformanceImpl(
      sillage: freezed == sillage
          ? _value.sillage
          : sillage // ignore: cast_nullable_to_non_nullable
              as double?,
      longevity: freezed == longevity
          ? _value.longevity
          : longevity // ignore: cast_nullable_to_non_nullable
              as double?,
      rating: freezed == rating
          ? _value.rating
          : rating // ignore: cast_nullable_to_non_nullable
              as double?,
      votes: freezed == votes
          ? _value.votes
          : votes // ignore: cast_nullable_to_non_nullable
              as int?,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$FragrancePerformanceImpl implements _FragrancePerformance {
  const _$FragrancePerformanceImpl(
      {this.sillage, this.longevity, this.rating, this.votes});

  factory _$FragrancePerformanceImpl.fromJson(Map<String, dynamic> json) =>
      _$$FragrancePerformanceImplFromJson(json);

  /// Projection strength, 1–10. Higher = the scent travels further from skin.
  @override
  final double? sillage;

  /// Staying power, 1–10. Higher = lasts longer on skin.
  @override
  final double? longevity;

  /// Community average rating, 0.0–5.0.
  @override
  final double? rating;

  /// Number of community votes that produced [rating].
  @override
  final int? votes;

  @override
  String toString() {
    return 'FragrancePerformance(sillage: $sillage, longevity: $longevity, rating: $rating, votes: $votes)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$FragrancePerformanceImpl &&
            (identical(other.sillage, sillage) || other.sillage == sillage) &&
            (identical(other.longevity, longevity) ||
                other.longevity == longevity) &&
            (identical(other.rating, rating) || other.rating == rating) &&
            (identical(other.votes, votes) || other.votes == votes));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode =>
      Object.hash(runtimeType, sillage, longevity, rating, votes);

  /// Create a copy of FragrancePerformance
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$FragrancePerformanceImplCopyWith<_$FragrancePerformanceImpl>
      get copyWith =>
          __$$FragrancePerformanceImplCopyWithImpl<_$FragrancePerformanceImpl>(
              this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$FragrancePerformanceImplToJson(
      this,
    );
  }
}

abstract class _FragrancePerformance implements FragrancePerformance {
  const factory _FragrancePerformance(
      {final double? sillage,
      final double? longevity,
      final double? rating,
      final int? votes}) = _$FragrancePerformanceImpl;

  factory _FragrancePerformance.fromJson(Map<String, dynamic> json) =
      _$FragrancePerformanceImpl.fromJson;

  /// Projection strength, 1–10. Higher = the scent travels further from skin.
  @override
  double? get sillage;

  /// Staying power, 1–10. Higher = lasts longer on skin.
  @override
  double? get longevity;

  /// Community average rating, 0.0–5.0.
  @override
  double? get rating;

  /// Number of community votes that produced [rating].
  @override
  int? get votes;

  /// Create a copy of FragrancePerformance
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$FragrancePerformanceImplCopyWith<_$FragrancePerformanceImpl>
      get copyWith => throw _privateConstructorUsedError;
}

FragranceNotes _$FragranceNotesFromJson(Map<String, dynamic> json) {
  return _FragranceNotes.fromJson(json);
}

/// @nodoc
mixin _$FragranceNotes {
  /// Top notes — first impression, evaporates within 15–30 minutes.
  List<String> get top => throw _privateConstructorUsedError;

  /// Heart notes — the core character of the fragrance.
  List<String> get heart => throw _privateConstructorUsedError;

  /// Base notes — the dry-down, lasting 4–8 hours on skin.
  List<String> get base => throw _privateConstructorUsedError;

  /// Serializes this FragranceNotes to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of FragranceNotes
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $FragranceNotesCopyWith<FragranceNotes> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $FragranceNotesCopyWith<$Res> {
  factory $FragranceNotesCopyWith(
          FragranceNotes value, $Res Function(FragranceNotes) then) =
      _$FragranceNotesCopyWithImpl<$Res, FragranceNotes>;
  @useResult
  $Res call({List<String> top, List<String> heart, List<String> base});
}

/// @nodoc
class _$FragranceNotesCopyWithImpl<$Res, $Val extends FragranceNotes>
    implements $FragranceNotesCopyWith<$Res> {
  _$FragranceNotesCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of FragranceNotes
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? top = null,
    Object? heart = null,
    Object? base = null,
  }) {
    return _then(_value.copyWith(
      top: null == top
          ? _value.top
          : top // ignore: cast_nullable_to_non_nullable
              as List<String>,
      heart: null == heart
          ? _value.heart
          : heart // ignore: cast_nullable_to_non_nullable
              as List<String>,
      base: null == base
          ? _value.base
          : base // ignore: cast_nullable_to_non_nullable
              as List<String>,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$FragranceNotesImplCopyWith<$Res>
    implements $FragranceNotesCopyWith<$Res> {
  factory _$$FragranceNotesImplCopyWith(_$FragranceNotesImpl value,
          $Res Function(_$FragranceNotesImpl) then) =
      __$$FragranceNotesImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({List<String> top, List<String> heart, List<String> base});
}

/// @nodoc
class __$$FragranceNotesImplCopyWithImpl<$Res>
    extends _$FragranceNotesCopyWithImpl<$Res, _$FragranceNotesImpl>
    implements _$$FragranceNotesImplCopyWith<$Res> {
  __$$FragranceNotesImplCopyWithImpl(
      _$FragranceNotesImpl _value, $Res Function(_$FragranceNotesImpl) _then)
      : super(_value, _then);

  /// Create a copy of FragranceNotes
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? top = null,
    Object? heart = null,
    Object? base = null,
  }) {
    return _then(_$FragranceNotesImpl(
      top: null == top
          ? _value._top
          : top // ignore: cast_nullable_to_non_nullable
              as List<String>,
      heart: null == heart
          ? _value._heart
          : heart // ignore: cast_nullable_to_non_nullable
              as List<String>,
      base: null == base
          ? _value._base
          : base // ignore: cast_nullable_to_non_nullable
              as List<String>,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$FragranceNotesImpl implements _FragranceNotes {
  const _$FragranceNotesImpl(
      {final List<String> top = const [],
      final List<String> heart = const [],
      final List<String> base = const []})
      : _top = top,
        _heart = heart,
        _base = base;

  factory _$FragranceNotesImpl.fromJson(Map<String, dynamic> json) =>
      _$$FragranceNotesImplFromJson(json);

  /// Top notes — first impression, evaporates within 15–30 minutes.
  final List<String> _top;

  /// Top notes — first impression, evaporates within 15–30 minutes.
  @override
  @JsonKey()
  List<String> get top {
    if (_top is EqualUnmodifiableListView) return _top;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_top);
  }

  /// Heart notes — the core character of the fragrance.
  final List<String> _heart;

  /// Heart notes — the core character of the fragrance.
  @override
  @JsonKey()
  List<String> get heart {
    if (_heart is EqualUnmodifiableListView) return _heart;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_heart);
  }

  /// Base notes — the dry-down, lasting 4–8 hours on skin.
  final List<String> _base;

  /// Base notes — the dry-down, lasting 4–8 hours on skin.
  @override
  @JsonKey()
  List<String> get base {
    if (_base is EqualUnmodifiableListView) return _base;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_base);
  }

  @override
  String toString() {
    return 'FragranceNotes(top: $top, heart: $heart, base: $base)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$FragranceNotesImpl &&
            const DeepCollectionEquality().equals(other._top, _top) &&
            const DeepCollectionEquality().equals(other._heart, _heart) &&
            const DeepCollectionEquality().equals(other._base, _base));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType,
      const DeepCollectionEquality().hash(_top),
      const DeepCollectionEquality().hash(_heart),
      const DeepCollectionEquality().hash(_base));

  /// Create a copy of FragranceNotes
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$FragranceNotesImplCopyWith<_$FragranceNotesImpl> get copyWith =>
      __$$FragranceNotesImplCopyWithImpl<_$FragranceNotesImpl>(
          this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$FragranceNotesImplToJson(
      this,
    );
  }
}

abstract class _FragranceNotes implements FragranceNotes {
  const factory _FragranceNotes(
      {final List<String> top,
      final List<String> heart,
      final List<String> base}) = _$FragranceNotesImpl;

  factory _FragranceNotes.fromJson(Map<String, dynamic> json) =
      _$FragranceNotesImpl.fromJson;

  /// Top notes — first impression, evaporates within 15–30 minutes.
  @override
  List<String> get top;

  /// Heart notes — the core character of the fragrance.
  @override
  List<String> get heart;

  /// Base notes — the dry-down, lasting 4–8 hours on skin.
  @override
  List<String> get base;

  /// Create a copy of FragranceNotes
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$FragranceNotesImplCopyWith<_$FragranceNotesImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

FragranceAccord _$FragranceAccordFromJson(Map<String, dynamic> json) {
  return _FragranceAccord.fromJson(json);
}

/// @nodoc
mixin _$FragranceAccord {
  /// Accord label, e.g., "Woody", "Citrus", "Spicy", "Powdery".
  String get accord => throw _privateConstructorUsedError;

  /// Relative dominance of this accord, 0–100.
  double get strength => throw _privateConstructorUsedError;

  /// Serializes this FragranceAccord to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of FragranceAccord
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $FragranceAccordCopyWith<FragranceAccord> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $FragranceAccordCopyWith<$Res> {
  factory $FragranceAccordCopyWith(
          FragranceAccord value, $Res Function(FragranceAccord) then) =
      _$FragranceAccordCopyWithImpl<$Res, FragranceAccord>;
  @useResult
  $Res call({String accord, double strength});
}

/// @nodoc
class _$FragranceAccordCopyWithImpl<$Res, $Val extends FragranceAccord>
    implements $FragranceAccordCopyWith<$Res> {
  _$FragranceAccordCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of FragranceAccord
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? accord = null,
    Object? strength = null,
  }) {
    return _then(_value.copyWith(
      accord: null == accord
          ? _value.accord
          : accord // ignore: cast_nullable_to_non_nullable
              as String,
      strength: null == strength
          ? _value.strength
          : strength // ignore: cast_nullable_to_non_nullable
              as double,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$FragranceAccordImplCopyWith<$Res>
    implements $FragranceAccordCopyWith<$Res> {
  factory _$$FragranceAccordImplCopyWith(_$FragranceAccordImpl value,
          $Res Function(_$FragranceAccordImpl) then) =
      __$$FragranceAccordImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call({String accord, double strength});
}

/// @nodoc
class __$$FragranceAccordImplCopyWithImpl<$Res>
    extends _$FragranceAccordCopyWithImpl<$Res, _$FragranceAccordImpl>
    implements _$$FragranceAccordImplCopyWith<$Res> {
  __$$FragranceAccordImplCopyWithImpl(
      _$FragranceAccordImpl _value, $Res Function(_$FragranceAccordImpl) _then)
      : super(_value, _then);

  /// Create a copy of FragranceAccord
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? accord = null,
    Object? strength = null,
  }) {
    return _then(_$FragranceAccordImpl(
      accord: null == accord
          ? _value.accord
          : accord // ignore: cast_nullable_to_non_nullable
              as String,
      strength: null == strength
          ? _value.strength
          : strength // ignore: cast_nullable_to_non_nullable
              as double,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$FragranceAccordImpl implements _FragranceAccord {
  const _$FragranceAccordImpl({required this.accord, required this.strength});

  factory _$FragranceAccordImpl.fromJson(Map<String, dynamic> json) =>
      _$$FragranceAccordImplFromJson(json);

  /// Accord label, e.g., "Woody", "Citrus", "Spicy", "Powdery".
  @override
  final String accord;

  /// Relative dominance of this accord, 0–100.
  @override
  final double strength;

  @override
  String toString() {
    return 'FragranceAccord(accord: $accord, strength: $strength)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$FragranceAccordImpl &&
            (identical(other.accord, accord) || other.accord == accord) &&
            (identical(other.strength, strength) ||
                other.strength == strength));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(runtimeType, accord, strength);

  /// Create a copy of FragranceAccord
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$FragranceAccordImplCopyWith<_$FragranceAccordImpl> get copyWith =>
      __$$FragranceAccordImplCopyWithImpl<_$FragranceAccordImpl>(
          this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$FragranceAccordImplToJson(
      this,
    );
  }
}

abstract class _FragranceAccord implements FragranceAccord {
  const factory _FragranceAccord(
      {required final String accord,
      required final double strength}) = _$FragranceAccordImpl;

  factory _FragranceAccord.fromJson(Map<String, dynamic> json) =
      _$FragranceAccordImpl.fromJson;

  /// Accord label, e.g., "Woody", "Citrus", "Spicy", "Powdery".
  @override
  String get accord;

  /// Relative dominance of this accord, 0–100.
  @override
  double get strength;

  /// Create a copy of FragranceAccord
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$FragranceAccordImplCopyWith<_$FragranceAccordImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

FragranceSummary _$FragranceSummaryFromJson(Map<String, dynamic> json) {
  return _FragranceSummary.fromJson(json);
}

/// @nodoc
mixin _$FragranceSummary {
  int get id => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  String get brand => throw _privateConstructorUsedError;
  @JsonKey(name: 'olfactive_family')
  String? get olfactiveFamily => throw _privateConstructorUsedError;
  @JsonKey(name: 'gender_target')
  String? get genderTarget => throw _privateConstructorUsedError;
  @JsonKey(name: 'release_year')
  int? get releaseYear => throw _privateConstructorUsedError;
  String? get concentration => throw _privateConstructorUsedError;

  /// Server-resolved image URL. Prefers local storage cache; falls back to
  /// the remote CDN URL stored at import time; null if neither is available.
  @JsonKey(name: 'image_url')
  String? get imageUrl => throw _privateConstructorUsedError;

  /// Sillage, longevity, rating, and vote count — grouped as on the server.
  FragrancePerformance? get performance => throw _privateConstructorUsedError;

  /// True when the fragrance has at least one note mapped to a climate profile.
  /// Used in the browse list to render an "unscored" badge so users know the
  /// engine can't recommend this fragrance until an admin maps its notes.
  @JsonKey(name: 'has_climate_profile')
  bool get hasClimateProfile => throw _privateConstructorUsedError;
  @JsonKey(name: 'is_active')
  bool get isActive => throw _privateConstructorUsedError;

  /// Serializes this FragranceSummary to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of FragranceSummary
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $FragranceSummaryCopyWith<FragranceSummary> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $FragranceSummaryCopyWith<$Res> {
  factory $FragranceSummaryCopyWith(
          FragranceSummary value, $Res Function(FragranceSummary) then) =
      _$FragranceSummaryCopyWithImpl<$Res, FragranceSummary>;
  @useResult
  $Res call(
      {int id,
      String name,
      String brand,
      @JsonKey(name: 'olfactive_family') String? olfactiveFamily,
      @JsonKey(name: 'gender_target') String? genderTarget,
      @JsonKey(name: 'release_year') int? releaseYear,
      String? concentration,
      @JsonKey(name: 'image_url') String? imageUrl,
      FragrancePerformance? performance,
      @JsonKey(name: 'has_climate_profile') bool hasClimateProfile,
      @JsonKey(name: 'is_active') bool isActive});

  $FragrancePerformanceCopyWith<$Res>? get performance;
}

/// @nodoc
class _$FragranceSummaryCopyWithImpl<$Res, $Val extends FragranceSummary>
    implements $FragranceSummaryCopyWith<$Res> {
  _$FragranceSummaryCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of FragranceSummary
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? brand = null,
    Object? olfactiveFamily = freezed,
    Object? genderTarget = freezed,
    Object? releaseYear = freezed,
    Object? concentration = freezed,
    Object? imageUrl = freezed,
    Object? performance = freezed,
    Object? hasClimateProfile = null,
    Object? isActive = null,
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
      olfactiveFamily: freezed == olfactiveFamily
          ? _value.olfactiveFamily
          : olfactiveFamily // ignore: cast_nullable_to_non_nullable
              as String?,
      genderTarget: freezed == genderTarget
          ? _value.genderTarget
          : genderTarget // ignore: cast_nullable_to_non_nullable
              as String?,
      releaseYear: freezed == releaseYear
          ? _value.releaseYear
          : releaseYear // ignore: cast_nullable_to_non_nullable
              as int?,
      concentration: freezed == concentration
          ? _value.concentration
          : concentration // ignore: cast_nullable_to_non_nullable
              as String?,
      imageUrl: freezed == imageUrl
          ? _value.imageUrl
          : imageUrl // ignore: cast_nullable_to_non_nullable
              as String?,
      performance: freezed == performance
          ? _value.performance
          : performance // ignore: cast_nullable_to_non_nullable
              as FragrancePerformance?,
      hasClimateProfile: null == hasClimateProfile
          ? _value.hasClimateProfile
          : hasClimateProfile // ignore: cast_nullable_to_non_nullable
              as bool,
      isActive: null == isActive
          ? _value.isActive
          : isActive // ignore: cast_nullable_to_non_nullable
              as bool,
    ) as $Val);
  }

  /// Create a copy of FragranceSummary
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $FragrancePerformanceCopyWith<$Res>? get performance {
    if (_value.performance == null) {
      return null;
    }

    return $FragrancePerformanceCopyWith<$Res>(_value.performance!, (value) {
      return _then(_value.copyWith(performance: value) as $Val);
    });
  }
}

/// @nodoc
abstract class _$$FragranceSummaryImplCopyWith<$Res>
    implements $FragranceSummaryCopyWith<$Res> {
  factory _$$FragranceSummaryImplCopyWith(_$FragranceSummaryImpl value,
          $Res Function(_$FragranceSummaryImpl) then) =
      __$$FragranceSummaryImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {int id,
      String name,
      String brand,
      @JsonKey(name: 'olfactive_family') String? olfactiveFamily,
      @JsonKey(name: 'gender_target') String? genderTarget,
      @JsonKey(name: 'release_year') int? releaseYear,
      String? concentration,
      @JsonKey(name: 'image_url') String? imageUrl,
      FragrancePerformance? performance,
      @JsonKey(name: 'has_climate_profile') bool hasClimateProfile,
      @JsonKey(name: 'is_active') bool isActive});

  @override
  $FragrancePerformanceCopyWith<$Res>? get performance;
}

/// @nodoc
class __$$FragranceSummaryImplCopyWithImpl<$Res>
    extends _$FragranceSummaryCopyWithImpl<$Res, _$FragranceSummaryImpl>
    implements _$$FragranceSummaryImplCopyWith<$Res> {
  __$$FragranceSummaryImplCopyWithImpl(_$FragranceSummaryImpl _value,
      $Res Function(_$FragranceSummaryImpl) _then)
      : super(_value, _then);

  /// Create a copy of FragranceSummary
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? brand = null,
    Object? olfactiveFamily = freezed,
    Object? genderTarget = freezed,
    Object? releaseYear = freezed,
    Object? concentration = freezed,
    Object? imageUrl = freezed,
    Object? performance = freezed,
    Object? hasClimateProfile = null,
    Object? isActive = null,
  }) {
    return _then(_$FragranceSummaryImpl(
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
      olfactiveFamily: freezed == olfactiveFamily
          ? _value.olfactiveFamily
          : olfactiveFamily // ignore: cast_nullable_to_non_nullable
              as String?,
      genderTarget: freezed == genderTarget
          ? _value.genderTarget
          : genderTarget // ignore: cast_nullable_to_non_nullable
              as String?,
      releaseYear: freezed == releaseYear
          ? _value.releaseYear
          : releaseYear // ignore: cast_nullable_to_non_nullable
              as int?,
      concentration: freezed == concentration
          ? _value.concentration
          : concentration // ignore: cast_nullable_to_non_nullable
              as String?,
      imageUrl: freezed == imageUrl
          ? _value.imageUrl
          : imageUrl // ignore: cast_nullable_to_non_nullable
              as String?,
      performance: freezed == performance
          ? _value.performance
          : performance // ignore: cast_nullable_to_non_nullable
              as FragrancePerformance?,
      hasClimateProfile: null == hasClimateProfile
          ? _value.hasClimateProfile
          : hasClimateProfile // ignore: cast_nullable_to_non_nullable
              as bool,
      isActive: null == isActive
          ? _value.isActive
          : isActive // ignore: cast_nullable_to_non_nullable
              as bool,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$FragranceSummaryImpl implements _FragranceSummary {
  const _$FragranceSummaryImpl(
      {required this.id,
      required this.name,
      required this.brand,
      @JsonKey(name: 'olfactive_family') this.olfactiveFamily,
      @JsonKey(name: 'gender_target') this.genderTarget,
      @JsonKey(name: 'release_year') this.releaseYear,
      this.concentration,
      @JsonKey(name: 'image_url') this.imageUrl,
      this.performance,
      @JsonKey(name: 'has_climate_profile') this.hasClimateProfile = false,
      @JsonKey(name: 'is_active') this.isActive = true});

  factory _$FragranceSummaryImpl.fromJson(Map<String, dynamic> json) =>
      _$$FragranceSummaryImplFromJson(json);

  @override
  final int id;
  @override
  final String name;
  @override
  final String brand;
  @override
  @JsonKey(name: 'olfactive_family')
  final String? olfactiveFamily;
  @override
  @JsonKey(name: 'gender_target')
  final String? genderTarget;
  @override
  @JsonKey(name: 'release_year')
  final int? releaseYear;
  @override
  final String? concentration;

  /// Server-resolved image URL. Prefers local storage cache; falls back to
  /// the remote CDN URL stored at import time; null if neither is available.
  @override
  @JsonKey(name: 'image_url')
  final String? imageUrl;

  /// Sillage, longevity, rating, and vote count — grouped as on the server.
  @override
  final FragrancePerformance? performance;

  /// True when the fragrance has at least one note mapped to a climate profile.
  /// Used in the browse list to render an "unscored" badge so users know the
  /// engine can't recommend this fragrance until an admin maps its notes.
  @override
  @JsonKey(name: 'has_climate_profile')
  final bool hasClimateProfile;
  @override
  @JsonKey(name: 'is_active')
  final bool isActive;

  @override
  String toString() {
    return 'FragranceSummary(id: $id, name: $name, brand: $brand, olfactiveFamily: $olfactiveFamily, genderTarget: $genderTarget, releaseYear: $releaseYear, concentration: $concentration, imageUrl: $imageUrl, performance: $performance, hasClimateProfile: $hasClimateProfile, isActive: $isActive)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$FragranceSummaryImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.brand, brand) || other.brand == brand) &&
            (identical(other.olfactiveFamily, olfactiveFamily) ||
                other.olfactiveFamily == olfactiveFamily) &&
            (identical(other.genderTarget, genderTarget) ||
                other.genderTarget == genderTarget) &&
            (identical(other.releaseYear, releaseYear) ||
                other.releaseYear == releaseYear) &&
            (identical(other.concentration, concentration) ||
                other.concentration == concentration) &&
            (identical(other.imageUrl, imageUrl) ||
                other.imageUrl == imageUrl) &&
            (identical(other.performance, performance) ||
                other.performance == performance) &&
            (identical(other.hasClimateProfile, hasClimateProfile) ||
                other.hasClimateProfile == hasClimateProfile) &&
            (identical(other.isActive, isActive) ||
                other.isActive == isActive));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType,
      id,
      name,
      brand,
      olfactiveFamily,
      genderTarget,
      releaseYear,
      concentration,
      imageUrl,
      performance,
      hasClimateProfile,
      isActive);

  /// Create a copy of FragranceSummary
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$FragranceSummaryImplCopyWith<_$FragranceSummaryImpl> get copyWith =>
      __$$FragranceSummaryImplCopyWithImpl<_$FragranceSummaryImpl>(
          this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$FragranceSummaryImplToJson(
      this,
    );
  }
}

abstract class _FragranceSummary implements FragranceSummary {
  const factory _FragranceSummary(
          {required final int id,
          required final String name,
          required final String brand,
          @JsonKey(name: 'olfactive_family') final String? olfactiveFamily,
          @JsonKey(name: 'gender_target') final String? genderTarget,
          @JsonKey(name: 'release_year') final int? releaseYear,
          final String? concentration,
          @JsonKey(name: 'image_url') final String? imageUrl,
          final FragrancePerformance? performance,
          @JsonKey(name: 'has_climate_profile') final bool hasClimateProfile,
          @JsonKey(name: 'is_active') final bool isActive}) =
      _$FragranceSummaryImpl;

  factory _FragranceSummary.fromJson(Map<String, dynamic> json) =
      _$FragranceSummaryImpl.fromJson;

  @override
  int get id;
  @override
  String get name;
  @override
  String get brand;
  @override
  @JsonKey(name: 'olfactive_family')
  String? get olfactiveFamily;
  @override
  @JsonKey(name: 'gender_target')
  String? get genderTarget;
  @override
  @JsonKey(name: 'release_year')
  int? get releaseYear;
  @override
  String? get concentration;

  /// Server-resolved image URL. Prefers local storage cache; falls back to
  /// the remote CDN URL stored at import time; null if neither is available.
  @override
  @JsonKey(name: 'image_url')
  String? get imageUrl;

  /// Sillage, longevity, rating, and vote count — grouped as on the server.
  @override
  FragrancePerformance? get performance;

  /// True when the fragrance has at least one note mapped to a climate profile.
  /// Used in the browse list to render an "unscored" badge so users know the
  /// engine can't recommend this fragrance until an admin maps its notes.
  @override
  @JsonKey(name: 'has_climate_profile')
  bool get hasClimateProfile;
  @override
  @JsonKey(name: 'is_active')
  bool get isActive;

  /// Create a copy of FragranceSummary
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$FragranceSummaryImplCopyWith<_$FragranceSummaryImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

Fragrance _$FragranceFromJson(Map<String, dynamic> json) {
  return _Fragrance.fromJson(json);
}

/// @nodoc
mixin _$Fragrance {
  int get id => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  String get brand => throw _privateConstructorUsedError;
  String? get description => throw _privateConstructorUsedError;
  @JsonKey(name: 'olfactive_family')
  String? get olfactiveFamily => throw _privateConstructorUsedError;
  @JsonKey(name: 'gender_target')
  String? get genderTarget => throw _privateConstructorUsedError;
  @JsonKey(name: 'release_year')
  int? get releaseYear => throw _privateConstructorUsedError;
  String? get concentration => throw _privateConstructorUsedError;

  /// Server-resolved image URL (same resolution logic as [FragranceSummary]).
  @JsonKey(name: 'image_url')
  String? get imageUrl => throw _privateConstructorUsedError;

  /// Sillage, longevity, community rating, and vote count.
  FragrancePerformance? get performance => throw _privateConstructorUsedError;

  /// Fragrance pyramid — only populated on the detail endpoint.
  FragranceNotes? get notes => throw _privateConstructorUsedError;

  /// Accords with strength weighting — only populated on the detail endpoint.
  List<FragranceAccord> get accords => throw _privateConstructorUsedError;
  @JsonKey(name: 'has_climate_profile')
  bool get hasClimateProfile => throw _privateConstructorUsedError;
  @JsonKey(name: 'is_active')
  bool get isActive => throw _privateConstructorUsedError;

  /// Serializes this Fragrance to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of Fragrance
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $FragranceCopyWith<Fragrance> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $FragranceCopyWith<$Res> {
  factory $FragranceCopyWith(Fragrance value, $Res Function(Fragrance) then) =
      _$FragranceCopyWithImpl<$Res, Fragrance>;
  @useResult
  $Res call(
      {int id,
      String name,
      String brand,
      String? description,
      @JsonKey(name: 'olfactive_family') String? olfactiveFamily,
      @JsonKey(name: 'gender_target') String? genderTarget,
      @JsonKey(name: 'release_year') int? releaseYear,
      String? concentration,
      @JsonKey(name: 'image_url') String? imageUrl,
      FragrancePerformance? performance,
      FragranceNotes? notes,
      List<FragranceAccord> accords,
      @JsonKey(name: 'has_climate_profile') bool hasClimateProfile,
      @JsonKey(name: 'is_active') bool isActive});

  $FragrancePerformanceCopyWith<$Res>? get performance;
  $FragranceNotesCopyWith<$Res>? get notes;
}

/// @nodoc
class _$FragranceCopyWithImpl<$Res, $Val extends Fragrance>
    implements $FragranceCopyWith<$Res> {
  _$FragranceCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of Fragrance
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? brand = null,
    Object? description = freezed,
    Object? olfactiveFamily = freezed,
    Object? genderTarget = freezed,
    Object? releaseYear = freezed,
    Object? concentration = freezed,
    Object? imageUrl = freezed,
    Object? performance = freezed,
    Object? notes = freezed,
    Object? accords = null,
    Object? hasClimateProfile = null,
    Object? isActive = null,
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
      description: freezed == description
          ? _value.description
          : description // ignore: cast_nullable_to_non_nullable
              as String?,
      olfactiveFamily: freezed == olfactiveFamily
          ? _value.olfactiveFamily
          : olfactiveFamily // ignore: cast_nullable_to_non_nullable
              as String?,
      genderTarget: freezed == genderTarget
          ? _value.genderTarget
          : genderTarget // ignore: cast_nullable_to_non_nullable
              as String?,
      releaseYear: freezed == releaseYear
          ? _value.releaseYear
          : releaseYear // ignore: cast_nullable_to_non_nullable
              as int?,
      concentration: freezed == concentration
          ? _value.concentration
          : concentration // ignore: cast_nullable_to_non_nullable
              as String?,
      imageUrl: freezed == imageUrl
          ? _value.imageUrl
          : imageUrl // ignore: cast_nullable_to_non_nullable
              as String?,
      performance: freezed == performance
          ? _value.performance
          : performance // ignore: cast_nullable_to_non_nullable
              as FragrancePerformance?,
      notes: freezed == notes
          ? _value.notes
          : notes // ignore: cast_nullable_to_non_nullable
              as FragranceNotes?,
      accords: null == accords
          ? _value.accords
          : accords // ignore: cast_nullable_to_non_nullable
              as List<FragranceAccord>,
      hasClimateProfile: null == hasClimateProfile
          ? _value.hasClimateProfile
          : hasClimateProfile // ignore: cast_nullable_to_non_nullable
              as bool,
      isActive: null == isActive
          ? _value.isActive
          : isActive // ignore: cast_nullable_to_non_nullable
              as bool,
    ) as $Val);
  }

  /// Create a copy of Fragrance
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $FragrancePerformanceCopyWith<$Res>? get performance {
    if (_value.performance == null) {
      return null;
    }

    return $FragrancePerformanceCopyWith<$Res>(_value.performance!, (value) {
      return _then(_value.copyWith(performance: value) as $Val);
    });
  }

  /// Create a copy of Fragrance
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $FragranceNotesCopyWith<$Res>? get notes {
    if (_value.notes == null) {
      return null;
    }

    return $FragranceNotesCopyWith<$Res>(_value.notes!, (value) {
      return _then(_value.copyWith(notes: value) as $Val);
    });
  }
}

/// @nodoc
abstract class _$$FragranceImplCopyWith<$Res>
    implements $FragranceCopyWith<$Res> {
  factory _$$FragranceImplCopyWith(
          _$FragranceImpl value, $Res Function(_$FragranceImpl) then) =
      __$$FragranceImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {int id,
      String name,
      String brand,
      String? description,
      @JsonKey(name: 'olfactive_family') String? olfactiveFamily,
      @JsonKey(name: 'gender_target') String? genderTarget,
      @JsonKey(name: 'release_year') int? releaseYear,
      String? concentration,
      @JsonKey(name: 'image_url') String? imageUrl,
      FragrancePerformance? performance,
      FragranceNotes? notes,
      List<FragranceAccord> accords,
      @JsonKey(name: 'has_climate_profile') bool hasClimateProfile,
      @JsonKey(name: 'is_active') bool isActive});

  @override
  $FragrancePerformanceCopyWith<$Res>? get performance;
  @override
  $FragranceNotesCopyWith<$Res>? get notes;
}

/// @nodoc
class __$$FragranceImplCopyWithImpl<$Res>
    extends _$FragranceCopyWithImpl<$Res, _$FragranceImpl>
    implements _$$FragranceImplCopyWith<$Res> {
  __$$FragranceImplCopyWithImpl(
      _$FragranceImpl _value, $Res Function(_$FragranceImpl) _then)
      : super(_value, _then);

  /// Create a copy of Fragrance
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? brand = null,
    Object? description = freezed,
    Object? olfactiveFamily = freezed,
    Object? genderTarget = freezed,
    Object? releaseYear = freezed,
    Object? concentration = freezed,
    Object? imageUrl = freezed,
    Object? performance = freezed,
    Object? notes = freezed,
    Object? accords = null,
    Object? hasClimateProfile = null,
    Object? isActive = null,
  }) {
    return _then(_$FragranceImpl(
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
      description: freezed == description
          ? _value.description
          : description // ignore: cast_nullable_to_non_nullable
              as String?,
      olfactiveFamily: freezed == olfactiveFamily
          ? _value.olfactiveFamily
          : olfactiveFamily // ignore: cast_nullable_to_non_nullable
              as String?,
      genderTarget: freezed == genderTarget
          ? _value.genderTarget
          : genderTarget // ignore: cast_nullable_to_non_nullable
              as String?,
      releaseYear: freezed == releaseYear
          ? _value.releaseYear
          : releaseYear // ignore: cast_nullable_to_non_nullable
              as int?,
      concentration: freezed == concentration
          ? _value.concentration
          : concentration // ignore: cast_nullable_to_non_nullable
              as String?,
      imageUrl: freezed == imageUrl
          ? _value.imageUrl
          : imageUrl // ignore: cast_nullable_to_non_nullable
              as String?,
      performance: freezed == performance
          ? _value.performance
          : performance // ignore: cast_nullable_to_non_nullable
              as FragrancePerformance?,
      notes: freezed == notes
          ? _value.notes
          : notes // ignore: cast_nullable_to_non_nullable
              as FragranceNotes?,
      accords: null == accords
          ? _value._accords
          : accords // ignore: cast_nullable_to_non_nullable
              as List<FragranceAccord>,
      hasClimateProfile: null == hasClimateProfile
          ? _value.hasClimateProfile
          : hasClimateProfile // ignore: cast_nullable_to_non_nullable
              as bool,
      isActive: null == isActive
          ? _value.isActive
          : isActive // ignore: cast_nullable_to_non_nullable
              as bool,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$FragranceImpl implements _Fragrance {
  const _$FragranceImpl(
      {required this.id,
      required this.name,
      required this.brand,
      this.description,
      @JsonKey(name: 'olfactive_family') this.olfactiveFamily,
      @JsonKey(name: 'gender_target') this.genderTarget,
      @JsonKey(name: 'release_year') this.releaseYear,
      this.concentration,
      @JsonKey(name: 'image_url') this.imageUrl,
      this.performance,
      this.notes,
      final List<FragranceAccord> accords = const [],
      @JsonKey(name: 'has_climate_profile') this.hasClimateProfile = false,
      @JsonKey(name: 'is_active') this.isActive = true})
      : _accords = accords;

  factory _$FragranceImpl.fromJson(Map<String, dynamic> json) =>
      _$$FragranceImplFromJson(json);

  @override
  final int id;
  @override
  final String name;
  @override
  final String brand;
  @override
  final String? description;
  @override
  @JsonKey(name: 'olfactive_family')
  final String? olfactiveFamily;
  @override
  @JsonKey(name: 'gender_target')
  final String? genderTarget;
  @override
  @JsonKey(name: 'release_year')
  final int? releaseYear;
  @override
  final String? concentration;

  /// Server-resolved image URL (same resolution logic as [FragranceSummary]).
  @override
  @JsonKey(name: 'image_url')
  final String? imageUrl;

  /// Sillage, longevity, community rating, and vote count.
  @override
  final FragrancePerformance? performance;

  /// Fragrance pyramid — only populated on the detail endpoint.
  @override
  final FragranceNotes? notes;

  /// Accords with strength weighting — only populated on the detail endpoint.
  final List<FragranceAccord> _accords;

  /// Accords with strength weighting — only populated on the detail endpoint.
  @override
  @JsonKey()
  List<FragranceAccord> get accords {
    if (_accords is EqualUnmodifiableListView) return _accords;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_accords);
  }

  @override
  @JsonKey(name: 'has_climate_profile')
  final bool hasClimateProfile;
  @override
  @JsonKey(name: 'is_active')
  final bool isActive;

  @override
  String toString() {
    return 'Fragrance(id: $id, name: $name, brand: $brand, description: $description, olfactiveFamily: $olfactiveFamily, genderTarget: $genderTarget, releaseYear: $releaseYear, concentration: $concentration, imageUrl: $imageUrl, performance: $performance, notes: $notes, accords: $accords, hasClimateProfile: $hasClimateProfile, isActive: $isActive)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$FragranceImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.brand, brand) || other.brand == brand) &&
            (identical(other.description, description) ||
                other.description == description) &&
            (identical(other.olfactiveFamily, olfactiveFamily) ||
                other.olfactiveFamily == olfactiveFamily) &&
            (identical(other.genderTarget, genderTarget) ||
                other.genderTarget == genderTarget) &&
            (identical(other.releaseYear, releaseYear) ||
                other.releaseYear == releaseYear) &&
            (identical(other.concentration, concentration) ||
                other.concentration == concentration) &&
            (identical(other.imageUrl, imageUrl) ||
                other.imageUrl == imageUrl) &&
            (identical(other.performance, performance) ||
                other.performance == performance) &&
            (identical(other.notes, notes) || other.notes == notes) &&
            const DeepCollectionEquality().equals(other._accords, _accords) &&
            (identical(other.hasClimateProfile, hasClimateProfile) ||
                other.hasClimateProfile == hasClimateProfile) &&
            (identical(other.isActive, isActive) ||
                other.isActive == isActive));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType,
      id,
      name,
      brand,
      description,
      olfactiveFamily,
      genderTarget,
      releaseYear,
      concentration,
      imageUrl,
      performance,
      notes,
      const DeepCollectionEquality().hash(_accords),
      hasClimateProfile,
      isActive);

  /// Create a copy of Fragrance
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$FragranceImplCopyWith<_$FragranceImpl> get copyWith =>
      __$$FragranceImplCopyWithImpl<_$FragranceImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$FragranceImplToJson(
      this,
    );
  }
}

abstract class _Fragrance implements Fragrance {
  const factory _Fragrance(
      {required final int id,
      required final String name,
      required final String brand,
      final String? description,
      @JsonKey(name: 'olfactive_family') final String? olfactiveFamily,
      @JsonKey(name: 'gender_target') final String? genderTarget,
      @JsonKey(name: 'release_year') final int? releaseYear,
      final String? concentration,
      @JsonKey(name: 'image_url') final String? imageUrl,
      final FragrancePerformance? performance,
      final FragranceNotes? notes,
      final List<FragranceAccord> accords,
      @JsonKey(name: 'has_climate_profile') final bool hasClimateProfile,
      @JsonKey(name: 'is_active') final bool isActive}) = _$FragranceImpl;

  factory _Fragrance.fromJson(Map<String, dynamic> json) =
      _$FragranceImpl.fromJson;

  @override
  int get id;
  @override
  String get name;
  @override
  String get brand;
  @override
  String? get description;
  @override
  @JsonKey(name: 'olfactive_family')
  String? get olfactiveFamily;
  @override
  @JsonKey(name: 'gender_target')
  String? get genderTarget;
  @override
  @JsonKey(name: 'release_year')
  int? get releaseYear;
  @override
  String? get concentration;

  /// Server-resolved image URL (same resolution logic as [FragranceSummary]).
  @override
  @JsonKey(name: 'image_url')
  String? get imageUrl;

  /// Sillage, longevity, community rating, and vote count.
  @override
  FragrancePerformance? get performance;

  /// Fragrance pyramid — only populated on the detail endpoint.
  @override
  FragranceNotes? get notes;

  /// Accords with strength weighting — only populated on the detail endpoint.
  @override
  List<FragranceAccord> get accords;
  @override
  @JsonKey(name: 'has_climate_profile')
  bool get hasClimateProfile;
  @override
  @JsonKey(name: 'is_active')
  bool get isActive;

  /// Create a copy of Fragrance
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$FragranceImplCopyWith<_$FragranceImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

PaginatedFragrances _$PaginatedFragrancesFromJson(Map<String, dynamic> json) {
  return _PaginatedFragrances.fromJson(json);
}

/// @nodoc
mixin _$PaginatedFragrances {
  List<FragranceSummary> get data => throw _privateConstructorUsedError;
  @JsonKey(name: 'current_page')
  int get currentPage => throw _privateConstructorUsedError;
  @JsonKey(name: 'last_page')
  int get lastPage => throw _privateConstructorUsedError;
  int get total => throw _privateConstructorUsedError;

  /// Serializes this PaginatedFragrances to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of PaginatedFragrances
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $PaginatedFragrancesCopyWith<PaginatedFragrances> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $PaginatedFragrancesCopyWith<$Res> {
  factory $PaginatedFragrancesCopyWith(
          PaginatedFragrances value, $Res Function(PaginatedFragrances) then) =
      _$PaginatedFragrancesCopyWithImpl<$Res, PaginatedFragrances>;
  @useResult
  $Res call(
      {List<FragranceSummary> data,
      @JsonKey(name: 'current_page') int currentPage,
      @JsonKey(name: 'last_page') int lastPage,
      int total});
}

/// @nodoc
class _$PaginatedFragrancesCopyWithImpl<$Res, $Val extends PaginatedFragrances>
    implements $PaginatedFragrancesCopyWith<$Res> {
  _$PaginatedFragrancesCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of PaginatedFragrances
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? data = null,
    Object? currentPage = null,
    Object? lastPage = null,
    Object? total = null,
  }) {
    return _then(_value.copyWith(
      data: null == data
          ? _value.data
          : data // ignore: cast_nullable_to_non_nullable
              as List<FragranceSummary>,
      currentPage: null == currentPage
          ? _value.currentPage
          : currentPage // ignore: cast_nullable_to_non_nullable
              as int,
      lastPage: null == lastPage
          ? _value.lastPage
          : lastPage // ignore: cast_nullable_to_non_nullable
              as int,
      total: null == total
          ? _value.total
          : total // ignore: cast_nullable_to_non_nullable
              as int,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$PaginatedFragrancesImplCopyWith<$Res>
    implements $PaginatedFragrancesCopyWith<$Res> {
  factory _$$PaginatedFragrancesImplCopyWith(_$PaginatedFragrancesImpl value,
          $Res Function(_$PaginatedFragrancesImpl) then) =
      __$$PaginatedFragrancesImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {List<FragranceSummary> data,
      @JsonKey(name: 'current_page') int currentPage,
      @JsonKey(name: 'last_page') int lastPage,
      int total});
}

/// @nodoc
class __$$PaginatedFragrancesImplCopyWithImpl<$Res>
    extends _$PaginatedFragrancesCopyWithImpl<$Res, _$PaginatedFragrancesImpl>
    implements _$$PaginatedFragrancesImplCopyWith<$Res> {
  __$$PaginatedFragrancesImplCopyWithImpl(_$PaginatedFragrancesImpl _value,
      $Res Function(_$PaginatedFragrancesImpl) _then)
      : super(_value, _then);

  /// Create a copy of PaginatedFragrances
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? data = null,
    Object? currentPage = null,
    Object? lastPage = null,
    Object? total = null,
  }) {
    return _then(_$PaginatedFragrancesImpl(
      data: null == data
          ? _value._data
          : data // ignore: cast_nullable_to_non_nullable
              as List<FragranceSummary>,
      currentPage: null == currentPage
          ? _value.currentPage
          : currentPage // ignore: cast_nullable_to_non_nullable
              as int,
      lastPage: null == lastPage
          ? _value.lastPage
          : lastPage // ignore: cast_nullable_to_non_nullable
              as int,
      total: null == total
          ? _value.total
          : total // ignore: cast_nullable_to_non_nullable
              as int,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$PaginatedFragrancesImpl implements _PaginatedFragrances {
  const _$PaginatedFragrancesImpl(
      {required final List<FragranceSummary> data,
      @JsonKey(name: 'current_page') required this.currentPage,
      @JsonKey(name: 'last_page') required this.lastPage,
      required this.total})
      : _data = data;

  factory _$PaginatedFragrancesImpl.fromJson(Map<String, dynamic> json) =>
      _$$PaginatedFragrancesImplFromJson(json);

  final List<FragranceSummary> _data;
  @override
  List<FragranceSummary> get data {
    if (_data is EqualUnmodifiableListView) return _data;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_data);
  }

  @override
  @JsonKey(name: 'current_page')
  final int currentPage;
  @override
  @JsonKey(name: 'last_page')
  final int lastPage;
  @override
  final int total;

  @override
  String toString() {
    return 'PaginatedFragrances(data: $data, currentPage: $currentPage, lastPage: $lastPage, total: $total)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$PaginatedFragrancesImpl &&
            const DeepCollectionEquality().equals(other._data, _data) &&
            (identical(other.currentPage, currentPage) ||
                other.currentPage == currentPage) &&
            (identical(other.lastPage, lastPage) ||
                other.lastPage == lastPage) &&
            (identical(other.total, total) || other.total == total));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(runtimeType,
      const DeepCollectionEquality().hash(_data), currentPage, lastPage, total);

  /// Create a copy of PaginatedFragrances
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$PaginatedFragrancesImplCopyWith<_$PaginatedFragrancesImpl> get copyWith =>
      __$$PaginatedFragrancesImplCopyWithImpl<_$PaginatedFragrancesImpl>(
          this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$PaginatedFragrancesImplToJson(
      this,
    );
  }
}

abstract class _PaginatedFragrances implements PaginatedFragrances {
  const factory _PaginatedFragrances(
      {required final List<FragranceSummary> data,
      @JsonKey(name: 'current_page') required final int currentPage,
      @JsonKey(name: 'last_page') required final int lastPage,
      required final int total}) = _$PaginatedFragrancesImpl;

  factory _PaginatedFragrances.fromJson(Map<String, dynamic> json) =
      _$PaginatedFragrancesImpl.fromJson;

  @override
  List<FragranceSummary> get data;
  @override
  @JsonKey(name: 'current_page')
  int get currentPage;
  @override
  @JsonKey(name: 'last_page')
  int get lastPage;
  @override
  int get total;

  /// Create a copy of PaginatedFragrances
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$PaginatedFragrancesImplCopyWith<_$PaginatedFragrancesImpl> get copyWith =>
      throw _privateConstructorUsedError;
}
