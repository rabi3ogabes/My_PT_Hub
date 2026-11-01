// Aurum API smoke + load test (k6).
//
//   make k6                                         # smoke: 1 VU, 30 s
//   SCENARIO=load VUS=500 DURATION=10m make k6      # load profile
//   docker run --rm -i -e BASE_URL=https://api.staging.aurum.coach/api/v1 \
//     -e EMAIL=demo.client@aurum.coach -e PASSWORD=... grafana/k6 run - < infra/k6/api-smoke.js
//
// Thresholds mirror the product NFRs: API p95 < 200 ms, error rate < 0.5 %.
// EMAIL/PASSWORD should be a seeded demo client (SEED_DEMO=true) on non-production stacks.
import http from 'k6/http';
import { check, group, sleep, fail } from 'k6';
import { Trend, Rate } from 'k6/metrics';

const BASE_URL = (__ENV.BASE_URL || 'http://localhost:8000/api/v1').replace(/\/$/, '');
const EMAIL = __ENV.EMAIL || 'demo.client@aurum.coach';
const PASSWORD = __ENV.PASSWORD || 'password';
const SCENARIO = __ENV.SCENARIO || 'smoke';
const LOCALES = ['en', 'ar', 'fr', 'es'];

const todayTrend = new Trend('aurum_today_ms', true);
const syncTrend = new Trend('aurum_sync_push_ms', true);
const envelopeOk = new Rate('aurum_envelope_ok');

const scenarios = {
  smoke: { executor: 'constant-vus', vus: 1, duration: __ENV.DURATION || '30s' },
  load: {
    executor: 'ramping-vus',
    startVUs: 0,
    stages: [
      { duration: '2m', target: Number(__ENV.VUS || 500) },
      { duration: __ENV.DURATION || '10m', target: Number(__ENV.VUS || 500) },
      { duration: '1m', target: 0 },
    ],
    gracefulRampDown: '30s',
  },
  spike: {
    executor: 'ramping-arrival-rate',
    startRate: 50,
    timeUnit: '1s',
    preAllocatedVUs: 200,
    maxVUs: Number(__ENV.VUS || 2000),
    stages: [{ duration: '30s', target: 50 }, { duration: '30s', target: 1500 }, { duration: '2m', target: 1500 }, { duration: '30s', target: 50 }],
  },
};

export const options = {
  scenarios: { [SCENARIO]: scenarios[SCENARIO] },
  thresholds: {
    http_req_failed: ['rate<0.005'],
    'http_req_duration{expected_response:true}': ['p(95)<200', 'p(99)<500'],
    aurum_today_ms: ['p(95)<200'],
    aurum_sync_push_ms: ['p(95)<250'],
    aurum_envelope_ok: ['rate>0.99'],
    checks: ['rate>0.99'],
  },
  summaryTrendStats: ['avg', 'med', 'p(90)', 'p(95)', 'p(99)', 'max'],
};

// Crockford base32 ULID (time + randomness) for idempotent offline-style writes.
const B32 = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
function ulid() {
  let t = Date.now();
  let time = '';
  for (let i = 0; i < 10; i++) { time = B32[t % 32] + time; t = Math.floor(t / 32); }
  let rand = '';
  for (let i = 0; i < 16; i++) rand += B32[Math.floor(Math.random() * 32)];
  return time + rand;
}

function headers(token, locale) {
  return {
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'Accept-Language': locale,
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
  };
}

function isEnvelope(res) {
  try {
    const body = res.json();
    return body && 'data' in body && 'meta' in body && Array.isArray(body.errors);
  } catch (_) {
    return false;
  }
}

export function setup() {
  const res = http.get(`${BASE_URL.replace(/\/api\/v1$/, '')}/up`);
  if (res.status !== 200) fail(`health check failed: ${res.status}`);
  const login = http.post(`${BASE_URL}/auth/login`, JSON.stringify({ email: EMAIL, password: PASSWORD, device_name: 'k6', platform: 'web' }), headers(null, 'en'));
  if (login.status !== 200) fail(`login failed (${login.status}) — seed demo data or pass EMAIL/PASSWORD`);
  const body = login.json();
  if (body.data.two_factor_required) fail('test account must not have 2FA enabled');
  return { token: body.data.access_token };
}

export default function (data) {
  const locale = LOCALES[__VU % LOCALES.length];
  const h = headers(data.token, locale);
  const today = new Date().toISOString().slice(0, 10);

  group('client home', () => {
    const me = http.get(`${BASE_URL}/me`, { ...h, tags: { name: 'GET /me' } });
    check(me, { 'me 200': (r) => r.status === 200 });
    envelopeOk.add(isEnvelope(me));

    const t = http.get(`${BASE_URL}/me/today?date=${today}`, { ...h, tags: { name: 'GET /me/today' } });
    check(t, { 'today 200': (r) => r.status === 200 });
    todayTrend.add(t.timings.duration);
    envelopeOk.add(isEnvelope(t));
  });

  group('library search', () => {
    const q = { en: 'bench', ar: 'ضغط', fr: 'développé', es: 'press' }[locale];
    const ex = http.get(`${BASE_URL}/exercises?q=${encodeURIComponent(q)}`, { ...h, tags: { name: 'GET /exercises' } });
    check(ex, { 'exercises 200': (r) => r.status === 200 });
    const foods = http.get(`${BASE_URL}/foods?q=${encodeURIComponent(locale === 'ar' ? 'كبسة' : 'rice')}`, { ...h, tags: { name: 'GET /foods' } });
    check(foods, { 'foods 200': (r) => r.status === 200 });
  });

  group('nutrition + habits', () => {
    const n = http.get(`${BASE_URL}/me/nutrition?date=${today}`, { ...h, tags: { name: 'GET /me/nutrition' } });
    check(n, { 'nutrition 200': (r) => r.status === 200 });
    const hb = http.get(`${BASE_URL}/me/habits?date=${today}`, { ...h, tags: { name: 'GET /me/habits' } });
    check(hb, { 'habits 200': (r) => r.status === 200 });
  });

  group('offline sync', () => {
    const since = new Date(Date.now() - 3600 * 1000).toISOString();
    const pull = http.get(`${BASE_URL}/sync/pull?since=${encodeURIComponent(since)}`, { ...h, tags: { name: 'GET /sync/pull' } });
    check(pull, { 'pull 200': (r) => r.status === 200 });

    // Water log mutation sent twice: the second push must be reported as a duplicate (idempotency).
    const mutation = { id: ulid(), entity: 'water_log', op: 'upsert', payload: { log_date: today, ml: 250 }, client_time: new Date().toISOString() };
    const push = http.post(`${BASE_URL}/sync/push`, JSON.stringify({ mutations: [mutation] }), { ...h, tags: { name: 'POST /sync/push' } });
    syncTrend.add(push.timings.duration);
    check(push, { 'push 200': (r) => r.status === 200 });
    if (SCENARIO === 'smoke') {
      const again = http.post(`${BASE_URL}/sync/push`, JSON.stringify({ mutations: [mutation] }), { ...h, tags: { name: 'POST /sync/push (replay)' } });
      check(again, {
        'replay is duplicate': (r) => r.status === 200 && r.json('data.results.0.status') === 'duplicate',
      });
    }
  });

  group('messaging', () => {
    const c = http.get(`${BASE_URL}/conversations`, { ...h, tags: { name: 'GET /conversations' } });
    check(c, { 'conversations 200': (r) => r.status === 200 });
    const n = http.get(`${BASE_URL}/notifications`, { ...h, tags: { name: 'GET /notifications' } });
    check(n, { 'notifications 200': (r) => r.status === 200 });
  });

  sleep(SCENARIO === 'smoke' ? 1 : Math.random() * 3 + 1);
}

export function handleSummary(data) {
  const p95 = data.metrics.http_req_duration.values['p(95)'];
  const failed = data.metrics.http_req_failed.values.rate;
  return {
    stdout: `\nAurum ${SCENARIO}: p95=${p95.toFixed(1)}ms failed=${(failed * 100).toFixed(2)}% (target p95<200ms, failed<0.5%)\n`,
    'k6-summary.json': JSON.stringify(data, null, 2),
  };
}
