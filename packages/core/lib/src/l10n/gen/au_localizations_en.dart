// ignore: unused_import
import 'package:intl/intl.dart' as intl;

import 'au_localizations.dart';

// ignore_for_file: type=lint

/// The translations for English (`en`).
class AuL10nEn extends AuL10n {
  AuL10nEn([String locale = 'en']) : super(locale);

  @override
  String get brandName => 'Aurum';

  @override
  String get ok => 'OK';

  @override
  String get cancel => 'Cancel';

  @override
  String get save => 'Save';

  @override
  String get done => 'Done';

  @override
  String get retry => 'Try again';

  @override
  String get close => 'Close';

  @override
  String get next => 'Next';

  @override
  String get back => 'Back';

  @override
  String get skip => 'Skip';

  @override
  String get continueLabel => 'Continue';

  @override
  String get edit => 'Edit';

  @override
  String get delete => 'Delete';

  @override
  String get confirm => 'Confirm';

  @override
  String get search => 'Search';

  @override
  String get seeAll => 'See all';

  @override
  String get add => 'Add';

  @override
  String get remove => 'Remove';

  @override
  String get apply => 'Apply';

  @override
  String get today => 'Today';

  @override
  String get yesterday => 'Yesterday';

  @override
  String get tomorrow => 'Tomorrow';

  @override
  String get loading => 'Loading…';

  @override
  String get offlineBanner =>
      'You\'re offline — changes are saved and will sync automatically.';

  @override
  String pendingSync(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count changes waiting to sync',
      one: '1 change waiting to sync',
      zero: 'All changes synced',
    );
    return '$_temp0';
  }

  @override
  String get syncing => 'Syncing…';

  @override
  String showingCachedData(String time) {
    return 'Showing saved data from $time';
  }

  @override
  String get errorTitle => 'Something went wrong';

  @override
  String get errorGeneric => 'We couldn\'t complete that. Please try again.';

  @override
  String get errorNetwork =>
      'No connection. Check your internet and try again.';

  @override
  String get errorTimeout =>
      'This is taking longer than usual. Please try again.';

  @override
  String get errorServer =>
      'Our servers are having a moment. Please try again shortly.';

  @override
  String get errorForbidden => 'You don\'t have access to this.';

  @override
  String get errorNotFound => 'We couldn\'t find what you were looking for.';

  @override
  String get errorValidation => 'Please check the highlighted fields.';

  @override
  String get errorPlanLimit => 'Your coach\'s plan limit has been reached.';

  @override
  String get errorAccountLocked =>
      'Your account is temporarily locked. Try again later.';

  @override
  String get errorTooManyRequests => 'Too many attempts. Please wait a moment.';

  @override
  String get errorInvitationInvalid => 'This invitation is no longer valid.';

  @override
  String get errorSessionExpired =>
      'Your session has expired. Please sign in again.';

  @override
  String get unitKg => 'kg';

  @override
  String get unitLb => 'lb';

  @override
  String get unitCm => 'cm';

  @override
  String get unitIn => 'in';

  @override
  String get unitMin => 'min';

  @override
  String get unitKcal => 'kcal';

  @override
  String get unitG => 'g';

  @override
  String get unitMl => 'ml';

  @override
  String get setLabel => 'Set';

  @override
  String setNumber(int number) {
    return 'Set $number';
  }

  @override
  String get reps => 'Reps';

  @override
  String get weight => 'Weight';

  @override
  String get rpe => 'RPE';

  @override
  String get previous => 'Previous';

  @override
  String get sameAsLast => 'Same as last';

  @override
  String markSetDone(int number) {
    return 'Mark set $number done';
  }

  @override
  String setDone(int number) {
    return 'Set $number completed';
  }

  @override
  String get setTypeWarmup => 'Warm-up';

  @override
  String get setTypeWorking => 'Working';

  @override
  String get setTypeDrop => 'Drop set';

  @override
  String get setTypeFailure => 'To failure';

  @override
  String get setTypeAmrap => 'AMRAP';

  @override
  String get increase => 'Increase';

  @override
  String get decrease => 'Decrease';

  @override
  String get duration => 'Duration';

  @override
  String get distance => 'Distance';

  @override
  String get protein => 'Protein';

  @override
  String get carbs => 'Carbs';

  @override
  String get fat => 'Fat';

  @override
  String get calories => 'Calories';

  @override
  String remainingOf(String value, String target) {
    return '$value of $target';
  }

  @override
  String get languageEnglish => 'English';

  @override
  String get languageArabic => 'العربية';

  @override
  String get languageFrench => 'Français';

  @override
  String get languageSpanish => 'Español';

  @override
  String get languageSystem => 'Device language';

  @override
  String get themeLight => 'Light';

  @override
  String get themeDark => 'Dark';

  @override
  String get themeSystem => 'Automatic';

  @override
  String get unitsMetric => 'Metric (kg, cm)';

  @override
  String get unitsImperial => 'Imperial (lb, in)';

  @override
  String minutesShort(int minutes) {
    return '$minutes min';
  }

  @override
  String secondsShort(int seconds) {
    return '${seconds}s';
  }

  @override
  String streakDays(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count days',
      one: '1 day',
    );
    return '$_temp0';
  }

  @override
  String notificationsBadge(int count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: '$count unread',
      one: '1 unread',
    );
    return '$_temp0';
  }

  @override
  String get dismiss => 'Dismiss';

  @override
  String get copied => 'Copied';

  @override
  String get email => 'Email';

  @override
  String get password => 'Password';

  @override
  String get name => 'Full name';

  @override
  String get fieldRequired => 'This field is required';

  @override
  String get invalidEmail => 'Enter a valid email address';

  @override
  String get passwordTooShort => 'Use at least 8 characters';

  @override
  String get showPassword => 'Show password';

  @override
  String get hidePassword => 'Hide password';
}
