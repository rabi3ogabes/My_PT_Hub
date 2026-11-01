# Aurum — developer commands. `make help` lists everything.
# Backend commands run inside the `app` container; Flutter commands run on the host.

SHELL := /bin/bash
.DEFAULT_GOAL := help

COMPOSE   ?= docker compose
APP       := $(COMPOSE) exec app
APP_RUN   := $(COMPOSE) run --rm --no-deps app
FLUTTER   ?= flutter
DART_DEFINES ?= --dart-define=API_BASE_URL=http://localhost:8000/api/v1 --dart-define=REVERB_HOST=localhost --dart-define=REVERB_PORT=8080 --dart-define=REVERB_APP_KEY=aurum-local-key --dart-define=FLAVOR=dev
FLUTTER_PACKAGES := packages/core apps/client apps/coach
export UID := $(shell id -u)
export GID := $(shell id -g)

.PHONY: help
help: ## Show this help
	@awk 'BEGIN {FS = ":.*##"; printf "\n\033[1mAurum\033[0m make targets\n\n"} /^[a-zA-Z0-9_.-]+:.*##/ { printf "  \033[33m%-18s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

# ---------------------------------------------------------------- stack
.PHONY: up down build restart ps logs shell tinker
up: ## Start the full local stack (builds images on first run)
	@test -f backend/.env || (cp backend/.env.docker.example backend/.env && echo "created backend/.env from .env.docker.example")
	$(COMPOSE) up -d --build
	@echo "\n  Web/API  http://localhost:8000   Mailpit http://localhost:8025   MinIO http://localhost:9001\n"

down: ## Stop the stack (keeps volumes; use `make nuke` to drop them)
	$(COMPOSE) down

build: ## Rebuild images without cache
	$(COMPOSE) build --no-cache

restart: ## Restart workers after code changes to jobs/events
	$(COMPOSE) restart horizon reverb scheduler

ps: ## Show service status
	$(COMPOSE) ps

logs: ## Tail logs (make logs s=horizon for one service)
	$(COMPOSE) logs -f --tail=200 $(s)

shell: ## Shell inside the app container
	$(APP) bash

tinker: ## Laravel tinker
	$(APP) php artisan tinker

.PHONY: nuke
nuke: ## Stop the stack and DELETE all local volumes (db, redis, minio, meili)
	$(COMPOSE) down -v --remove-orphans

# ---------------------------------------------------------------- backend
.PHONY: install migrate seed fresh demo key
install: ## composer + npm install, app key, storage link, front-end build
	$(APP) composer install --no-interaction
	$(APP) sh -c 'grep -q "^APP_KEY=base64" .env || php artisan key:generate --force'
	$(APP) php artisan storage:link --force
	$(APP) npm ci --no-audit --no-fund
	$(APP) npm run build

migrate: ## Run pending migrations
	$(APP) php artisan migrate

seed: ## Seed platform data (roles, plans, libraries) + demo data when SEED_DEMO=true
	$(APP) php artisan db:seed

fresh: ## Drop everything, migrate and seed (demo coach + clients)
	$(APP) php artisan migrate:fresh --seed

demo: ## Re-seed only the flagged demo dataset
	$(APP) php artisan db:seed --class=Database\\Seeders\\DemoSeeder

# ---------------------------------------------------------------- quality
.PHONY: test test-pg lint lint-fix stan audit
test: ## Pest suite (SQLite in-memory, as in phpunit.xml)
	$(APP) php artisan test

test-pg: ## Pest suite against the Postgres `aurum_testing` database (CI parity)
	$(APP) env DB_CONNECTION=pgsql DB_HOST=postgres DB_DATABASE=aurum_testing DB_USERNAME=aurum DB_PASSWORD=aurum php artisan test

lint: ## Pint (check only) + OpenAPI coverage + store metadata limits
	$(APP) vendor/bin/pint --test
	node infra/scripts/openapi/generate.mjs --check
	node store/validate.mjs

lint-fix: ## Pint (fix)
	$(APP) vendor/bin/pint

stan: ## Larastan level 8 (installed on the fly; composer.json/lock restored afterwards)
	$(APP) sh -c 'cp composer.json /tmp/composer.json.bak && cp composer.lock /tmp/composer.lock.bak \
	  && composer require --dev "larastan/larastan:^3.0" --no-interaction --quiet \
	  && printf "includes:\n  - /var/www/html/vendor/larastan/larastan/extension.neon\nparameters:\n  level: 8\n  paths:\n    - /var/www/html/app\n" > /tmp/phpstan.neon \
	  ; vendor/bin/phpstan analyse -c /tmp/phpstan.neon --memory-limit=1G; status=$$?; \
	  cp /tmp/composer.json.bak composer.json; cp /tmp/composer.lock.bak composer.lock; composer install --no-interaction --quiet; exit $$status'

audit: ## composer + npm dependency audit
	$(APP) composer audit
	$(APP) npm audit --omit=dev

# ---------------------------------------------------------------- generators
.PHONY: tokens openapi erd docs-lint
tokens: ## Regenerate Tailwind CSS + Dart tokens from packages/design_tokens/tokens.json
	node packages/design_tokens/generate.mjs

openapi: ## Regenerate docs/openapi.yaml from routes/api.php
	node infra/scripts/openapi/generate.mjs

erd: ## Regenerate docs/erd.mmd + ERD.md tables from migrations
	node infra/scripts/generate-erd.mjs

docs-lint: openapi ## Validate the OpenAPI document with Redocly
	npx -y @redocly/cli@1.34.5 lint docs/openapi.yaml

# ---------------------------------------------------------------- mobile
.PHONY: flutter-get flutter-test flutter-analyze run-client run-coach whitelabel
flutter-get: ## flutter pub get for core + both apps
	@for p in $(FLUTTER_PACKAGES); do [ -f $$p/pubspec.yaml ] && (cd $$p && $(FLUTTER) pub get) || echo "skip $$p (no pubspec.yaml)"; done

flutter-analyze: ## flutter analyze for core + both apps
	@for p in $(FLUTTER_PACKAGES); do [ -f $$p/pubspec.yaml ] && (cd $$p && $(FLUTTER) analyze) || echo "skip $$p"; done

flutter-test: ## flutter test for core + both apps
	@set -e; for p in $(FLUTTER_PACKAGES); do if [ -f $$p/pubspec.yaml ]; then echo "▸ $$p"; (cd $$p && $(FLUTTER) test); else echo "skip $$p (no pubspec.yaml)"; fi; done

run-client: ## Run the client app against the local API (override DART_DEFINES=...)
	cd apps/client && $(FLUTTER) run $(DART_DEFINES)

run-coach: ## Run the coach app against the local API
	cd apps/coach && $(FLUTTER) run $(DART_DEFINES)

whitelabel: ## Generate white-label config: make whitelabel tenant=<slug>
	@test -n "$(tenant)" || (echo "usage: make whitelabel tenant=<slug>" && exit 1)
	dart run infra/whitelabel/generate.dart $(tenant)

# ---------------------------------------------------------------- ops
.PHONY: store-validate k6
store-validate: ## Check store listing character limits
	node store/validate.mjs

k6: ## API smoke/load test (BASE_URL=... VUS=... make k6)
	docker run --rm -i --network host -e BASE_URL=$${BASE_URL:-http://localhost:8000/api/v1} -e EMAIL -e PASSWORD -v $(PWD)/infra/k6:/scripts grafana/k6:0.55.0 run /scripts/api-smoke.js
