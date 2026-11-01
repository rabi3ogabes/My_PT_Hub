import 'dart:math';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/date_symbol_data_local.dart';

void main() {
  setUpAll(() => initializeDateFormatting());

  group('Ulid', () {
    test('valid, 26 chars and embeds the timestamp', () {
      final id = Ulid(now: () => 1761987600000).next();
      expect(id, hasLength(26));
      expect(Ulid.isValid(id), isTrue);
      expect(Ulid.timestampOf(id).millisecondsSinceEpoch, 1761987600000);
    });

    test('monotonic within the same millisecond', () {
      final gen = Ulid(random: Random(1), now: () => 1000);
      final ids = List.generate(500, (_) => gen.next());
      final sorted = [...ids]..sort();
      expect(ids, sorted);
      expect(ids.toSet(), hasLength(500));
    });
  });

  group('Units', () {
    test('converts kg/lb and cm/in symmetrically', () {
      expect(Units.kgToLb(100), closeTo(220.46, 0.01));
      expect(Units.lbToKg(Units.kgToLb(82.5)), closeTo(82.5, 1e-9));
      expect(Units.cmToIn(2.54), closeTo(1, 1e-9));
      expect(Units.weightFromDisplay(225, UnitSystem.imperial), closeTo(102.06, 0.01));
    });

    test('formats with locale and unit', () {
      expect(Units.formatWeight(82.5, UnitSystem.metric, locale: 'en'), '82.5 kg');
      expect(Units.formatWeight(100, UnitSystem.imperial, locale: 'en'), '220.5 lb');
      expect(Units.formatWeight(82.5, UnitSystem.metric, locale: 'fr'), '82,5 kg');
      expect(Units.formatLength(80, UnitSystem.imperial, locale: 'en'), '31.5 in');
      expect(Units.formatWater(1250, UnitSystem.metric, locale: 'en'), '1.25 L');
    });

    test('rounds to loadable increments', () {
      expect(Units.roundToIncrement(81.3, UnitSystem.metric), 82.5);
      expect(Units.roundToIncrement(183, UnitSystem.imperial), 185);
    });
  });

  group('MoneyFormat', () {
    test('respects currency fraction digits', () {
      expect(MoneyFormat.format(39900, 'QAR', locale: 'en'), contains('399.00'));
      expect(MoneyFormat.format(12345, 'KWD', locale: 'en'), contains('12.345'));
      expect(MoneyFormat.currencyDigits('JPY'), 0);
    });

    test('Money prefers the server-formatted string', () {
      const money = Money(amountMinor: 39900, currency: 'QAR', formatted: 'QAR 399.00');
      expect(money.display('ar'), 'QAR 399.00');
      expect(const Money(amountMinor: 1500, currency: 'EUR').display('fr'), contains('15,00'));
    });
  });

  group('JSON helpers', () {
    test('API dates', () {
      expect(toApiDate(DateTime(2026, 3, 7)), '2026-03-07');
      expect(parseApiDate('2026-03-07'), DateTime(2026, 3, 7));
      expect(toApiDateTime(DateTime.utc(2026, 11, 1, 9, 0, 0, 123, 456)), '2026-11-01T09:00:00.123Z');
    });

    test('contract field names are preserved exactly', () {
      final food = Food.fromJson({
        'id': 'f1',
        'name': 'Laban',
        'per_100g': {'kcal': 61, 'protein_g': '3.3', 'carbs_g': 4.7, 'fat_g': 3.3, 'fiber_g': 0},
        'servings': [
          {'unit': 'cup', 'grams': 245, 'label': '1 cup'},
        ],
      });
      expect(food.macrosFor(245).kcal, closeTo(149.45, 0.01));
      expect(food.toJson()['per_100g'], isA<Map<String, dynamic>>());

      final habit = HabitItem.fromJson({
        'assignment_id': 'a1',
        'habit': {'id': 'h1', 'name': 'Water', 'icon': 'water', 'type': 'count', 'unit': 'glasses'},
        'target': 8,
        'scheduled_today': true,
        'value': 4,
        'completed': false,
        'streak': {'current': 3, 'best': 10},
        'completion_pct_30d': 76.5,
        'reminder_times': ['08:00'],
      });
      expect(habit.progress, 0.5);
      expect(habit.completionPct30d, 76.5);

      final template = SetTemplate.fromJson({'id': 's', 'position': 1, 'set_type': 'drop', 'percent_1rm': 75, 'reps_min': 8});
      expect(template.percent1rm, 75);
      expect(template.setType, SetType.drop);
      expect(SetTemplate.fromJson({'set_type': 'mystery'}).setType, SetType.working);

      final block = WorkoutBlock.fromJson({'id': 'b', 'type': 'emom', 'work_seconds': 60, 'rounds': 10});
      expect(block.type.isTimed, isTrue);
    });
  });

  group('DeepLinkParser', () {
    test('parses invite and magic links on both schemes', () {
      expect((DeepLinkParser.parse(Uri.parse('https://aurum.coach/invite/SARA-26')) as InviteLink).code, 'SARA-26');
      expect((DeepLinkParser.parse(Uri.parse('aurum://invite/SARA-26')) as InviteLink).code, 'SARA-26');
      expect((DeepLinkParser.parse(Uri.parse('https://sara.aurum.coach/auth/magic/tok123'), hosts: {'aurum.coach'}) as MagicLink).token,
          'tok123');
      expect((DeepLinkParser.parse(Uri.parse('aurum://auth/magic/tok123')) as MagicLink).token, 'tok123');
      expect((DeepLinkParser.parse(Uri.parse('/messages/c1')) as PathLink).path, '/messages/c1');
      expect(DeepLinkParser.parse(Uri.parse('https://evil.com/invite/x'), hosts: {'aurum.coach'}), isNull);
    });
  });

  group('authRedirect', () {
    String? redirect(AuthStatus status, String location) {
      final router = GoRouter(routes: [GoRoute(path: '/:any(.*)', builder: (_, _) => const SizedBox())]);
      final match = router.configuration.findMatch(Uri.parse(location));
      final state = GoRouterState(
        router.configuration,
        uri: Uri.parse(location),
        matchedLocation: Uri.parse(location).path,
        fullPath: '/:any(.*)',
        pathParameters: const {},
        pageKey: const ValueKey('k'),
        topRoute: match.lastOrNull?.route as GoRoute?,
      );
      return authRedirect(
        status,
        state,
        splashPath: '/splash',
        signInPath: '/auth/sign-in',
        homePath: '/today',
        publicPaths: const ['/welcome', '/auth', '/invite'],
        signedInAllowedPublicPaths: const ['/invite'],
      );
    }

    test('unknown status waits on splash', () {
      expect(redirect(AuthStatus.unknown, '/train'), '/splash?from=%2Ftrain');
      expect(redirect(AuthStatus.unknown, '/splash'), isNull);
    });

    test('signed-out users are sent to sign-in but may open public pages', () {
      expect(redirect(AuthStatus.signedOut, '/today'), '/auth/sign-in?from=%2Ftoday');
      expect(redirect(AuthStatus.signedOut, '/invite/ABC'), isNull);
      expect(redirect(AuthStatus.signedOut, '/splash?from=%2Finvite%2FABC'), '/invite/ABC');
    });

    test('signed-in users skip auth screens but can accept invites', () {
      expect(redirect(AuthStatus.signedIn, '/auth/sign-in'), '/today');
      expect(redirect(AuthStatus.signedIn, '/auth/sign-in?from=%2Fnutrition'), '/nutrition');
      expect(redirect(AuthStatus.signedIn, '/invite/ABC'), isNull);
      expect(redirect(AuthStatus.signedIn, '/train'), isNull);
    });
  });
}
