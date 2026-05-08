// coverage:ignore-file
// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'collection_item.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

T _$identity<T>(T value) => value;

final _privateConstructorUsedError = UnsupportedError(
    'It seems like you constructed your class using `MyClass._()`. This constructor is only meant to be used by freezed and you are not supposed to need it nor use it.\nPlease check the documentation here for more information: https://github.com/rrousselGit/freezed#adding-getters-and-methods-to-our-models');

CollectionItem _$CollectionItemFromJson(Map<String, dynamic> json) {
  return _CollectionItem.fromJson(json);
}

/// @nodoc
mixin _$CollectionItem {
  @JsonKey(name: 'fragrance_id')
  int get fragranceId => throw _privateConstructorUsedError;
  CollectionFragrance get fragrance => throw _privateConstructorUsedError;
  @JsonKey(name: 'is_favorite')
  bool get isFavorite => throw _privateConstructorUsedError;

  /// Optional personal note (e.g. "Gift from mum, summer 2024").
  @JsonKey(name: 'personal_notes')
  String? get personalNotes => throw _privateConstructorUsedError;

  /// Date the bottle was acquired. Null if not recorded.
  @JsonKey(name: 'acquired_date')
  DateTime? get acquiredDate => throw _privateConstructorUsedError;
  @JsonKey(name: 'created_at')
  DateTime get createdAt => throw _privateConstructorUsedError;

  /// Serializes this CollectionItem to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of CollectionItem
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $CollectionItemCopyWith<CollectionItem> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $CollectionItemCopyWith<$Res> {
  factory $CollectionItemCopyWith(
          CollectionItem value, $Res Function(CollectionItem) then) =
      _$CollectionItemCopyWithImpl<$Res, CollectionItem>;
  @useResult
  $Res call(
      {@JsonKey(name: 'fragrance_id') int fragranceId,
      CollectionFragrance fragrance,
      @JsonKey(name: 'is_favorite') bool isFavorite,
      @JsonKey(name: 'personal_notes') String? personalNotes,
      @JsonKey(name: 'acquired_date') DateTime? acquiredDate,
      @JsonKey(name: 'created_at') DateTime createdAt});

  $CollectionFragranceCopyWith<$Res> get fragrance;
}

/// @nodoc
class _$CollectionItemCopyWithImpl<$Res, $Val extends CollectionItem>
    implements $CollectionItemCopyWith<$Res> {
  _$CollectionItemCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of CollectionItem
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? fragranceId = null,
    Object? fragrance = null,
    Object? isFavorite = null,
    Object? personalNotes = freezed,
    Object? acquiredDate = freezed,
    Object? createdAt = null,
  }) {
    return _then(_value.copyWith(
      fragranceId: null == fragranceId
          ? _value.fragranceId
          : fragranceId // ignore: cast_nullable_to_non_nullable
              as int,
      fragrance: null == fragrance
          ? _value.fragrance
          : fragrance // ignore: cast_nullable_to_non_nullable
              as CollectionFragrance,
      isFavorite: null == isFavorite
          ? _value.isFavorite
          : isFavorite // ignore: cast_nullable_to_non_nullable
              as bool,
      personalNotes: freezed == personalNotes
          ? _value.personalNotes
          : personalNotes // ignore: cast_nullable_to_non_nullable
              as String?,
      acquiredDate: freezed == acquiredDate
          ? _value.acquiredDate
          : acquiredDate // ignore: cast_nullable_to_non_nullable
              as DateTime?,
      createdAt: null == createdAt
          ? _value.createdAt
          : createdAt // ignore: cast_nullable_to_non_nullable
              as DateTime,
    ) as $Val);
  }

  /// Create a copy of CollectionItem
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $CollectionFragranceCopyWith<$Res> get fragrance {
    return $CollectionFragranceCopyWith<$Res>(_value.fragrance, (value) {
      return _then(_value.copyWith(fragrance: value) as $Val);
    });
  }
}

/// @nodoc
abstract class _$$CollectionItemImplCopyWith<$Res>
    implements $CollectionItemCopyWith<$Res> {
  factory _$$CollectionItemImplCopyWith(_$CollectionItemImpl value,
          $Res Function(_$CollectionItemImpl) then) =
      __$$CollectionItemImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {@JsonKey(name: 'fragrance_id') int fragranceId,
      CollectionFragrance fragrance,
      @JsonKey(name: 'is_favorite') bool isFavorite,
      @JsonKey(name: 'personal_notes') String? personalNotes,
      @JsonKey(name: 'acquired_date') DateTime? acquiredDate,
      @JsonKey(name: 'created_at') DateTime createdAt});

  @override
  $CollectionFragranceCopyWith<$Res> get fragrance;
}

/// @nodoc
class __$$CollectionItemImplCopyWithImpl<$Res>
    extends _$CollectionItemCopyWithImpl<$Res, _$CollectionItemImpl>
    implements _$$CollectionItemImplCopyWith<$Res> {
  __$$CollectionItemImplCopyWithImpl(
      _$CollectionItemImpl _value, $Res Function(_$CollectionItemImpl) _then)
      : super(_value, _then);

  /// Create a copy of CollectionItem
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? fragranceId = null,
    Object? fragrance = null,
    Object? isFavorite = null,
    Object? personalNotes = freezed,
    Object? acquiredDate = freezed,
    Object? createdAt = null,
  }) {
    return _then(_$CollectionItemImpl(
      fragranceId: null == fragranceId
          ? _value.fragranceId
          : fragranceId // ignore: cast_nullable_to_non_nullable
              as int,
      fragrance: null == fragrance
          ? _value.fragrance
          : fragrance // ignore: cast_nullable_to_non_nullable
              as CollectionFragrance,
      isFavorite: null == isFavorite
          ? _value.isFavorite
          : isFavorite // ignore: cast_nullable_to_non_nullable
              as bool,
      personalNotes: freezed == personalNotes
          ? _value.personalNotes
          : personalNotes // ignore: cast_nullable_to_non_nullable
              as String?,
      acquiredDate: freezed == acquiredDate
          ? _value.acquiredDate
          : acquiredDate // ignore: cast_nullable_to_non_nullable
              as DateTime?,
      createdAt: null == createdAt
          ? _value.createdAt
          : createdAt // ignore: cast_nullable_to_non_nullable
              as DateTime,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$CollectionItemImpl implements _CollectionItem {
  const _$CollectionItemImpl(
      {@JsonKey(name: 'fragrance_id') required this.fragranceId,
      required this.fragrance,
      @JsonKey(name: 'is_favorite') required this.isFavorite,
      @JsonKey(name: 'personal_notes') this.personalNotes,
      @JsonKey(name: 'acquired_date') this.acquiredDate,
      @JsonKey(name: 'created_at') required this.createdAt});

  factory _$CollectionItemImpl.fromJson(Map<String, dynamic> json) =>
      _$$CollectionItemImplFromJson(json);

  @override
  @JsonKey(name: 'fragrance_id')
  final int fragranceId;
  @override
  final CollectionFragrance fragrance;
  @override
  @JsonKey(name: 'is_favorite')
  final bool isFavorite;

  /// Optional personal note (e.g. "Gift from mum, summer 2024").
  @override
  @JsonKey(name: 'personal_notes')
  final String? personalNotes;

  /// Date the bottle was acquired. Null if not recorded.
  @override
  @JsonKey(name: 'acquired_date')
  final DateTime? acquiredDate;
  @override
  @JsonKey(name: 'created_at')
  final DateTime createdAt;

  @override
  String toString() {
    return 'CollectionItem(fragranceId: $fragranceId, fragrance: $fragrance, isFavorite: $isFavorite, personalNotes: $personalNotes, acquiredDate: $acquiredDate, createdAt: $createdAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$CollectionItemImpl &&
            (identical(other.fragranceId, fragranceId) ||
                other.fragranceId == fragranceId) &&
            (identical(other.fragrance, fragrance) ||
                other.fragrance == fragrance) &&
            (identical(other.isFavorite, isFavorite) ||
                other.isFavorite == isFavorite) &&
            (identical(other.personalNotes, personalNotes) ||
                other.personalNotes == personalNotes) &&
            (identical(other.acquiredDate, acquiredDate) ||
                other.acquiredDate == acquiredDate) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(runtimeType, fragranceId, fragrance,
      isFavorite, personalNotes, acquiredDate, createdAt);

  /// Create a copy of CollectionItem
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$CollectionItemImplCopyWith<_$CollectionItemImpl> get copyWith =>
      __$$CollectionItemImplCopyWithImpl<_$CollectionItemImpl>(
          this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$CollectionItemImplToJson(
      this,
    );
  }
}

abstract class _CollectionItem implements CollectionItem {
  const factory _CollectionItem(
          {@JsonKey(name: 'fragrance_id') required final int fragranceId,
          required final CollectionFragrance fragrance,
          @JsonKey(name: 'is_favorite') required final bool isFavorite,
          @JsonKey(name: 'personal_notes') final String? personalNotes,
          @JsonKey(name: 'acquired_date') final DateTime? acquiredDate,
          @JsonKey(name: 'created_at') required final DateTime createdAt}) =
      _$CollectionItemImpl;

  factory _CollectionItem.fromJson(Map<String, dynamic> json) =
      _$CollectionItemImpl.fromJson;

  @override
  @JsonKey(name: 'fragrance_id')
  int get fragranceId;
  @override
  CollectionFragrance get fragrance;
  @override
  @JsonKey(name: 'is_favorite')
  bool get isFavorite;

  /// Optional personal note (e.g. "Gift from mum, summer 2024").
  @override
  @JsonKey(name: 'personal_notes')
  String? get personalNotes;

  /// Date the bottle was acquired. Null if not recorded.
  @override
  @JsonKey(name: 'acquired_date')
  DateTime? get acquiredDate;
  @override
  @JsonKey(name: 'created_at')
  DateTime get createdAt;

  /// Create a copy of CollectionItem
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$CollectionItemImplCopyWith<_$CollectionItemImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

CollectionFragrance _$CollectionFragranceFromJson(Map<String, dynamic> json) {
  return _CollectionFragrance.fromJson(json);
}

/// @nodoc
mixin _$CollectionFragrance {
  int get id => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  String get brand => throw _privateConstructorUsedError;

  /// Relative path on Laravel Storage. Prefix with the server origin to form
  /// a full URL: '${ApiEndpoints.storageBaseUrl}/${item.cachedImagePath}'
  @JsonKey(name: 'cached_image_path')
  String? get cachedImagePath => throw _privateConstructorUsedError;

  /// Original CDN URL as fallback if [cachedImagePath] is null.
  @JsonKey(name: 'remote_image_url')
  String? get remoteImageUrl => throw _privateConstructorUsedError;

  /// Serializes this CollectionFragrance to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of CollectionFragrance
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $CollectionFragranceCopyWith<CollectionFragrance> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $CollectionFragranceCopyWith<$Res> {
  factory $CollectionFragranceCopyWith(
          CollectionFragrance value, $Res Function(CollectionFragrance) then) =
      _$CollectionFragranceCopyWithImpl<$Res, CollectionFragrance>;
  @useResult
  $Res call(
      {int id,
      String name,
      String brand,
      @JsonKey(name: 'cached_image_path') String? cachedImagePath,
      @JsonKey(name: 'remote_image_url') String? remoteImageUrl});
}

/// @nodoc
class _$CollectionFragranceCopyWithImpl<$Res, $Val extends CollectionFragrance>
    implements $CollectionFragranceCopyWith<$Res> {
  _$CollectionFragranceCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of CollectionFragrance
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? brand = null,
    Object? cachedImagePath = freezed,
    Object? remoteImageUrl = freezed,
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
      cachedImagePath: freezed == cachedImagePath
          ? _value.cachedImagePath
          : cachedImagePath // ignore: cast_nullable_to_non_nullable
              as String?,
      remoteImageUrl: freezed == remoteImageUrl
          ? _value.remoteImageUrl
          : remoteImageUrl // ignore: cast_nullable_to_non_nullable
              as String?,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$CollectionFragranceImplCopyWith<$Res>
    implements $CollectionFragranceCopyWith<$Res> {
  factory _$$CollectionFragranceImplCopyWith(_$CollectionFragranceImpl value,
          $Res Function(_$CollectionFragranceImpl) then) =
      __$$CollectionFragranceImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {int id,
      String name,
      String brand,
      @JsonKey(name: 'cached_image_path') String? cachedImagePath,
      @JsonKey(name: 'remote_image_url') String? remoteImageUrl});
}

/// @nodoc
class __$$CollectionFragranceImplCopyWithImpl<$Res>
    extends _$CollectionFragranceCopyWithImpl<$Res, _$CollectionFragranceImpl>
    implements _$$CollectionFragranceImplCopyWith<$Res> {
  __$$CollectionFragranceImplCopyWithImpl(_$CollectionFragranceImpl _value,
      $Res Function(_$CollectionFragranceImpl) _then)
      : super(_value, _then);

  /// Create a copy of CollectionFragrance
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? brand = null,
    Object? cachedImagePath = freezed,
    Object? remoteImageUrl = freezed,
  }) {
    return _then(_$CollectionFragranceImpl(
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
      cachedImagePath: freezed == cachedImagePath
          ? _value.cachedImagePath
          : cachedImagePath // ignore: cast_nullable_to_non_nullable
              as String?,
      remoteImageUrl: freezed == remoteImageUrl
          ? _value.remoteImageUrl
          : remoteImageUrl // ignore: cast_nullable_to_non_nullable
              as String?,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$CollectionFragranceImpl implements _CollectionFragrance {
  const _$CollectionFragranceImpl(
      {required this.id,
      required this.name,
      required this.brand,
      @JsonKey(name: 'cached_image_path') this.cachedImagePath,
      @JsonKey(name: 'remote_image_url') this.remoteImageUrl});

  factory _$CollectionFragranceImpl.fromJson(Map<String, dynamic> json) =>
      _$$CollectionFragranceImplFromJson(json);

  @override
  final int id;
  @override
  final String name;
  @override
  final String brand;

  /// Relative path on Laravel Storage. Prefix with the server origin to form
  /// a full URL: '${ApiEndpoints.storageBaseUrl}/${item.cachedImagePath}'
  @override
  @JsonKey(name: 'cached_image_path')
  final String? cachedImagePath;

  /// Original CDN URL as fallback if [cachedImagePath] is null.
  @override
  @JsonKey(name: 'remote_image_url')
  final String? remoteImageUrl;

  @override
  String toString() {
    return 'CollectionFragrance(id: $id, name: $name, brand: $brand, cachedImagePath: $cachedImagePath, remoteImageUrl: $remoteImageUrl)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$CollectionFragranceImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.brand, brand) || other.brand == brand) &&
            (identical(other.cachedImagePath, cachedImagePath) ||
                other.cachedImagePath == cachedImagePath) &&
            (identical(other.remoteImageUrl, remoteImageUrl) ||
                other.remoteImageUrl == remoteImageUrl));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType, id, name, brand, cachedImagePath, remoteImageUrl);

  /// Create a copy of CollectionFragrance
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$CollectionFragranceImplCopyWith<_$CollectionFragranceImpl> get copyWith =>
      __$$CollectionFragranceImplCopyWithImpl<_$CollectionFragranceImpl>(
          this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$CollectionFragranceImplToJson(
      this,
    );
  }
}

abstract class _CollectionFragrance implements CollectionFragrance {
  const factory _CollectionFragrance(
          {required final int id,
          required final String name,
          required final String brand,
          @JsonKey(name: 'cached_image_path') final String? cachedImagePath,
          @JsonKey(name: 'remote_image_url') final String? remoteImageUrl}) =
      _$CollectionFragranceImpl;

  factory _CollectionFragrance.fromJson(Map<String, dynamic> json) =
      _$CollectionFragranceImpl.fromJson;

  @override
  int get id;
  @override
  String get name;
  @override
  String get brand;

  /// Relative path on Laravel Storage. Prefix with the server origin to form
  /// a full URL: '${ApiEndpoints.storageBaseUrl}/${item.cachedImagePath}'
  @override
  @JsonKey(name: 'cached_image_path')
  String? get cachedImagePath;

  /// Original CDN URL as fallback if [cachedImagePath] is null.
  @override
  @JsonKey(name: 'remote_image_url')
  String? get remoteImageUrl;

  /// Create a copy of CollectionFragrance
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$CollectionFragranceImplCopyWith<_$CollectionFragranceImpl> get copyWith =>
      throw _privateConstructorUsedError;
}
