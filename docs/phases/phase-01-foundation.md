# Phase 1 — Foundation (Backend + Mobile + Admin)

> Paste `00-PROJECT-CONTEXT.md` first, then this file. Phase 0 docs (`/docs/*`) are the source of truth — read them before coding.

## Goal
A running, tested platform skeleton: authentication, multi-tenancy, roles, i18n with Arabic RTL, SaaS subscription billing, super-admin panel, and both Flutter apps able to sign in, switch language and work offline.

## Backend (Laravel) — `/backend`

### 1. Setup
- Fresh latest-stable Laravel, PHP 8.3+, Postgres, Redis, Horizon, Octane, Reverb, Sanctum, Pest, Larastan (level 8), Pint, Scramble (OpenAPI).
- Domain folder structure: `app/Domain/{Identity,Tenancy,Billing,Shared}/{Models,Actions,Data,Policies,Events,Jobs}`; HTTP in `app/Http/{Api/V1,Web}`.
- Base classes: `ApiController` with envelope helpers, `ApiException` handler (consistent error JSON), `BaseModel` with ULID public id, `HasTenant` trait + `TenantScope`.

### 2. Identity & auth
- Email/password, Apple and Google sign-in (Socialite), magic link, optional TOTP 2FA, email verification, password reset.
- Sanctum tokens for mobile (device name, abilities, refresh rotation); session auth for web.
- `devices` table storing FCM tokens, platform, app version, locale, timezone.
- Rate limiting per IP + per account; lockout after repeated failures; login activity log.

### 3. Multi-tenancy & roles
- `tenants` (name, slug, logo, colors, currency, timezone, locale default, plan), `tenant_user` pivot with role.
- Roles/permissions via spatie/permission scoped by tenant (teams feature). Seed: owner, coach, assistant, client.
- Middleware `ResolveTenant` (from token, subdomain `{slug}.app.domain`, or header `X-Tenant`).
- Invitation system: coach invites client by email/phone/link/QR; client accepts → account + tenant membership. Bulk CSV import of clients.
- **Cross-tenant isolation test suite** (must exist and pass).

### 4. Internationalization
- `ar`, `fr`, `en`, `es` lang files (JSON + PHP), `spatie/laravel-translatable` for DB content.
- `SetLocale` middleware from user preference → `Accept-Language` → tenant default.
- Blade layouts with `dir="rtl"` when Arabic, logical Tailwind utilities, Arabic font stack.
- Number/date/currency formatting helpers (intl).
- Admin-editable translations stored in DB with cache.

### 5. SaaS billing (coach pays the platform)
- Laravel Cashier (Stripe) with plans Free / Pro / Studio / Enterprise, monthly and annual prices, multi-currency.
- Plan feature gates (`Feature::active('ai_copilot')` via Laravel Pennant) and limits (clients on Free = 3, coaches per plan).
- **Trust rules:** trial with no card; emails 7 days + 1 day before trial end and before every renewal; cancel in two clicks (web) / two taps (app); cancel = end of period on monthly, pro-rata refund on annual; "remove my card" action; invoices downloadable; billing portal.
- Webhooks handled idempotently; dunning emails on failed payment.

### 6. Super-admin (Filament)
- Tenants, users, subscriptions, plans, feature flags, translations editor, exercise/food master data placeholders, impersonation with audit log and banner, system health (queues, failed jobs, Reverb, storage).

### 7. Shared platform services
- Notifications: database + mail + FCM push channels, user notification preferences per type.
- Media: spatie/medialibrary on S3/MinIO, signed URLs, image conversions (webp, thumbnails).
- Activity log, GDPR export (zip of JSON + media) and account deletion job.
- Health endpoint `/up`, Sentry, structured JSON logs.

### 8. Web UI shell
- Tailwind config generated from design tokens; light/dark toggle; RTL support.
- Auth pages (luxury split-screen design), onboarding wizard (business name, logo, brand color, currency, language), empty dashboard shell with sidebar navigation for all future modules (disabled items show "coming soon").

## Mobile (Flutter) — `/packages/core`, `/apps/coach`, `/apps/client`

### 1. Shared `core` package
- Design system: `AppTokens`, `ThemeData` light/dark from tokens, typography with Arabic font fallback, components from the inventory (buttons, inputs, cards, skeletons, empty states, bottom sheets, toasts).
- Networking: Dio + Retrofit, interceptors (auth token, refresh, locale, tenant, retry with backoff, logging in debug), error mapping to typed failures.
- Offline: Drift database, `SyncQueue` table (mutation id ULID, entity, payload, attempts, status), background sync worker (on connectivity + app resume + periodic), conflict policy from ADR.
- Storage: secure storage for tokens, shared preferences for settings.
- Localization: ARB files for ar/fr/en/es, `Directionality` from locale, RTL-safe widgets only.
- Firebase: Crashlytics, Analytics (event helper with typed events), Remote Config, FCM handler + local notifications, Performance.
- Routing: go_router with auth guard and deep links (`https://app.domain/invite/{code}`, custom scheme).

### 2. Coach app & client app
- Flavors: dev / staging / prod (and white-label flavor hook for Phase 9).
- Splash (native), onboarding carousel, sign in / sign up / magic link / Apple / Google, invite acceptance, language picker, theme toggle, biometric unlock option.
- Home shells with bottom navigation placeholders for upcoming modules.
- Settings: profile, language, units (metric/imperial), notifications, privacy (export / delete account), subscription (coach app — opens billing; client app — none), about, support.

## Tests & quality
- Pest feature tests: auth flows, tenant isolation, invitations, billing webhooks, plan limits, locale switching.
- Flutter: unit tests for sync queue and repositories, widget tests for auth screens, golden tests for core components in LTR and RTL, light and dark.
- CI (GitHub Actions): backend lint + static analysis + tests; Flutter analyze + test for each package/app.

## Acceptance checklist
- [ ] Coach can sign up, get a tenant, pick plan, invite a client; client accepts on mobile.
- [ ] Switching to Arabic flips web and app to RTL with correct fonts.
- [ ] Airplane-mode edits on mobile sync when back online.
- [ ] Cancel subscription works in two clicks and sends confirmation email.
- [ ] All tests green, Larastan level 8 clean, `flutter analyze` clean.
