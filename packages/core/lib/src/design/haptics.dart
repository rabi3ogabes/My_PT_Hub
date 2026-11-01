import 'package:flutter/services.dart';

/// Central haptics so the user preference is respected everywhere.
abstract final class AuHaptics {
  static bool enabled = true;

  /// Light tick: toggles, steppers, selection changes.
  static Future<void> selection() => enabled ? HapticFeedback.selectionClick() : Future.value();

  /// Button presses.
  static Future<void> light() => enabled ? HapticFeedback.lightImpact() : Future.value();

  /// Set completed, item logged.
  static Future<void> medium() => enabled ? HapticFeedback.mediumImpact() : Future.value();

  /// Rest over, PR achieved.
  static Future<void> heavy() => enabled ? HapticFeedback.heavyImpact() : Future.value();

  /// A double pulse for "success" moments (workout finished, PR).
  static Future<void> success() async {
    if (!enabled) return;
    await HapticFeedback.mediumImpact();
    await Future<void>.delayed(const Duration(milliseconds: 90));
    await HapticFeedback.heavyImpact();
  }

  /// Rejected action / validation error.
  static Future<void> error() => enabled ? HapticFeedback.vibrate() : Future.value();
}
