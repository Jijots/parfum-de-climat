/// GoRouter configuration with auth-guard redirect.
/// Route tree:
///   /auth/login      → LoginScreen
///   /auth/register   → RegisterScreen
///   /                → ShellRoute (bottom navigation)
///     /recommend     → RecommendScreen   (tab 0 — "Today")
///     /wardrobe      → WardrobeScreen    (tab 1 — "Wardrobe")
///     /browse        → BrowseScreen      (tab 2 — "Browse")
///     /history       → HistoryScreen     (tab 3 — "History")
///   /fragrances/:id  → FragranceDetailScreen (full-screen, no shell)
/// Auth guard:
///   Unauthenticated users trying to reach any non-/auth route are redirected
///   to /auth/login.  Authenticated users hitting /auth/* are sent to /.
///   The guard is re-evaluated every time [AuthChangeNotifier] fires, which
///   happens after login, logout, and 401 responses from [AuthInterceptor].

import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../app/theme.dart';
import '../app/theme_provider.dart';
import '../core/providers/core_providers.dart';

// Screens
import '../screens/auth/login_screen.dart';
import '../screens/auth/register_screen.dart';
import '../screens/recommend_screen.dart';
import '../screens/wardrobe_screen.dart';
import '../screens/browse_screen.dart';
import '../screens/history_screen.dart';
import '../screens/fragrance_detail_screen.dart';

// Route paths

class AppRoutes {
  AppRoutes._();

  static const String login         = '/auth/login';
  static const String register      = '/auth/register';
  static const String recommend     = '/recommend';
  static const String wardrobe      = '/wardrobe';
  static const String browse        = '/browse';
  static const String history       = '/history';
  static const String fragranceDetail = '/fragrances/:id';

  static String fragranceDetailPath(int id) => '/fragrances/$id';
}

// Router provider

final routerProvider = Provider<GoRouter>((ref) {
  final authChangeNotifier = ref.watch(authChangeNotifierProvider);
  final storage = ref.watch(secureStorageProvider);

  return GoRouter(
    initialLocation: AppRoutes.recommend,
    debugLogDiagnostics: true,

    // Re-evaluate redirect whenever auth state changes (login/logout/401).
    refreshListenable: authChangeNotifier,

    // Auth guard
    redirect: (context, state) async {
      final isLoggedIn = await storage.hasToken();
      final isAuthRoute = state.matchedLocation.startsWith('/auth');

      if (!isLoggedIn && !isAuthRoute) {
        // Not logged in and trying to access a protected route.
        return AppRoutes.login;
      }

      if (isLoggedIn && isAuthRoute) {
        // Already logged in — don't show login/register screens.
        return AppRoutes.recommend;
      }

      return null; // No redirect needed.
    },

    routes: [
      // Auth routes
      GoRoute(
        path: AppRoutes.login,
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        path: AppRoutes.register,
        builder: (context, state) => const RegisterScreen(),
      ),

      // Main shell (bottom navigation)
      StatefulShellRoute.indexedStack(
        builder: (context, state, navigationShell) =>
            _AppShell(navigationShell: navigationShell),
        branches: [
          // Tab 0 — Today (recommendations)
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: AppRoutes.recommend,
                builder: (context, state) => const RecommendScreen(),
              ),
            ],
          ),
          // Tab 1 — Wardrobe
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: AppRoutes.wardrobe,
                builder: (context, state) => const WardrobeScreen(),
              ),
            ],
          ),
          // Tab 2 — Browse
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: AppRoutes.browse,
                builder: (context, state) => const BrowseScreen(),
              ),
            ],
          ),
          // Tab 3 — History
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: AppRoutes.history,
                builder: (context, state) => const HistoryScreen(),
              ),
            ],
          ),
        ],
      ),

      // Fragrance detail (full-screen, outside shell)
      GoRoute(
        path: AppRoutes.fragranceDetail,
        builder: (context, state) {
          final id = int.parse(state.pathParameters['id']!);
          return FragranceDetailScreen(id: id);
        },
      ),
    ],

    // Error page
    errorBuilder: (context, state) => Scaffold(
      body: Center(
        child: Text(
          'Page not found\n${state.error}',
          textAlign: TextAlign.center,
        ),
      ),
    ),
  );
});

// App shell

class _AppShell extends ConsumerWidget {
  const _AppShell({required this.navigationShell});
  final StatefulNavigationShell navigationShell;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      // extendBody lets content scroll under the translucent glass nav bar.
      extendBody: true,
      body: navigationShell,
      bottomNavigationBar: _GlassNavBar(shell: navigationShell, ref: ref),
    );
  }
}

// Glass bottom navigation bar

class _GlassNavBar extends StatelessWidget {
  const _GlassNavBar({required this.shell, required this.ref});
  final StatefulNavigationShell shell;
  final WidgetRef ref;

  static const _items = [
    _NavItem(icon: Icons.wb_sunny_outlined, activeIcon: Icons.wb_sunny,       label: 'Today'),
    _NavItem(icon: Icons.inventory_2_outlined, activeIcon: Icons.inventory_2, label: 'Wardrobe'),
    _NavItem(icon: Icons.search_outlined,   activeIcon: Icons.search,         label: 'Browse'),
    _NavItem(icon: Icons.history_outlined,  activeIcon: Icons.history,        label: 'History'),
  ];

  @override
  Widget build(BuildContext context) {
    final isDark = context.isDark;

    return ClipRect(
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 24, sigmaY: 24),
        child: Container(
          decoration: BoxDecoration(
            color: isDark
                ? Colors.white.withValues(alpha: 0.06)
                : Colors.white.withValues(alpha: 0.72),
            border: Border(
              top: BorderSide(
                color: isDark
                    ? Colors.white.withValues(alpha: 0.08)
                    : Colors.white.withValues(alpha: 0.50),
                width: 0.5,
              ),
            ),
          ),
          child: SafeArea(
            top: false,
            child: SizedBox(
              height: 60,
              child: Row(
                children: List.generate(_items.length, (i) {
                  final selected = shell.currentIndex == i;
                  final item     = _items[i];
                  return Expanded(
                    child: GestureDetector(
                      behavior: HitTestBehavior.opaque,
                      onTap:    () => shell.goBranch(i),
                      child:    AnimatedContainer(
                        duration: const Duration(milliseconds: 200),
                        child:    Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              selected ? item.activeIcon : item.icon,
                              size:  22,
                              color: selected
                                  ? AppColors.accent
                                  : context.muted,
                            ),
                            const SizedBox(height: 3),
                            Text(
                              item.label,
                              style: AppTextStyles.labelSmall(
                                selected ? AppColors.accent : context.muted,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  );
                }),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _NavItem {
  const _NavItem({
    required this.icon,
    required this.activeIcon,
    required this.label,
  });
  final IconData icon;
  final IconData activeIcon;
  final String   label;
}

// Theme toggle helper (used from screens)

/// Tap to cycle: system → light → dark → system.
class ThemeToggleButton extends ConsumerWidget {
  const ThemeToggleButton({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final mode = ref.watch(themeModeProvider);
    IconData icon;
    String tooltip;
    switch (mode) {
      case ThemeMode.light:
        icon    = Icons.light_mode_outlined;
        tooltip = 'Light mode';
      case ThemeMode.dark:
        icon    = Icons.dark_mode_outlined;
        tooltip = 'Dark mode';
      case ThemeMode.system:
        icon    = Icons.brightness_auto_outlined;
        tooltip = 'System mode';
    }

    return IconButton(
      icon:    Icon(icon, size: 20),
      tooltip: tooltip,
      color:   context.muted,
      onPressed: () {
        final next = switch (mode) {
          ThemeMode.system => ThemeMode.light,
          ThemeMode.light  => ThemeMode.dark,
          ThemeMode.dark   => ThemeMode.system,
        };
        ref.read(themeModeProvider.notifier).state = next;
      },
    );
  }
}
