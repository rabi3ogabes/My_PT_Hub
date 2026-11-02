# Changelog

All notable changes to Aurum. Format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/);
versions follow SemVer. Mobile apps are tagged `client-vX.Y.Z` / `coach-vX.Y.Z`; the platform is tagged `vX.Y.Z`.

## [Unreleased]

### Added — documentation, infrastructure & store (Phase 0 / Phase 9 deliverables)
- Product documentation: `PRODUCT_SPEC.md`, `INFORMATION_ARCHITECTURE.md`, `USER_FLOWS.md` (8 Mermaid flows), `DESIGN_SYSTEM.md`, `ARCHITECTURE.md`, `SECURITY.md`, `LAUNCH.md`, `PHASES.md`, ADRs 0001–0011, runbooks, `seo/keyword-map.md`.
- Generated artefacts with generators checked into `infra/scripts`: `ERD.md` / `erd.mmd` / `erd.json` from migrations; `openapi.yaml` (OpenAPI 3.1, 346 operations) from `routes/api.php` with a `--check` mode that fails CI on undocumented routes.
- Local stack: root `docker-compose.yml` (php-fpm with pdo_pgsql/redis/intl/gd/zip/pcntl/bcmath + ffmpeg, nginx with brotli/gzip and security headers, Postgres 16 with extensions + test DB, Redis 7, Meilisearch, Mailpit, MinIO + bucket bootstrap, Reverb, Horizon, scheduler), `backend/.env.docker.example`, `Makefile`.
- Production: multi-stage non-root `infra/docker/production/Dockerfile` (composer + Vite build, `app` and `web` targets), blue/green deploy bundle `infra/deploy` (Caddy edge, `deploy.sh`, `rollback.sh`).
- GitHub Actions: `backend.yml`, `mobile.yml`, `web-quality.yml` (Lighthouse CI + lychee), `deploy.yml` (GHCR, SBOM/provenance, Trivy, staging → production with approval), `mobile-release.yml` (Fastlane beta/release/rollout/whitelabel), `dependabot.yml`.
- Fastlane (`infra/fastlane`): `Fastfile`, `Appfile`, `Matchfile`, `ExportOptions.plist`, crash-free rollout gate; white-label generator `infra/whitelabel/generate.dart` with example tenant and README; k6 smoke/load script.
- Store metadata in `store/` for both apps × en/ar/fr/es with `store/validate.mjs`, privacy labels / Data safety mapped from actual data flows, categories & ratings, custom product pages, release notes.
- Framework translations `backend/lang/{ar,fr,es}/{validation,passwords,pagination}.php` (key and placeholder parity with `en` verified).

### Backend (Phases 1–8, all modules present; 569 Pest tests passing)
- Phase 1: auth (password, social, magic link, TOTP), Sanctum refresh rotation, tenancy with pre-binding resolution, invitations/CSV import, i18n with RTL, SaaS billing with trust rules, Filament admin with impersonation audit, notifications, GDPR export/delete, web shell.
- Phase 2: exercise library (182 exercises × 4 languages), workout/program builders API, 26 templates, progression engine, idempotent logger + PR detection, offline sync endpoints, FFmpeg → HLS pipeline.
- Phase 3: regional food database + barcode lookup, recipes, targets calculator with safety floors, meal plans incl. Ramadan mode, logging from plan, habits with streaks, check-ins with reminders/escalation, progress photos, compliance scoring.
- Phase 4: Reverb messaging, broadcasts, templates, automations engine, notification preferences, community + challenges + badges, calendar with RRULE, waitlists, credits ledger, bulk attendance.
- Phase 5: nine payment gateway drivers, products/prices/coupons, public checkout, fulfilment pipeline, manual payments, invoices with tax (ZATCA QR), analytics snapshots, CRM, contracts with e-signature.
- Phase 6: AI driver layer (Anthropic/OpenAI/Fake), approval gate, metering, program/meal-plan generators, check-in copilot, smart inbox, churn, form check, business assistant, self-guided coach, eval suite.
- Phase 7: wearable providers, HealthKit/Health Connect ingest, calendar two-way sync, Zoom, WhatsApp templates, outgoing webhooks, API tokens.
- Phase 8: localized marketing site with SEO foundations, exercise/template/calculator/recipe/blog pages, coach profiles, directory, reviews, marketplace match.

### Mobile
- `packages/core`: design system, networking, models, Drift offline DB + `SyncEngine`, l10n, Firebase services, routing.
- `apps/client`: auth, today, train + logger, nutrition, habits, check-ins, progress, messages, calendar, community, shop, settings.

### Not yet in the repository
- `apps/coach`; coach web builders/inbox/calendar/business dashboard UIs; Meilisearch Scout indexing; StoreKit IAP mapping; generated store screenshots; pentest/restore-drill/load-test execution records. See `PHASES.md`.

## [0.1.0] — 2026-01-05
### Added
- Repository scaffold, project context (`CLAUDE.md`), engineering conventions, API contract, phase plans, design tokens generator.
