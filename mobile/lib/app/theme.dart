/// Design language: Neumorphism-Liquid Glass, minimalist.
///   Neumorphism → interactive elements (buttons, inputs, stat cards).
///                 Elements appear extruded FROM the background.
///   Liquid Glass → floating panels (navigation bar, weather banner, modals).
///                  Elements appear to float ABOVE the background.
/// Colours mirror the web CSS design system exactly (app.css @layer base).
/// Shadow math — consistent 6/12 system:
///   Raised:  6px 6px 12px shadow-dark,  -6px -6px 12px shadow-light
///   Inset:   inset 4px 4px 8px shadow-dark, inset -4px -4px 8px shadow-light
/// Two ThemeExtensions carry neumorphic tokens through the widget tree:
///   [NeuTheme]   — shadow colours + card background colour
///   [GlassTheme] — glass panel background + border colours

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

// Palette

class AppColors {
  AppColors._();

  // Light palette
  static const Color bg          = Color(0xFFF0ECE7); // warm parchment
  static const Color ink         = Color(0xFF1A1A1A); // near-black
  static const Color muted       = Color(0xFF888580); // warm grey
  static const Color border      = Color(0xFFE0DBD5); // hairline
  static const Color surface     = Color(0xFFFFFFFF); // pure white

  // Dark palette
  static const Color bgDark      = Color(0xFF18161A); // deep warm dark
  static const Color inkDark     = Color(0xFFF0ECE7); // warm off-white
  static const Color mutedDark   = Color(0xFF6B6670); // dim warm grey
  static const Color borderDark  = Color(0xFF2C2A30); // subtle edge

  // Universal
  static const Color accent      = Color(0xFFC4A882); // wheat gold
  static const Color accentDim   = Color(0x1FC4A882); // accent @ 12%
  static const Color error       = Color(0xFFB94040); // red (light)
  static const Color errorDark   = Color(0xFFE05858); // red (dark)
}

// BuildContext extensions — always use these in screens, never raw AppColors

extension ThemeX on BuildContext {
  bool   get isDark    => Theme.of(this).brightness == Brightness.dark;
  Color  get bgColor   => isDark ? AppColors.bgDark   : AppColors.bg;
  Color  get inkColor  => isDark ? AppColors.inkDark  : AppColors.ink;
  Color  get muted     => isDark ? AppColors.mutedDark : AppColors.muted;
  Color  get errorColor => isDark ? AppColors.errorDark : AppColors.error;
  Color  get hairline  => isDark
      ? Colors.white.withValues(alpha: 0.08)
      : Colors.white.withValues(alpha: 0.40);

  NeuTheme   get neu   => Theme.of(this).extension<NeuTheme>()!;
  GlassTheme get glass => Theme.of(this).extension<GlassTheme>()!;
}

// NeuTheme — neumorphic shadow tokens

@immutable
class NeuTheme extends ThemeExtension<NeuTheme> {
  const NeuTheme({
    required this.shadowDark,
    required this.shadowLight,
    required this.cardColor,
  });

  /// Darker of the two shadows — cast bottom-right.
  final Color shadowDark;

  /// Lighter of the two shadows (highlight) — cast top-left.
  final Color shadowLight;

  /// Surface color of neumorphic elements.
  /// MUST equal the scaffold background for the effect to read correctly.
  final Color cardColor;

  // Pre-built instances

  static const light = NeuTheme(
    shadowDark:  Color(0xFFD0C9C2),
    shadowLight: Color(0xFFFFFFFF),
    cardColor:   Color(0xFFF0ECE7),
  );

  static const dark = NeuTheme(
    shadowDark:  Color(0xFF0E0C10),
    shadowLight: Color(0xFF222028),
    cardColor:   Color(0xFF18161A),
  );

  // Convenience shadow lists

  /// Standard raised neumorphic shadow (extruded from bg).
  List<BoxShadow> get raised => [
        BoxShadow(color: shadowDark,  offset: const Offset(6, 6),   blurRadius: 12),
        BoxShadow(color: shadowLight, offset: const Offset(-6, -6), blurRadius: 12),
      ];

  /// Pressed / inset neumorphic shadow (recessed into bg).
  List<BoxShadow> get inset => [
        BoxShadow(color: shadowDark,  offset: const Offset(4, 4),   blurRadius: 8,  spreadRadius: -1),
        BoxShadow(color: shadowLight, offset: const Offset(-4, -4), blurRadius: 8,  spreadRadius: -1),
      ];

  /// Subtle raised — for smaller elements (chips, badges).
  List<BoxShadow> get raisedSm => [
        BoxShadow(color: shadowDark,  offset: const Offset(3, 3),  blurRadius: 7),
        BoxShadow(color: shadowLight, offset: const Offset(-3, -3), blurRadius: 7),
      ];

  // ThemeExtension interface

  @override
  NeuTheme copyWith({Color? shadowDark, Color? shadowLight, Color? cardColor}) =>
      NeuTheme(
        shadowDark:  shadowDark  ?? this.shadowDark,
        shadowLight: shadowLight ?? this.shadowLight,
        cardColor:   cardColor   ?? this.cardColor,
      );

  @override
  NeuTheme lerp(NeuTheme? other, double t) {
    if (other == null) return this;
    return NeuTheme(
      shadowDark:  Color.lerp(shadowDark,  other.shadowDark,  t)!,
      shadowLight: Color.lerp(shadowLight, other.shadowLight, t)!,
      cardColor:   Color.lerp(cardColor,   other.cardColor,   t)!,
    );
  }
}

// GlassTheme — liquid glass surface tokens

@immutable
class GlassTheme extends ThemeExtension<GlassTheme> {
  const GlassTheme({required this.background, required this.border});

  /// Semi-transparent fill behind the blur.
  final Color background;

  /// Edge colour — typically a bright white/light highlight.
  final Color border;

  static const light = GlassTheme(
    background: Color(0xA6FFFFFF), // white @ 65%
    border:     Color(0x66FFFFFF), // white @ 40%
  );

  static const dark = GlassTheme(
    background: Color(0x0FFFFFFF), // white @ 6%
    border:     Color(0x14FFFFFF), // white @ 8%
  );

  @override
  GlassTheme copyWith({Color? background, Color? border}) =>
      GlassTheme(
        background: background ?? this.background,
        border:     border     ?? this.border,
      );

  @override
  GlassTheme lerp(GlassTheme? other, double t) {
    if (other == null) return this;
    return GlassTheme(
      background: Color.lerp(background, other.background, t)!,
      border:     Color.lerp(border,     other.border,     t)!,
    );
  }
}

// Text styles

class AppTextStyles {
  AppTextStyles._();

  // Display — Cormorant Garamond
  static TextStyle displayLarge([Color? color]) =>
      GoogleFonts.cormorantGaramond(
        fontSize: 36, fontWeight: FontWeight.w300, letterSpacing: -0.5,
        color: color,
      );

  static TextStyle displayMedium([Color? color]) =>
      GoogleFonts.cormorantGaramond(
        fontSize: 28, fontWeight: FontWeight.w300, letterSpacing: -0.3,
        color: color,
      );

  static TextStyle displaySmall([Color? color]) =>
      GoogleFonts.cormorantGaramond(
        fontSize: 22, fontWeight: FontWeight.w400,
        color: color,
      );

  // Body — Inter
  static TextStyle bodyLarge([Color? color]) =>
      GoogleFonts.inter(fontSize: 16, fontWeight: FontWeight.w400,
          height: 1.6, color: color);

  static TextStyle bodyMedium([Color? color]) =>
      GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.w400,
          height: 1.5, color: color);

  static TextStyle bodySmall([Color? color]) =>
      GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.w400,
          height: 1.4, color: color ?? AppColors.muted);

  // Labels — Inter
  static TextStyle labelLarge([Color? color]) =>
      GoogleFonts.inter(fontSize: 11, fontWeight: FontWeight.w600,
          letterSpacing: 1.2, color: color);

  static TextStyle labelSmall([Color? color]) =>
      GoogleFonts.inter(fontSize: 10, fontWeight: FontWeight.w500,
          letterSpacing: 0.8, color: color ?? AppColors.muted);
}

// AppTheme

class AppTheme {
  AppTheme._();

  static ThemeData _build({
    required Brightness brightness,
    required NeuTheme neu,
    required GlassTheme glass,
    required Color bg,
    required Color ink,
    required Color mutedColor,
    required Color errorColor,
    required Color borderColor,
  }) {
    final base = brightness == Brightness.light
        ? ThemeData.light(useMaterial3: true)
        : ThemeData.dark(useMaterial3: true);

    final textTheme = base.textTheme.copyWith(
      displayLarge:  AppTextStyles.displayLarge(ink),
      displayMedium: AppTextStyles.displayMedium(ink),
      displaySmall:  AppTextStyles.displaySmall(ink),
      bodyLarge:     AppTextStyles.bodyLarge(ink),
      bodyMedium:    AppTextStyles.bodyMedium(ink),
      bodySmall:     AppTextStyles.bodySmall(mutedColor),
      labelLarge:    AppTextStyles.labelLarge(ink),
      labelSmall:    AppTextStyles.labelSmall(mutedColor),
    );

    return base.copyWith(
      extensions: [neu, glass],
      scaffoldBackgroundColor: bg,

      colorScheme: brightness == Brightness.light
          ? ColorScheme.light(
              primary:    ink,
              onPrimary:  AppColors.surface,
              secondary:  AppColors.accent,
              onSecondary: AppColors.surface,
              error:      errorColor,
              surface:    bg,
              onSurface:  ink,
            )
          : ColorScheme.dark(
              primary:    AppColors.accent,
              onPrimary:  AppColors.bgDark,
              secondary:  AppColors.accent,
              onSecondary: AppColors.bgDark,
              error:      errorColor,
              surface:    AppColors.bgDark,
              onSurface:  AppColors.inkDark,
            ),

      textTheme: textTheme,

      appBarTheme: AppBarTheme(
        backgroundColor:       bg,
        foregroundColor:       ink,
        elevation:             0,
        scrolledUnderElevation: 0,
        centerTitle:           true,
        titleTextStyle:        AppTextStyles.displaySmall(ink),
        iconTheme:             IconThemeData(color: ink, size: 22),
        surfaceTintColor:      Colors.transparent,
      ),

      // The navigation bar is custom (glass) — this theme is a fallback.
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor:  Colors.transparent,
        indicatorColor:   AppColors.accentDim,
        shadowColor:      Colors.transparent,
        surfaceTintColor: Colors.transparent,
        labelTextStyle:   WidgetStateProperty.all(
          AppTextStyles.labelSmall(mutedColor),
        ),
        iconTheme: WidgetStateProperty.resolveWith((states) {
          final active = states.contains(WidgetState.selected);
          return IconThemeData(
            size:  22,
            color: active ? AppColors.accent : mutedColor,
          );
        }),
      ),

      cardTheme: CardThemeData(
        color:     neu.cardColor,
        elevation: 0,
        shape:     RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
        ),
        margin: EdgeInsets.zero,
      ),

      // Inputs default to neumorphic inset — see NeuTextField widget.
      inputDecorationTheme: InputDecorationTheme(
        filled:           true,
        fillColor:        bg,
        border:           OutlineInputBorder(
          borderSide:     BorderSide.none,
          borderRadius:   BorderRadius.circular(10),
        ),
        enabledBorder:    OutlineInputBorder(
          borderSide:     BorderSide.none,
          borderRadius:   BorderRadius.circular(10),
        ),
        focusedBorder:    OutlineInputBorder(
          borderSide:     const BorderSide(color: AppColors.accent, width: 1.5),
          borderRadius:   BorderRadius.circular(10),
        ),
        errorBorder:      OutlineInputBorder(
          borderSide:     BorderSide(color: errorColor),
          borderRadius:   BorderRadius.circular(10),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderSide:     BorderSide(color: errorColor, width: 1.5),
          borderRadius:   BorderRadius.circular(10),
        ),
        contentPadding:   const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        hintStyle:        AppTextStyles.bodyMedium(mutedColor),
        errorStyle:       AppTextStyles.bodySmall(errorColor),
      ),

      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ButtonStyle(
          backgroundColor: WidgetStateProperty.all(AppColors.accent),
          foregroundColor: WidgetStateProperty.all(const Color(0xFF1A1A1A)),
          overlayColor:    WidgetStateProperty.all(Colors.transparent),
          elevation:       WidgetStateProperty.all(0),
          padding:         WidgetStateProperty.all(
            const EdgeInsets.symmetric(horizontal: 32, vertical: 16),
          ),
          shape:           WidgetStateProperty.all(
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          ),
          textStyle: WidgetStateProperty.all(
            AppTextStyles.labelLarge(const Color(0xFF1A1A1A)),
          ),
        ),
      ),

      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: AppColors.accent,
          textStyle:       AppTextStyles.bodyMedium(),
          padding:         const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        ),
      ),

      dividerTheme: DividerThemeData(
        color:     borderColor,
        thickness: 0.5,
        space:     0,
      ),

      listTileTheme: ListTileThemeData(
        contentPadding:  const EdgeInsets.symmetric(horizontal: 20, vertical: 4),
        titleTextStyle:  AppTextStyles.bodyMedium(ink),
        subtitleTextStyle: AppTextStyles.bodySmall(mutedColor),
        iconColor:       mutedColor,
      ),

      bottomSheetTheme: BottomSheetThemeData(
        backgroundColor:   bg,
        surfaceTintColor:  Colors.transparent,
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
      ),

      dialogTheme: DialogThemeData(
        backgroundColor: bg,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        titleTextStyle: AppTextStyles.displaySmall(ink),
        contentTextStyle: AppTextStyles.bodyMedium(mutedColor),
      ),

      snackBarTheme: SnackBarThemeData(
        backgroundColor: ink,
        contentTextStyle: AppTextStyles.bodySmall(bg),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  static ThemeData get light => _build(
        brightness:  Brightness.light,
        neu:         NeuTheme.light,
        glass:       GlassTheme.light,
        bg:          AppColors.bg,
        ink:         AppColors.ink,
        mutedColor:  AppColors.muted,
        errorColor:  AppColors.error,
        borderColor: AppColors.border,
      );

  static ThemeData get dark => _build(
        brightness:  Brightness.dark,
        neu:         NeuTheme.dark,
        glass:       GlassTheme.dark,
        bg:          AppColors.bgDark,
        ink:         AppColors.inkDark,
        mutedColor:  AppColors.mutedDark,
        errorColor:  AppColors.errorDark,
        borderColor: AppColors.borderDark,
      );
}
