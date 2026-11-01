# Phase 7 — Integrations & Wearables

> Paste `00-PROJECT-CONTEXT.md` first, then this file. Build on Phases 1–6.

## Goal
Bring every relevant data source into one reliable timeline (steps, sleep, heart rate, workouts, weight, nutrition) and connect the tools coaches already use (Google/Outlook calendars, Zoom, WhatsApp, Zapier/Make) — with sync that actually works, unlike competitors' "sync only when you open the app".

## Competitor complaints this phase must solve
- MyFitnessPal syncs only on app open and is inconsistent.
- Apple Watch, Fitbit, Garmin unreliable or hard to connect.
- One-way calendar sync.

## Architecture
- `integrations` (provider, scope, status, last_sync_at, error), `integration_tokens` (encrypted, refresh handling), `health_samples` (normalized: type, value, unit, start, end, source, external_id unique for dedupe), daily aggregates table for fast charts.
- Provider driver interface: `connect()`, `refresh()`, `backfill(days)`, `handleWebhook()`, `pull(since)`.
- Sync strategy: webhooks where available → queued jobs; otherwise scheduled pulls with backoff; on-device background fetch for HealthKit/Health Connect.
- Data health panel for coach and client: connected sources, last sync, errors with "reconnect" CTA.
- Normalization rules: one source of truth per metric per day (priority list configurable), deduplication by external id + time window.

## Health & wearables
| Source | Method | Data |
|---|---|---|
| Apple Health (HealthKit) | Flutter `health` + background delivery (HKObserverQuery) | steps, active energy, workouts, HR, resting HR, HRV, sleep, weight, body fat, nutrition |
| Health Connect (Android) | Flutter `health` + WorkManager periodic | same set |
| Apple Watch | watchOS companion app (SwiftUI) — today's workout, set logging, rest timer, HR during session; syncs via WatchConnectivity | sessions, HR |
| Wear OS | Tile + simple logger (Phase 7b if time-boxed) | sessions |
| Garmin | Garmin Health API (push/webhook) | daily summaries, activities, sleep, body comp |
| Fitbit | Fitbit Web API + subscriptions | steps, sleep, HR, weight |
| Whoop | Whoop API | recovery, strain, sleep |
| Oura | Oura API v2 (webhooks) | readiness, sleep, activity |
| Polar | Polar AccessLink | training sessions, daily activity |
| Strava | OAuth + webhooks | activities (runs, rides) |
| Huawei Health | Health Kit (where available) | steps, sleep, activities |
| Withings | API + notifications | weight, body comp, BP |
| MyFitnessPal | CSV/export import (no public API) | historical nutrition |

- Habits auto-complete from health data (e.g. "10k steps", "8h sleep").
- Readiness score (simple, explainable: sleep vs baseline, HRV vs baseline, resting HR, training load) shown to coach; feeds Phase 6 progression suggestions.

## Productivity integrations
- **Google Calendar & Outlook (Microsoft Graph): two-way sync** — bookings push to external calendar; external busy blocks reduce availability; webhooks (push notifications/subscriptions) + periodic reconciliation; conflict handling.
- **Zoom & Google Meet:** auto-create meeting links for online sessions and live classes; recordings link back to on-demand library.
- **WhatsApp Business (Cloud API):** template messages for reminders, payment links, check-in nudges; opt-in tracking; inbound replies routed into the Phase 4 inbox (tagged WhatsApp).
- **Email providers:** SMTP/Postmark/SES driver; Mailchimp & Brevo audience sync for coaches' marketing.
- **Zapier & Make:** official app definitions (triggers: new client, payment, check-in submitted, workout completed, lead created; actions: create client, assign program, send message, add lead). Included in Pro — no add-on fee.
- **Public REST API + outgoing webhooks** for coaches: personal access tokens with scopes, webhook endpoints with signing secret, delivery log and retries, API docs page.
- **Google Sheets export** (scheduled) for coaches who live in spreadsheets.

## UI
- Coach web: Integrations marketplace page (cards with connect/disconnect, status), client data tab with merged health timeline and charts, webhook/API settings.
- Client app: "Connect your devices" onboarding step, permissions explainer screens (required by Apple/Google review), sync status per source, manual "sync now".
- Watch app: start today's workout, log sets with Digital Crown, rest timer haptics, HR display.

## Compliance
- Apple HealthKit and Google Health Connect policy compliance: purpose strings, data use disclosures, no advertising use, privacy policy sections.
- Store health data encrypted; per-source revoke deletes stored samples on request.

## Tests
- Driver tests with recorded fixtures (VCR-style) for each provider; webhook signature validation; dedupe logic; token refresh failure → reconnect notification; two-way calendar conflict scenarios; WhatsApp template rendering in 4 languages.
- Device integration test on simulator for HealthKit/Health Connect read and background delivery.

## Acceptance checklist
- [ ] Client connects Apple Health and Garmin; steps appear on coach dashboard within 15 minutes without opening the app.
- [ ] No duplicated workouts when the same run comes from Garmin and Strava.
- [ ] Booking created on the platform appears in Google Calendar; a Google event blocks that slot for booking.
- [ ] Session reminder delivered via WhatsApp in Arabic.
- [ ] Zapier trigger "workout completed" fires within 1 minute.
