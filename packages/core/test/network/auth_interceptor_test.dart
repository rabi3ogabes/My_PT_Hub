import 'dart:async';

import 'package:aurum_core/aurum_core.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/scripted_adapter.dart';

void main() {
  late MemoryTokenStore store;
  late ScriptedAdapter adapter;
  late ApiClient client;
  late int expiredCalls;
  late FakeClock clock;

  Tokens tokens(String access, String refresh, {DateTime? expiresAt}) =>
      Tokens(accessToken: access, refreshToken: refresh, expiresAt: expiresAt);

  Map<String, dynamic> tokenJson(String access, String refresh) => {
        'access_token': access,
        'refresh_token': refresh,
        'token_type': 'Bearer',
        'expires_at': '2026-11-01T11:00:00+00:00',
        'refresh_expires_at': '2026-12-31T00:00:00+00:00',
      };

  ApiClient build() => ApiClient(
        config: const ApiConfig(baseUrl: 'https://api.test/api/v1', app: 'client', enableLogging: false),
        tokenStore: store,
        locale: () => 'en',
        tenantId: () => null,
        clock: clock,
        adapter: adapter,
        retrySleep: (_) async {},
        onSessionExpired: () => expiredCalls++,
      );

  setUp(() {
    clock = FakeClock(DateTime.utc(2026, 11, 1, 9));
    store = MemoryTokenStore(tokens('old-access', 'old-refresh', expiresAt: DateTime.utc(2026, 11, 1, 10)));
    expiredCalls = 0;
  });

  test('attaches the bearer token', () async {
    adapter = ScriptedAdapter((o) async => Reply.ok({'ok': true}));
    client = build();
    await client.get('/me');
    expect(adapter.requests.single.headers['Authorization'], 'Bearer old-access');
  });

  test('401 refreshes once, rotates tokens and replays the request', () async {
    adapter = ScriptedAdapter((o) async {
      if (o.path == '/auth/refresh') {
        expect(bodyOf(o)['refresh_token'], 'old-refresh');
        return Reply.ok(tokenJson('new-access', 'new-refresh'));
      }
      if (o.headers['Authorization'] == 'Bearer new-access') return Reply.ok({'id': 'u1'});
      return Reply.error(401, 'unauthenticated');
    });
    client = build();

    final envelope = await client.get('/me');

    expect(envelope.dataMap['id'], 'u1');
    expect(adapter.requestsTo('/auth/refresh'), hasLength(1));
    expect(adapter.requestsTo('/auth/refresh').single.headers.containsKey('Authorization'), isFalse);
    final stored = await store.read();
    expect(stored!.accessToken, 'new-access');
    expect(stored.refreshToken, 'new-refresh');
    expect(expiredCalls, 0);
  });

  test('concurrent 401s share a single refresh (request queueing)', () async {
    final refreshGate = Completer<void>();
    adapter = ScriptedAdapter((o) async {
      if (o.path == '/auth/refresh') {
        await refreshGate.future;
        return Reply.ok(tokenJson('new-access', 'new-refresh'));
      }
      if (o.headers['Authorization'] == 'Bearer new-access') return Reply.ok({'path': o.path});
      return Reply.error(401, 'unauthenticated');
    });
    client = build();

    final futures = [client.get('/a'), client.get('/b'), client.get('/c')];
    // Let all three hit 401 before the refresh completes.
    await Future<void>.delayed(const Duration(milliseconds: 20));
    refreshGate.complete();
    final results = await Future.wait(futures);

    expect(results.map((e) => e.dataMap['path']), ['/a', '/b', '/c']);
    expect(adapter.requestsTo('/auth/refresh'), hasLength(1), reason: 'refresh tokens are single-use');
    expect(client.auth.refreshCount, 1);
  });

  test('rejected refresh token clears the session and reports unauthenticated', () async {
    adapter = ScriptedAdapter((o) async {
      if (o.path == '/auth/refresh') return Reply.error(401, 'invalid_refresh_token');
      return Reply.error(401, 'unauthenticated');
    });
    client = build();

    await expectLater(
      client.get('/me'),
      throwsA(isA<ApiFailure>().having((f) => f.isUnauthenticated, 'unauthenticated', isTrue)),
    );
    expect(await store.read(), isNull);
    expect(expiredCalls, 1);
  });

  test('network failure during refresh keeps the session (offline is not a logout)', () async {
    adapter = ScriptedAdapter((o) async {
      if (o.path == '/auth/refresh') throw const NetworkDown();
      return Reply.error(401, 'unauthenticated');
    });
    client = build();

    await expectLater(client.get('/me'), throwsA(isA<ApiFailure>()));
    expect((await store.read())!.refreshToken, 'old-refresh');
    expect(expiredCalls, 0);
  });

  test('refreshes proactively when the access token is already expired', () async {
    clock.set(DateTime.utc(2026, 11, 1, 10, 30));
    adapter = ScriptedAdapter((o) async {
      if (o.path == '/auth/refresh') return Reply.ok(tokenJson('fresh', 'r2'));
      return Reply.ok({'auth': o.headers['Authorization']});
    });
    client = build();

    final envelope = await client.get('/me/today');

    expect(envelope.dataMap['auth'], 'Bearer fresh');
    expect(adapter.requests.map((r) => r.path), ['/auth/refresh', '/me/today']);
  });

  test('public requests never carry a token', () async {
    adapter = ScriptedAdapter((o) async => Reply.ok({'code': 'X'}));
    client = build();
    await client.get('/invitations/X', options: ApiClient.public);
    expect(adapter.requests.single.headers.containsKey('Authorization'), isFalse);
  });
}
