/// Data layer for authentication.
/// All methods communicate with the Laravel Sanctum API.  They throw typed
/// [AppException] subtypes on failure — callers never see [DioException].
/// Token lifecycle:
///   login()    → stores token in [SecureStorage]
///   logout()   → deletes token from [SecureStorage]
///   getMe()    → reads the stored token (injected by [AuthInterceptor])
/// The repository is stateless — auth STATE lives in [AuthNotifier].

import '../../../core/api/api_client.dart';
import '../../../core/api/api_endpoints.dart';
import '../../../core/errors/app_exception.dart';
import '../../../core/storage/secure_storage.dart';
import 'models/user.dart';

class AuthRepository {
  const AuthRepository({
    required ApiClient apiClient,
    required SecureStorage storage,
  })  : _api = apiClient,
        _storage = storage;

  final ApiClient _api;
  final SecureStorage _storage;

  /// Register a new account.
  /// On success, stores the returned Sanctum token and returns the [User].
  /// Throws [ValidationException] for field errors (email taken, weak password).
  Future<User> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    final response = await _api.post<Map<String, dynamic>>(
      ApiEndpoints.register,
      data: {
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': passwordConfirmation,
      },
    );

    final body = response.data!;
    await _storage.saveToken(body['token'] as String);
    return User.fromJson(body['user'] as Map<String, dynamic>);
  }

  /// Authenticate with email + password.
  /// On success, stores the returned Sanctum token and returns the [User].
  /// Throws [UnauthorisedException] for wrong credentials.
  Future<User> login({
    required String email,
    required String password,
  }) async {
    final response = await _api.post<Map<String, dynamic>>(
      ApiEndpoints.login,
      data: {'email': email, 'password': password},
    );

    final body = response.data!;
    await _storage.saveToken(body['token'] as String);
    return User.fromJson(body['user'] as Map<String, dynamic>);
  }

  /// Fetch the currently authenticated user's profile.
  /// Used on app start to restore the session from a stored token.
  /// Returns null if the token has been revoked server-side (401 response).
  /// The [AuthInterceptor] will handle the 401 → token deletion automatically,
  /// so callers can simply treat a null return as "not logged in".
  Future<User?> getMe() async {
    try {
      final response = await _api.get<Map<String, dynamic>>(ApiEndpoints.me);
      return User.fromJson(response.data!);
    } on UnauthorisedException {
      // Token was revoked server-side — clear it and signal no user.
      await _storage.deleteToken();
      return null;
    }
  }

  /// Revoke the current Sanctum token server-side and clear local storage.
  /// Succeeds silently even if the server returns an error — the local token
  /// is always deleted regardless of the server response.
  Future<void> logout() async {
    try {
      await _api.post<void>(ApiEndpoints.logout);
    } catch (_) {
      // Best-effort: ignore server errors on logout (e.g. already expired).
    } finally {
      await _storage.deleteToken();
    }
  }

  /// True if a Sanctum token is stored locally.
  /// Does NOT validate the token with the server.
  Future<bool> get isLoggedIn => _storage.hasToken();
}
