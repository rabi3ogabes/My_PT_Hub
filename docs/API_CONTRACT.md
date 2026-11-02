# Aurum API v1 — Contract

Base URL: `https://api.{domain}/api/v1` (local: `http://localhost:8000/api/v1`). Routes: `backend/routes/api.php` (source of truth for paths). This document fixes **payload shapes**. Backend resources and Flutter models MUST match these field names exactly.

## Conventions
- Headers: `Authorization: Bearer <access_token>`, `Accept: application/json`, `Accept-Language: ar|fr|en|es`, `X-Tenant: <tenant id>` (optional; defaults to user's current tenant), `Idempotency-Key: <uuid>` on payment POSTs.
- Envelope (every response):
  ```json
  {"data": <object|array|null>, "meta": {}, "links": {}, "errors": []}
  ```
- Error: `{"data": null, "meta": {}, "links": {}, "errors": [{"code": "validation_failed", "message": "…", "field_errors": {"email": ["…"]}}]}`. Codes used by clients: `unauthenticated` (401 → try refresh then logout), `invalid_refresh_token`, `forbidden`, `not_found`, `validation_failed`, `plan_limit_reached` (402), `account_locked` (423), `too_many_requests` (429), `invitation_invalid` (410).
- Cursor lists: `meta: {per_page, next_cursor, prev_cursor}`; request next page with `?cursor=<next_cursor>`.
- IDs are ULID strings. Datetimes ISO 8601 UTC (`2026-11-01T09:00:00+00:00`). Dates `YYYY-MM-DD` (user-local calendar day). Weights kg, lengths cm, water ml, distance m. Money: `{"amount_minor": 39900, "currency": "QAR", "formatted": "QAR 399.00"}`.
- Translatable fields are returned localized (per `Accept-Language`) as plain strings, plus `<field>_translations` objects where editing is possible (staff endpoints).

## Auth
`POST /auth/register` body: `{name, email, password, account_type: coach|client|standalone, invite_code?, business_name?, currency?, country?, locale?, terms: true, device_name?, platform?: ios|android|web, push_token?, app?: client|coach, app_version?, timezone?}`
`POST /auth/login` body: `{email, password, ...device}` → either a **Session** or `{two_factor_required: true, challenge}`; then `POST /auth/two-factor {challenge, code, ...device}`.
`POST /auth/refresh {refresh_token}` → **Tokens**. `POST /auth/magic-link {email}`; `POST /auth/magic-link/verify {token}`. `POST /auth/social/{google|apple} {token, name?, account_type?, invite_code?}`.

**Tokens**: `{access_token, refresh_token, token_type: "Bearer", expires_at, refresh_expires_at}` (access 60 min, refresh 60 days, rotated).
**Session** = Tokens + `{user: User, tenants: [Tenant]}`.
**User**: `{id, name, first_name, email, phone, avatar_url, locale, timezone, units: metric|imperial, theme: light|dark|system, email_verified, two_factor_enabled, current_tenant_id, is_super_admin, notification_preferences: {<category>: {push, email}}, quiet_hours: {enabled, from, to}, created_at}`
**Tenant**: `{id, type: business|personal, name, slug, logo_url, brand_color, currency, country, timezone, locale, plan, trial_ends_at, onboarded, role: owner|coach|assistant|client}`
`GET /me` → `{user, tenants}`. `PATCH /me` accepts user fields (+ `current_password` & `password`). `POST /me/devices {platform, push_token, app, app_version, device_name, timezone}`.
`GET /invitations/{code}` → `{code, role, email, name, tenant: {name, logo_url, brand_color}, coach: {name}}`. `POST /invitations/accept {code}` → `{user, tenants}`.

## Client — Today
`GET /me/today?date=YYYY-MM-DD` →
```json
{
  "date": "2026-11-01",
  "greeting_name": "Omar",
  "coach": {"id": "…", "name": "Sara", "avatar_url": null} ,
  "workout": TodayWorkout|null,
  "streak": {"current": 5, "best": 21},
  "next_session": Booking|null,
  "nutrition": {"targets": Macros|null, "consumed": Macros, "water_ml": 1250, "water_target_ml": 2500},
  "habits": {"completed": 2, "total": 4},
  "checkin_due": {"id": "…", "due_on": "2026-11-02", "form_name": "Weekly check-in"}|null,
  "unread_messages": 3
}
```
**TodayWorkout**: `{workout_id, program_day_id, program_assignment_id, name, estimated_minutes, exercise_count, week_number, day_number, status: pending|in_progress|completed, session_id|null}`
**Macros**: `{kcal, protein_g, carbs_g, fat_g}` (numbers).

## Workouts
**Exercise**: `{id, slug, name, primary_muscle, secondary_muscles: [], equipment: [], pattern, mechanics, force, level, is_unilateral, is_bodyweight, metrics: ["weight","reps"], video: {provider: hls|youtube|vimeo|upload|null, url, poster_url, preview_url, status}, instructions: [string], tips: [string], mistakes: [string], alternatives: [{id, name}], is_custom, is_favorite}`; list items omit instructions/tips/mistakes/alternatives.
`GET /exercises?q=&muscle=&equipment=&level=&favorites=1&cursor=` (search works in all 4 languages incl. aliases).
**Workout**: `{id, name, description, difficulty, estimated_minutes, tags: [], equipment_summary: [], blocks: [Block], updated_at}`
**Block**: `{id, position, type: straight|superset|giant|circuit|tabata|emom|amrap|interval|warmup|cooldown, name, rounds, work_seconds, rest_seconds, rest_between_rounds, time_cap_seconds, notes, exercises: [WorkoutExercise]}`
**WorkoutExercise**: `{id, position, exercise: Exercise(list form), rest_seconds, tempo, notes, alternative_ids: [], sets: [SetTemplate]}`
**SetTemplate**: `{id, position, set_type: warmup|working|drop|failure|amrap, reps_min, reps_max, weight_kg, percent_1rm, rpe, rir, duration_seconds, distance_m, rest_seconds}`
Write (staff `POST/PUT /workouts`): same nested shape without ids (ids optional; full replace of blocks on update).
**Program**: `{id, name, description, goal, level, days_per_week, equipment, tags, visibility, is_template, weeks_count, weeks: [{id, number, phase, is_deload, notes, days: [{id, day_number, title, is_rest, workout: {id, name, estimated_minutes}|null, meal_plan_id, habit_ids}]}]}`
`POST /programs/{id}/assign {client_ids: [], start_date, unlock_mode?: all|weekly_drip|daily}` → `[ProgramAssignment]`.
**ProgramAssignment**: `{id, program: {id, name}, client: {id, name}, start_date, status, unlock_mode, current_week, progress_pct}`

`GET /me/program` → `{assignment: ProgramAssignment, program: Program, unlocked_through: "YYYY-MM-DD"}` or `data: null`.
`GET /me/workouts/{id}` → Workout + `{download_size_bytes}` and each exercise includes `last_performance` (see below).

### Sessions (offline-first logger)
**Session**: `{id, workout_id, program_day_id, name, status: in_progress|completed|abandoned, started_at, ended_at, duration_seconds, total_volume_kg, perceived_effort, mood, notes, sets: [SessionSet], personal_records: [PR]}`
**SessionSet**: `{id, exercise_id, workout_exercise_id, set_number, set_type, reps, weight_kg, duration_seconds, distance_m, rpe, is_completed, completed_at}`
- `POST /me/sessions {id?: client ULID, workout_id?, program_day_id?, program_assignment_id?, name?, started_at?}` — idempotent on `id`.
- `POST /me/sessions/{id}/sets {sets: [SessionSet]}` — batch upsert by set `id` (client ULIDs). Re-sending the same set is a no-op. Returns `{sets: [SessionSet]}`.
- `POST /me/sessions/{id}/complete {ended_at?, perceived_effort?, mood?, notes?}` → Session with `personal_records` detected. Idempotent.
- `GET /me/exercises/{id}/last` → **LastPerformance** `{session_id, performed_at, sets: [{set_number, reps, weight_kg, duration_seconds, distance_m, rpe}]}` or null — used for prefill.
- `GET /me/exercises/{id}/history` → `{best_e1rm_kg, best_weight_kg, sessions: [{session_id, date, top_set: {reps, weight_kg}, volume_kg, e1rm_kg}]}`
- **PR**: `{id, exercise_id, exercise_name, type: e1rm|weight|reps|volume, value, previous_value, achieved_at}`. `GET /me/records` → `[PR]` (best per exercise/type).

## Nutrition
**Food**: `{id, name, brand, barcode, source, region, category, per_100g: {kcal, protein_g, carbs_g, fat_g, fiber_g, sugar_g, sodium_mg}, servings: [{unit, grams, label}], diet_tags: [], allergens: [], is_verified, is_custom, is_favorite}`
`GET /foods?q=&region=&cursor=` · `GET /foods/barcode/{code}` (falls back to Open Food Facts; 404 `not_found` when unknown).
**Recipe**: `{id, slug, name, description, cuisine, prep_minutes, cook_minutes, servings, tags, photo_url, per_serving: Macros, items: [{food: Food(list), grams, display_qty, display_unit}], steps: [string]}`
`GET /me/nutrition?date=` →
```json
{"date":"…","targets":Macros|null,"water_ml":1250,"water_target_ml":2500,"consumed":Macros,
 "meals":[{"slot":"breakfast","label":"Breakfast","consumed":Macros,"logs":[FoodLog],"planned":[PlannedItem]}],
 "fasting":{"mode":"none|intermittent|ramadan","window_start":"18:05","window_end":"04:12"}|null}
```
**FoodLog**: `{id, log_date, slot, food_id, recipe_id, name, quantity, unit, kcal, protein_g, carbs_g, fat_g, source: search|barcode|quick|photo|plan|copy, logged_at}`
**PlannedItem**: `{meal_item_id, food_id, recipe_id, name, quantity, unit, kcal, protein_g, carbs_g, fat_g, logged: bool}`
- `POST /me/food-logs {id?: ULID, log_date, slot, food_id?|recipe_id?|name (quick add), quantity, unit: g|serving|<serving unit>, kcal?, protein_g?, carbs_g?, fat_g?, source}` (macros computed server-side when food/recipe given). Idempotent on `id`.
- `POST /me/food-logs/from-plan {log_date, slot}` logs every planned item of that meal. `POST /me/food-logs/copy-day {from_date, to_date, slot?}`.
- `POST /me/water {id?, log_date, ml}`. `POST /me/food-logs/photo` multipart `photo` → `{candidates: [{food: Food(list)|null, name, grams, confidence, kcal, protein_g, carbs_g, fat_g}], ai_output_id}`.
`GET /me/nutrition/plan` → active meal plan `{id, name, type, days: [{day_number, label, meals: [{slot, name, time, items: [PlannedItem]}]}]}` or null.

## Habits
`GET /me/habits?date=` → `[{assignment_id, habit: {id, name, icon, type: boolean|count|duration|numeric, unit}, target, scheduled_today, value, completed, streak: {current, best}, completion_pct_30d, reminder_times: ["08:00"]}]`
`POST /me/habits/{assignment_id}/log {date, value?, completed?}` → same item.

## Check-ins & progress
**CheckinForm** questions: `[{id, type: short_text|long_text|scale|single_choice|multi_choice|number|photo|measurement|file, label, required, options?: [string], min?, max?, unit?}]` (labels localized).
`GET /me/checkins` → `[{id, form_name, due_on, status: pending|submitted|reviewed|overdue, submitted_at, reviewed_at}]`
`GET /me/checkins/{id}` → `{id, status, due_on, form: {id, name, intro, questions, include_metrics, include_photos}, answers, coach_feedback, comments: [{id, user: {id, name}, body, created_at}]}`
`POST /me/checkins/{id}/submit {answers: {<question_id>: value}, weight_kg?, body_fat_pct?, measurements?: {waist: 80,…}, photo_ids?: []}`
`POST /me/progress-photos` multipart `photo, pose: front|side|back, taken_on, checkin_response_id?` → `{id, pose, taken_on, url (signed, 10 min)}`
`POST /me/body-metrics {measured_on, weight_kg?, body_fat_pct?, measurements?}`
`GET /me/progress` → `{weight: [{date, weight_kg}], body_fat: [{date, value}], measurements: [{date, values}], photos: [{id, pose, taken_on, url}], records: [PR], compliance: {score, workouts_pct, nutrition_pct, habits_pct, checkins_pct}}`

## Messaging (Reverb: `presence-conversation.{id}` events `message.sent`, `message.updated`, `message.deleted`, `typing`; `private-user.{id}` event `conversation.updated`)
**Conversation**: `{id, type: direct|group|broadcast|checkin, title, participants: [{id, name, avatar_url, role}], last_message: Message|null, unread_count, is_muted, updated_at}`
**Message**: `{id, conversation_id, sender: {id, name, avatar_url}|null, type: text|voice|image|video|file|share|system, body, meta: {}, attachments: [{id, mime, size, url, duration_ms, waveform}], reply_to: {id, body, sender_name}|null, reactions: [{emoji, count, mine}], channel, edited_at, created_at, deleted: bool}`
- `GET /conversations/{id}/messages?cursor=` newest first. `POST /conversations/{id}/messages` (JSON `{id?: ULID, type, body, reply_to_id?, meta?}` or multipart with `file`) — idempotent on `id`.
- `PATCH /messages/{id} {body}` (≤15 min, own). `DELETE /messages/{id}` (delete for everyone). `POST /messages/{id}/reactions {emoji}` toggles. `POST /conversations/{id}/read`.

## Calendar & bookings
**Event**: `{id, type: session|class|event|live|block, title, description, starts_at, ends_at, timezone, capacity, booked_count, waitlist_count, credits_cost, coach: {id, name}, location: {id, name}|null, meeting_url, rrule, status}`
**Booking**: `{id, event: Event, status: booked|waitlisted|attended|no_show|late_cancel|cancelled, waitlist_position, checkin_code, can_cancel_free_until, created_at}`
- `GET /me/slots?coach_id=&date=` → `[{starts_at, ends_at}]` (availability minus busy). `GET /me/classes?from=&to=` → `[Event]`.
- `POST /me/bookings {event_id}` (class) or `{coach_id, starts_at}` (1:1 slot) → Booking (waitlisted when full). `POST /me/bookings/{id}/cancel` (late cancel → credit forfeited per policy). `POST /me/bookings/{id}/claim` (waitlist offer). `GET /me/credits` → `{balance, entries: [{id, amount, reason, note, created_at}]}`.

## Shop & payments (client)
**Product**: `{id, type, slug, name, description, image_url, prices: [{id, type: one_time|recurring|installments, amount: Money, interval, interval_count, installments, trial_days}], ios_purchase_mode: hidden|external|iap}`
`POST /me/shop/{product}/checkout {price_id, coupon?}` → `{order_id, checkout_url|null, status}` (open `checkout_url` in an in-app browser; poll `GET /public/orders/{id}/status` → `{status, fulfilled}`).
`GET /me/purchases` → `{orders: [...], subscriptions: [{id, product_name, status, amount: Money, interval, current_period_end, cancel_at_period_end}]}`. `POST /me/subscriptions/{id}/cancel` (two taps; ends at period end).

## Sync
`GET /sync/pull?since=<ISO>` → `{server_time, changes: {sessions: [...], session_sets: [...], food_logs: [...], water_logs: [...], habit_logs: [...], body_metrics: [...], bookings: [...]}, deleted: {food_logs: [ids], ...}}`
`POST /sync/push {mutations: [{id: ULID, entity: session|session_set|food_log|water_log|habit_log|body_metric|message, op: upsert|delete, payload: {...}, client_time}]}` → `{results: [{id, status: applied|duplicate|rejected, error?}], server_time}`. Conflict policy: server timestamps; last-write-wins per field; session sets append-only.

## Notifications
`GET /notifications?cursor=` → `[{id, category, title, body, link, data, read_at, created_at}]`; `meta.unread_count`. `POST /notifications/read {ids?: []}` (all when omitted).

## Coach endpoints
Staff endpoints (`/dashboard`, `/clients`, `/programs`, …) reuse the shapes above. Key additions:
- **ClientSummary**: `{id, name, email, avatar_url, status, goal, level, tags, coach: {id, name}|null, compliance_score, risk_status: green|amber|red, last_active_at, program: {id, name}|null, unread_messages, joined_at}`.
- `GET /dashboard` → `{kpis: {active_clients, at_risk, checkins_to_review, unread_messages, sessions_today, mrr: Money, revenue_month: Money}, at_risk_clients: [ClientSummary], todays_sessions: [Event], recent_activity: [{type, client, text, at}], onboarding: {steps: [{key, done}]}}`.

## Addenda (implemented additions — all additive, no renames)
**Workouts**
- Exercise detail: `progression_parent_id`, `progression_step`; staff also get `*_translations`, `aliases`.
- Workout: `is_template`, `is_global`, `version`; WorkoutExercise: `progression_rule`. Program: `is_global`, `cover_url` (+ `name_translations`, `description_translations` for staff). ProgramAssignment: `shift_days`, `paused_at`. PR: `weight_kg`. Session: `program_assignment_id`.
- `GET /me/workouts/{id}` returns set targets with progression rules applied and %1RM converted to kg when an estimated 1RM exists.
- First-ever log of an exercise stores a baseline record (`previous_value: null`) visible on `/me/records` but not celebrated/evented.
- Sync: pull rows carry `updated_at`; session sets carry `session_id`; rejected push results carry `message`; first pull without `since` returns the last 90 days (config `workouts.sync_initial_days`). `message` entity → rejected `use_messages_endpoint`.

**Nutrition / habits / check-ins**
- Staff-only: Food `name_translations`, `aliases`, `moderation`; Recipe `*_translations`, `is_published`, `is_custom`. `?servings=N` scales a recipe. `/me/foods/recent?favorites=1`. Quick-add food log accepts `save_food: true` (saves as user-submitted, moderation pending).
- Meta: targets list `meta.current`, `meta.profile_inputs`; check-in inbox and compliance list `meta.counts`.
- `GET /clients/{id}/compliance` → `{score, risk_status, at_risk, workouts_pct, nutrition_pct, habits_pct, checkins_pct, previous_score, days_inactive, last_active_on, weights, components, reasons}`.

**Messaging / calendar**
- Event: `series_id`, `is_occurrence`, `occurrence_starts_at`. `POST /me/bookings {event_id, starts_at}` books a recurring-class occurrence. Staff `PATCH|DELETE /events/{id}` take `scope: this|future|all` + `occurrence_starts_at`.
- Booking: `waitlist_offer_expires_at`, `checked_in_at`, `client`. Conversations list: `meta.total_unread`. Credit entries: `reason_label`, `booking_id`, `expires_on`.
- Credit balance is only enforced at booking when tenant setting `calendar.require_credits` is true.

**Payments**
- `GET /me/purchases` also returns `credits`. `GET /me/invoices` is cursor-paginated. Public checkout `POST` requires `name` + `email`; redirect URLs must be on our host. Shareable checkout page: `{APP_URL}/checkout/{tenantSlug}/{productSlug}`.
- Tenant settings read by payments: `payments.tax_enabled`, `payments.tax_rate`, `payments.client_can_pause`, `payments.invoice_prefix.*`; invoice seller block from `tenants.legal` (`legal_name`, `vat_number`, `address`, `registration_number`).

**AI**
- Free plan: `ai_requests_month` acts as a trial allowance for coach features, then 402 `plan_limit_reached`.
- Photo-log candidates include `low_confidence`. `GET /ai/churn` → `data: [clients]`, `meta: {weights, ai_output_id}`.
- `POST /ai/outputs/{id}/approve {apply: true, edited_payload?}` applies program / meal_plan / progression drafts (409 `program_shared` if the client's program is shared). Form check for coached clients → `status: pending_coach_review`. `POST /me/ai/coach-chat` streams SSE when `stream=true` or `Accept: text/event-stream`.
- Owner `PUT /ai/settings` accepts `client_tools: {client_photo_logging, self_guided_chat, client_form_check, client_weekly_recap}`.

**Integrations / marketplace**
- `POST /me/health/samples` accepts sources `apple_health|health_connect|manual` only → `{created, updated, duplicates, skipped, server_time}`. `GET /me/health/summary` → `{date, today, daily, readiness, sources}`. `GET /clients/{id}/health` → `{client, from, to, timeline, daily, readiness, sources}`.
- Integrations catalogue item: `{provider, name, description, category, method, audience, configured, connected, status, needs_reconnect, account, connected_at, last_sync_at, last_error}`.
- Personal API tokens carry abilities `access`, `pat`, `tenant:<id>` + scopes (`clients:read|write`, `webhooks:manage`, …); pinned to one tenant; cannot call `/api-tokens`. Outgoing webhook header `X-Aurum-Signature: t=<unix>,v1=<hmac-sha256(secret, "t.body")>`.
- Leads from public profile → `source: profile`; from match quiz → `marketplace`; repeat within 24h updates the open lead. Review submission: signed `POST marketplace/reviews/{tenant}/{client}` link emailed 30 days after joining.
