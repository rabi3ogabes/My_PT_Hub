#!/usr/bin/env node
/**
 * Generates docs/openapi.yaml (OpenAPI 3.1) from backend/routes/api.php.
 *
 * - Every route in routes/api.php is discovered by parsing the route file
 *   (groups, prefixes, middleware, apiResource expansion, whereIn actions).
 * - Each operation's summary, request and response schema come from OPS below;
 *   component schemas live in ./schemas.mjs and mirror docs/API_CONTRACT.md.
 * - The script exits 1 when a route has no OPS entry (or an OPS entry has no
 *   route), so CI fails whenever the API and its documentation drift.
 *
 * Usage:  node infra/scripts/openapi/generate.mjs [--check]
 *   --check  verify coverage and that docs/openapi.yaml is up to date; write nothing.
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { schemas, T, obj } from './schemas.mjs';

const root = join(dirname(fileURLToPath(import.meta.url)), '..', '..', '..');
const routesFile = join(root, 'backend', 'routes', 'api.php');
const outFile = join(root, 'docs', 'openapi.yaml');

// ---------------------------------------------------------------- route parser
function camelSingular(seg) {
  const s = seg.replace(/ies$/, 'y').replace(/(ses|xes)$/, (m) => m.slice(0, -2)).replace(/s$/, '');
  return s.replace(/-([a-z])/g, (_, c) => c.toUpperCase());
}

function parseRoutes(src) {
  const routes = [];
  const stack = [{ prefix: '', middleware: [] }];
  const ctx = () => ({
    prefix: stack.map((s) => s.prefix).filter(Boolean).join('/'),
    middleware: stack.flatMap((s) => s.middleware),
  });
  const mwOf = (s) => {
    const out = [];
    for (const m of s.matchAll(/middleware\((\[[^\]]*\]|'[^']*')\)/g)) {
      out.push(...[...m[1].matchAll(/'([^']+)'/g)].map((x) => x[1]));
    }
    return out;
  };
  for (const raw of src.split('\n')) {
    const line = raw.trim();
    if (line.startsWith('//') || !line) continue;
    if (/->group\(function\s*\(\)\s*\{\s*$/.test(line) || /^Route::(prefix|middleware|name)\(.*\{\s*$/.test(line)) {
      const prefix = (line.match(/prefix\('([^']*)'\)/) || [])[1] || '';
      stack.push({ prefix, middleware: mwOf(line) });
      continue;
    }
    if (/^\}\);/.test(line)) { stack.pop(); continue; }
    const c = ctx();
    const join2 = (p) => '/' + [c.prefix, p].filter(Boolean).join('/').replace(/^v1\/?/, '');
    let m = line.match(/^Route::(get|post|put|patch|delete)\('([^']*)',\s*\[([^\]]+)\]\)(.*);/);
    if (m) {
      const [, method, path, handler, tail] = m;
      const mw = [...c.middleware, ...mwOf(tail)];
      const whereIn = tail.match(/whereIn\('(\w+)',\s*\[([^\]]+)\]\)/);
      const hp = handler.split(',').map((x) => x.trim());
      const base = { method: method.toUpperCase(), middleware: mw, controller: hp[0].replace('::class', '').replace(/^V1\\/, ''), action: hp[1].replace(/'/g, '') };
      if (whereIn) {
        for (const v of [...whereIn[2].matchAll(/'([^']+)'/g)].map((x) => x[1])) {
          routes.push({ ...base, path: join2(path.replace(`{${whereIn[1]}}`, v)) });
        }
      } else routes.push({ ...base, path: join2(path) });
      continue;
    }
    m = line.match(/^Route::apiResource\('([^']+)',\s*([\w\\]+)::class\)(.*);/);
    if (m) {
      const [, name, controller, tail] = m;
      const except = [...(tail.match(/except\(([^)]*)\)/)?.[1] ?? '').matchAll(/'(\w+)'/g)].map((x) => x[1]);
      const param = `{${camelSingular(name.split('/').pop())}}`;
      const mw = [...c.middleware, ...mwOf(tail)];
      const ctrl = controller.replace(/^V1\\/, '');
      const defs = [['index', 'GET', name], ['store', 'POST', name], ['show', 'GET', `${name}/${param}`], ['update', 'PUT', `${name}/${param}`], ['update', 'PATCH', `${name}/${param}`], ['destroy', 'DELETE', `${name}/${param}`]];
      for (const [action, method, p] of defs) {
        if (except.includes(action)) continue;
        routes.push({ method, path: join2(p), middleware: mw, controller: ctrl, action });
      }
    }
  }
  return routes;
}

// ---------------------------------------------------------------- operation table
// r: response schema shorthand ('$X', '[$X]', {…}); list: cursor-paginated array of X;
// page: length-aware paginated; status: success code; b: JSON body; mp: multipart body;
// q: query params [name, type, description]; bin: binary response content-type.
const C = (x) => ({ list: x });
const P = (x) => ({ page: x });
const NULL = null;
const OPS = {
  // Auth (public)
  'POST /auth/register': { t: 'Auth', s: 'Create an account (coach, client or standalone)', b: '$RegisterRequest', r: '$AuthSession', status: 201, e: [422, 410] },
  'POST /auth/login': { t: 'Auth', s: 'Sign in with email + password', d: 'Returns a Session, or `{two_factor_required, challenge}` when TOTP is enabled. Account locks for 15 minutes after repeated failures (423 `account_locked`).', b: { allOf: [obj({ email: 'email', password: 'string' }), { $ref: '#/components/schemas/DeviceFields' }] }, r: { oneOf: [{ $ref: '#/components/schemas/AuthSession' }, { $ref: '#/components/schemas/TwoFactorRequired' }] }, e: [422, 423] },
  'POST /auth/two-factor': { t: 'Auth', s: 'Complete a two-factor challenge', b: { allOf: [obj({ challenge: 'string', code: 'string' }), { $ref: '#/components/schemas/DeviceFields' }] }, r: '$AuthSession', e: [422] },
  'POST /auth/magic-link': { t: 'Auth', s: 'Email a one-time sign-in link', b: obj({ email: 'email' }), r: obj({ sent: 'bool' }), d: 'Always answers `sent: true` to avoid account enumeration.' },
  'POST /auth/magic-link/verify': { t: 'Auth', s: 'Exchange a magic-link token for a session', b: { allOf: [obj({ token: 'string' }), { $ref: '#/components/schemas/DeviceFields' }] }, r: '$AuthSession', e: [422] },
  'POST /auth/social/{provider}': { t: 'Auth', s: 'Sign in with Apple or Google', b: obj({ token: 'string', 'name?': 'string', 'account_type?': 'coach|client|standalone', 'invite_code?': 'string' }), r: '$AuthSession', p: { provider: 'google|apple' }, e: [422] },
  'POST /auth/forgot-password': { t: 'Auth', s: 'Send a password reset email', b: obj({ email: 'email' }), r: obj({ sent: 'bool' }) },
  'POST /auth/reset-password': { t: 'Auth', s: 'Reset password with an emailed token', b: obj({ token: 'string', email: 'email', password: 'string', password_confirmation: 'string' }), r: obj({ reset: 'bool' }), e: [422] },
  'POST /auth/refresh': { t: 'Auth', s: 'Rotate tokens with a refresh token', d: 'Refresh tokens are single-use: every call rotates the pair and revokes the presented token; an unknown, expired or already-used token answers 401 `invalid_refresh_token`.', b: obj({ refresh_token: 'string' }), r: '$Tokens', e: [401] },
  'GET /invitations/{code}': { t: 'Auth', s: 'Preview an invitation before accepting', r: '$InvitationPreview', e: [404, 410] },

  // Public marketplace / checkout
  'GET /public/coaches': { t: 'Marketplace', s: 'Search the public coach directory', q: [['city', 'string'], ['specialty', 'string'], ['language', 'string'], ['online', 'bool'], ['in_person', 'bool'], ['gender', 'string'], ['price_max', 'int', 'Minor units'], ['q', 'string'], ['cursor', 'string']], r: C('$PublicCoach') },
  'GET /public/coaches/{slug}': { t: 'Marketplace', s: 'Public coach profile with packages and verified reviews', r: '$PublicCoach', e: [404] },
  'GET /public/coaches/{slug}/slots': { t: 'Marketplace', s: 'Bookable consultation slots for a coach', q: [['date', 'date']], r: '[$Slot]' },
  'POST /public/coaches/{slug}/leads': { t: 'Marketplace', s: 'Send an enquiry to a coach (creates a CRM lead)', b: '$LeadCapture', r: obj({ received: 'bool' }), status: 201, e: [422] },
  'GET /public/checkout/{tenantSlug}/{productSlug}': { t: 'Public checkout', s: 'Branded checkout page data for a product', r: obj({ tenant: obj({ name: 'string', logo_url: 'uri?', brand_color: 'string' }), product: '$Product' }), e: [404] },
  'POST /public/checkout/{tenantSlug}/{productSlug}': { t: 'Public checkout', s: 'Start a guest checkout', b: '$PublicCheckoutRequest', r: '$CheckoutResult', status: 201, idem: true, e: [422] },
  'GET /public/orders/{order}/status': { t: 'Public checkout', s: 'Poll order payment and fulfilment status', r: '$OrderStatus', e: [404] },
  'GET /public/program-templates': { t: 'Marketplace', s: 'Platform-public program templates', q: [['goal', 'string'], ['level', 'string'], ['cursor', 'string']], r: C('$Program') },

  // Account
  'POST /auth/logout': { t: 'Account', s: 'Revoke the current access + refresh token', r: NULL },
  'GET /me': { t: 'Account', s: 'Current user and memberships', r: '$Me' },
  'PATCH /me': { t: 'Account', s: 'Update profile, preferences or password', b: obj({ 'name?': 'string', 'phone?': 'string', 'locale?': '$Locale', 'timezone?': 'string', 'units?': 'metric|imperial', 'theme?': 'light|dark|system', 'notification_preferences?': 'json', 'quiet_hours?': 'json', 'current_password?': 'string', 'password?': 'string' }), r: '$Me', e: [422] },
  'POST /me/avatar': { t: 'Account', s: 'Upload avatar', mp: obj({ avatar: 'binary' }), r: '$User', e: [422] },
  'POST /me/devices': { t: 'Account', s: 'Register or refresh a push device', b: obj({ platform: 'ios|android|web', 'push_token?': 'string', app: 'client|coach', 'app_version?': 'string', 'device_name?': 'string', 'timezone?': 'string' }), r: obj({ id: 'ulid' }) },
  'POST /me/two-factor': { t: 'Account', s: 'Start TOTP enrolment', r: '$TwoFactorSetup' },
  'POST /me/two-factor/confirm': { t: 'Account', s: 'Confirm TOTP enrolment with a code', b: obj({ code: 'string' }), r: '$User', e: [422] },
  'DELETE /me/two-factor': { t: 'Account', s: 'Disable two-factor authentication', b: obj({ current_password: 'string' }), r: '$User', e: [422] },
  'POST /me/export': { t: 'Account', s: 'Request a GDPR data export (emailed zip)', r: '$ExportStatus', status: 202 },
  'DELETE /me': { t: 'Account', s: 'Delete my account (GDPR erasure, queued)', b: obj({ 'current_password?': 'string', confirm: { type: 'string', const: 'DELETE' } }), r: NULL, e: [422] },
  'POST /invitations/accept': { t: 'Account', s: 'Accept an invitation into a tenant', b: obj({ code: 'string' }), r: '$Me', e: [410] },
  'POST /tenants': { t: 'Account', s: 'Create an additional business tenant', b: obj({ business_name: 'string', 'currency?': 'string', 'country?': 'string', 'timezone?': 'string', 'locale?': '$Locale' }), r: '$Tenant', status: 201 },
  'POST /tenants/switch': { t: 'Account', s: 'Switch current tenant', b: obj({ tenant_id: 'ulid' }), r: '$Me', e: [403] },
  'GET /notifications': { t: 'Account', s: 'In-app notification centre', q: [['category', 'string'], ['unread', 'bool'], ['cursor', 'string']], r: C('$Notification'), d: '`meta.unread_count` carries the badge count.' },
  'POST /notifications/read': { t: 'Account', s: 'Mark notifications read (all when ids omitted)', b: obj({ 'ids?': '[string]' }), r: obj({ unread_count: 'int' }) },
  'GET /marketplace/coaches': { t: 'Marketplace', s: 'Coach directory for signed-in standalone clients', q: [['city', 'string'], ['specialty', 'string'], ['language', 'string'], ['online', 'bool'], ['cursor', 'string']], r: C('$PublicCoach') },
  'POST /marketplace/match': { t: 'Marketplace', s: 'Goal quiz → ranked coach matches + introduction request', b: '$MatchRequest', r: obj({ matches: '[$PublicCoach]' }) },

  // Tenant
  'GET /tenant': { t: 'Tenancy', s: 'Active tenant', r: '$Tenant' },
  'PATCH /tenant': { t: 'Owner settings', s: 'Update business settings (name, brand colour, currency, legal details)', b: obj({ 'name?': 'string', 'brand_color?': 'string', 'currency?': 'string', 'country?': 'string', 'timezone?': 'string', 'locale?': '$Locale', 'legal?': 'json', 'settings?': 'json' }), r: '$Tenant', e: [422] },
  'POST /tenant/logo': { t: 'Owner settings', s: 'Upload tenant logo', mp: obj({ logo: 'binary' }), r: '$Tenant' },

  // Messaging
  'GET /conversations': { t: 'Messaging', s: 'Conversations I participate in', q: [['type', 'string'], ['q', 'string'], ['cursor', 'string']], r: C('$Conversation') },
  'POST /conversations': { t: 'Messaging', s: 'Open a direct or group conversation', b: obj({ type: 'direct|group', participant_ids: '[ulid]', 'title?': 'string' }), r: '$Conversation', status: 201 },
  'GET /conversations/{conversation}': { t: 'Messaging', s: 'Conversation detail', r: '$Conversation', e: [403, 404] },
  'GET /conversations/{conversation}/messages': { t: 'Messaging', s: 'Message history (newest first)', q: [['cursor', 'string']], r: C('$Message') },
  'POST /conversations/{conversation}/messages': { t: 'Messaging', s: 'Send a message (idempotent on client ULID)', b: '$SendMessage', mp: '$SendMessageMultipart', r: '$Message', status: 201 },
  'POST /conversations/{conversation}/read': { t: 'Messaging', s: 'Mark conversation read (read receipt)', r: obj({ unread_count: 'int' }) },
  'POST /conversations/{conversation}/typing': { t: 'Messaging', s: 'Broadcast a typing indicator', r: NULL },
  'PATCH /messages/{message}': { t: 'Messaging', s: 'Edit own message (within 15 minutes)', b: obj({ body: 'string' }), r: '$Message', e: [403] },
  'DELETE /messages/{message}': { t: 'Messaging', s: 'Delete for everyone', r: NULL, e: [403] },
  'POST /messages/{message}/reactions': { t: 'Messaging', s: 'Toggle a reaction', b: obj({ emoji: 'string' }), r: '$Message' },
  'GET /messages/{message}/attachments/{attachment}': { t: 'Messaging', s: 'Download an attachment (participant-checked, signed redirect)', bin: 'application/octet-stream', e: [403, 404] },

  // Community
  'GET /community/spaces': { t: 'Community', s: 'Spaces I can see', r: '[$Space]' },
  'GET /community/spaces/{space}/posts': { t: 'Community', s: 'Feed for a space', q: [['cursor', 'string']], r: C('$Post') },
  'POST /community/spaces/{space}/posts': { t: 'Community', s: 'Create a post (text, media, poll, workout share)', b: obj({ type: 'text|media|poll|workout_share', 'body?': 'string', 'meta?': 'json' }), r: '$Post', status: 201, e: [403] },
  'DELETE /community/posts/{post}': { t: 'Community', s: 'Delete own post (staff: any)', r: NULL },
  'GET /community/posts/{post}/comments': { t: 'Community', s: 'Comments on a post', q: [['cursor', 'string']], r: C('$Comment') },
  'POST /community/posts/{post}/comments': { t: 'Community', s: 'Comment on a post', b: obj({ body: 'string' }), r: '$Comment', status: 201 },
  'POST /community/posts/{post}/react': { t: 'Community', s: 'Toggle reaction on a post', b: obj({ emoji: 'string' }), r: '$Post' },
  'POST /community/posts/{post}/vote': { t: 'Community', s: 'Vote in a poll', b: obj({ option: 'int' }), r: '$Post' },
  'POST /community/reports': { t: 'Community', s: 'Report a post, comment or message', b: obj({ reportable_type: 'post|comment|message', reportable_id: 'ulid', reason: 'string' }), r: '$Report', status: 201 },
  'GET /challenges': { t: 'Community', s: 'Active and upcoming challenges', r: '[$Challenge]' },
  'GET /challenges/{challenge}': { t: 'Community', s: 'Challenge with leaderboard', r: '$Challenge' },
  'POST /challenges/{challenge}/join': { t: 'Community', s: 'Join a challenge', b: obj({ 'team?': 'string' }), r: '$Challenge' },
  'GET /badges': { t: 'Community', s: 'Badge cabinet', r: '[$Badge]' },

  // Library
  'GET /exercises': { t: 'Library', s: 'Search exercises (4 languages + aliases)', q: [['q', 'string'], ['muscle', 'string'], ['equipment', 'string'], ['level', 'string'], ['favorites', 'bool'], ['cursor', 'string']], r: C('$ExerciseListItem') },
  'GET /exercises/{exercise}': { t: 'Library', s: 'Exercise detail', r: '$Exercise', etag: true },
  'GET /foods': { t: 'Library', s: 'Search foods', q: [['q', 'string'], ['region', 'string'], ['cursor', 'string']], r: C('$Food') },
  'GET /foods/barcode/{code}': { t: 'Library', s: 'Barcode lookup (falls back to Open Food Facts)', r: '$Food', e: [404] },
  'GET /foods/{food}': { t: 'Library', s: 'Food detail', r: '$Food' },
  'GET /recipes': { t: 'Library', s: 'Search recipes', q: [['q', 'string'], ['cuisine', 'string'], ['tag', 'string'], ['cursor', 'string']], r: C('$Recipe') },
  'GET /recipes/{recipe}': { t: 'Library', s: 'Recipe detail', r: '$Recipe', etag: true },

  // Sync
  'GET /sync/pull': { t: 'Sync', s: 'Pull server changes since a timestamp', q: [['since', 'datetime', 'Last `server_time` received', true]], r: '$SyncPull' },
  'POST /sync/push': { t: 'Sync', s: 'Push batched offline mutations', d: 'Each mutation is applied idempotently on its ULID. Conflict policy: server timestamps, last-write-wins per field, session sets append-only.', b: '$SyncPush', r: '$SyncPushResult' },

  // Client self
  'GET /me/today': { t: 'Client · Today', s: 'Today screen aggregate', q: [['date', 'date']], r: '$Today' },
  'GET /me/program': { t: 'Client · Workouts', s: 'Active program assignment (or null)', r: '$ClientProgram?' },
  'GET /me/workouts/{workout}': { t: 'Client · Workouts', s: 'Workout with last performance per exercise', r: '$Workout', etag: true },
  'GET /me/sessions': { t: 'Client · Workouts', s: 'Workout history', q: [['cursor', 'string']], r: C('$WorkoutSession') },
  'POST /me/sessions': { t: 'Client · Workouts', s: 'Start a session (idempotent on id)', b: '$StartSession', r: '$WorkoutSession', status: 201 },
  'GET /me/sessions/{session}': { t: 'Client · Workouts', s: 'Session detail', r: '$WorkoutSession' },
  'POST /me/sessions/{session}/sets': { t: 'Client · Workouts', s: 'Batch upsert sets by client ULID', b: obj({ sets: '[$SessionSet]' }), r: obj({ sets: '[$SessionSet]' }) },
  'POST /me/sessions/{session}/complete': { t: 'Client · Workouts', s: 'Complete a session and detect PRs (idempotent)', b: '$CompleteSession', r: '$WorkoutSession' },
  'GET /me/exercises/{exercise}/history': { t: 'Client · Workouts', s: 'Exercise progress history', r: '$ExerciseHistory' },
  'GET /me/exercises/{exercise}/last': { t: 'Client · Workouts', s: 'Last performance for logger prefill', r: '$LastPerformance?' },
  'GET /me/records': { t: 'Client · Workouts', s: 'Personal records (best per exercise/type)', r: '[$PersonalRecord]' },
  'GET /me/nutrition': { t: 'Client · Nutrition', s: 'Nutrition day view', q: [['date', 'date']], r: '$NutritionDay' },
  'GET /me/nutrition/week': { t: 'Client · Nutrition', s: 'Seven-day summary and adherence', q: [['date', 'date', 'Any day in the week']], r: '$NutritionWeek' },
  'GET /me/nutrition/plan': { t: 'Client · Nutrition', s: 'Active meal plan (or null)', r: '$MealPlan?' },
  'POST /me/food-logs': { t: 'Client · Nutrition', s: 'Log food (idempotent on id)', b: '$FoodLogWrite', r: '$FoodLog', status: 201 },
  'DELETE /me/food-logs/{foodLog}': { t: 'Client · Nutrition', s: 'Delete a food log', r: NULL },
  'POST /me/food-logs/from-plan': { t: 'Client · Nutrition', s: 'Log every planned item of a meal', b: obj({ log_date: 'date', slot: 'string' }), r: '[$FoodLog]', status: 201 },
  'POST /me/food-logs/copy-day': { t: 'Client · Nutrition', s: 'Copy a day (or one meal) to another date', b: obj({ from_date: 'date', to_date: 'date', 'slot?': 'string' }), r: '[$FoodLog]', status: 201 },
  'POST /me/food-logs/photo': { t: 'Client · Nutrition', s: 'AI photo logging → candidate foods', mp: obj({ photo: 'binary', 'log_date?': 'date', 'slot?': 'string' }), r: '$PhotoFoodResult', e: [402] },
  'POST /me/water': { t: 'Client · Nutrition', s: 'Log water', b: obj({ 'id?': 'ulid', log_date: 'date', ml: 'int' }), r: obj({ log_date: 'date', water_ml: 'int' }), status: 201 },
  'GET /me/foods/recent': { t: 'Client · Nutrition', s: 'Recent and favourite foods', r: '[$Food]' },
  'POST /me/foods/{food}/favorite': { t: 'Client · Nutrition', s: 'Toggle favourite food', r: obj({ is_favorite: 'bool' }) },
  'GET /me/habits': { t: 'Client · Habits', s: 'Habits for a day', q: [['date', 'date']], r: '[$HabitToday]' },
  'POST /me/habits/{assignment}/log': { t: 'Client · Habits', s: 'Log a habit value', b: obj({ date: 'date', 'value?': 'num', 'completed?': 'bool' }), r: '$HabitToday' },
  'GET /me/checkins': { t: 'Client · Check-ins & progress', s: 'My check-ins', r: '[$CheckinListItem]' },
  'GET /me/checkins/{response}': { t: 'Client · Check-ins & progress', s: 'Check-in form, answers and thread', r: '$CheckinDetail' },
  'POST /me/checkins/{response}/submit': { t: 'Client · Check-ins & progress', s: 'Submit a check-in', b: '$CheckinSubmit', r: '$CheckinDetail', e: [422] },
  'POST /me/progress-photos': { t: 'Client · Check-ins & progress', s: 'Upload a progress photo (private, encrypted disk)', mp: obj({ photo: 'binary', pose: 'front|side|back', taken_on: 'date', 'checkin_response_id?': 'ulid' }), r: '$ProgressPhoto', status: 201 },
  'GET /me/progress-photos/{photo}': { t: 'Client · Check-ins & progress', s: 'Stream my progress photo (signed URL target)', bin: 'image/jpeg', e: [403] },
  'POST /me/body-metrics': { t: 'Client · Check-ins & progress', s: 'Record body metrics', b: '$BodyMetricWrite', r: obj({ id: 'ulid', measured_on: 'date' }), status: 201 },
  'GET /me/progress': { t: 'Client · Check-ins & progress', s: 'Progress summary (charts, photos, PRs, compliance)', r: '$ProgressSummary' },
  'GET /me/bookings': { t: 'Client · Bookings', s: 'My bookings', q: [['status', 'string'], ['cursor', 'string']], r: C('$Booking') },
  'GET /me/slots': { t: 'Client · Bookings', s: '1:1 availability', q: [['coach_id', 'ulid'], ['date', 'date', '', true]], r: '[$Slot]' },
  'GET /me/classes': { t: 'Client · Bookings', s: 'Classes in a range', q: [['from', 'date'], ['to', 'date']], r: '[$Event]' },
  'POST /me/bookings': { t: 'Client · Bookings', s: 'Book a class or 1:1 slot (waitlisted when full)', b: { oneOf: [obj({ event_id: 'ulid' }), obj({ coach_id: 'ulid', starts_at: 'datetime' })] }, r: '$Booking', status: 201, e: [409, 422] },
  'POST /me/bookings/{booking}/cancel': { t: 'Client · Bookings', s: 'Cancel (late cancel forfeits credit per policy)', r: '$Booking' },
  'POST /me/bookings/{booking}/claim': { t: 'Client · Bookings', s: 'Claim a waitlist offer', r: '$Booking', e: [409, 410] },
  'GET /me/bookings/{booking}/ics': { t: 'Client · Bookings', s: 'Calendar file for a booking', bin: 'text/calendar' },
  'GET /me/credits': { t: 'Client · Bookings', s: 'Credit balance and ledger', r: '$Credits' },
  'GET /me/shop': { t: 'Client · Shop', s: "Coach's products (iOS purchase mode applied)", r: '[$Product]' },
  'POST /me/shop/{product}/checkout': { t: 'Client · Shop', s: 'Start checkout for a product', b: '$CheckoutRequest', r: '$CheckoutResult', status: 201, idem: true, e: [422] },
  'GET /me/purchases': { t: 'Client · Shop', s: 'Orders and subscriptions', r: '$Purchases' },
  'GET /me/invoices': { t: 'Client · Shop', s: 'My invoices', r: '[$Invoice]' },
  'GET /me/invoices/{invoice}/pdf': { t: 'Client · Shop', s: 'Invoice PDF', bin: 'application/pdf' },
  'POST /me/subscriptions/{subscription}/cancel': { t: 'Client · Shop', s: 'Cancel subscription at period end (two taps)', r: '$ClientSubscription' },
  'POST /me/subscriptions/{subscription}/pause': { t: 'Client · Shop', s: 'Request a pause', b: obj({ 'until?': 'date', 'reason?': 'string' }), r: '$ClientSubscription' },
  'GET /me/contracts': { t: 'Client · Shop', s: 'Contracts and waivers to sign', r: '[$Contract]' },
  'POST /me/contracts/{contract}/sign': { t: 'Client · Shop', s: 'E-sign a contract (IP, timestamp and hash recorded)', b: '$SignContract', r: '$Contract', e: [409] },
  'POST /me/health/samples': { t: 'Client · Integrations', s: 'Ingest HealthKit / Health Connect samples (deduplicated by external_id)', b: '$HealthSampleBatch', r: obj({ accepted: 'int', duplicates: 'int' }) },
  'GET /me/health/summary': { t: 'Client · Integrations', s: 'Daily health aggregates', q: [['from', 'date'], ['to', 'date']], r: '$HealthSummary' },
  'GET /me/integrations': { t: 'Client · Integrations', s: 'My connected sources', r: '[$Integration]' },
  'POST /me/integrations/{provider}/connect': { t: 'Client · Integrations', s: 'Start OAuth connection to a provider', r: '$ConnectResult' },
  'DELETE /me/integrations/{provider}': { t: 'Client · Integrations', s: 'Disconnect (optionally delete stored samples)', q: [['purge', 'bool']], r: NULL },
  'POST /me/integrations/{provider}/sync': { t: 'Client · Integrations', s: 'Sync now', r: '$Integration', status: 202 },
  'POST /me/ai/coach-chat': { t: 'Client · AI', s: 'Self-guided AI coach chat (guard-railed)', b: obj({ message: 'string', 'conversation_id?': 'ulid' }), r: obj({ reply: 'string', flags: '[string]', suggest_coach: 'bool' }), e: [402] },
  'POST /me/ai/starter-plan': { t: 'Client · AI', s: 'Generate a safe starter plan for standalone users', b: obj({ goal: 'string', level: 'string', days_per_week: 'int', equipment: '[string]', 'minutes?': 'int' }), r: '$AiQueued', status: 202, e: [402] },
  'POST /me/ai/form-check': { t: 'Client · AI', s: 'Upload a set video (≤30 s) for coach-reviewed form feedback', mp: obj({ video: 'binary', exercise_id: 'ulid', 'session_set_id?': 'ulid' }), r: '$AiQueued', status: 202, e: [402, 422] },
  'GET /me/ai/weekly-recap': { t: 'Client · AI', s: 'Approved AI weekly recap card (or null)', r: '$AiOutput?' },

  // Coach
  'GET /dashboard': { t: 'Coach · Dashboard', s: 'Coach home KPIs, at-risk clients, today', r: '$Dashboard' },
  'GET /search': { t: 'Coach · Dashboard', s: 'Global search (Cmd/Ctrl+K)', q: [['q', 'string', '', true]], r: '$SearchResults' },
  'GET /clients': { t: 'Coach · Clients', s: 'Client list', q: [['filter[status]', 'string'], ['filter[risk]', 'string'], ['filter[coach_id]', 'ulid'], ['filter[tag]', 'string'], ['q', 'string'], ['sort', 'string', 'e.g. -last_active_at'], ['page', 'int']], r: P('$ClientSummary') },
  'POST /clients': { t: 'Coach · Clients', s: 'Add a client and send an invitation', b: '$ClientCreate', r: obj({ client: '$ClientSummary?', invitation: '$Invitation' }), status: 201, e: [402, 422] },
  'POST /clients/import': { t: 'Coach · Clients', s: 'Bulk CSV import (My PT Hub / Trainerize exports supported)', mp: obj({ file: 'binary', 'send_invites?': 'bool' }), r: obj({ imported: 'int', skipped: 'int', errors: '[json]' }), e: [402, 422] },
  'GET /clients/{client}': { t: 'Coach · Clients', s: 'Client profile', r: '$ClientDetail' },
  'PATCH /clients/{client}': { t: 'Coach · Clients', s: 'Update client', b: '$ClientUpdate', r: '$ClientDetail' },
  'DELETE /clients/{client}': { t: 'Coach · Clients', s: 'Archive client (soft delete)', r: NULL },
  'GET /clients/{client}/schedule': { t: 'Coach · Clients', s: 'Per-client schedule strip', q: [['from', 'date'], ['to', 'date']], r: '$ClientSchedule' },
  'GET /clients/{client}/sessions': { t: 'Coach · Clients', s: "Client's workout sessions", q: [['cursor', 'string']], r: C('$WorkoutSession') },
  'GET /clients/{client}/sessions/{session}': { t: 'Coach · Clients', s: 'Session set-by-set detail', r: '$WorkoutSession' },
  'GET /clients/{client}/nutrition': { t: 'Coach · Clients', s: "Client's nutrition day", q: [['date', 'date']], r: '$NutritionDay' },
  'GET /clients/{client}/progress': { t: 'Coach · Clients', s: "Client's progress summary", r: '$ProgressSummary' },
  'GET /clients/{client}/compliance': { t: 'Coach · Clients', s: 'Compliance breakdown and trend', r: '$Compliance' },
  'GET /clients/{client}/health': { t: 'Coach · Clients', s: 'Merged health timeline', q: [['from', 'date'], ['to', 'date']], r: '$HealthSummary' },
  'GET /clients/{client}/credits': { t: 'Coach · Calendar', s: "Client's credit ledger", r: '$Credits' },
  'POST /clients/{client}/credits': { t: 'Coach · Calendar', s: 'Manual credit adjustment (reason required, audited)', b: obj({ amount: 'int', reason: 'string', 'note?': 'string', 'expires_on?': 'date' }), r: '$Credits', status: 201 },
  'GET /clients/{client}/nutrition-targets': { t: 'Coach · Nutrition', s: 'Target history', r: '[$NutritionTarget]' },
  'POST /clients/{client}/nutrition-targets': { t: 'Coach · Nutrition', s: 'Set targets (safety floors unless override)', b: obj({ 'day_type?': 'default|training|rest', kcal: 'int', protein_g: 'int', carbs_g: 'int', fat_g: 'int', 'fiber_g?': 'int', 'water_ml?': 'int', effective_from: 'date', 'override_safety?': 'bool', 'calculation?': 'json' }), r: '$NutritionTarget', status: 201, e: [422] },
  'POST /nutrition/calculate': { t: 'Coach · Nutrition', s: 'BMR/TDEE/macro calculator', b: '$CalculateTargets', r: obj({ bmr: 'num', tdee: 'num', targets: '$Macros', water_ml: 'int', warnings: '[string]' }) },
  'GET /compliance': { t: 'Coach · Clients', s: 'Compliance board', q: [['filter[risk]', 'string'], ['page', 'int']], r: P('$ClientSummary') },
  'POST /compliance/message': { t: 'Coach · Clients', s: 'Bulk message selected / at-risk clients', b: obj({ client_ids: '[ulid]', body: 'string' }), r: obj({ sent: 'int' }) },
  'GET /invitations': { t: 'Coach · Clients', s: 'Pending invitations', r: '[$Invitation]' },
  'POST /invitations/{invitation}/resend': { t: 'Coach · Clients', s: 'Resend invitation', r: '$Invitation' },
  'DELETE /invitations/{invitation}': { t: 'Coach · Clients', s: 'Revoke invitation', r: NULL },
  'GET /invitations/{invitation}/qr': { t: 'Coach · Clients', s: 'Invitation QR code (SVG)', bin: 'image/svg+xml' },
  'GET /groups': { t: 'Coach · Clients', s: 'Client groups', r: '[$Group]' },
  'POST /groups': { t: 'Coach · Clients', s: 'Create group', b: obj({ name: 'string', 'color?': 'string', 'member_ids?': '[ulid]' }), r: '$Group', status: 201 },
  'GET /groups/{group}': { t: 'Coach · Clients', s: 'Group detail', r: '$Group' },
  'PUT /groups/{group}': { t: 'Coach · Clients', s: 'Replace group', b: obj({ name: 'string', 'color?': 'string', 'member_ids?': '[ulid]' }), r: '$Group' },
  'PATCH /groups/{group}': { t: 'Coach · Clients', s: 'Update group', b: obj({ 'name?': 'string', 'color?': 'string', 'member_ids?': '[ulid]' }), r: '$Group' },
  'DELETE /groups/{group}': { t: 'Coach · Clients', s: 'Delete group', r: NULL },

  'POST /exercises': { t: 'Coach · Workouts', s: 'Create custom exercise', b: '$ExerciseWrite', r: '$Exercise', status: 201 },
  'PATCH /exercises/{exercise}': { t: 'Coach · Workouts', s: 'Update custom exercise', b: '$ExerciseWrite', r: '$Exercise', e: [403] },
  'DELETE /exercises/{exercise}': { t: 'Coach · Workouts', s: 'Delete custom exercise', r: NULL, e: [403] },
  'POST /exercises/{exercise}/favorite': { t: 'Coach · Workouts', s: 'Toggle favourite', r: obj({ is_favorite: 'bool' }) },
  'POST /exercises/{exercise}/video': { t: 'Coach · Workouts', s: 'Upload exercise video (queued HLS transcode)', mp: obj({ video: 'binary' }), r: '$ExerciseVideo', status: 202, e: [402, 422] },
  'GET /workouts': { t: 'Coach · Workouts', s: 'Workout library', q: [['q', 'string'], ['tag', 'string'], ['cursor', 'string']], r: C('$Workout') },
  'POST /workouts': { t: 'Coach · Workouts', s: 'Create workout (nested blocks/exercises/sets)', b: '$WorkoutWrite', r: '$Workout', status: 201, e: [422] },
  'GET /workouts/{workout}': { t: 'Coach · Workouts', s: 'Workout detail', r: '$Workout', etag: true },
  'PUT /workouts/{workout}': { t: 'Coach · Workouts', s: 'Replace workout (full replace of blocks)', b: '$WorkoutWrite', r: '$Workout' },
  'PATCH /workouts/{workout}': { t: 'Coach · Workouts', s: 'Update workout', b: '$WorkoutWrite', r: '$Workout' },
  'DELETE /workouts/{workout}': { t: 'Coach · Workouts', s: 'Delete workout', r: NULL },
  'POST /workouts/{workout}/duplicate': { t: 'Coach · Workouts', s: 'Duplicate workout', r: '$Workout', status: 201 },
  'GET /workouts/{workout}/export': { t: 'Coach · Workouts', s: 'Export workout as portable JSON', r: '$WorkoutWrite' },
  'POST /workouts/import': { t: 'Coach · Workouts', s: 'Import workout JSON', b: '$WorkoutWrite', r: '$Workout', status: 201 },
  'GET /programs': { t: 'Coach · Workouts', s: 'Programs', q: [['q', 'string'], ['is_template', 'bool'], ['cursor', 'string']], r: C('$Program') },
  'POST /programs': { t: 'Coach · Workouts', s: 'Create program', b: '$ProgramWrite', r: '$Program', status: 201 },
  'GET /programs/{program}': { t: 'Coach · Workouts', s: 'Program with weeks and days', r: '$Program', etag: true },
  'PUT /programs/{program}': { t: 'Coach · Workouts', s: 'Replace program', b: '$ProgramWrite', r: '$Program' },
  'PATCH /programs/{program}': { t: 'Coach · Workouts', s: 'Update program', b: '$ProgramWrite', r: '$Program' },
  'DELETE /programs/{program}': { t: 'Coach · Workouts', s: 'Delete program', r: NULL },
  'POST /programs/{program}/duplicate': { t: 'Coach · Workouts', s: 'Duplicate program', r: '$Program', status: 201 },
  'POST /programs/{program}/assign': { t: 'Coach · Workouts', s: 'Assign program to clients', b: '$AssignProgram', r: '[$ProgramAssignment]', status: 201 },
  'POST /programs/{program}/weeks/{week}/copy': { t: 'Coach · Workouts', s: 'Copy a week (optionally applying progression)', b: obj({ 'to_week?': 'int', 'apply_progression?': 'bool' }), r: '$Program' },
  'GET /assignments': { t: 'Coach · Workouts', s: 'Program assignments', q: [['client_id', 'ulid'], ['status', 'string']], r: '[$ProgramAssignment]' },
  'PATCH /assignments/{assignment}': { t: 'Coach · Workouts', s: 'Pause / resume / shift schedule', b: obj({ 'status?': 'active|paused|completed|cancelled', 'start_date?': 'date', 'shift_days?': 'int', 'unlock_mode?': 'all|weekly_drip|daily' }), r: '$ProgramAssignment' },
  'DELETE /assignments/{assignment}': { t: 'Coach · Workouts', s: 'Remove assignment', r: NULL },

  'POST /foods': { t: 'Coach · Nutrition', s: 'Create custom food', b: '$FoodWrite', r: '$Food', status: 201 },
  'PATCH /foods/{food}': { t: 'Coach · Nutrition', s: 'Update custom food', b: '$FoodWrite', r: '$Food', e: [403] },
  'POST /recipes': { t: 'Coach · Nutrition', s: 'Create recipe (macros computed)', b: '$RecipeWrite', r: '$Recipe', status: 201 },
  'PATCH /recipes/{recipe}': { t: 'Coach · Nutrition', s: 'Update recipe', b: '$RecipeWrite', r: '$Recipe' },
  'DELETE /recipes/{recipe}': { t: 'Coach · Nutrition', s: 'Delete recipe', r: NULL },
  'GET /meal-plans': { t: 'Coach · Nutrition', s: 'Meal plans', q: [['client_id', 'ulid'], ['is_template', 'bool']], r: C('$MealPlan') },
  'POST /meal-plans': { t: 'Coach · Nutrition', s: 'Create meal plan', b: '$MealPlanWrite', r: '$MealPlan', status: 201 },
  'GET /meal-plans/{mealPlan}': { t: 'Coach · Nutrition', s: 'Meal plan detail', r: '$MealPlan' },
  'PUT /meal-plans/{mealPlan}': { t: 'Coach · Nutrition', s: 'Replace meal plan', b: '$MealPlanWrite', r: '$MealPlan' },
  'PATCH /meal-plans/{mealPlan}': { t: 'Coach · Nutrition', s: 'Update meal plan', b: '$MealPlanWrite', r: '$MealPlan' },
  'DELETE /meal-plans/{mealPlan}': { t: 'Coach · Nutrition', s: 'Delete meal plan', r: NULL },
  'POST /meal-plans/{mealPlan}/assign': { t: 'Coach · Nutrition', s: 'Assign plan to clients', b: obj({ client_ids: '[ulid]', start_date: 'date' }), r: '[$MealPlan]', status: 201 },
  'GET /meal-plans/{mealPlan}/grocery-list': { t: 'Coach · Nutrition', s: 'Grocery list grouped by aisle', r: '$GroceryList' },
  'GET /meal-plans/{mealPlan}/pdf': { t: 'Coach · Nutrition', s: 'Printable plan PDF (RTL for Arabic)', bin: 'application/pdf' },
  'POST /meal-plans/{mealPlan}/items/{mealItem}/swaps': { t: 'Coach · Nutrition', s: 'Swap candidates within ±10 % macros', r: '[$Food]' },

  'GET /habits': { t: 'Coach · Habits & check-ins', s: 'Habit library', r: '[$Habit]' },
  'POST /habits': { t: 'Coach · Habits & check-ins', s: 'Create custom habit', b: obj({ name: 'any', type: 'boolean|count|duration|numeric', 'icon?': 'string', 'target?': 'num', 'unit?': 'string', 'health_metric?': 'string' }), r: '$Habit', status: 201 },
  'POST /habit-assignments': { t: 'Coach · Habits & check-ins', s: 'Assign habit to clients', b: obj({ habit_id: 'ulid', client_ids: '[ulid]', schedule: 'daily|days|per_week', 'days?': '[int]', 'per_week?': 'int', 'target?': 'num', 'reminder_times?': '[time]', 'start_date?': 'date', 'end_date?': 'date' }), r: '[$HabitAssignment]', status: 201 },
  'PATCH /habit-assignments/{assignment}': { t: 'Coach · Habits & check-ins', s: 'Update habit assignment', b: obj({ 'schedule?': 'daily|days|per_week', 'days?': '[int]', 'per_week?': 'int', 'target?': 'num', 'reminder_times?': '[time]', 'is_active?': 'bool' }), r: '$HabitAssignment' },
  'DELETE /habit-assignments/{assignment}': { t: 'Coach · Habits & check-ins', s: 'Unassign habit', r: NULL },
  'GET /checkin-forms': { t: 'Coach · Habits & check-ins', s: 'Check-in forms', r: C('$CheckinForm') },
  'POST /checkin-forms': { t: 'Coach · Habits & check-ins', s: 'Create form', b: obj({ name: 'string', 'intro?': 'string', questions: '[$CheckinQuestion]', 'include_metrics?': 'bool', 'include_photos?': 'bool' }), r: '$CheckinForm', status: 201 },
  'GET /checkin-forms/{checkinForm}': { t: 'Coach · Habits & check-ins', s: 'Form detail', r: '$CheckinForm' },
  'PUT /checkin-forms/{checkinForm}': { t: 'Coach · Habits & check-ins', s: 'Replace form', b: obj({ name: 'string', 'intro?': 'string', questions: '[$CheckinQuestion]', 'include_metrics?': 'bool', 'include_photos?': 'bool' }), r: '$CheckinForm' },
  'PATCH /checkin-forms/{checkinForm}': { t: 'Coach · Habits & check-ins', s: 'Update form', b: obj({ 'name?': 'string', 'intro?': 'string', 'questions?': '[$CheckinQuestion]', 'include_metrics?': 'bool', 'include_photos?': 'bool' }), r: '$CheckinForm' },
  'DELETE /checkin-forms/{checkinForm}': { t: 'Coach · Habits & check-ins', s: 'Delete form', r: NULL },
  'POST /checkin-schedules': { t: 'Coach · Habits & check-ins', s: 'Schedule recurring check-ins', b: obj({ checkin_form_id: 'ulid', client_ids: '[ulid]', frequency: 'weekly|every_n_days|program_week_end', 'weekday?': 'int', 'every_days?': 'int', 'due_time?': 'time', 'start_on?': 'date' }), r: '[json]', status: 201 },
  'DELETE /checkin-schedules/{schedule}': { t: 'Coach · Habits & check-ins', s: 'Stop a schedule', r: NULL },
  'GET /checkins': { t: 'Coach · Habits & check-ins', s: 'Check-in inbox', q: [['filter[status]', 'pending|submitted|reviewed|overdue'], ['client_id', 'ulid'], ['cursor', 'string']], r: C('$CheckinListItem') },
  'GET /checkins/{response}': { t: 'Coach · Habits & check-ins', s: 'Review screen data (answers, trends, photos, thread)', r: '$CheckinDetail' },
  'POST /checkins/{response}/review': { t: 'Coach · Habits & check-ins', s: 'Mark reviewed with feedback', b: obj({ 'coach_feedback?': 'string', 'ai_output_id?': 'ulid' }), r: '$CheckinDetail' },
  'POST /checkins/{response}/comments': { t: 'Coach · Habits & check-ins', s: 'Comment on a check-in', b: obj({ body: 'string' }), r: '$CheckinComment', status: 201 },
  'GET /clients/{client}/progress-photos/{photo}': { t: 'Coach · Habits & check-ins', s: "Stream a client's progress photo", bin: 'image/jpeg' },

  'GET /broadcasts': { t: 'Coach · Messaging', s: 'Scheduled and sent broadcasts', r: '[$Broadcast]' },
  'POST /broadcasts': { t: 'Coach · Messaging', s: 'Schedule a broadcast to a segment', b: '$BroadcastWrite', r: '$Broadcast', status: 201 },
  'POST /broadcasts/preview': { t: 'Coach · Messaging', s: 'Preview segment recipients', b: '$BroadcastWrite', r: obj({ recipients_count: 'int', sample: '[$ClientSummary]' }) },
  'DELETE /broadcasts/{scheduledMessage}': { t: 'Coach · Messaging', s: 'Cancel a scheduled broadcast', r: NULL },
  'GET /message-templates': { t: 'Coach · Messaging', s: 'Saved replies', r: '[$MessageTemplate]' },
  'POST /message-templates': { t: 'Coach · Messaging', s: 'Create saved reply', b: obj({ name: 'string', body: 'any' }), r: '$MessageTemplate', status: 201 },
  'PUT /message-templates/{messageTemplate}': { t: 'Coach · Messaging', s: 'Replace saved reply', b: obj({ name: 'string', body: 'any' }), r: '$MessageTemplate' },
  'PATCH /message-templates/{messageTemplate}': { t: 'Coach · Messaging', s: 'Update saved reply', b: obj({ 'name?': 'string', 'body?': 'any' }), r: '$MessageTemplate' },
  'DELETE /message-templates/{messageTemplate}': { t: 'Coach · Messaging', s: 'Delete saved reply', r: NULL },
  'GET /automations': { t: 'Coach · Messaging', s: 'Automations', r: '[$Automation]' },
  'POST /automations': { t: 'Coach · Messaging', s: 'Create automation', b: obj({ name: 'string', trigger: 'string', 'trigger_config?': 'json', 'conditions?': '[json]', actions: '[json]', 'is_active?': 'bool' }), r: '$Automation', status: 201, e: [402] },
  'GET /automations/{automation}': { t: 'Coach · Messaging', s: 'Automation with recent runs', r: '$Automation' },
  'PUT /automations/{automation}': { t: 'Coach · Messaging', s: 'Replace automation', b: obj({ name: 'string', trigger: 'string', 'trigger_config?': 'json', 'conditions?': '[json]', actions: '[json]', 'is_active?': 'bool' }), r: '$Automation' },
  'PATCH /automations/{automation}': { t: 'Coach · Messaging', s: 'Update automation', b: obj({ 'name?': 'string', 'is_active?': 'bool', 'conditions?': '[json]', 'actions?': '[json]' }), r: '$Automation' },
  'DELETE /automations/{automation}': { t: 'Coach · Messaging', s: 'Delete automation', r: NULL },

  'POST /community/spaces': { t: 'Coach · Community', s: 'Create space', b: obj({ name: 'string', 'description?': 'string', 'client_group_id?': 'ulid', 'program_id?': 'ulid', 'announcement_only?': 'bool' }), r: '$Space', status: 201, e: [402] },
  'PATCH /community/spaces/{space}': { t: 'Coach · Community', s: 'Update space', b: obj({ 'name?': 'string', 'description?': 'string', 'announcement_only?': 'bool' }), r: '$Space' },
  'POST /community/posts/{post}/pin': { t: 'Coach · Community', s: 'Pin / unpin post', r: '$Post' },
  'POST /community/posts/{post}/hide': { t: 'Coach · Community', s: 'Hide / unhide post', r: '$Post' },
  'GET /community/reports': { t: 'Coach · Community', s: 'Moderation queue', r: '[$Report]' },
  'POST /community/reports/{report}/resolve': { t: 'Coach · Community', s: 'Resolve a report', b: obj({ action: 'dismiss|hide|mute_member', 'note?': 'string' }), r: '$Report' },
  'POST /challenges': { t: 'Coach · Community', s: 'Create challenge', b: obj({ name: 'string', 'description?': 'string', metric: 'steps|workouts|habit_streak|volume|custom', 'goal?': 'num', starts_on: 'date', ends_on: 'date', 'community_space_id?': 'ulid', 'badge_key?': 'string' }), r: '$Challenge', status: 201 },
  'PATCH /challenges/{challenge}': { t: 'Coach · Community', s: 'Update challenge', b: obj({ 'name?': 'string', 'description?': 'string', 'goal?': 'num', 'ends_on?': 'date' }), r: '$Challenge' },
  'DELETE /challenges/{challenge}': { t: 'Coach · Community', s: 'Delete challenge', r: NULL },

  'GET /events': { t: 'Coach · Calendar', s: 'Calendar events in a range', q: [['from', 'datetime', '', true], ['to', 'datetime', '', true], ['coach_id', 'ulid'], ['location_id', 'ulid'], ['type', 'string']], r: '[$Event]' },
  'POST /events': { t: 'Coach · Calendar', s: 'Create event / class / session (RRULE supported)', b: '$EventWrite', r: '$Event', status: 201, e: [409] },
  'GET /events/{event}': { t: 'Coach · Calendar', s: 'Event with attendees', r: { allOf: [{ $ref: '#/components/schemas/Event' }, obj({ bookings: '[$Booking]' })] } },
  'PATCH /events/{event}': { t: 'Coach · Calendar', s: 'Reschedule / edit (this, following, all)', b: '$EventWrite', r: '$Event' },
  'DELETE /events/{event}': { t: 'Coach · Calendar', s: 'Cancel event', q: [['scope', 'this|following|all']], r: NULL },
  'POST /events/{event}/bookings': { t: 'Coach · Calendar', s: 'Book a client into an event', b: obj({ client_id: 'ulid', 'use_credits?': 'bool' }), r: '$Booking', status: 201 },
  'PATCH /bookings/{booking}': { t: 'Coach · Calendar', s: 'Set booking status (retroactive allowed)', b: obj({ status: 'booked|attended|no_show|late_cancel|cancelled' }), r: '$Booking' },
  'POST /bookings/bulk-status': { t: 'Coach · Calendar', s: 'Bulk mark attendance; credits deducted per policy', b: obj({ booking_ids: '[ulid]', status: 'attended|no_show|late_cancel|cancelled' }), r: obj({ updated: 'int', bookings: '[$Booking]' }) },
  'POST /bookings/check-in': { t: 'Coach · Calendar', s: 'QR check-in', b: obj({ checkin_code: 'string' }), r: '$Booking', e: [404] },
  'GET /availability': { t: 'Coach · Calendar', s: 'My availability rules and booking settings', q: [['coach_id', 'ulid']], r: '$Availability' },
  'PUT /availability': { t: 'Coach · Calendar', s: 'Replace availability rules and settings', b: '$Availability', r: '$Availability' },
  'GET /locations': { t: 'Coach · Calendar', s: 'Locations', r: '[$Location]' },
  'POST /locations': { t: 'Coach · Calendar', s: 'Create location', b: obj({ name: 'string', 'address?': 'string', 'timezone?': 'string' }), r: '$Location', status: 201 },

  'POST /ai/programs': { t: 'Coach · AI copilot', s: 'Generate a program draft from profile, text or upload', b: obj({ 'client_id?': 'ulid', 'prompt?': 'string', 'days_per_week?': 'int', 'minutes?': 'int', 'equipment?': '[string]', 'goal?': 'string', 'level?': 'string' }), mp: obj({ file: 'binary', 'prompt?': 'string' }), r: '$AiQueued', status: 202, e: [402] },
  'POST /ai/meal-plans': { t: 'Coach · AI copilot', s: 'Generate a meal plan draft (±5 % of targets)', b: obj({ 'client_id?': 'ulid', 'targets?': '$Macros', 'diet_tags?': '[string]', 'allergies?': '[string]', 'cuisines?': '[string]', 'meals_per_day?': 'int', 'ramadan?': 'bool', 'budget?': 'low|medium|high', 'cook_minutes?': 'int' }), r: '$AiQueued', status: 202, e: [402] },
  'POST /ai/checkins/{response}/summary': { t: 'Coach · AI copilot', s: 'Check-in summary + drafted reply', r: '$AiQueued', status: 202, e: [402] },
  'POST /ai/reply-draft': { t: 'Coach · AI copilot', s: "Draft a reply in the coach's voice", b: obj({ conversation_id: 'ulid', 'instruction?': 'string' }), r: '$AiQueued', status: 202 },
  'POST /ai/progression/{client}': { t: 'Coach · AI copilot', s: 'Weekly progression suggestions (diff)', r: '$AiQueued', status: 202 },
  'POST /ai/ask': { t: 'Coach · AI copilot', s: 'Business assistant question over own analytics', b: obj({ question: 'string' }), r: obj({ answer: 'string', tools_used: '[string]', data: 'json' }) },
  'POST /ai/content': { t: 'Coach · AI copilot', s: 'Marketing copy helper (captions, descriptions)', b: obj({ kind: 'instagram_caption|program_description|sales_page', brief: 'string', 'locales?': '[$Locale]' }), r: '$AiQueued', status: 202 },
  'POST /ai/summarize/{conversation}': { t: 'Coach · AI copilot', s: 'Summarize a long thread', r: '$AiQueued', status: 202 },
  'GET /ai/outputs': { t: 'Coach · AI copilot', s: 'AI drafts awaiting review', q: [['status', 'string'], ['feature', 'string'], ['cursor', 'string']], r: C('$AiOutput') },
  'GET /ai/outputs/{output}': { t: 'Coach · AI copilot', s: 'Draft detail with grounding', r: '$AiOutput' },
  'POST /ai/outputs/{output}/approve': { t: 'Coach · AI copilot', s: 'Approve (optionally edited) — only now does it reach the client', b: obj({ 'edited_payload?': 'json' }), r: '$AiOutput' },
  'POST /ai/outputs/{output}/reject': { t: 'Coach · AI copilot', s: 'Reject a draft', b: obj({ 'reason?': 'string' }), r: '$AiOutput' },
  'GET /ai/settings': { t: 'Coach · AI copilot', s: 'Tone of voice and feature toggles', r: '$AiSettings' },
  'PUT /ai/settings': { t: 'Coach · AI copilot', s: 'Update AI settings', b: '$AiSettings', r: '$AiSettings' },
  'GET /ai/usage': { t: 'Coach · AI copilot', s: 'Usage and cost meter', q: [['period', 'string', 'YYYY-MM']], r: '$AiUsage' },
  'GET /ai/churn': { t: 'Coach · AI copilot', s: 'Clients to contact today (churn score)', r: '[$ChurnItem]' },

  'GET /lead-stages': { t: 'CRM', s: 'Pipeline stages', r: '[$LeadStage]' },
  'PUT /lead-stages': { t: 'CRM', s: 'Replace pipeline stages', b: obj({ stages: { type: 'array', items: obj({ 'id?': 'ulid', name: 'string', kind: 'open|won|lost' }) } }), r: '[$LeadStage]' },
  'GET /leads': { t: 'CRM', s: 'Leads', q: [['filter[stage]', 'ulid'], ['filter[source]', 'string'], ['q', 'string'], ['page', 'int']], r: P('$Lead') },
  'POST /leads': { t: 'CRM', s: 'Create lead', b: '$LeadWrite', r: '$Lead', status: 201 },
  'GET /leads/{lead}': { t: 'CRM', s: 'Lead with activity timeline', r: '$Lead' },
  'PUT /leads/{lead}': { t: 'CRM', s: 'Replace lead', b: '$LeadWrite', r: '$Lead' },
  'PATCH /leads/{lead}': { t: 'CRM', s: 'Update lead / move stage', b: '$LeadWrite', r: '$Lead' },
  'DELETE /leads/{lead}': { t: 'CRM', s: 'Delete lead', r: NULL },
  'POST /leads/{lead}/activities': { t: 'CRM', s: 'Add note, call, email or task', b: obj({ type: 'note|call|email|task', 'body?': 'string', 'due_at?': 'datetime' }), r: '$LeadActivity', status: 201 },
  'POST /leads/{lead}/convert': { t: 'CRM', s: 'Convert lead to client (invite + optional checkout link)', b: obj({ 'product_id?': 'ulid', 'price_id?': 'ulid', 'program_id?': 'ulid' }), r: obj({ client: '$ClientSummary?', invitation: '$Invitation', checkout_url: 'uri?' }), e: [402] },
  'GET /contract-templates': { t: 'CRM', s: 'Contract / waiver / PAR-Q templates', r: '[$ContractTemplate]' },
  'POST /contract-templates': { t: 'CRM', s: 'Create template', b: obj({ name: 'string', kind: 'contract|waiver|parq', body: 'string' }), r: '$ContractTemplate', status: 201 },
  'PUT /contract-templates/{contractTemplate}': { t: 'CRM', s: 'Replace template', b: obj({ name: 'string', kind: 'contract|waiver|parq', body: 'string' }), r: '$ContractTemplate' },
  'PATCH /contract-templates/{contractTemplate}': { t: 'CRM', s: 'Update template', b: obj({ 'name?': 'string', 'body?': 'string' }), r: '$ContractTemplate' },
  'DELETE /contract-templates/{contractTemplate}': { t: 'CRM', s: 'Delete template', r: NULL },
  'GET /contracts': { t: 'CRM', s: 'Contracts sent and signed', q: [['client_id', 'ulid'], ['status', 'string']], r: '[$Contract]' },
  'POST /contracts': { t: 'CRM', s: 'Send a contract to a client', b: obj({ contract_template_id: 'ulid', client_id: 'ulid', 'title?': 'string' }), r: '$Contract', status: 201 },
  'GET /contracts/{contract}': { t: 'CRM', s: 'Contract with audit trail', r: '$Contract' },
  'GET /contracts/{contract}/pdf': { t: 'CRM', s: 'Signed contract PDF', bin: 'application/pdf' },

  'GET /payment-gateways': { t: 'Payments', s: 'Connected gateways', r: '[$Gateway]' },
  'POST /payment-gateways': { t: 'Payments', s: 'Connect a gateway (credentials encrypted at rest)', b: '$GatewayWrite', r: obj({ gateway: '$Gateway', authorize_url: 'uri?' }), status: 201, e: [422] },
  'PATCH /payment-gateways/{gateway}': { t: 'Payments', s: 'Update gateway', b: '$GatewayWrite', r: '$Gateway' },
  'DELETE /payment-gateways/{gateway}': { t: 'Payments', s: 'Disconnect gateway', r: NULL },
  'GET /products': { t: 'Payments', s: 'Products', r: C('$Product') },
  'POST /products': { t: 'Payments', s: 'Create product with prices', b: '$ProductWrite', r: '$Product', status: 201 },
  'GET /products/{product}': { t: 'Payments', s: 'Product detail', r: '$Product' },
  'PUT /products/{product}': { t: 'Payments', s: 'Replace product', b: '$ProductWrite', r: '$Product' },
  'PATCH /products/{product}': { t: 'Payments', s: 'Update product', b: '$ProductWrite', r: '$Product' },
  'DELETE /products/{product}': { t: 'Payments', s: 'Archive product', r: NULL },
  'GET /products/{product}/link': { t: 'Payments', s: 'Shareable payment link + QR', r: '$PaymentLink' },
  'GET /coupons': { t: 'Payments', s: 'Coupons', r: '[$Coupon]' },
  'POST /coupons': { t: 'Payments', s: 'Create coupon', b: obj({ code: 'string', type: 'percent|fixed', value: 'int', 'currency?': 'string', 'duration?': 'once|repeating|forever', 'duration_months?': 'int', 'max_redemptions?': 'int', 'product_ids?': '[ulid]', 'expires_at?': 'datetime' }), r: '$Coupon', status: 201 },
  'PUT /coupons/{coupon}': { t: 'Payments', s: 'Replace coupon', b: obj({ code: 'string', type: 'percent|fixed', value: 'int' }), r: '$Coupon' },
  'PATCH /coupons/{coupon}': { t: 'Payments', s: 'Update coupon', b: obj({ 'is_active?': 'bool', 'expires_at?': 'datetime', 'max_redemptions?': 'int' }), r: '$Coupon' },
  'DELETE /coupons/{coupon}': { t: 'Payments', s: 'Delete coupon', r: NULL },
  'GET /orders': { t: 'Payments', s: 'Orders', q: [['filter[status]', 'string'], ['client_id', 'ulid'], ['page', 'int']], r: P('$Order') },
  'GET /orders/{order}': { t: 'Payments', s: 'Order with payments and refunds', r: '$Order' },
  'POST /orders/{order}/refund': { t: 'Payments', s: 'Refund (full or partial) via the original gateway', b: '$Refund', r: '$Order', idem: true, e: [409, 422] },
  'POST /manual-payments': { t: 'Payments', s: 'Record cash / bank transfer (same fulfilment pipeline)', mp: '$ManualPayment', b: '$ManualPayment', r: '$Order', status: 201, idem: true },
  'GET /subscriptions': { t: 'Payments', s: 'Client subscriptions', q: [['filter[status]', 'string'], ['page', 'int']], r: P('$ClientSubscription') },
  'POST /subscriptions/{subscription}/cancel': { t: 'Payments', s: 'Cancel a client subscription', b: obj({ 'immediately?': 'bool' }), r: '$ClientSubscription' },
  'POST /subscriptions/{subscription}/pause': { t: 'Payments', s: 'Pause a client subscription', b: obj({ 'until?': 'date' }), r: '$ClientSubscription' },
  'POST /subscriptions/{subscription}/resume': { t: 'Payments', s: 'Resume a client subscription', r: '$ClientSubscription' },
  'GET /invoices': { t: 'Payments', s: 'Invoices and credit notes', q: [['client_id', 'ulid'], ['page', 'int']], r: P('$Invoice') },
  'GET /invoices/{invoice}/pdf': { t: 'Payments', s: 'Invoice PDF (multilingual, RTL)', bin: 'application/pdf' },
  'GET /analytics/overview': { t: 'Analytics', s: 'MRR, revenue, churn, LTV', q: [['from', 'date'], ['to', 'date']], r: '$AnalyticsOverview' },
  'GET /analytics/retention': { t: 'Analytics', s: 'Retention cohorts by start month', r: '$RetentionCohorts' },
  'GET /analytics/coaches': { t: 'Analytics', s: 'Per-coach performance and commissions (Studio)', q: [['from', 'date'], ['to', 'date']], r: '$CoachPerformance', e: [402] },
  'GET /analytics/export': { t: 'Analytics', s: 'CSV export for accounting', q: [['report', 'revenue|orders|payouts|clients', '', true], ['from', 'date'], ['to', 'date']], bin: 'text/csv' },

  'GET /integrations': { t: 'Developer', s: 'Integrations marketplace with status', r: '[$Integration]' },
  'GET /webhook-endpoints': { t: 'Developer', s: 'Outgoing webhook endpoints', r: '[$WebhookEndpoint]' },
  'POST /webhook-endpoints': { t: 'Developer', s: 'Create endpoint (signing secret returned once)', b: obj({ url: 'uri', events: '[string]' }), r: '$WebhookEndpoint', status: 201, e: [402] },
  'PUT /webhook-endpoints/{webhookEndpoint}': { t: 'Developer', s: 'Replace endpoint', b: obj({ url: 'uri', events: '[string]', 'is_active?': 'bool' }), r: '$WebhookEndpoint' },
  'PATCH /webhook-endpoints/{webhookEndpoint}': { t: 'Developer', s: 'Update endpoint', b: obj({ 'url?': 'uri', 'events?': '[string]', 'is_active?': 'bool' }), r: '$WebhookEndpoint' },
  'DELETE /webhook-endpoints/{webhookEndpoint}': { t: 'Developer', s: 'Delete endpoint', r: NULL },
  'GET /webhook-endpoints/{endpoint}/deliveries': { t: 'Developer', s: 'Delivery log', q: [['cursor', 'string']], r: C('$WebhookDelivery') },
  'GET /api-tokens': { t: 'Developer', s: 'Personal access tokens', r: '[$ApiToken]' },
  'POST /api-tokens': { t: 'Developer', s: 'Create scoped token (plain text returned once)', b: obj({ name: 'string', abilities: '[string]', 'expires_at?': 'datetime' }), r: '$ApiToken', status: 201, e: [402] },
  'DELETE /api-tokens/{token}': { t: 'Developer', s: 'Revoke token', r: NULL },

  'GET /team': { t: 'Owner settings', s: 'Team members', r: '[$TeamMember]' },
  'POST /team': { t: 'Owner settings', s: 'Invite coach or assistant (plan seat limit)', b: obj({ email: 'email', name: 'string', role: 'coach|assistant', 'permissions?': '[string]' }), r: '$Invitation', status: 201, e: [402] },
  'PATCH /team/{user}': { t: 'Owner settings', s: 'Change role or permissions', b: obj({ 'role?': 'coach|assistant', 'permissions?': '[string]', 'status?': 'active|archived' }), r: '$TeamMember' },
  'DELETE /team/{user}': { t: 'Owner settings', s: 'Remove member (clients reassigned)', b: obj({ 'reassign_to?': 'ulid' }), r: NULL },
  'GET /billing': { t: 'Billing', s: 'Plan, usage, card and invoices', r: '$Billing' },
  'POST /billing/subscribe': { t: 'Billing', s: 'Subscribe / change plan', b: obj({ plan: 'pro|studio', interval: 'month|year' }), r: obj({ billing: '$Billing', checkout_url: 'uri?' }) },
  'POST /billing/cancel': { t: 'Billing', s: 'Cancel (monthly → period end; annual → pro-rata refund)', b: obj({ 'reason?': 'string' }), r: '$Billing' },
  'POST /billing/resume': { t: 'Billing', s: 'Undo a pending cancellation', r: '$Billing' },
  'POST /billing/remove-card': { t: 'Billing', s: 'Remove stored card', r: '$Billing' },
  'GET /billing/portal': { t: 'Billing', s: 'Gateway billing portal URL', r: obj({ url: 'uri' }) },
  'PUT /coach-profile': { t: 'Owner settings', s: 'Edit public coach profile', b: obj({ 'slug?': 'string', 'headline?': 'string', 'bio?': 'any', 'specialties?': '[string]', 'languages?': '[string]', 'certifications?': '[json]', 'city?': 'string', 'country?': 'string', 'gender?': 'string', 'offers_online?': 'bool', 'offers_in_person?': 'bool', 'price_from_minor?': 'int', 'instagram?': 'string', 'intro_video_url?': 'uri', 'is_published?': 'bool' }), r: '$CoachProfile' },
  'GET /coach-profile': { t: 'Owner settings', s: 'Public coach profile (editable form)', r: '$CoachProfile' },
  'POST /reviews/{review}/reply': { t: 'Owner settings', s: 'Reply publicly to a review', b: obj({ body: 'string' }), r: '$Review' },
  'POST /commission-rules': { t: 'Owner settings', s: 'Save commission rules (Studio)', b: obj({ rules: '[$CommissionRule]' }), r: '[$CommissionRule]', e: [402] },
};

// ---------------------------------------------------------------- build
const routes = parseRoutes(readFileSync(routesFile, 'utf8'));

// Unify parameter names across structurally identical paths (OpenAPI forbids
// two paths that differ only by template names).
const shapeNames = new Map();
for (const r of routes) {
  const shape = r.path.replace(/\{[^}]+\}/g, '{}');
  if (!shapeNames.has(shape)) shapeNames.set(shape, r.path);
  r.docPath = shapeNames.get(shape);
}

const errors = [];
const seen = new Set();
const paths = {};
const tagsUsed = new Set();

const ERR = {
401: ['Unauthenticated — refresh then retry, else sign out', 'unauthenticated'], 402: ['Plan limit reached or feature not in plan', 'plan_limit_reached'],
  403: ['Forbidden (role, ownership or tenant)', 'forbidden'], 404: ['Not found (or not visible in this tenant)', 'not_found'], 409: ['Conflict (capacity, state or idempotency in progress)', 'conflict'],
  410: ['Gone (invitation expired or used)', 'invitation_invalid'], 422: ['Validation failed', 'validation_failed'], 423: ['Account locked', 'account_locked'], 429: ['Too many requests', 'too_many_requests'],
};

function envelope(dataSchema, kind) {
  const props = { data: dataSchema, meta: kind === 'cursor' ? { $ref: '#/components/schemas/CursorMeta' } : kind === 'page' ? { $ref: '#/components/schemas/PageMeta' } : { type: 'object' }, links: kind ? { $ref: '#/components/schemas/Links' } : { type: 'object' }, errors: { type: 'array', maxItems: 0 } };
  return { type: 'object', required: ['data', 'meta', 'links', 'errors'], properties: props };
}

function responseSchema(op) {
  if (op.r === null) return envelope({ type: 'null' });
  if (op.r && typeof op.r === 'object' && op.r.list) return envelope({ type: 'array', items: T(op.r.list) }, 'cursor');
  if (op.r && typeof op.r === 'object' && op.r.page) return envelope({ type: 'array', items: T(op.r.page) }, 'page');
  return envelope(T(op.r));
}

for (const r of routes) {
  const key = `${r.method} ${r.path}`;
  const op = OPS[key];
  seen.add(key);
  if (!op) { errors.push(`Undocumented route: ${key} (${r.controller}@${r.action})`); continue; }
  const authed = r.middleware.includes('auth:sanctum');
  const tenant = r.middleware.includes('tenant');
  const roleMw = r.middleware.filter((m) => m.startsWith('role:')).pop();
  const throttle = r.middleware.filter((m) => m.startsWith('throttle:')).pop()?.slice(9);
  const idem = r.middleware.includes('idempotent');
  tagsUsed.add(op.t);

  const params = [];
  const original = [...r.path.matchAll(/\{([^}]+)\}/g)].map((x) => x[1]);
  [...r.docPath.matchAll(/\{([^}]+)\}/g)].forEach((m, i) => {
    const real = original[i];
    const own = op.p?.[real];
    const description = real === 'code' ? (r.path.includes('barcode') ? 'EAN/UPC barcode' : 'Invitation code')
      : ['slug', 'tenantSlug', 'productSlug'].includes(real) ? `${real} (URL slug)`
      : real === 'provider' ? 'Provider key' : `${real} id (ULID)`;
    params.push({ name: m[1], in: 'path', required: true, schema: own ? T(own) : { type: 'string' }, description });
  });
  for (const [name, type, description, required] of op.q ?? []) {
    params.push({ name, in: 'query', required: !!required, schema: T(type), ...(description ? { description } : {}) });
  }
  params.push({ $ref: '#/components/parameters/AcceptLanguage' });
  if (tenant) params.push({ $ref: '#/components/parameters/XTenant' });
  if (idem) params.push({ $ref: '#/components/parameters/IdempotencyKey' });
  if (op.etag) params.push({ $ref: '#/components/parameters/IfNoneMatch' });

  const operation = {
    operationId: `${r.method.toLowerCase()}${r.docPath.replace(/\{([^}]+)\}/g, 'By-$1').split(/[\/\-_]/).filter(Boolean).map((s) => s[0].toUpperCase() + s.slice(1)).join('')}`,
    summary: op.s,
    tags: [op.t],
    ...(op.d ? { description: op.d } : {}),
    parameters: params,
  };
  if (op.b || op.mp) {
    const content = {};
    if (op.b) content['application/json'] = { schema: T(op.b) };
    if (op.mp) content['multipart/form-data'] = { schema: T(op.mp) };
    operation.requestBody = { required: true, content };
  }
  const ok = String(op.status ?? 200);
  const responses = {};
  if (op.bin) {
    responses[ok] = { description: 'File', content: { [op.bin]: { schema: { type: 'string', format: 'binary' } } } };
  } else {
    responses[ok] = { description: ok === '201' ? 'Created' : ok === '202' ? 'Accepted (queued)' : 'OK', content: { 'application/json': { schema: responseSchema(op) } } };
    if (op.etag) responses['304'] = { description: 'Not modified (ETag matched)' };
  }
  const errs = new Set(op.e ?? []);
  if (authed) errs.add(401);
  if (roleMw || tenant) errs.add(403);
  if (r.docPath.includes('{')) errs.add(404);
  if (op.b || op.mp) errs.add(422);
  if (idem) errs.add(409);
  errs.add(429);
  for (const code of [...errs].sort()) responses[String(code)] = { $ref: `#/components/responses/E${code}` };
  operation.responses = responses;
  if (!authed) operation.security = [];
  const ext = {};
  if (roleMw) ext['x-roles'] = roleMw.slice(5).split(',');
  else if (tenant) ext['x-roles'] = ['owner', 'coach', 'assistant', 'client'];
  if (throttle) ext['x-rate-limit'] = throttle;
  Object.assign(operation, ext);

  paths[r.docPath] ??= {};
  if (paths[r.docPath][r.method.toLowerCase()]) errors.push(`Duplicate operation ${key}`);
  paths[r.docPath][r.method.toLowerCase()] = operation;
}
for (const key of Object.keys(OPS)) if (!seen.has(key)) errors.push(`OPS entry without route: ${key}`);

const TAG_DESC = {
  Auth: 'Registration, sign-in (password, magic link, Apple/Google, TOTP), token refresh.',
  Account: 'The signed-in user: profile, devices, 2FA, GDPR export & deletion, notifications.',
  Tenancy: 'The active coaching business.', Marketplace: 'Public coach directory, profiles, enquiries and matching.',
  'Public checkout': 'Guest checkout for payment links and coach profile packages.',
  Messaging: 'Real-time chat (Reverb `presence-conversation.{id}`).', Community: 'Spaces, posts, comments, challenges, badges.',
  Library: 'Exercise, food and recipe libraries (global + tenant rows).', Sync: 'Offline-first mobile sync.',
  'Client · Today': 'Client app home aggregate.', 'Client · Workouts': 'Program, offline-first workout logger, history and PRs.',
  'Client · Nutrition': 'Food logging (search, barcode, plan, photo), water, meal plan.', 'Client · Habits': 'Daily habit checklist and streaks.',
  'Client · Check-ins & progress': 'Check-in forms, body metrics, private progress photos, progress summary.',
  'Client · Bookings': '1:1 slots, classes, waitlist, credits.', 'Client · Shop': "Coach's products, checkout, purchases, invoices, contracts.",
  'Client · Integrations': 'HealthKit / Health Connect ingest and wearable connections.', 'Client · AI': 'Guard-railed client AI tools enabled by the coach.',
  'Coach · Dashboard': 'Coach home and global search.', 'Coach · Clients': 'Client management, invitations, groups, compliance.',
  'Coach · Workouts': 'Exercise library curation, workout and program builder, assignments.', 'Coach · Nutrition': 'Foods, recipes, meal plans, targets.',
  'Coach · Habits & check-ins': 'Habit library and assignment, check-in forms, schedules and review inbox.',
  'Coach · Messaging': 'Broadcasts, saved replies and automations.', 'Coach · Community': 'Space and challenge management, moderation.',
  'Coach · Calendar': 'Events, bookings, attendance, availability, locations, credits.',
  'Coach · AI copilot': 'Draft-first AI features. Nothing reaches a client until a coach approves the output.',
  CRM: 'Lead pipeline, contracts, waivers and e-signatures.', Payments: 'Gateways, products, coupons, orders, refunds, client subscriptions, invoices.',
  Analytics: 'Business analytics from daily snapshots.', Developer: 'Integrations, outgoing webhooks and personal access tokens.',
  'Owner settings': 'Owner-only: business profile, team, public coach profile, commissions.', Billing: 'The coach paying Aurum (SaaS plan).',
};
const order = ['Auth', 'Account', 'Tenancy', 'Marketplace', 'Public checkout', 'Messaging', 'Community', 'Library', 'Sync'];
const tags = [...order.filter((t) => tagsUsed.has(t)), ...[...tagsUsed].filter((t) => !order.includes(t)).sort()].map((name) => ({ name, ...(TAG_DESC[name] ? { description: TAG_DESC[name] } : {}) }));

const errorResponses = {};
for (const [code, [description, errCode]] of Object.entries(ERR)) {
  errorResponses[`E${code}`] = {
    description,
    content: { 'application/json': { schema: { $ref: '#/components/schemas/ErrorEnvelope' }, example: { data: null, meta: {}, links: {}, errors: [{ code: errCode, message: description, ...(code === '422' ? { field_errors: { email: ['The email field is required.'] } } : {}) }] } } },
    ...(code === '429' ? { headers: { 'Retry-After': { schema: { type: 'integer' }, description: 'Seconds until the limit resets' } } } : {}),
  };
}

const doc = {
  openapi: '3.1.0',
  info: {
    title: 'Aurum API',
    version: '1.0.0',
    summary: 'REST API shared by the Aurum web dashboard, coach and client apps, and the public developer API.',
    description: [
      'Source of truth for paths: `backend/routes/api.php`; payload shapes: `docs/API_CONTRACT.md`. This file is generated by `node infra/scripts/openapi/generate.mjs`; CI fails when a route is undocumented.',
      '',
      '**Local development.** `docker compose up` serves the API at `http://localhost:8000/api/v1`.',
      '',
      '**Envelope.** Every response is `{data, meta, links, errors}`. Errors carry `code`, `message` and optional `field_errors`.',
      '',
      '**Tenancy.** Tenant-scoped endpoints resolve the tenant from `X-Tenant` (id or slug), the subdomain, or the user\'s current tenant.',
      '',
      '**Localization.** `Accept-Language: ar|fr|en|es` localizes messages and translatable fields. Units are metric; money is `{amount_minor, currency, formatted}`; datetimes are ISO 8601 UTC.',
      '',
      '**Pagination.** Feeds use cursor pagination (`?cursor=` + `meta.next_cursor`); admin tables use `?page=`. Filtering: `filter[field]=`, sorting: `sort=-created_at`, search: `q=`.',
      '',
      '**Realtime (Reverb / Pusher protocol).** `private-tenant.{id}` (staff), `private-user.{id}` (`conversation.updated`, notifications), `presence-conversation.{id}` (`message.sent`, `message.updated`, `message.deleted`, `typing`). Authorize at `POST /broadcasting/auth` with the bearer token.',
      '',
      '**Rate limits.** `api` 120/min per user, `auth` 10/min per IP and per account, `ai` 20/min per user, `public` 30/min per IP. See `x-rate-limit` on each operation.',
    ].join('\n'),
    contact: { name: 'Aurum Engineering', email: 'support@aurum.coach', url: 'https://aurum.coach' },
    license: { name: 'Proprietary', identifier: 'LicenseRef-Aurum-Proprietary' },
  },
  servers: [
    { url: 'https://api.aurum.coach/api/v1', description: 'Production' },
    { url: 'https://api.staging.aurum.coach/api/v1', description: 'Staging' },
  ],
  security: [{ bearerAuth: [] }],
  tags,
  paths,
  components: {
    securitySchemes: { bearerAuth: { type: 'http', scheme: 'bearer', bearerFormat: 'Sanctum', description: 'Access token (60 min) with the `access` ability. Rotate with `POST /auth/refresh`.' } },
    parameters: {
      AcceptLanguage: { name: 'Accept-Language', in: 'header', required: false, schema: { type: 'string', enum: ['en', 'ar', 'fr', 'es'], default: 'en' }, description: 'Response language; falls back to user preference then tenant default.' },
      XTenant: { name: 'X-Tenant', in: 'header', required: false, schema: { type: 'string' }, description: 'Tenant id or slug. Defaults to the user\'s current tenant.' },
      IdempotencyKey: { name: 'Idempotency-Key', in: 'header', required: false, schema: { type: 'string', format: 'uuid' }, description: 'Strongly recommended on payment POSTs. A repeated key within 24 h replays the stored response (`Idempotent-Replay: true`).' },
      IfNoneMatch: { name: 'If-None-Match', in: 'header', required: false, schema: { type: 'string' }, description: 'ETag from a previous response; returns 304 when unchanged.' },
    },
    responses: errorResponses,
    schemas,
  },
};

// ---------------------------------------------------------------- YAML emitter
const plain = /^[A-Za-z_][A-Za-z0-9_.\-\/ ]*$/;
const reserved = /^(true|false|null|yes|no|on|off|~|y|n)$/i;
function scalar(v) {
  if (v === null) return 'null';
  if (typeof v === 'number' || typeof v === 'boolean') return String(v);
  const s = String(v);
  if (plain.test(s) && !reserved.test(s) && !s.endsWith(' ') && !/: |#/.test(s)) return s;
  return JSON.stringify(s);
}
function key(k) {
  return /^[A-Za-z_$\/][A-Za-z0-9_.$\-\/{}]*$/.test(k) && !reserved.test(k) ? k : JSON.stringify(k);
}
function yaml(v, indent = 0) {
  const pad = '  '.repeat(indent);
  if (Array.isArray(v)) {
    if (!v.length) return ' []';
    return v.map((item) => {
      if (item && typeof item === 'object' && !Array.isArray(item) && Object.keys(item).length) {
        const inner = yaml(item, indent + 1).replace(/^\n/, '');
        return `\n${pad}- ${inner.trimStart()}`;
      }
      return `\n${pad}-${yaml(item, indent + 1)}`;
    }).join('');
  }
  if (v && typeof v === 'object') {
    const entries = Object.entries(v);
    if (!entries.length) return ' {}';
    return entries.map(([k, val]) => `\n${pad}${key(k)}:${yaml(val, indent + 1)}`).join('');
  }
  return ' ' + scalar(v);
}

const out = '# Generated by infra/scripts/openapi/generate.mjs from backend/routes/api.php — do not edit by hand.\n' + yaml(doc).replace(/^\n/, '') + '\n';
const opCount = Object.values(paths).reduce((n, p) => n + Object.keys(p).length, 0);

if (errors.length) {
  console.error(errors.join('\n'));
  console.error(`\n${errors.length} problem(s). Add/adjust entries in OPS (infra/scripts/openapi/generate.mjs).`);
  process.exit(1);
}
if (process.argv.includes('--check')) {
  let cur = '';
  try { cur = readFileSync(outFile, 'utf8'); } catch {}
  if (cur !== out) { console.error('docs/openapi.yaml is out of date — run: node infra/scripts/openapi/generate.mjs'); process.exit(1); }
  console.log(`openapi: ${opCount} operations, ${Object.keys(paths).length} paths — up to date`);
} else {
  writeFileSync(outFile, out);
  console.log(`openapi: wrote docs/openapi.yaml — ${opCount} operations across ${Object.keys(paths).length} paths (${routes.length} routes)`);
}
