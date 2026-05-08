/// Immutable [User] model returned by GET /auth/me and POST /auth/login.
/// The `@freezed` macro generates:
///   - [copyWith] for non-destructive updates
///   - Structural [==] and [hashCode]
///   - A readable [toString]
/// Run `dart run build_runner build` to regenerate the *.freezed.dart and
/// *.g.dart files after any changes to this class.

import 'package:freezed_annotation/freezed_annotation.dart';

part 'user.freezed.dart';
part 'user.g.dart';

@freezed
class User with _$User {
  const factory User({
    required int id,
    required String name,
    required String email,

    /// 'user' or 'admin'. Admin users can access /admin/* routes.
    required String role,

    /// IANA timezone string (e.g. 'Asia/Manila').
    /// Used by the backend's WeatherService to derive the correct season
    /// for the user's hemisphere. Null if the user has not set a timezone.
    String? timezone,

    /// Relative path served by Laravel Storage (e.g. 'avatars/1.jpg').
    /// Prefix with ApiEndpoints.baseUrl's origin to form the full URL.
    String? avatarPath,

    @JsonKey(name: 'created_at') required DateTime createdAt,
  }) = _User;

  factory User.fromJson(Map<String, dynamic> json) => _$UserFromJson(json);
}
