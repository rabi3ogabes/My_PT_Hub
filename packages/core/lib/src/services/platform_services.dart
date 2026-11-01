import 'dart:async';

import 'package:flutter/foundation.dart';

/// Crash & error reporting (Crashlytics in production).
abstract class CrashReporter {
  Future<void> recordError(Object error, StackTrace? stack, {bool fatal = false, String? reason});
  Future<void> recordFlutterError(FlutterErrorDetails details);
  Future<void> log(String message);
  Future<void> setUserId(String? id);
}

class NoopCrashReporter implements CrashReporter {
  const NoopCrashReporter();

  @override
  Future<void> recordError(Object error, StackTrace? stack, {bool fatal = false, String? reason}) async {
    if (kDebugMode) debugPrint('[crash] ${reason ?? ''} $error');
  }

  @override
  Future<void> recordFlutterError(FlutterErrorDetails details) async => FlutterError.presentError(details);

  @override
  Future<void> log(String message) async {}

  @override
  Future<void> setUserId(String? id) async {}
}

/// A push message (FCM data payload).
@immutable
class PushMessage {
  const PushMessage({this.title, this.body, this.data = const {}});

  final String? title;
  final String? body;
  final Map<String, dynamic> data;

  /// In-app deep link (`link` field set by the backend notification).
  String? get link => data['link'] as String?;
}

/// Push notifications (FCM in production).
abstract class PushService {
  Future<bool> requestPermission();
  Future<String?> token();
  Stream<String> get onTokenRefresh;

  /// Messages received while the app is in the foreground.
  Stream<PushMessage> get onMessage;

  /// Notification taps that opened the app from background.
  Stream<PushMessage> get onOpened;

  /// The notification that launched the app from terminated state.
  Future<PushMessage?> initialMessage();
}

class NoopPushService implements PushService {
  const NoopPushService();

  @override
  Future<bool> requestPermission() async => false;

  @override
  Future<String?> token() async => null;

  @override
  Stream<String> get onTokenRefresh => const Stream.empty();

  @override
  Stream<PushMessage> get onMessage => const Stream.empty();

  @override
  Stream<PushMessage> get onOpened => const Stream.empty();

  @override
  Future<PushMessage?> initialMessage() async => null;
}

/// Remote feature flags (Firebase Remote Config in production).
abstract class RemoteConfigService {
  Future<void> fetchAndActivate();
  bool getBool(String key, {bool fallback = false});
  String getString(String key, {String fallback = ''});
  int getInt(String key, {int fallback = 0});
}

class StaticRemoteConfig implements RemoteConfigService {
  const StaticRemoteConfig([this.values = const {}]);

  final Map<String, Object> values;

  @override
  Future<void> fetchAndActivate() async {}

  @override
  bool getBool(String key, {bool fallback = false}) => values[key] as bool? ?? fallback;

  @override
  String getString(String key, {String fallback = ''}) => values[key] as String? ?? fallback;

  @override
  int getInt(String key, {int fallback = 0}) => values[key] as int? ?? fallback;
}

/// Performance tracing (Firebase Performance in production).
abstract class PerformanceService {
  Future<T> trace<T>(String name, Future<T> Function() body);
}

class NoopPerformance implements PerformanceService {
  const NoopPerformance();

  @override
  Future<T> trace<T>(String name, Future<T> Function() body) => body();
}
