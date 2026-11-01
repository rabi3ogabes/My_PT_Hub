// GENERATED FILE — do not edit. Source: packages/design_tokens/tokens.json
// ignore_for_file: constant_identifier_names

import 'package:flutter/widgets.dart';

abstract final class AuBrandColors {
  static const gold = Color(0xFFD4AF6A);
  static const goldLight = Color(0xFFE8CF9B);
  static const goldDark = Color(0xFFA9853F);
  static const onGold = Color(0xFF0B0B0F);
}

abstract final class AuDarkColors {
  static const background = Color(0xFF0B0B0F);
  static const surface = Color(0xFF16161D);
  static const surfaceRaised = Color(0xFF1E1E27);
  static const surfaceSunken = Color(0xFF101015);
  static const border = Color(0xFF2A2A35);
  static const borderStrong = Color(0xFF3A3A48);
  static const textPrimary = Color(0xFFF5F3EE);
  static const textSecondary = Color(0xFFB4B1AA);
  static const textMuted = Color(0xFF7D7A74);
  static const overlay = Color(0xB3000000);
}

abstract final class AuLightColors {
  static const background = Color(0xFFFAF8F4);
  static const surface = Color(0xFFFFFFFF);
  static const surfaceRaised = Color(0xFFFFFFFF);
  static const surfaceSunken = Color(0xFFF2EFE8);
  static const border = Color(0xFFE7E2D8);
  static const borderStrong = Color(0xFFD2CBBD);
  static const textPrimary = Color(0xFF121217);
  static const textSecondary = Color(0xFF4B4A52);
  static const textMuted = Color(0xFF86848C);
  static const overlay = Color(0x800B0B0F);
}

abstract final class AuSemanticColors {
  static const success = Color(0xFF3DBE8B);
  static const successSoft = Color(0x1F3DBE8B);
  static const warning = Color(0xFFF2B544);
  static const warningSoft = Color(0x1FF2B544);
  static const danger = Color(0xFFEF5A5A);
  static const dangerSoft = Color(0x1FEF5A5A);
  static const info = Color(0xFF5B8DEF);
  static const infoSoft = Color(0x1F5B8DEF);
}

abstract final class AuMacroColors {
  static const protein = Color(0xFFE76F6F);
  static const carbs = Color(0xFFD4AF6A);
  static const fat = Color(0xFF6FA8E7);
  static const kcal = Color(0xFF3DBE8B);
}

abstract final class AuSpacing {
  static const double s0 = 0;
  static const double s1 = 4;
  static const double s2 = 8;
  static const double s3 = 12;
  static const double s4 = 16;
  static const double s5 = 20;
  static const double s6 = 24;
  static const double s8 = 32;
  static const double s10 = 40;
  static const double s12 = 48;
  static const double s16 = 64;
  static const double s20 = 80;
}

abstract final class AuRadius {
  static const double xs = 6;
  static const double sm = 10;
  static const double md = 14;
  static const double lg = 20;
  static const double xl = 28;
  static const double pill = 999;
}

abstract final class AuMotion {
  static const fast = Duration(milliseconds: 150);
  static const base = Duration(milliseconds: 220);
  static const slow = Duration(milliseconds: 300);
  static const easeOut = Cubic(0.16, 1, 0.3, 1);
  static const easeInOut = Cubic(0.65, 0, 0.35, 1);
}

class AuTypeSpec {
  const AuTypeSpec(this.size, this.height, this.weight, this.tracking);
  final double size;
  final double height;
  final int weight;
  final double tracking;
}

abstract final class AuType {
  static const fontLatin = 'Manrope';
  static const fontArabic = 'IBM Plex Sans Arabic';
  static const display = AuTypeSpec(48, 1.05, 800, -0.03);
  static const h1 = AuTypeSpec(36, 1.1, 800, -0.025);
  static const h2 = AuTypeSpec(28, 1.15, 700, -0.02);
  static const h3 = AuTypeSpec(22, 1.2, 700, -0.015);
  static const title = AuTypeSpec(18, 1.3, 600, -0.01);
  static const body = AuTypeSpec(16, 1.5, 400, 0);
  static const bodySmall = AuTypeSpec(14, 1.45, 400, 0);
  static const label = AuTypeSpec(13, 1.3, 600, 0.01);
  static const caption = AuTypeSpec(12, 1.3, 500, 0.02);
  static const overline = AuTypeSpec(11, 1.2, 700, 0.12);
}

const double kAuTouchTarget = 44;
