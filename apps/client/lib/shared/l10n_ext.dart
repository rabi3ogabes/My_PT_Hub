import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../l10n/gen/client_localizations.dart';

extension L10nContext on BuildContext {
  /// Client app strings.
  ClientL10n get l10n => ClientL10n.of(this);

  /// Shared core strings (errors, units, common actions).
  AuL10n get core => AuL10n.of(this);

  String get localeName => Localizations.localeOf(this).toLanguageTag();
}

/// Locale-aware formatting helpers bound to the current context.
extension FormatContext on BuildContext {
  String formatNumber(num value, {int maxDecimals = 1}) =>
      Units.formatNumber(value.toDouble(), locale: localeName, maxDecimals: maxDecimals);

  String formatDate(DateTime date, {String pattern = 'MMMEd'}) => DateFormat(pattern, localeName).format(date);

  String formatTime(DateTime time) => DateFormat.jm(localeName).format(time.toLocal());

  String formatDayTime(DateTime time) => DateFormat('EEE d MMM · HH:mm', localeName).format(time.toLocal());

  /// "Today", "Yesterday" or a short date.
  String relativeDay(DateTime date) {
    final now = DateTime.now();
    final d = DateTime(date.year, date.month, date.day);
    final today = DateTime(now.year, now.month, now.day);
    final diff = d.difference(today).inDays;
    if (diff == 0) return core.today;
    if (diff == -1) return core.yesterday;
    if (diff == 1) return core.tomorrow;
    return formatDate(date);
  }
}

/// Unit-aware formatting bound to the user's unit preference.
extension UnitsRef on WidgetRef {
  UnitSystem get units => watch(unitsProvider);
}

String weightUnitLabel(BuildContext context, UnitSystem units) =>
    units == UnitSystem.imperial ? context.core.unitLb : context.core.unitKg;

String lengthUnitLabel(BuildContext context, UnitSystem units) =>
    units == UnitSystem.imperial ? context.core.unitIn : context.core.unitCm;

/// Weight stored in kg → display string with the localized unit.
String displayWeight(BuildContext context, double kg, UnitSystem units, {int maxDecimals = 1}) {
  final v = Units.weightToDisplay(kg, units);
  return '${context.formatNumber(v, maxDecimals: maxDecimals)} ${weightUnitLabel(context, units)}';
}
