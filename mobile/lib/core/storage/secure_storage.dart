/// Thin wrapper around [FlutterSecureStorage].
/// Tokens are stored in Android Keystore / iOS Keychain — they survive app
/// restarts but are wiped on uninstall and cannot be read by other apps.
/// All methods are async; the underlying platform channel is non-blocking.

import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class SecureStorage {
  SecureStorage() : _storage = const FlutterSecureStorage(
    // Android: use EncryptedSharedPreferences backed by Keystore.
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
    // iOS: item accessible only when device is unlocked.
    iOptions: IOSOptions(accessibility: KeychainAccessibility.first_unlock),
  );

  final FlutterSecureStorage _storage;

  static const _tokenKey = 'sanctum_token';

  // Auth token

  /// Persist the Sanctum bearer token after a successful login.
  Future<void> saveToken(String token) =>
      _storage.write(key: _tokenKey, value: token);

  /// Read the stored token, or null if the user is not logged in.
  Future<String?> readToken() => _storage.read(key: _tokenKey);

  /// Delete the token on logout or 401 response.
  Future<void> deleteToken() => _storage.delete(key: _tokenKey);

  /// True if a token is currently stored (does not validate with the server).
  Future<bool> hasToken() async => (await readToken()) != null;

  // Generic helpers

  /// Write an arbitrary string value. Prefer typed methods above for
  /// domain values — use this only for ad-hoc storage needs.
  Future<void> write(String key, String value) =>
      _storage.write(key: key, value: value);

  Future<String?> read(String key) => _storage.read(key: key);

  Future<void> delete(String key) => _storage.delete(key: key);

  /// Wipe all stored values — called on account deletion.
  Future<void> clear() => _storage.deleteAll();
}
