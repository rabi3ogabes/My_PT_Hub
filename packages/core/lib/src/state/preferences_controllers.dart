import 'dart:ui';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../design/haptics.dart';
import '../storage/app_prefs.dart';
import '../util/units.dart';

/// Supported locales, in picker order.
abstract final class AuLocales {
  static const supported = [Locale('en'), Locale('ar'), Locale('fr'), Locale('es')];

  static bool isSupported(Locale locale) => supported.any((l) => l.languageCode == locale.languageCode);

  static TextDirection directionOf(Locale locale) =>
      locale.languageCode == 'ar' ? TextDirection.rtl : TextDirection.ltr;

  /// Resolves the device locale list to a supported locale (fallback `en`).
  static Locale resolve(List<Locale>? device) {
    for (final l in device ?? const <Locale>[]) {
      if (isSupported(l)) return Locale(l.languageCode);
    }
    return supported.first;
  }
}

/// Loaded preferences. Override in `main()` with the loaded instance.
final appPrefsProvider = Provider<AppPrefs>((ref) => throw StateError('appPrefsProvider must be overridden'));

/// The locale the user picked (null = follow the device).
class LocaleController extends Notifier<Locale?> {
  @override
  Locale? build() {
    final code = ref.read(appPrefsProvider).localeCode;
    return code == null ? null : Locale(code);
  }

  Future<void> set(Locale? locale) async {
    state = locale;
    await ref.read(appPrefsProvider).setLocaleCode(locale?.languageCode);
  }
}

final localeControllerProvider = NotifierProvider<LocaleController, Locale?>(LocaleController.new);

/// The device locales (overridable in tests).
final deviceLocalesProvider = Provider<List<Locale>>((ref) => PlatformDispatcher.instance.locales);

/// The effective locale: user choice → device → English.
final effectiveLocaleProvider = Provider<Locale>((ref) {
  return ref.watch(localeControllerProvider) ?? AuLocales.resolve(ref.watch(deviceLocalesProvider));
});

class ThemeModeController extends Notifier<ThemeMode> {
  @override
  ThemeMode build() => ref.read(appPrefsProvider).themeMode;

  Future<void> set(ThemeMode mode) async {
    state = mode;
    await ref.read(appPrefsProvider).setThemeMode(mode);
  }
}

final themeModeProvider = NotifierProvider<ThemeModeController, ThemeMode>(ThemeModeController.new);

class UnitsController extends Notifier<UnitSystem> {
  @override
  UnitSystem build() => ref.read(appPrefsProvider).units;

  Future<void> set(UnitSystem units) async {
    state = units;
    await ref.read(appPrefsProvider).setUnits(units);
  }
}

final unitsProvider = NotifierProvider<UnitsController, UnitSystem>(UnitsController.new);

class HapticsController extends Notifier<bool> {
  @override
  bool build() {
    final enabled = ref.read(appPrefsProvider).haptics;
    AuHaptics.enabled = enabled;
    return enabled;
  }

  Future<void> set(bool enabled) async {
    state = enabled;
    AuHaptics.enabled = enabled;
    await ref.read(appPrefsProvider).setHaptics(enabled);
  }
}

final hapticsProvider = NotifierProvider<HapticsController, bool>(HapticsController.new);
