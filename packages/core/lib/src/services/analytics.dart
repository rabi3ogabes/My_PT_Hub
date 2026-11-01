import 'package:flutter/foundation.dart';

/// A typed analytics event. Names follow GA4 conventions (snake_case, ≤ 40
/// chars); parameters are primitives only. Never put PII in parameters.
@immutable
class AnalyticsEvent {
  const AnalyticsEvent(this.name, [this.parameters = const {}]);

  factory AnalyticsEvent.signUp({required String method, required String accountType}) =>
      AnalyticsEvent('sign_up', {'method': method, 'account_type': accountType});
  factory AnalyticsEvent.login({required String method}) => AnalyticsEvent('login', {'method': method});
  factory AnalyticsEvent.inviteAccepted() => const AnalyticsEvent('invite_accepted');
  factory AnalyticsEvent.onboardingCompleted() => const AnalyticsEvent('onboarding_completed');
  factory AnalyticsEvent.workoutStarted({String? workoutId}) =>
      AnalyticsEvent('workout_started', {'workout_id': ?workoutId});
  factory AnalyticsEvent.setCompleted({required String exerciseId}) =>
      AnalyticsEvent('set_completed', {'exercise_id': exerciseId});
  factory AnalyticsEvent.workoutCompleted({required int durationSeconds, required double volumeKg, required int prs}) =>
      AnalyticsEvent('workout_completed', {'duration_s': durationSeconds, 'volume_kg': volumeKg.round(), 'prs': prs});
  factory AnalyticsEvent.foodLogged({required String source, required String slot}) =>
      AnalyticsEvent('food_logged', {'source': source, 'slot': slot});
  factory AnalyticsEvent.waterLogged({required int ml}) => AnalyticsEvent('water_logged', {'ml': ml});
  factory AnalyticsEvent.habitCompleted() => const AnalyticsEvent('habit_completed');
  factory AnalyticsEvent.checkinSubmitted() => const AnalyticsEvent('checkin_submitted');
  factory AnalyticsEvent.messageSent({required String type}) => AnalyticsEvent('message_sent', {'type': type});
  factory AnalyticsEvent.bookingCreated({required String type}) => AnalyticsEvent('booking_created', {'type': type});
  factory AnalyticsEvent.bookingCancelled() => const AnalyticsEvent('booking_cancelled');
  factory AnalyticsEvent.checkoutStarted({required String productId}) =>
      AnalyticsEvent('begin_checkout', {'product_id': productId});
  factory AnalyticsEvent.subscriptionCancelled() => const AnalyticsEvent('subscription_cancelled');
  factory AnalyticsEvent.languageChanged({required String locale}) => AnalyticsEvent('language_changed', {'locale': locale});

  final String name;
  final Map<String, Object> parameters;
}

/// Analytics backend (Firebase Analytics in production).
abstract class AnalyticsService {
  Future<void> log(AnalyticsEvent event);
  Future<void> screen(String name);
  Future<void> setUserId(String? id);
  Future<void> setUserProperty(String name, String? value);
}

class NoopAnalytics implements AnalyticsService {
  const NoopAnalytics();

  @override
  Future<void> log(AnalyticsEvent event) async {}

  @override
  Future<void> screen(String name) async {}

  @override
  Future<void> setUserId(String? id) async {}

  @override
  Future<void> setUserProperty(String name, String? value) async {}
}

/// Records every event in memory (tests and debug overlays).
class RecordingAnalytics implements AnalyticsService {
  final events = <AnalyticsEvent>[];
  final screens = <String>[];

  @override
  Future<void> log(AnalyticsEvent event) async => events.add(event);

  @override
  Future<void> screen(String name) async => screens.add(name);

  @override
  Future<void> setUserId(String? id) async {}

  @override
  Future<void> setUserProperty(String name, String? value) async {}
}
