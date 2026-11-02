# Aurum

**The coaching platform professionals are proud to put their name on.**

Aurum is a luxury, multilingual, multi-tenant SaaS for personal trainers, online coaches,
nutritionists and studios: programs, nutrition, check-ins, messaging, bookings, payments and an
AI copilot that drafts while the coach decides. Arabic (RTL), French, English and Spanish from
day one; offline-first apps; regional payment gateways; billing you can trust.

| | |
|---|---|
| Web platform | Laravel 13 · PHP 8.3 · Livewire 4 · Filament 5 · Tailwind 4 — marketing site, coach dashboard, client portal, super-admin |
| Mobile | Flutter 3.47 / Dart 3.13 · Riverpod · go_router · Dio · Freezed · Drift — client app and coach app |
| Data | PostgreSQL 16 · Redis 7 · Meilisearch · S3-compatible storage + CDN |
| Realtime & jobs | Reverb (WebSockets) · Horizon · scheduler |
| AI | Anthropic Claude by default, OpenAI and Fake drivers, coach-approval gate |
| Delivery | Docker · GitHub Actions · blue/green deploys · Fastlane · Sentry |

## Monorepo map

```
backend/                 Laravel API + web + admin   (app/Domain/{Module} · app/Http/Api/V1 · app/Livewire · app/Filament)
apps/client              Flutter client app (coach-led and standalone)
apps/coach               Flutter coach app (planned — see docs/PHASES.md)
packages/core            Shared Dart: design system, API client, models, offline DB + sync, l10n
packages/design_tokens   tokens.json + generate.mjs → Tailwind CSS + Dart
infra/
  docker/                dev php/nginx images · production multi-stage Dockerfile · postgres init · minio bootstrap
  deploy/                blue/green deploy bundle (Caddy edge, deploy.sh, rollback.sh)
  fastlane/              lanes: beta · release · rollout · metadata · build_whitelabel
  whitelabel/            tenant JSON → flavor config, icons, splash, store skeleton
  k6/                    API smoke / load test
  lighthouse/            Lighthouse CI budgets
  scripts/               ERD + OpenAPI generators
store/                   App Store / Play listings (client, coach × en/ar/fr/es), privacy declarations, ASO, validate.mjs
docs/                    specs, IA, flows, ERD, OpenAPI, design system, architecture, ADRs, security, launch, runbooks, SEO
.github/workflows/       backend · mobile · web-quality · deploy · mobile-release
```

## Quick start (local)

Prerequisites: Docker 24+ with Compose v2, Node 22 (for generators), Flutter 3.47 (for the apps).

```bash
git clone <repo> aurum && cd aurum
cp backend/.env.docker.example backend/.env
make up          # builds images and starts: web/api :8000, reverb :8080, mailpit :8025, minio console :9001, meilisearch :7700
make install     # composer install, APP_KEY, storage link, npm ci + build
make fresh       # migrate:fresh + seed (roles, plans, libraries, flagged demo tenant "Coach Sara" when SEED_DEMO=true)
```

Then open <http://localhost:8000>. Demo accounts are printed by the seeder (`SEED_DEMO=true`; never set in production).
Horizon: <http://localhost:8000/horizon>, admin: <http://localhost:8000/admin>.

Useful targets (`make help` lists all):

| Target | What it does |
|---|---|
| `make up` / `make down` / `make logs s=horizon` / `make shell` | stack lifecycle |
| `make migrate` / `make seed` / `make fresh` / `make demo` | database |
| `make test` / `make test-pg` | Pest on SQLite (fast) / on Postgres `aurum_testing` (CI parity) |
| `make lint` / `make lint-fix` / `make stan` / `make audit` | Pint, OpenAPI coverage, store limits / Larastan level 8 / dependency audits |
| `make tokens` / `make openapi` / `make erd` / `make docs-lint` | regenerate tokens, `docs/openapi.yaml`, `docs/ERD.md`; Redocly lint |
| `make flutter-get` / `make flutter-analyze` / `make flutter-test` | core + both apps |
| `make run-client` / `make run-coach` | run an app against the local API |
| `make whitelabel tenant=noor-fitness` | generate a white-label flavor |
| `make store-validate` / `make k6` | store metadata limits / API smoke test |

### Running the apps

```bash
cd apps/client
flutter pub get
flutter run \
  --dart-define=API_BASE_URL=http://localhost:8000/api/v1 \
  --dart-define=REVERB_HOST=localhost --dart-define=REVERB_PORT=8080 --dart-define=REVERB_APP_KEY=aurum-local-key \
  --dart-define=FLAVOR=dev
```

On the Android emulator use `http://10.0.2.2:8000/api/v1`; on a physical device use your machine's LAN IP
(and `APP_URL` in `backend/.env`). White-label flavors use `--flavor <flavor> --dart-define-from-file=config/flavors/<flavor>.json`
(see [`infra/whitelabel/README.md`](infra/whitelabel/README.md)).

### Tests & quality

```bash
make test                 # backend: 569 Pest tests
make stan                 # Larastan level 8 (installed on the fly, composer files restored)
make lint                 # pint --test + openapi --check + store validate
make flutter-test         # packages/core, apps/client (apps/coach when present)
node infra/scripts/openapi/generate.mjs --check && npx -y @redocly/cli@1.34.5 lint docs/openapi.yaml
node store/validate.mjs
docker compose config -q
```

CI runs the same on every PR (`.github/workflows`). Production images are built from
`infra/docker/production/Dockerfile`, pushed to GHCR, deployed to staging on `main` and to
production on `v*` tags behind a manual approval, with blue/green switching and automatic rollback.

## Documentation

| | |
|---|---|
| Product | [Product spec](docs/PRODUCT_SPEC.md) · [Information architecture](docs/INFORMATION_ARCHITECTURE.md) · [User flows](docs/USER_FLOWS.md) · [Design system](docs/DESIGN_SYSTEM.md) |
| Engineering | [Conventions](docs/CONVENTIONS.md) · [Architecture](docs/ARCHITECTURE.md) · [ADRs](docs/adr) · [ERD](docs/ERD.md) · [API contract](docs/API_CONTRACT.md) · [OpenAPI](docs/openapi.yaml) |
| Operations | [Security & privacy](docs/SECURITY.md) · [Runbooks](docs/runbooks) · [Launch plan](docs/LAUNCH.md) · [White label](infra/whitelabel/README.md) |
| Growth | [SEO keyword map](docs/seo/keyword-map.md) · [Store listings & ASO](store) |
| Delivery | [Phase checklist](docs/PHASES.md) · [Changelog](docs/CHANGELOG.md) · [Phase specs](docs/phases) |

Project rules for every contributor (human or agent) live in [`CLAUDE.md`](CLAUDE.md) and
[`docs/CONVENTIONS.md`](docs/CONVENTIONS.md).

## License

Proprietary. © Aurum. All rights reserved.
