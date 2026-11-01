import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../util/units.dart';

/// Non-sensitive user preferences (shared_preferences).
class AppPrefs {
  AppPrefs(this._prefs);

  static Future<AppPrefs> load() async => AppPrefs(await SharedPreferences.getInstance());

  final SharedPreferences _prefs;

  static const _locale = 'au.locale';
  static const _theme = 'au.theme';
  static const _units = 'au.units';
  static const _onboarded = 'au.onboarding_seen';
  static const _biometric = 'au.biometric_unlock';
  static const _tenant = 'au.tenant_id';
  static const _restSound = 'au.rest_sound';
  static const _haptics = 'au.haptics';
  static const _dataSaver = 'au.data_saver';

  String? get localeCode => _prefs.getString(_locale);
  Future<void> setLocaleCode(String? code) => code == null ? _prefs.remove(_locale) : _prefs.setString(_locale, code);

  ThemeMode get themeMode => switch (_prefs.getString(_theme)) {
        'light' => ThemeMode.light,
        'dark' => ThemeMode.dark,
        _ => ThemeMode.system,
      };
  Future<void> setThemeMode(ThemeMode mode) => _prefs.setString(_theme, mode.name);

  UnitSystem get units => UnitSystem.parse(_prefs.getString(_units));
  Future<void> setUnits(UnitSystem units) => _prefs.setString(_units, units.name);

  bool get onboardingSeen => _prefs.getBool(_onboarded) ?? false;
  Future<void> setOnboardingSeen() => _prefs.setBool(_onboarded, true);

  bool get biometricUnlock => _prefs.getBool(_biometric) ?? false;
  Future<void> setBiometricUnlock(bool value) => _prefs.setBool(_biometric, value);

  String? get tenantId => _prefs.getString(_tenant);
  Future<void> setTenantId(String? id) => id == null ? _prefs.remove(_tenant) : _prefs.setString(_tenant, id);

  bool get restSound => _prefs.getBool(_restSound) ?? true;
  Future<void> setRestSound(bool value) => _prefs.setBool(_restSound, value);

  bool get haptics => _prefs.getBool(_haptics) ?? true;
  Future<void> setHaptics(bool value) => _prefs.setBool(_haptics, value);

  bool get dataSaver => _prefs.getBool(_dataSaver) ?? false;
  Future<void> setDataSaver(bool value) => _prefs.setBool(_dataSaver, value);

  /// Clears account-bound values on sign-out (keeps device preferences).
  Future<void> clearAccount() async {
    await _prefs.remove(_tenant);
    await _prefs.remove(_biometric);
  }
}
