import 'package:aurum_core/aurum_core.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/scripted_adapter.dart';

void main() {
  late ScriptedAdapter adapter;
  final sleeps = <Duration>[];
  var locale = 'ar';
  String? tenant = 'tenant-1';

  ApiClient build() => ApiClient(
        config: const ApiConfig(baseUrl: 'https://api.test/api/v1', app: 'client', appVersion: '1.0.0', enableLogging: false),
        tokenStore: MemoryTokenStore(const Tokens(accessToken: 'a', refreshToken: 'r')),
        locale: () => locale,
        tenantId: () => tenant,
        adapter: adapter,
        retrySleep: (d) async => sleeps.add(d),
      );

  setUp(() {
    sleeps.clear();
    locale = 'ar';
    tenant = 'tenant-1';
  });

  test('unwraps the envelope and exposes meta for cursor pages', () async {
    adapter = ScriptedAdapter((o) async => Reply.ok(
          [
            {'id': 'n1', 'title': 'Hi', 'created_at': '2026-11-01T09:00:00+00:00'},
          ],
          meta: {'per_page': 20, 'next_cursor': 'abc', 'prev_cursor': null, 'unread_count': 4},
        ));
    final page = await build().getPage('/notifications', AppNotification.fromJson);
    expect(page.items.single.title, 'Hi');
    expect(page.nextCursor, 'abc');
    expect(page.hasMore, isTrue);
    expect(page.meta['unread_count'], 4);
  });

  test('sends Accept-Language, X-Tenant and Accept headers read lazily', () async {
    adapter = ScriptedAdapter((o) async => Reply.ok(null));
    final client = build();
    await client.get('/me/today');
    locale = 'fr';
    tenant = 'tenant-2';
    await client.get('/me/today');
    expect(adapter.requests[0].headers['Accept-Language'], 'ar');
    expect(adapter.requests[0].headers['X-Tenant'], 'tenant-1');
    expect(adapter.requests[0].headers['Accept'], 'application/json');
    expect(adapter.requests[1].headers['Accept-Language'], 'fr');
    expect(adapter.requests[1].headers['X-Tenant'], 'tenant-2');
  });

  test('maps validation errors to a typed ApiFailure with field errors', () async {
    adapter = ScriptedAdapter((o) async => Reply.error(
          422,
          'validation_failed',
          message: 'The email has already been taken.',
          fieldErrors: {
            'email': ['The email has already been taken.'],
          },
        ));
    await expectLater(
      build().post('/auth/register', data: {}),
      throwsA(isA<ApiFailure>()
          .having((f) => f.code, 'code', ApiFailure.validationFailed)
          .having((f) => f.statusCode, 'status', 422)
          .having((f) => f.fieldError('email'), 'field', 'The email has already been taken.')),
    );
  });

  test('maps status codes without an envelope', () async {
    adapter = ScriptedAdapter((o) async => const Reply(503, 'Service Unavailable'));
    await expectLater(
      build().post('/x'),
      throwsA(isA<ApiFailure>().having((f) => f.code, 'code', ApiFailure.serverError).having((f) => f.isTransient, 'transient', isTrue)),
    );
  });

  test('retries idempotent GETs with exponential backoff', () async {
    var calls = 0;
    adapter = ScriptedAdapter((o) async {
      calls++;
      return calls < 3 ? Reply.error(503, 'server_error') : Reply.ok({'ok': true});
    });
    final envelope = await build().get('/me/today');
    expect(envelope.dataMap['ok'], isTrue);
    expect(calls, 3);
    expect(sleeps, hasLength(2));
    // Second delay window is larger than the first (exponential).
    expect(sleeps[1].inMilliseconds, greaterThanOrEqualTo(400));
  });

  test('honors Retry-After on 429', () async {
    var calls = 0;
    adapter = ScriptedAdapter((o) async {
      calls++;
      return calls == 1
          ? const Reply(429, {'data': null, 'errors': <dynamic>[]}, {'retry-after': ['3']})
          : Reply.ok({'ok': true});
    });
    await build().get('/me/today');
    expect(sleeps.single, const Duration(seconds: 3));
  });

  test('does not retry non-idempotent POSTs', () async {
    var calls = 0;
    adapter = ScriptedAdapter((o) async {
      calls++;
      return Reply.error(503, 'server_error');
    });
    await expectLater(build().post('/me/bookings', data: {'event_id': 'e'}), throwsA(isA<ApiFailure>()));
    expect(calls, 1);
  });

  test('retries POSTs explicitly marked idempotent and gives up after max retries', () async {
    var calls = 0;
    adapter = ScriptedAdapter((o) async {
      calls++;
      throw const NetworkDown();
    });
    await expectLater(
      build().post('/sync/push', data: {}, options: ApiClient.idempotent),
      throwsA(isA<ApiFailure>().having((f) => f.code, 'code', ApiFailure.network)),
    );
    expect(calls, 4, reason: '1 attempt + 3 retries');
  });

  test('backoff is capped', () {
    final interceptor = RetryInterceptor(dio: build().dio, maxDelay: const Duration(seconds: 8));
    for (var i = 0; i < 10; i++) {
      expect(interceptor.delayFor(i).inMilliseconds, lessThanOrEqualTo(8000));
    }
  });
}
