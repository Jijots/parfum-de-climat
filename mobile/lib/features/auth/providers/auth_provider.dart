/// Riverpod state management for authentication.
/// [AuthNotifier] is an [AsyncNotifier<User?>] with three possible states:
///   AsyncLoading  — app just launched, checking stored token with the server
///   AsyncData(User)   — authenticated, user profile loaded
///   AsyncData(null)   — not authenticated (or session expired)
///   AsyncError    — unexpected error during token check / login
/// The router reads [authNotifierProvider] in its `redirect` callback:
///   - AsyncData(null) or AsyncError → redirect to /auth/login
///   - AsyncData(User) → allow navigation
///   - AsyncLoading → show splash screen (router yields, GoRouter waits)
/// After login/logout, [AuthChangeNotifier.notifyLogin/notifyLogout] triggers
/// GoRouter to re-evaluate its redirect, so navigation is immediate.

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/providers/core_providers.dart';
import '../data/auth_repository.dart';
import '../data/models/user.dart';

// Repository provider

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(
    apiClient: ref.watch(apiClientProvider),
    storage: ref.watch(secureStorageProvider),
  );
});

// Notifier

/// Manages the [User?] auth state and exposes login / logout / register actions.
/// Extends [AsyncNotifier] (not AutoDisposeAsyncNotifier) because auth state
/// must persist for the full app lifetime — it should never be disposed.
class AuthNotifier extends AsyncNotifier<User?> {
  @override
  Future<User?> build() async {
    // On startup: check if a stored token is still valid by calling /auth/me.
    // This runs once when the provider is first watched (app launch).
    final repository = ref.watch(authRepositoryProvider);
    final hasToken = await repository.isLoggedIn;

    if (!hasToken) return null;

    // Validate the token with the server. Returns null on 401 (token expired).
    return repository.getMe();
  }

  // Actions

  /// Authenticate with [email] + [password].
  /// Updates state to [AsyncData(User)] on success.
  /// Notifies the router so it stops redirecting to login.
  /// Re-throws typed [AppException] so the UI can display field-level errors.
  Future<void> login({
    required String email,
    required String password,
  }) async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      final user = await ref.read(authRepositoryProvider).login(
            email: email,
            password: password,
          );
      ref.read(authChangeNotifierProvider).notifyLogin();
      return user;
    });
  }

  /// Register a new account.
  /// Logs the user in immediately on success (token is stored by repository).
  Future<void> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      final user = await ref.read(authRepositoryProvider).register(
            name: name,
            email: email,
            password: password,
            passwordConfirmation: passwordConfirmation,
          );
      ref.read(authChangeNotifierProvider).notifyLogin();
      return user;
    });
  }

  /// Revoke the Sanctum token and clear local state.
  /// Notifies the router so it immediately redirects to the login screen.
  Future<void> logout() async {
    await ref.read(authRepositoryProvider).logout();
    state = const AsyncData(null);
    ref.read(authChangeNotifierProvider).notifyLogout();
  }

  // Convenience getters

  /// The authenticated user, or null if not logged in.
  User? get currentUser => state.valueOrNull;

  /// True if the user is authenticated and their profile is loaded.
  bool get isAuthenticated => currentUser != null;

  /// True if the authenticated user has the 'admin' role.
  bool get isAdmin => currentUser?.role == 'admin';
}

// Provider

/// The single source of truth for authentication state.
/// The single source of truth for authentication state.
/// Not autoDisposed — auth state must persist for the full app lifetime.
final authNotifierProvider = AsyncNotifierProvider<AuthNotifier, User?>(
  AuthNotifier.new,
);
