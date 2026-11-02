# Phase checklist

Ticked = implemented in this repository and covered by tests or verifiable by a command.
Unticked = not yet in the repo (honest gaps), with the owner surface in parentheses.
Backend status as of 2026-11-02: **569 Pest tests passing** (`php artisan test`), all 17 domain modules present.
Detailed phase specs: [`phases/`](phases).

## Phase 0 — Discovery & spec
- [x] `PRODUCT_SPEC.md` — vision, personas, competitor gaps, features with acceptance criteria, pricing, NFRs, KPIs
- [x] `INFORMATION_ARCHITECTURE.md` — marketing, coach dashboard, client app, super-admin sitemaps
- [x] `USER_FLOWS.md` — 8 Mermaid flows (parse-validated)
- [x] `ERD.md` + `erd.mmd` + `erd.json` generated from the real migrations (124 tables, tenant scope + PII/encryption flags) via `infra/scripts/generate-erd.mjs`
- [x] `API_CONTRACT.md` + `openapi.yaml` (346 operations generated from `routes/api.php`; `npx @redocly/cli lint` passes with 0 warnings; CI fails on undocumented routes)
- [x] `DESIGN_SYSTEM.md` + `packages/design_tokens/tokens.json` + `generate.mjs` (Tailwind CSS + Dart outputs)
- [x] `ARCHITECTURE.md` + ADRs 0001–0011
- [x] Repo scaffold: root `README.md`, `CLAUDE.md`, `.editorconfig`, `.gitignore`, `docker-compose.yml`, `Makefile`, `CHANGELOG.md`, this file
- [x] `docker compose config -q` passes; `docker compose up` brings up php-fpm, nginx, postgres, redis, meilisearch, mailpit, minio (+ bucket bootstrap), reverb, horizon, scheduler

## Phase 1 — Foundation
- [x] Auth: email/password, Google/Apple, magic link, TOTP 2FA, email verification, password reset, Sanctum access + rotating refresh tokens, lockout, rate limits
- [x] Tenancy: tenants, memberships/roles (spatie teams), `ResolveTenant` before route binding, invitations (email/link/QR), CSV import, cross-tenant isolation tests
- [x] i18n: ar/fr/en/es lang files incl. framework `validation`/`passwords`/`pagination`, `SetLocale`, RTL layouts, translatable JSON columns, admin translations table
- [x] SaaS billing: plans seeder (Free 3 clients / Pro $39 / Studio $99 / Enterprise), trials without card, 7 d + 1 d reminders, cancel/resume/remove-card/portal, Stripe + Local gateways, idempotent webhooks, `PlanGate` limits & feature flags
- [x] Super-admin (Filament): tenants, users, plans, subscriptions, feature flags, translations, exercises/foods/recipes/programs, webhook events + replay, activity logs, impersonation with audit
- [x] Shared services: notifications (database/mail/FCM, prefs, quiet hours), activity log, GDPR export + account deletion, `/up`, security headers
- [x] Web shell: auth pages, onboarding wizard, dashboard with KPIs, settings (profile, business, team, billing, notifications), tenant switcher
- [x] `packages/core`: tokens/theme/components, Dio client + interceptors, Freezed models, Drift DB + `SyncEngine`, secure storage, ARB l10n, Firebase services, go_router + deep links; unit/widget tests
- [x] Client app (`apps/client`): flavors, auth/invite/magic-link screens, home shell, settings, biometric lock
- [ ] Coach app (`apps/coach`) — not scaffolded yet (mobile)
- [ ] Golden tests in LTR + RTL × light + dark for core components (mobile)
- [x] CI: `backend.yml` (Pint, Larastan on the fly, Pest on Postgres + Redis, audits), `mobile.yml` (analyze + test per package; skips packages without `pubspec.yaml`)

## Phase 2 — Workouts
- [x] Exercise library (182 seeded exercises, 4 languages, aliases, alternatives, progression chains), importer command, custom exercises, favourites
- [x] Video pipeline job (`ProcessExerciseVideo`: FFmpeg → HLS ladder + poster + preview), YouTube/Vimeo embeds
- [x] Workout builder API (all block/set types, duplicate, import/export JSON), programs (weeks/days, copy week, progression engine, deload), assignments (unlock modes, pause/shift), 26 program templates + public templates endpoint
- [x] Logger API: idempotent sessions/sets on client ULIDs, last performance, history, PR detection (e1RM/weight/reps/volume), `/sync/pull` + `/sync/push`
- [x] Client app logger: prefill, apply-to-all, rest timer, block modes, audio cues, plate/1RM calculators, finish summary, offline persistence
- [ ] Coach web builders (three-pane workout builder, program grid) in Livewire — API-complete, UI not yet built (web)
- [ ] Meilisearch index for exercises (Scout) — Postgres `search_text` fallback in place (backend)
- [ ] Lock-screen Live Activity (iOS) / ongoing notification (Android) for the rest timer (mobile)

## Phase 3 — Nutrition, habits, check-ins, progress
- [x] Foods (regional pack + barcode lookup with Open Food Facts fallback, moderation), recipes (seeded, macros computed), targets calculator with safety floors, meal plans (types incl. Ramadan, swaps, grocery list, PDF), food/water logs (from plan — regression-tested, copy day, recents, favourites)
- [x] Habits (library, assignments, logs, streaks across DST), check-in forms/schedules/responses/comments with reminders and escalation, body metrics, private progress photos with signed URLs, compliance scoring and at-risk digest, bulk message
- [x] Client app: nutrition day/plan/log sheets, habits, check-in flow, progress
- [ ] Coach web: nutrition plan builder, check-in inbox/review screen, compliance dashboard (web)

## Phase 4 — Communication & calendar
- [x] Messaging (Reverb channels, text/voice/media, replies, reactions, edit/delete, read receipts, typing, encrypted bodies), broadcasts + segments, saved templates, automations engine (idempotent runs)
- [x] Notifications service with preferences, quiet hours, FCM channel, device cleanup
- [x] Community (spaces, posts, comments, reactions, polls, reports, pin/hide), challenges + leaderboards, badges
- [x] Calendar: availability rules, booking settings, events with RRULE, bookings, waitlist auto-promotion + claim, QR check-in, bulk retroactive attendance, append-only credit ledger, ICS, reminders
- [x] Client app: messages, calendar, community
- [ ] Coach web inbox (three-pane), automation builder, calendar views (web)
- [ ] 1,000-connection Reverb load test (ops)

## Phase 5 — Payments, business, CRM
- [x] `PaymentGateway` drivers: Stripe Connect, PayPal, Tap, PayTabs, HyperPay, MyFatoorah, Paymob, Flutterwave, Manual; signature verification; idempotent `webhook_events` with admin replay
- [x] Products/prices/coupons, public branded checkout page + payment links/QR, orders, payments, refunds, client subscriptions (pause/resume/cancel), fulfilment pipeline, manual payments with receipts, expiry/revoke
- [x] Invoices: sequential per tenant, tax per country (ZATCA QR for KSA), multilingual RTL PDF, credit notes
- [x] Analytics daily snapshots (MRR, churn, LTV, cohorts, coach performance, commissions, CSV export)
- [x] CRM: stages, leads, activities, convert, lead capture from profiles; contracts/waivers/PAR-Q with e-signature and hash
- [x] iOS purchase mode per product (`sell_in_app_ios`) — ADR 0011
- [x] Client app shop/purchases/contracts
- [ ] Coach web: business dashboard, products manager, CRM kanban (web)
- [ ] StoreKit IAP mapping for `iap` products (mobile)

## Phase 6 — AI copilot
- [x] `AiProvider` with Anthropic, OpenAI, Fake drivers; versioned prompts; structured outputs with retry; metering (`ai_requests`, `usage_meters`, caps); `ai_outputs` approval gate; settings (tone, banned words, opt-out)
- [x] Features: program generator (library-only exercises), meal plan generator (±5 % verification), check-in summary + reply draft, progression diff, photo food logging, smart inbox (reply draft, thread summary), churn list, form check pipeline, business assistant (tool functions), content helper, self-guided coach + starter plan + weekly recap
- [x] Guardrails + eval suite (`tests/AiEvals`, red-team fixtures)
- [ ] Coach web Copilot command bar and draft review UI (web); client-app camera/form-check UI (mobile)
- [ ] Nightly eval workflow against the live provider (ops)

## Phase 7 — Integrations & wearables
- [x] `integrations` + encrypted tokens, OAuth state service, health providers (Garmin, Fitbit, Oura, Whoop, Strava, Withings, Polar), HealthKit/Health Connect ingest with dedupe, daily aggregates, habit auto-complete
- [x] Google Calendar + Microsoft two-way sync, Zoom meetings, WhatsApp Cloud API templates, outgoing webhooks with signatures + delivery log, scoped API tokens
- [ ] Huawei Health, MyFitnessPal CSV import, Mailchimp/Brevo sync, Google Sheets export (backend)
- [ ] Zapier/Make app definitions published (partner platforms)
- [ ] watchOS / Wear OS companions (mobile)

## Phase 8 — Website, SEO, marketplace
- [x] Server-rendered marketing pages (home, features, solutions, pricing, compare, switch, about, contact, legal), exercise pages, templates, calculators, recipes, blog/CMS with SEO fields, localized routing + hreflang, sitemaps, JSON-LD, checkout pages
- [x] Coach profiles + directory + city pages, reviews (verified, requests), marketplace match quiz, lead capture
- [x] `docs/seo/keyword-map.md` (36 pages × 4 locales), Lighthouse CI config and workflow
- [ ] Lighthouse budgets verified green on CI against built pages (needs first full run) (web)
- [ ] Referral and affiliate programs, lifecycle email sequences (backend/marketing)

## Phase 9 — White label, ASO, CI/CD, launch
- [x] White-label generator (`infra/whitelabel/generate.dart`, example tenant, README), Fastlane lanes `beta` / `release` / `rollout` / `metadata` / `build_whitelabel tenant:<slug>`, crash-rate gate script, ADR 0010
- [x] Store listings en/ar/fr/es for both apps (title, subtitle, short, keywords, full description, What's New template, screenshots storyboard, promo video storyboard), privacy labels + Data safety, categories & ratings, custom product pages; `store/validate.mjs` passes
- [x] CI/CD: `deploy.yml` (GHCR images with SBOM/provenance + Trivy, staging on `main`, production on tags behind a manual-approval environment, blue/green `infra/deploy/deploy.sh` with automatic rollback), `mobile-release.yml` (TestFlight + Play internal, staged rollout gated by crash-free rate), `web-quality.yml`, Dependabot
- [x] Hardening docs: `SECURITY.md` (ASVS L2 mapping with honest status, GDPR/PDPPL/PDPL), runbooks (outage, payment webhooks, push, store rejection, queue backlog, restore drill), `LAUNCH.md`, k6 script
- [ ] Real screenshots generated from integration tests and uploaded; icons A/B variants (mobile/design)
- [ ] Review-reply workflow pulling store reviews into admin (backend)
- [ ] External pentest, first restore drill, 10k-VU load test executed and recorded (ops)
- [ ] `in_app_review` prompt rules wired in the client app (mobile)
