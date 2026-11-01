import 'package:intl/intl.dart';

/// Display unit system. Values are always stored metric (kg, cm, ml, m);
/// conversion happens only at the presentation edge.
enum UnitSystem {
  metric,
  imperial;

  static UnitSystem parse(String? value) => value == 'imperial' ? UnitSystem.imperial : UnitSystem.metric;
}

abstract final class Units {
  static const double lbPerKg = 2.2046226218;
  static const double cmPerIn = 2.54;
  static const double mlPerFlOz = 29.5735295625;
  static const double mPerMile = 1609.344;

  static double kgToLb(double kg) => kg * lbPerKg;
  static double lbToKg(double lb) => lb / lbPerKg;
  static double cmToIn(double cm) => cm / cmPerIn;
  static double inToCm(double inches) => inches * cmPerIn;
  static double mlToFlOz(double ml) => ml / mlPerFlOz;

  /// Converts a stored kilogram value into the display unit.
  static double weightToDisplay(double kg, UnitSystem system) =>
      system == UnitSystem.imperial ? kgToLb(kg) : kg;

  /// Converts a value typed by the user in the display unit back to kg.
  static double weightFromDisplay(double value, UnitSystem system) =>
      system == UnitSystem.imperial ? lbToKg(value) : value;

  static double lengthToDisplay(double cm, UnitSystem system) =>
      system == UnitSystem.imperial ? cmToIn(cm) : cm;

  static double lengthFromDisplay(double value, UnitSystem system) =>
      system == UnitSystem.imperial ? inToCm(value) : value;

  static String weightUnit(UnitSystem system) => system == UnitSystem.imperial ? 'lb' : 'kg';
  static String lengthUnit(UnitSystem system) => system == UnitSystem.imperial ? 'in' : 'cm';

  /// Formats a weight stored in kg, e.g. `82.5 kg` / `181.9 lb`.
  static String formatWeight(double kg, UnitSystem system, {String? locale, int maxDecimals = 1}) {
    final value = weightToDisplay(kg, system);
    return '${formatNumber(value, locale: locale, maxDecimals: maxDecimals)} ${weightUnit(system)}';
  }

  /// Formats a length stored in cm.
  static String formatLength(double cm, UnitSystem system, {String? locale, int maxDecimals = 1}) {
    final value = lengthToDisplay(cm, system);
    return '${formatNumber(value, locale: locale, maxDecimals: maxDecimals)} ${lengthUnit(system)}';
  }

  /// Formats a volume of water stored in ml as `1.25 L` or `42 fl oz`.
  static String formatWater(double ml, UnitSystem system, {String? locale}) {
    if (system == UnitSystem.imperial) {
      return '${formatNumber(mlToFlOz(ml), locale: locale, maxDecimals: 0)} fl oz';
    }
    return '${formatNumber(ml / 1000, locale: locale, maxDecimals: 2)} L';
  }

  /// Formats a total volume load (kg) compactly, e.g. `12.4k kg`.
  static String formatVolume(double kg, UnitSystem system, {String? locale}) {
    final value = weightToDisplay(kg, system);
    final compact = NumberFormat.compact(locale: locale).format(value);
    return '$compact ${weightUnit(system)}';
  }

  /// Locale-aware number with up to [maxDecimals] fraction digits and no
  /// trailing zeros.
  static String formatNumber(double value, {String? locale, int maxDecimals = 1}) {
    final format = NumberFormat.decimalPattern(locale)
      ..minimumFractionDigits = 0
      ..maximumFractionDigits = maxDecimals;
    return format.format(value);
  }

  /// Rounds a display weight to the nearest loadable increment (2.5 kg / 5 lb).
  static double roundToIncrement(double value, UnitSystem system) {
    final step = system == UnitSystem.imperial ? 5.0 : 2.5;
    return (value / step).round() * step;
  }
}
