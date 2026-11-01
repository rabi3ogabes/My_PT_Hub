import 'dart:convert';

import 'package:aurum_core/aurum_core.dart';
import 'package:drift/native.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/scripted_adapter.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  late AuDatabase db;
  late ScriptedAdapter adapter;
  late MemoryTokenStore tokens;
  late ApiClient api;
  late SyncEngine sync;

  setUp(() {
    db = AuDatabase(NativeDatabase.memory());
    tokens = MemoryTokenStore();
    adapter = ScriptedAdapter((o) async => Reply.ok(null));
    api = ApiClient(
      config: const ApiConfig(baseUrl: 'https://api.test/api/v1', app: 'client', enableLogging: false),
      tokenStore: tokens,
      locale: () => 'en',
      tenantId: () => null,
      adapter: adapter,
      retrySleep: (_) async {},
    );
    sync = SyncEngine(db: db, api: HttpSyncApi(api));
  });

  tearDown(() async {
    await sync.dispose();
    await db.close();
  });

  group('AuthRepository', () {
    final sessionJson = {
      'access_token': 'acc',
      'refresh_token': 'ref',
      'token_type': 'Bearer',
      'expires_at': '2026-11-01T10:00:00+00:00',
      'refresh_expires_at': '2026-12-31T10:00:00+00:00',
      'user': {
        'id': '01JUSER',
        'name': 'Omar Al-Kuwari',
        'first_name': 'Omar',
        'email': 'omar@example.com',
        'locale': 'ar',
        'timezone': 'Asia/Qatar',
        'units': 'metric',
        'theme': 'dark',
        'email_verified': true,
        'two_factor_enabled': false,
        'current_tenant_id': '01JTENANT',
        'is_super_admin': false,
        'notification_preferences': {
          'messages': {'push': true, 'email': false},
        },
        'quiet_hours': {'enabled': true, 'from': '22:00', 'to': '07:00'},
        'created_at': '2026-10-01T09:00:00+00:00',
      },
      'tenants': [
        {
          'id': '01JTENANT',
          'type': 'business',
          'name': 'Sara Coaching',
          'slug': 'sara',
          'logo_url': null,
          'brand_color': '#D4AF6A',
          'currency': 'QAR',
          'country': 'QA',
          'timezone': 'Asia/Qatar',
          'locale': 'ar',
          'plan': 'pro',
          'trial_ends_at': null,
          'onboarded': true,
          'role': 'client',
        },
      ],
    };

    test('login stores rotated tokens and parses the contract session', () async {
      adapter.handler = (o) async {
        expect(o.path, '/auth/login');
        expect(bodyOf(o)['email'], 'omar@example.com');
        expect(bodyOf(o)['app'], 'client');
        return Reply.ok(sessionJson);
      };
      final repo = AuthRepository(api, tokens);
      final result = await repo.login(
        email: 'omar@example.com',
        password: 'secret123',
        device: const DeviceInfo(app: 'client', platform: 'ios'),
      );
      final session = (result as LoginSuccess).session;
      expect(session.user.firstName, 'Omar');
      expect(session.user.notificationPreferences['messages']!.email, isFalse);
      expect(session.user.quietHours!.from, '22:00');
      expect(session.tenants.single.role, 'client');
      expect((await tokens.read())!.accessToken, 'acc');
    });

    test('login surfaces a two-factor challenge without storing tokens', () async {
      adapter.handler = (o) async => Reply.ok({'two_factor_required': true, 'challenge': 'ch-1'});
      final result = await AuthRepository(api, tokens).login(email: 'a@b.c', password: 'x');
      expect(result, isA<LoginTwoFactorRequired>());
      expect((result as LoginTwoFactorRequired).challenge, 'ch-1');
      expect(await tokens.read(), isNull);
    });

    test('register sends account type, invite code and terms', () async {
      adapter.handler = (o) async {
        final body = bodyOf(o);
        expect(body['account_type'], 'client');
        expect(body['invite_code'], 'SARA-2026');
        expect(body['terms'], isTrue);
        expect(body.containsKey('business_name'), isFalse);
        return Reply.ok(sessionJson);
      };
      await AuthRepository(api, tokens).register(
        name: 'Omar',
        email: 'omar@example.com',
        password: 'secret123',
        accountType: AccountType.client,
        inviteCode: 'SARA-2026',
      );
      expect(await tokens.read(), isNotNull);
    });

    test('invitation preview is fetched without auth', () async {
      adapter.handler = (o) async => Reply.ok({
            'code': 'SARA-2026',
            'role': 'client',
            'email': null,
            'name': 'Omar',
            'tenant': {'name': 'Sara Coaching', 'logo_url': null, 'brand_color': '#A0522D'},
            'coach': {'name': 'Sara'},
          });
      final preview = await AuthRepository(api, tokens).invitation('SARA-2026');
      expect(preview.tenant.brandColor, '#A0522D');
      expect(preview.coach!.name, 'Sara');
      expect(adapter.requests.single.headers.containsKey('Authorization'), isFalse);
    });

    test('logout clears tokens even when offline', () async {
      await tokens.write(const Tokens(accessToken: 'a', refreshToken: 'r'));
      adapter.handler = (o) async => throw const NetworkDown();
      await AuthRepository(api, tokens).logout();
      expect(await tokens.read(), isNull);
    });
  });

  group('cached reads', () {
    final todayJson = {
      'date': '2026-11-01',
      'greeting_name': 'Omar',
      'coach': {'id': 'c1', 'name': 'Sara', 'avatar_url': null},
      'workout': {
        'workout_id': 'w1',
        'program_day_id': 'pd1',
        'program_assignment_id': 'pa1',
        'name': 'Push A',
        'estimated_minutes': 55,
        'exercise_count': 6,
        'week_number': 2,
        'day_number': 1,
        'status': 'pending',
        'session_id': null,
      },
      'streak': {'current': 5, 'best': 21},
      'next_session': null,
      'nutrition': {
        'targets': {'kcal': 2400, 'protein_g': 180, 'carbs_g': 250, 'fat_g': 70},
        'consumed': {'kcal': '850.5', 'protein_g': 60, 'carbs_g': 90, 'fat_g': 20},
        'water_ml': 1250,
        'water_target_ml': 2500,
      },
      'habits': {'completed': 2, 'total': 4},
      'checkin_due': {'id': 'ci1', 'due_on': '2026-11-02', 'form_name': 'Weekly check-in'},
      'unread_messages': 3,
    };

    test('today is fetched, cached, and served from cache when offline', () async {
      adapter.handler = (o) async {
        expect(o.queryParameters['date'], '2026-11-01');
        return Reply.ok(todayJson);
      };
      final repo = TodayRepository(api, db);
      final live = await repo.today(DateTime(2026, 11, 1));
      expect(live.fromCache, isFalse);
      expect(live.value.workout!.name, 'Push A');
      expect(live.value.nutrition.consumed.kcal, 850.5, reason: 'decimal strings are accepted');

      adapter.handler = (o) async => throw const NetworkDown();
      final offline = await repo.today(DateTime(2026, 11, 1));
      expect(offline.fromCache, isTrue);
      expect(offline.value.checkinDue!.formName, 'Weekly check-in');
    });

    test('offline without cache surfaces the failure', () async {
      adapter.handler = (o) async => throw const NetworkDown();
      await expectLater(TodayRepository(api, db).today(DateTime(2026, 11, 1)), throwsA(isA<ApiFailure>()));
    });

    test('null program is cached as null', () async {
      adapter.handler = (o) async => Reply.ok(null);
      final repo = WorkoutRepository(api, db);
      expect((await repo.myProgram()).value, isNull);
      adapter.handler = (o) async => throw const NetworkDown();
      final cached = await repo.myProgram();
      expect(cached.fromCache, isTrue);
      expect(cached.value, isNull);
    });

    test('server errors that are not transient are not masked by the cache', () async {
      adapter.handler = (o) async => Reply.ok(todayJson);
      final repo = TodayRepository(api, db);
      await repo.today(DateTime(2026, 11, 1));
      adapter.handler = (o) async => Reply.error(403, 'forbidden');
      await expectLater(repo.today(DateTime(2026, 11, 1)), throwsA(isA<ApiFailure>().having((f) => f.code, 'c', 'forbidden')));
    });
  });

  group('offline-first writes', () {
    test('food logs are queued with a client ULID; macros left to the server for known foods', () async {
      final repo = NutritionRepository(api, db, sync);
      final log = await repo.logFood(
        date: DateTime(2026, 11, 1),
        slot: 'lunch',
        name: 'Chicken machboos',
        quantity: 300,
        unit: 'g',
        macros: const Macros(kcal: 540, proteinG: 38, carbsG: 60, fatG: 14),
        source: 'search',
        foodId: 'food-1',
      );
      expect(Ulid.isValid(log.id), isTrue);
      expect(log.kcal, 540, reason: 'optimistic local macros for the UI');
      final entry = (await sync.pendingEntries()).single;
      final payload = jsonDecode(entry.payload) as Map<String, dynamic>;
      expect(entry.entity, 'food_log');
      expect(payload['id'], log.id);
      expect(payload['food_id'], 'food-1');
      expect(payload.containsKey('kcal'), isFalse);
      expect(payload['log_date'], '2026-11-01');
    });

    test('quick-add keeps macros in the payload', () async {
      final repo = NutritionRepository(api, db, sync);
      await repo.logFood(
        date: DateTime(2026, 11, 1),
        slot: 'snack',
        name: 'Karak',
        quantity: 1,
        unit: 'serving',
        macros: const Macros(kcal: 180, proteinG: 4, carbsG: 26, fatG: 6),
        source: 'quick',
      );
      final payload = jsonDecode((await sync.pendingEntries()).single.payload) as Map<String, dynamic>;
      expect(payload['kcal'], 180);
      expect(payload['source'], 'quick');
    });

    test('habit completion and body metrics are queued', () async {
      await HabitRepository(api, db, sync).log('assign-1', DateTime(2026, 11, 1), completed: true);
      await ProgressRepository(api, db, sync).logBodyMetric(measuredOn: DateTime(2026, 11, 1), weightKg: 82.4);
      final entities = (await sync.pendingEntries()).map((e) => e.entity).toList();
      expect(entities, ['habit_log', 'body_metric']);
    });

    test('sync push sends the contract mutation shape', () async {
      adapter.handler = (o) async {
        final mutations = (bodyOf(o)['mutations'] as List).cast<Map<String, dynamic>>();
        expect(mutations.single.keys, containsAll(['id', 'entity', 'op', 'payload', 'client_time']));
        return Reply.ok({
          'results': [
            {'id': mutations.single['id'], 'status': 'applied'},
          ],
          'server_time': '2026-11-01T12:00:00+00:00',
        });
      };
      await NutritionRepository(api, db, sync).logWater(DateTime(2026, 11, 1), 250);
      final report = await sync.flush();
      expect(report.applied, 1);
      expect(adapter.requests.single.path, '/sync/push');
    });

    test('text messages go through the compose queue and are listed as outgoing', () async {
      final repo = MessagingRepository(api, db, sync);
      final sent = await repo.sendText('conv-1', 'See you at 6', replyToId: 'm0');
      final outgoing = await repo.outgoing('conv-1');
      expect(outgoing.single.id, sent.id);
      expect(outgoing.single.body, 'See you at 6');
      expect(await repo.outgoing('conv-2'), isEmpty);
    });
  });

  test('barcode lookup returns null for unknown products', () async {
    adapter.handler = (o) async => Reply.error(404, 'not_found');
    expect(await NutritionRepository(api, db, sync).barcode('6291041500213'), isNull);
  });
}
