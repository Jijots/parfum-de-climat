/// Riverpod providers for the core infrastructure layer.
/// Providers here have no feature-layer imports — they are the foundation
/// that feature providers build on top of.  Import order is acyclic:
///   secure_storage  ←  api_client  ←  core_providers  ←  feature providers
/// [AuthChangeNotifier]
/// A [ChangeNotifier] used as GoRouter's [refreshListenable].
/// When the [AuthInterceptor] receives a 401 it deletes the stored token and
/// calls [AuthChangeNotifier.notifyLogout].  The GoRouter then re-evaluates
/// its redirect callback, finds no token, and navigates to the login screen.
/// This decouples the HTTP layer from navigation without any circular imports.

import 'package:flutter/foundation.dart' show ChangeNotifier;
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../api/api_client.dart';
import '../storage/secure_storage.dart';

// Providers

/// Singleton [SecureStorage] for the lifetime of the app.
final secureStorageProvider = Provider<SecureStorage>(
  (ref) => SecureStorage(),
);

/// [ChangeNotifier] that signals GoRouter to re-evaluate redirect on logout.
/// keepAlive is implicit for Provider — this is never disposed.
final authChangeNotifierProvider = Provider<AuthChangeNotifier>(
  (ref) => AuthChangeNotifier(),
);

/// Configured [ApiClient] shared by all repositories.
/// The [onUnauthenticated] callback is called by [AuthInterceptor] on 401.
/// It notifies [authChangeNotifier] → GoRouter refreshes → redirect to login.
final apiClientProvider = Provider<ApiClient>((ref) {
  final storage = ref.watch(secureStorageProvider);
  final authChangeNotifier = ref.watch(authChangeNotifierProvider);

  return ApiClient(
    storage: storage,
    onUnauthenticated: authChangeNotifier.notifyLogout,
  );
});

// AuthChangeNotifier

/// Thin [ChangeNotifier] used exclusively to trigger GoRouter refreshes.
/// Usage in router setup:
///   GoRouter(
///     refreshListenable: ref.watch(authChangeNotifierProvider),
///     redirect: (context, state) async { ... },
///   )
class AuthChangeNotifier extends ChangeNotifier {
  /// Called by [AuthInterceptor] on HTTP 401.
  /// Triggers GoRouter's redirect evaluation.
  void notifyLogout() => notifyListeners();

  /// Called after a successful login or token validation.
  /// Triggers GoRouter to re-evaluate (and stop redirecting to login).
  void notifyLogin() => notifyListeners();
}
