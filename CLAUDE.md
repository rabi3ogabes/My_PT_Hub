# Aurum — Project Context (paste this FIRST in every session)

> Keep this file in the repo root as `CLAUDE.md` (or paste it at the start of every Claude session) so every phase shares the same rules.

## 1. What we are building
A production-grade, luxury, modern SaaS platform for personal trainers, online coaches, nutritionists and small gyms. It must beat My PT Hub, Trainerize, Everfit and PT Distinction on: **speed, AI depth, language reach (Arabic RTL + French), client-side growth, and billing trust.**

Products:
1. **Web platform (Laravel)** — marketing site, coach dashboard, client web portal, super-admin panel.
2. **Coach app (Flutter, iOS + Android)** — full parity with the web dashboard.
3. **Client app (Flutter, iOS + Android)** — works with a coach AND standalone (free self-guided plans).

## 2. Repository layout (monorepo)
```
/backend            Laravel app (API + web + admin)
/apps/coach         Flutter coach app
/apps/client        Flutter client app
/packages/core      Shared Dart package: models, API client, design system, l10n, offline DB
/docs               Specs, ERD, OpenAPI, ADRs, phase checklists
/infra              Docker, CI/CD, Fastlane, scripts
```

## 3. Tech stack (non-negotiable)
**Backend:** latest stable Laravel, PHP 8.3+, PostgreSQL 16 (or MySQL 8), Redis, Horizon, Octane (Swoole/FrankenPHP), Reverb (WebSockets), Sanctum, Cashier (Stripe), Scout + Meilisearch, Filament (admin), Spatie: permission, medialibrary, translatable, activitylog, backup, query-builder, data. Pest for tests, Larastan level 8, Pint.

**Web UI:** Blade + Livewire 3 + Alpine.js + Tailwind CSS. Marketing pages server-rendered for SEO.

**API:** REST `/api/v1`, OpenAPI 3 (Scramble or l5-swagger), consistent envelope `{data, meta, links, errors}`, cursor pagination, rate limiting, idempotency keys on payment endpoints, ETags for heavy GETs.

**Mobile:** latest stable Flutter + Dart 3, Riverpod (with codegen), go_router, Dio + Retrofit, Freezed + json_serializable, Drift (offline SQLite + sync queue), flutter_secure_storage, Firebase (FCM, Crashlytics, Analytics, Remote Config, Performance), `health`, `in_app_review`, `local_auth`, `video_player`/`better_player` (HLS), `flutter_localizations` + ARB.

**Media:** S3-compatible storage + CDN; videos to HLS (multi-bitrate) via queued FFmpeg job (or Mux/Cloudflare Stream driver).

**AI:** provider-agnostic `AiService` interface with drivers; streaming; usage metering per tenant; every AI output needs coach approval before reaching a client.

**DevOps:** Docker, GitHub Actions, Fastlane, Sentry, staging + production, daily backups.

## 4. Global rules
- **Multi-tenancy:** a Tenant = coaching business. Every tenant-owned table has `tenant_id`, enforced by a global scope + policies. Never leak data across tenants (write tests for it).
- **Roles:** super_admin, owner, coach, assistant, client. Granular permissions via spatie/permission.
- **Languages:** `ar` (RTL), `fr`, `en`, `es` from day one. No hard-coded user-facing strings anywhere. Use logical CSS (`ms-*`, `me-*`, `start`, `end`) and `Directionality`-aware Flutter widgets (`EdgeInsetsDirectional`, `AlignmentDirectional`).
- **Units & money:** store metric internally (kg, cm, ml); convert for display. Money stored as integer minor units + ISO currency.
- **Time:** store UTC; display in user timezone.
- **IDs:** ULIDs for public IDs.
- **Offline-first mobile:** every client write goes to local DB first, then a sync queue with retry + conflict resolution (server timestamps, last-write-wins per field, append-only logs for workout sets).
- **Security:** OWASP ASVS L2; encrypted health data at rest (Laravel encrypted casts for sensitive fields); signed URLs for media; audit log for sensitive actions; GDPR export & delete.
- **Billing trust:** monthly by default; two-tap cancel; reminders 7 days and 1 day before trial end/renewal; card removal on request; pro-rata refunds on annual.
- **Quality targets:** API p95 < 200 ms; web LCP < 2.0 s; app cold start < 2 s; crash-free > 99.8%; backend domain coverage > 80%.
- **No placeholders, no TODOs, no fake data in production code.** Seeders may create demo data, clearly flagged.

## 5. Design direction — professional, luxury, modern, smart
- Design tokens shared by Tailwind config and Flutter `ThemeExtension`: color, type scale, spacing (4pt grid), radius, elevation, motion durations/curves.
- Palette: near-black/charcoal base (`#0B0B0F`, `#16161D`), warm off-white surfaces for light mode, ONE signature accent (choose: electric lime `#C6FF3D` or champagne gold `#D4AF6A`), semantic success/warning/danger.
- Type: Inter or Manrope (Latin), IBM Plex Sans Arabic or Tajawal (Arabic). Large confident headings, tight letter-spacing on display sizes.
- Motion: 150–300 ms, ease-out; Rive/Lottie for key moments (PR, streak, workout complete); haptics on key actions.
- Light + dark mode. WCAG 2.2 AA. 44pt minimum touch targets. Skeleton loaders everywhere; every empty state teaches the next action.

## 6. How every phase is delivered
1. Restate the phase goal in 3 lines.
2. List every file you will create/modify.
3. Build completely — migrations, models, policies, services, controllers, requests, resources, routes, Livewire components, Flutter features, tests.
4. Run tests/analyzers; fix until green.
5. Update `/docs/CHANGELOG.md` and tick the phase checklist.
6. Stop and summarise what was built, what to test manually, and open questions.

## 7. Phase map
| # | File | Scope |
|---|------|-------|
| 0 | `phase-00-discovery-and-spec.md` | Spec, IA, flows, ERD, API contract, design tokens |
| 1 | `phase-01-foundation.md` | Auth, tenancy, roles, i18n/RTL, SaaS billing, admin, Flutter foundation |
| 2 | `phase-02-workouts.md` | Exercise library, builder, programs, smart logging |
| 3 | `phase-03-nutrition-habits-progress.md` | Nutrition, habits, check-ins, progress, compliance |
| 4 | `phase-04-communication-calendar.md` | Chat, notifications, community, calendar & bookings |
| 5 | `phase-05-payments-business.md` | Client payments, packages, gateways, analytics, CRM |
| 6 | `phase-06-ai-copilot.md` | AI Coach Copilot |
| 7 | `phase-07-integrations-wearables.md` | Health, wearables, calendars, Zoom, WhatsApp, webhooks |
| 8 | `phase-08-website-seo-marketplace.md` | Marketing site, SEO, coach profiles, marketplace |
| 9 | `phase-09-white-label-aso-launch.md` | White label, store listings, ASO, CI/CD, launch |

## 8. Decisions taken (Phase 0)
- Brand: **Aurum** (configurable via `BRAND_NAME`), accent **champagne gold `#D4AF6A`**.
- Launch markets: Qatar → KSA → UAE → France → Morocco.
- AI: Anthropic Claude by default (driver layer also ships OpenAI + Fake).
- Hosting: Docker images; Forge + Hetzner reference deployment.
- Actual versions in repo: Laravel 13, PHP 8.3, Livewire 4, Filament 5, Pest 4, Tailwind 4, Flutter 3.47 / Dart 3.13.
- Primary keys are ULIDs on every table (public id == primary key).
