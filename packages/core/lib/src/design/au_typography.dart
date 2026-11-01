import 'package:flutter/widgets.dart';

import 'app_tokens.dart';

/// The token type scale as [TextStyle]s.
///
/// Latin text uses Manrope with IBM Plex Sans Arabic as fallback (so mixed
/// strings render correctly); Arabic locales flip the order. Arabic never
/// gets negative tracking (it breaks letter joining) and gets slightly more
/// line height for diacritics.
@immutable
class AuTextStyles {
  const AuTextStyles({
    required this.display,
    required this.h1,
    required this.h2,
    required this.h3,
    required this.title,
    required this.body,
    required this.bodySmall,
    required this.label,
    required this.caption,
    required this.overline,
    required this.numeric,
  });

  factory AuTextStyles.build({required Color color, bool arabic = false}) {
    TextStyle style(AuTypeSpec spec) => AuTypography.fromSpec(spec, color: color, arabic: arabic);
    return AuTextStyles(
      display: style(AuType.display),
      h1: style(AuType.h1),
      h2: style(AuType.h2),
      h3: style(AuType.h3),
      title: style(AuType.title),
      body: style(AuType.body),
      bodySmall: style(AuType.bodySmall),
      label: style(AuType.label),
      caption: style(AuType.caption),
      overline: style(AuType.overline),
      // Tabular figures for timers, weights and counters (no jitter).
      numeric: AuTypography.fromSpec(AuType.h2, color: color).copyWith(
        fontFeatures: const [FontFeature.tabularFigures()],
        letterSpacing: -0.5,
      ),
    );
  }

  final TextStyle display;
  final TextStyle h1;
  final TextStyle h2;
  final TextStyle h3;
  final TextStyle title;
  final TextStyle body;
  final TextStyle bodySmall;
  final TextStyle label;
  final TextStyle caption;
  final TextStyle overline;
  final TextStyle numeric;

  static AuTextStyles lerp(AuTextStyles a, AuTextStyles b, double t) => AuTextStyles(
        display: TextStyle.lerp(a.display, b.display, t)!,
        h1: TextStyle.lerp(a.h1, b.h1, t)!,
        h2: TextStyle.lerp(a.h2, b.h2, t)!,
        h3: TextStyle.lerp(a.h3, b.h3, t)!,
        title: TextStyle.lerp(a.title, b.title, t)!,
        body: TextStyle.lerp(a.body, b.body, t)!,
        bodySmall: TextStyle.lerp(a.bodySmall, b.bodySmall, t)!,
        label: TextStyle.lerp(a.label, b.label, t)!,
        caption: TextStyle.lerp(a.caption, b.caption, t)!,
        overline: TextStyle.lerp(a.overline, b.overline, t)!,
        numeric: TextStyle.lerp(a.numeric, b.numeric, t)!,
      );
}

abstract final class AuTypography {
  /// Fonts are bundled in this package.
  static const package = 'aurum_core';

  static FontWeight weight(int value) => FontWeight.values[((value ~/ 100) - 1).clamp(0, 8)];

  static TextStyle fromSpec(AuTypeSpec spec, {required Color color, bool arabic = false}) {
    return TextStyle(
      package: package,
      fontFamily: arabic ? AuType.fontArabic : AuType.fontLatin,
      fontFamilyFallback: arabic ? const [AuType.fontLatin] : const [AuType.fontArabic],
      fontSize: spec.size,
      height: arabic ? spec.height + 0.15 : spec.height,
      fontWeight: weight(spec.weight > 700 && arabic ? 700 : spec.weight),
      letterSpacing: arabic ? 0 : spec.size * spec.tracking,
      color: color,
      leadingDistribution: TextLeadingDistribution.even,
    );
  }

  /// Whether [locale] should use the Arabic-first font stack.
  static bool isArabic(Locale? locale) => locale?.languageCode == 'ar';
}
