/// Application entry point.
/// ThemeMode is driven by [themeModeProvider] (StateProvider of ThemeMode).
/// Defaults to [ThemeMode.system] — the user can toggle from the settings
/// icon in the app header.

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'app/router.dart';
import 'app/theme.dart';
import 'app/theme_provider.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();

  // Force portrait — the layout is optimised for portrait only.
  SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);

  // Transparent status bar — warm background shows through.
  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor:             Colors.transparent,
      statusBarIconBrightness:    Brightness.dark,
      statusBarBrightness:        Brightness.light,
    ),
  );

  runApp(const ProviderScope(child: ParfumDeClimatApp()));
}

class ParfumDeClimatApp extends ConsumerWidget {
  const ParfumDeClimatApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router    = ref.watch(routerProvider);
    final themeMode = ref.watch(themeModeProvider);

    // Update status bar icon brightness whenever theme changes.
    final isDark = themeMode == ThemeMode.dark ||
        (themeMode == ThemeMode.system &&
            MediaQuery.platformBrightnessOf(context) == Brightness.dark);

    SystemChrome.setSystemUIOverlayStyle(
      SystemUiOverlayStyle(
        statusBarColor:          Colors.transparent,
        statusBarIconBrightness: isDark ? Brightness.light : Brightness.dark,
        statusBarBrightness:     isDark ? Brightness.dark  : Brightness.light,
      ),
    );

    return MaterialApp.router(
      title:                   'Parfum de Climat',
      debugShowCheckedModeBanner: false,
      theme:                   AppTheme.light,
      darkTheme:               AppTheme.dark,
      themeMode:               themeMode,
      routerConfig:            router,
    );
  }
}
