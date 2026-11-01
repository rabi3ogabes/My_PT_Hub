import 'package:flutter/material.dart';

import 'app_tokens.dart';
import 'au_typography.dart';

/// Aurum design tokens resolved for one brightness, exposed as a
/// [ThemeExtension]. Read it with `context.au`.
@immutable
class AuTheme extends ThemeExtension<AuTheme> {
  const AuTheme({
    required this.brightness,
    required this.background,
    required this.surface,
    required this.surfaceRaised,
    required this.surfaceSunken,
    required this.border,
    required this.borderStrong,
    required this.textPrimary,
    required this.textSecondary,
    required this.textMuted,
    required this.overlay,
    required this.accent,
    required this.accentLight,
    required this.accentDark,
    required this.onAccent,
    required this.success,
    required this.successSoft,
    required this.warning,
    required this.warningSoft,
    required this.danger,
    required this.dangerSoft,
    required this.info,
    required this.infoSoft,
    required this.protein,
    required this.carbs,
    required this.fat,
    required this.kcal,
    required this.text,
  });

  /// Dark theme (the signature look). [accent] lets white-label tenants swap
  /// the gold for their brand colour.
  factory AuTheme.dark({Color? accent, bool arabic = false}) => AuTheme(
        brightness: Brightness.dark,
        background: AuDarkColors.background,
        surface: AuDarkColors.surface,
        surfaceRaised: AuDarkColors.surfaceRaised,
        surfaceSunken: AuDarkColors.surfaceSunken,
        border: AuDarkColors.border,
        borderStrong: AuDarkColors.borderStrong,
        textPrimary: AuDarkColors.textPrimary,
        textSecondary: AuDarkColors.textSecondary,
        textMuted: AuDarkColors.textMuted,
        overlay: AuDarkColors.overlay,
        accent: accent ?? AuBrandColors.gold,
        accentLight: accent == null ? AuBrandColors.goldLight : Color.lerp(accent, Colors.white, 0.35)!,
        accentDark: accent == null ? AuBrandColors.goldDark : Color.lerp(accent, Colors.black, 0.25)!,
        onAccent: accent == null ? AuBrandColors.onGold : _onColor(accent),
        success: AuSemanticColors.success,
        successSoft: AuSemanticColors.successSoft,
        warning: AuSemanticColors.warning,
        warningSoft: AuSemanticColors.warningSoft,
        danger: AuSemanticColors.danger,
        dangerSoft: AuSemanticColors.dangerSoft,
        info: AuSemanticColors.info,
        infoSoft: AuSemanticColors.infoSoft,
        protein: AuMacroColors.protein,
        carbs: AuMacroColors.carbs,
        fat: AuMacroColors.fat,
        kcal: AuMacroColors.kcal,
        text: AuTextStyles.build(color: AuDarkColors.textPrimary, arabic: arabic),
      );

  factory AuTheme.light({Color? accent, bool arabic = false}) => AuTheme(
        brightness: Brightness.light,
        background: AuLightColors.background,
        surface: AuLightColors.surface,
        surfaceRaised: AuLightColors.surfaceRaised,
        surfaceSunken: AuLightColors.surfaceSunken,
        border: AuLightColors.border,
        borderStrong: AuLightColors.borderStrong,
        textPrimary: AuLightColors.textPrimary,
        textSecondary: AuLightColors.textSecondary,
        textMuted: AuLightColors.textMuted,
        overlay: AuLightColors.overlay,
        // Gold on warm white needs the deeper shade for AA contrast on text.
        accent: accent ?? AuBrandColors.goldDark,
        accentLight: accent == null ? AuBrandColors.gold : Color.lerp(accent, Colors.white, 0.35)!,
        accentDark: accent == null ? const Color(0xFF8A6A2E) : Color.lerp(accent, Colors.black, 0.25)!,
        onAccent: accent == null ? AuBrandColors.onGold : _onColor(accent),
        success: AuSemanticColors.success,
        successSoft: AuSemanticColors.successSoft,
        warning: AuSemanticColors.warning,
        warningSoft: AuSemanticColors.warningSoft,
        danger: AuSemanticColors.danger,
        dangerSoft: AuSemanticColors.dangerSoft,
        info: AuSemanticColors.info,
        infoSoft: AuSemanticColors.infoSoft,
        protein: AuMacroColors.protein,
        carbs: AuMacroColors.carbs,
        fat: AuMacroColors.fat,
        kcal: AuMacroColors.kcal,
        text: AuTextStyles.build(color: AuLightColors.textPrimary, arabic: arabic),
      );

  final Brightness brightness;
  final Color background;
  final Color surface;
  final Color surfaceRaised;
  final Color surfaceSunken;
  final Color border;
  final Color borderStrong;
  final Color textPrimary;
  final Color textSecondary;
  final Color textMuted;
  final Color overlay;
  final Color accent;
  final Color accentLight;
  final Color accentDark;
  final Color onAccent;
  final Color success;
  final Color successSoft;
  final Color warning;
  final Color warningSoft;
  final Color danger;
  final Color dangerSoft;
  final Color info;
  final Color infoSoft;
  final Color protein;
  final Color carbs;
  final Color fat;
  final Color kcal;
  final AuTextStyles text;

  bool get isDark => brightness == Brightness.dark;

  /// Gold sheen used on hero surfaces and the primary button.
  LinearGradient get goldGradient => LinearGradient(
        begin: AlignmentDirectional.topStart,
        end: AlignmentDirectional.bottomEnd,
        colors: [accentLight, accent, accentDark],
        stops: const [0, 0.55, 1],
      );

  List<BoxShadow> get elevation1 => [
        BoxShadow(color: const Color(0xFF0B0B0F).withValues(alpha: isDark ? 0.4 : 0.06), blurRadius: 2, offset: const Offset(0, 1)),
        BoxShadow(color: const Color(0xFF0B0B0F).withValues(alpha: isDark ? 0.3 : 0.08), blurRadius: 3, offset: const Offset(0, 1)),
      ];

  List<BoxShadow> get elevation2 => [
        BoxShadow(color: const Color(0xFF0B0B0F).withValues(alpha: isDark ? 0.5 : 0.08), blurRadius: 12, offset: const Offset(0, 4)),
        BoxShadow(color: const Color(0xFF0B0B0F).withValues(alpha: isDark ? 0.3 : 0.06), blurRadius: 4, offset: const Offset(0, 2)),
      ];

  List<BoxShadow> get elevation3 => [
        BoxShadow(color: const Color(0xFF0B0B0F).withValues(alpha: isDark ? 0.6 : 0.14), blurRadius: 32, offset: const Offset(0, 12)),
        BoxShadow(color: const Color(0xFF0B0B0F).withValues(alpha: isDark ? 0.35 : 0.08), blurRadius: 8, offset: const Offset(0, 4)),
      ];

  List<BoxShadow> get glow => [
        BoxShadow(color: accent.withValues(alpha: 0.35), spreadRadius: 1),
        BoxShadow(color: accent.withValues(alpha: 0.18), blurRadius: 30, offset: const Offset(0, 8)),
      ];

  @override
  AuTheme copyWith({Color? accent, AuTextStyles? text}) => AuTheme(
        brightness: brightness,
        background: background,
        surface: surface,
        surfaceRaised: surfaceRaised,
        surfaceSunken: surfaceSunken,
        border: border,
        borderStrong: borderStrong,
        textPrimary: textPrimary,
        textSecondary: textSecondary,
        textMuted: textMuted,
        overlay: overlay,
        accent: accent ?? this.accent,
        accentLight: accentLight,
        accentDark: accentDark,
        onAccent: onAccent,
        success: success,
        successSoft: successSoft,
        warning: warning,
        warningSoft: warningSoft,
        danger: danger,
        dangerSoft: dangerSoft,
        info: info,
        infoSoft: infoSoft,
        protein: protein,
        carbs: carbs,
        fat: fat,
        kcal: kcal,
        text: text ?? this.text,
      );

  @override
  AuTheme lerp(covariant AuTheme? other, double t) {
    if (other == null) return this;
    Color c(Color a, Color b) => Color.lerp(a, b, t)!;
    return AuTheme(
      brightness: t < 0.5 ? brightness : other.brightness,
      background: c(background, other.background),
      surface: c(surface, other.surface),
      surfaceRaised: c(surfaceRaised, other.surfaceRaised),
      surfaceSunken: c(surfaceSunken, other.surfaceSunken),
      border: c(border, other.border),
      borderStrong: c(borderStrong, other.borderStrong),
      textPrimary: c(textPrimary, other.textPrimary),
      textSecondary: c(textSecondary, other.textSecondary),
      textMuted: c(textMuted, other.textMuted),
      overlay: c(overlay, other.overlay),
      accent: c(accent, other.accent),
      accentLight: c(accentLight, other.accentLight),
      accentDark: c(accentDark, other.accentDark),
      onAccent: c(onAccent, other.onAccent),
      success: c(success, other.success),
      successSoft: c(successSoft, other.successSoft),
      warning: c(warning, other.warning),
      warningSoft: c(warningSoft, other.warningSoft),
      danger: c(danger, other.danger),
      dangerSoft: c(dangerSoft, other.dangerSoft),
      info: c(info, other.info),
      infoSoft: c(infoSoft, other.infoSoft),
      protein: c(protein, other.protein),
      carbs: c(carbs, other.carbs),
      fat: c(fat, other.fat),
      kcal: c(kcal, other.kcal),
      text: AuTextStyles.lerp(text, other.text, t),
    );
  }

  static Color _onColor(Color background) =>
      ThemeData.estimateBrightnessForColor(background) == Brightness.dark ? Colors.white : AuBrandColors.onGold;

  /// Parses `#RRGGBB` brand colours from the API (tenant.brand_color).
  static Color? parseHex(String? hex) {
    if (hex == null) return null;
    final clean = hex.replaceAll('#', '');
    if (clean.length != 6) return null;
    final value = int.tryParse(clean, radix: 16);
    return value == null ? null : Color(0xFF000000 | value);
  }
}

extension AuThemeContext on BuildContext {
  /// The active Aurum tokens.
  AuTheme get au => Theme.of(this).extension<AuTheme>() ?? AuTheme.dark();
}
