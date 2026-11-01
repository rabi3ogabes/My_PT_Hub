import 'package:flutter/cupertino.dart' show CupertinoPageTransitionsBuilder;
import 'package:flutter/material.dart';

import 'app_tokens.dart';
import 'au_theme.dart';
import 'au_typography.dart';

/// Builds Material [ThemeData] from the Aurum tokens.
abstract final class AuThemeData {
  static ThemeData dark({Color? accent, Locale? locale}) =>
      build(AuTheme.dark(accent: accent, arabic: AuTypography.isArabic(locale)));

  static ThemeData light({Color? accent, Locale? locale}) =>
      build(AuTheme.light(accent: accent, arabic: AuTypography.isArabic(locale)));

  static ThemeData build(AuTheme au) {
    final t = au.text;
    final scheme = ColorScheme(
      brightness: au.brightness,
      primary: au.accent,
      onPrimary: au.onAccent,
      primaryContainer: au.accent.withValues(alpha: 0.16),
      onPrimaryContainer: au.textPrimary,
      secondary: au.accentLight,
      onSecondary: au.onAccent,
      error: au.danger,
      onError: Colors.white,
      surface: au.surface,
      onSurface: au.textPrimary,
      onSurfaceVariant: au.textSecondary,
      surfaceContainerLowest: au.background,
      surfaceContainerLow: au.surfaceSunken,
      surfaceContainer: au.surface,
      surfaceContainerHigh: au.surfaceRaised,
      surfaceContainerHighest: au.surfaceRaised,
      outline: au.borderStrong,
      outlineVariant: au.border,
      shadow: Colors.black,
      scrim: au.overlay,
      inverseSurface: au.textPrimary,
      onInverseSurface: au.background,
    );

    final textTheme = TextTheme(
      displayLarge: t.display,
      displayMedium: t.h1,
      displaySmall: t.h2,
      headlineLarge: t.h1,
      headlineMedium: t.h2,
      headlineSmall: t.h3,
      titleLarge: t.h3,
      titleMedium: t.title,
      titleSmall: t.label,
      bodyLarge: t.body,
      bodyMedium: t.bodySmall,
      bodySmall: t.caption.copyWith(color: au.textSecondary),
      labelLarge: t.label,
      labelMedium: t.caption,
      labelSmall: t.overline,
    );

    final radiusMd = BorderRadius.circular(AuRadius.md);

    return ThemeData(
      useMaterial3: true,
      brightness: au.brightness,
      colorScheme: scheme,
      scaffoldBackgroundColor: au.background,
      canvasColor: au.background,
      textTheme: textTheme,
      primaryTextTheme: textTheme,
      fontFamily: t.body.fontFamily,
      extensions: [au],
      splashFactory: InkSparkle.splashFactory,
      highlightColor: au.accent.withValues(alpha: 0.06),
      splashColor: au.accent.withValues(alpha: 0.10),
      dividerColor: au.border,
      materialTapTargetSize: MaterialTapTargetSize.padded,
      visualDensity: VisualDensity.standard,
      iconTheme: IconThemeData(color: au.textPrimary, size: 22),
      dividerTheme: DividerThemeData(color: au.border, thickness: 1, space: 1),
      appBarTheme: AppBarTheme(
        backgroundColor: au.background,
        surfaceTintColor: Colors.transparent,
        foregroundColor: au.textPrimary,
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: false,
        titleTextStyle: t.title,
      ),
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: au.surface,
        surfaceTintColor: Colors.transparent,
        indicatorColor: au.accent.withValues(alpha: 0.14),
        height: 68,
        elevation: 0,
        labelTextStyle: WidgetStateProperty.resolveWith(
          (states) => t.caption.copyWith(
            color: states.contains(WidgetState.selected) ? au.accent : au.textMuted,
            fontWeight: states.contains(WidgetState.selected) ? FontWeight.w700 : FontWeight.w500,
          ),
        ),
        iconTheme: WidgetStateProperty.resolveWith(
          (states) => IconThemeData(color: states.contains(WidgetState.selected) ? au.accent : au.textMuted, size: 24),
        ),
      ),
      cardTheme: CardThemeData(
        color: au.surface,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AuRadius.lg), side: BorderSide(color: au.border)),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: au.surfaceSunken,
        contentPadding: const EdgeInsetsDirectional.symmetric(horizontal: AuSpacing.s4, vertical: AuSpacing.s4),
        hintStyle: t.body.copyWith(color: au.textMuted),
        labelStyle: t.bodySmall.copyWith(color: au.textSecondary),
        floatingLabelStyle: t.label.copyWith(color: au.accent),
        errorStyle: t.caption.copyWith(color: au.danger),
        border: OutlineInputBorder(borderRadius: radiusMd, borderSide: BorderSide(color: au.border)),
        enabledBorder: OutlineInputBorder(borderRadius: radiusMd, borderSide: BorderSide(color: au.border)),
        focusedBorder: OutlineInputBorder(borderRadius: radiusMd, borderSide: BorderSide(color: au.accent, width: 1.5)),
        errorBorder: OutlineInputBorder(borderRadius: radiusMd, borderSide: BorderSide(color: au.danger)),
        focusedErrorBorder: OutlineInputBorder(borderRadius: radiusMd, borderSide: BorderSide(color: au.danger, width: 1.5)),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: au.accent,
          foregroundColor: au.onAccent,
          minimumSize: const Size(kAuTouchTarget, 52),
          textStyle: t.label.copyWith(fontSize: 15),
          shape: RoundedRectangleBorder(borderRadius: radiusMd),
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: au.accent,
          minimumSize: const Size(kAuTouchTarget, kAuTouchTarget),
          textStyle: t.label,
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: au.textPrimary,
          minimumSize: const Size(kAuTouchTarget, 52),
          side: BorderSide(color: au.borderStrong),
          shape: RoundedRectangleBorder(borderRadius: radiusMd),
          textStyle: t.label,
        ),
      ),
      iconButtonTheme: IconButtonThemeData(
        style: IconButton.styleFrom(minimumSize: const Size(kAuTouchTarget, kAuTouchTarget), foregroundColor: au.textPrimary),
      ),
      bottomSheetTheme: BottomSheetThemeData(
        backgroundColor: au.surfaceRaised,
        surfaceTintColor: Colors.transparent,
        modalBarrierColor: au.overlay,
        showDragHandle: false,
        shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(AuRadius.xl))),
      ),
      dialogTheme: DialogThemeData(
        backgroundColor: au.surfaceRaised,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AuRadius.xl)),
        titleTextStyle: t.h3,
        contentTextStyle: t.body.copyWith(color: au.textSecondary),
      ),
      snackBarTheme: SnackBarThemeData(
        behavior: SnackBarBehavior.floating,
        backgroundColor: au.surfaceRaised,
        contentTextStyle: t.bodySmall,
        shape: RoundedRectangleBorder(borderRadius: radiusMd),
      ),
      switchTheme: SwitchThemeData(
        thumbColor: WidgetStateProperty.resolveWith(
          (s) => s.contains(WidgetState.selected) ? au.onAccent : au.textMuted,
        ),
        trackColor: WidgetStateProperty.resolveWith(
          (s) => s.contains(WidgetState.selected) ? au.accent : au.surfaceSunken,
        ),
        trackOutlineColor: WidgetStateProperty.resolveWith(
          (s) => s.contains(WidgetState.selected) ? au.accent : au.borderStrong,
        ),
      ),
      checkboxTheme: CheckboxThemeData(
        fillColor: WidgetStateProperty.resolveWith((s) => s.contains(WidgetState.selected) ? au.accent : Colors.transparent),
        checkColor: WidgetStatePropertyAll(au.onAccent),
        side: BorderSide(color: au.borderStrong, width: 1.5),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AuRadius.xs)),
      ),
      progressIndicatorTheme: ProgressIndicatorThemeData(color: au.accent, linearTrackColor: au.surfaceSunken),
      sliderTheme: SliderThemeData(activeTrackColor: au.accent, thumbColor: au.accent, inactiveTrackColor: au.border),
      tabBarTheme: TabBarThemeData(
        labelColor: au.textPrimary,
        unselectedLabelColor: au.textMuted,
        labelStyle: t.label,
        indicatorColor: au.accent,
        dividerColor: au.border,
      ),
      pageTransitionsTheme: const PageTransitionsTheme(
        builders: {
          TargetPlatform.iOS: CupertinoPageTransitionsBuilder(),
          TargetPlatform.android: FadeForwardsPageTransitionsBuilder(),
        },
      ),
    );
  }
}
