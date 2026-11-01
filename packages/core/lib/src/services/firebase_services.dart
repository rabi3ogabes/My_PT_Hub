import 'dart:async';

import 'package:firebase_analytics/firebase_analytics.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_crashlytics/firebase_crashlytics.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:firebase_performance/firebase_performance.dart';
import 'package:firebase_remote_config/firebase_remote_config.dart';
import 'package:flutter/foundation.dart';

import 'analytics.dart';
import 'platform_services.dart';

/// The bundle of platform services an app uses.
class AuServices {
  const AuServices({
    this.analytics = const NoopAnalytics(),
    this.crash = const NoopCrashReporter(),
    this.push = const NoopPushService(),
    this.remoteConfig = const StaticRemoteConfig(),
    this.performance = const NoopPerformance(),
    this.firebaseEnabled = false,
  });

  final AnalyticsService analytics;
  final CrashReporter crash;
  final PushService push;
  final RemoteConfigService remoteConfig;
  final PerformanceService performance;
  final bool firebaseEnabled;

  /// Initializes Firebase when [enabled] (dart-define `FIREBASE_ENABLED=true`
  /// and native config files present). Any failure falls back to no-op
  /// services so the app always runs without Firebase.
  static Future<AuServices> initialize({required bool enabled}) async {
    if (!enabled) return const AuServices();
    try {
      await Firebase.initializeApp();
      final crashlytics = FirebaseCrashlytics.instance;
      await crashlytics.setCrashlyticsCollectionEnabled(!kDebugMode);
      return AuServices(
        analytics: FirebaseAnalyticsService(FirebaseAnalytics.instance),
        crash: FirebaseCrashReporter(crashlytics),
        push: FirebasePushService(FirebaseMessaging.instance),
        remoteConfig: FirebaseRemoteConfigService(FirebaseRemoteConfig.instance),
        performance: FirebasePerformanceService(FirebasePerformance.instance),
        firebaseEnabled: true,
      );
    } on Object catch (error) {
      debugPrint('[firebase] disabled: $error');
      return const AuServices();
    }
  }
}

class FirebaseAnalyticsService implements AnalyticsService {
  FirebaseAnalyticsService(this._analytics);

  final FirebaseAnalytics _analytics;

  @override
  Future<void> log(AnalyticsEvent event) => _analytics.logEvent(name: event.name, parameters: event.parameters);

  @override
  Future<void> screen(String name) => _analytics.logScreenView(screenName: name);

  @override
  Future<void> setUserId(String? id) => _analytics.setUserId(id: id);

  @override
  Future<void> setUserProperty(String name, String? value) => _analytics.setUserProperty(name: name, value: value);
}

class FirebaseCrashReporter implements CrashReporter {
  FirebaseCrashReporter(this._crashlytics);

  final FirebaseCrashlytics _crashlytics;

  @override
  Future<void> recordError(Object error, StackTrace? stack, {bool fatal = false, String? reason}) =>
      _crashlytics.recordError(error, stack, fatal: fatal, reason: reason);

  @override
  Future<void> recordFlutterError(FlutterErrorDetails details) => _crashlytics.recordFlutterFatalError(details);

  @override
  Future<void> log(String message) => _crashlytics.log(message);

  @override
  Future<void> setUserId(String? id) => _crashlytics.setUserIdentifier(id ?? '');
}

class FirebasePushService implements PushService {
  FirebasePushService(this._messaging);

  final FirebaseMessaging _messaging;

  static PushMessage _map(RemoteMessage m) =>
      PushMessage(title: m.notification?.title, body: m.notification?.body, data: m.data);

  @override
  Future<bool> requestPermission() async {
    final settings = await _messaging.requestPermission();
    return settings.authorizationStatus == AuthorizationStatus.authorized ||
        settings.authorizationStatus == AuthorizationStatus.provisional;
  }

  // The backend's FCM channel targets registration tokens (POST /me/devices
  // push_token), so the registration-token API is used deliberately.
  @override
  // ignore: deprecated_member_use
  Future<String?> token() => _messaging.getToken();

  @override
  // ignore: deprecated_member_use
  Stream<String> get onTokenRefresh => _messaging.onTokenRefresh;

  @override
  Stream<PushMessage> get onMessage => FirebaseMessaging.onMessage.map(_map);

  @override
  Stream<PushMessage> get onOpened => FirebaseMessaging.onMessageOpenedApp.map(_map);

  @override
  Future<PushMessage?> initialMessage() async {
    final m = await _messaging.getInitialMessage();
    return m == null ? null : _map(m);
  }
}

class FirebaseRemoteConfigService implements RemoteConfigService {
  FirebaseRemoteConfigService(this._config);

  final FirebaseRemoteConfig _config;

  @override
  Future<void> fetchAndActivate() async {
    try {
      await _config.fetchAndActivate();
    } on Object catch (e) {
      debugPrint('[remote-config] $e');
    }
  }

  @override
  bool getBool(String key, {bool fallback = false}) {
    final v = _config.getValue(key);
    return v.source == ValueSource.valueStatic ? fallback : v.asBool();
  }

  @override
  String getString(String key, {String fallback = ''}) {
    final v = _config.getValue(key);
    return v.source == ValueSource.valueStatic ? fallback : v.asString();
  }

  @override
  int getInt(String key, {int fallback = 0}) {
    final v = _config.getValue(key);
    return v.source == ValueSource.valueStatic ? fallback : v.asInt();
  }
}

class FirebasePerformanceService implements PerformanceService {
  FirebasePerformanceService(this._performance);

  final FirebasePerformance _performance;

  @override
  Future<T> trace<T>(String name, Future<T> Function() body) async {
    final trace = _performance.newTrace(name);
    await trace.start();
    try {
      return await body();
    } finally {
      await trace.stop();
    }
  }
}
