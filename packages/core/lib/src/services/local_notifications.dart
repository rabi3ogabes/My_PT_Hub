import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:timezone/data/latest.dart' as tzdata;
import 'package:timezone/timezone.dart' as tz;

/// Local notifications: foreground push display and wall-clock alarms such as
/// "rest is over" (scheduled at an absolute time so they fire even if the app
/// is backgrounded or killed).
abstract class LocalNotifier {
  Future<void> initialize({void Function(String? payload)? onTap});
  Future<void> show({required int id, required String title, String? body, String? payload});
  Future<void> scheduleAt({required int id, required DateTime at, required String title, String? body, String? payload});
  Future<void> cancel(int id);
}

class NoopLocalNotifier implements LocalNotifier {
  const NoopLocalNotifier();

  @override
  Future<void> initialize({void Function(String? payload)? onTap}) async {}

  @override
  Future<void> show({required int id, required String title, String? body, String? payload}) async {}

  @override
  Future<void> scheduleAt({required int id, required DateTime at, required String title, String? body, String? payload}) async {}

  @override
  Future<void> cancel(int id) async {}
}

class PluginLocalNotifier implements LocalNotifier {
  PluginLocalNotifier([FlutterLocalNotificationsPlugin? plugin]) : _plugin = plugin ?? FlutterLocalNotificationsPlugin();

  final FlutterLocalNotificationsPlugin _plugin;
  bool _ready = false;

  static const _channel = AndroidNotificationDetails(
    'aurum_training',
    'Training',
    channelDescription: 'Rest timer and workout alerts',
    importance: Importance.high,
    priority: Priority.high,
    category: AndroidNotificationCategory.alarm,
  );
  static const _details = NotificationDetails(
    android: _channel,
    iOS: DarwinNotificationDetails(interruptionLevel: InterruptionLevel.timeSensitive),
  );

  @override
  Future<void> initialize({void Function(String? payload)? onTap}) async {
    if (_ready) return;
    try {
      tzdata.initializeTimeZones();
      await _plugin.initialize(
        settings: const InitializationSettings(
          android: AndroidInitializationSettings('@mipmap/ic_launcher'),
          iOS: DarwinInitializationSettings(
            requestAlertPermission: false,
            requestBadgePermission: false,
            requestSoundPermission: false,
          ),
        ),
        onDidReceiveNotificationResponse: (r) => onTap?.call(r.payload),
      );
      _ready = true;
    } on Object catch (e) {
      debugPrint('[notifications] unavailable: $e');
    }
  }

  @override
  Future<void> show({required int id, required String title, String? body, String? payload}) async {
    if (!_ready) return;
    await _plugin.show(id: id, title: title, body: body, notificationDetails: _details, payload: payload);
  }

  @override
  Future<void> scheduleAt({required int id, required DateTime at, required String title, String? body, String? payload}) async {
    if (!_ready || !at.isAfter(DateTime.now())) return;
    try {
      await _plugin.zonedSchedule(
        id: id,
        scheduledDate: tz.TZDateTime.from(at.toUtc(), tz.UTC),
        notificationDetails: _details,
        androidScheduleMode: AndroidScheduleMode.inexactAllowWhileIdle,
        title: title,
        body: body,
        payload: payload,
      );
    } on Object catch (e) {
      debugPrint('[notifications] schedule failed: $e');
    }
  }

  @override
  Future<void> cancel(int id) async {
    if (!_ready) return;
    await _plugin.cancel(id: id);
  }
}
