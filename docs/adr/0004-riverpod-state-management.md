# 0004 — Riverpod (with codegen) for Flutter state

- Status: Accepted · Date: 2026-01-06

## Context
Two apps share most data access (auth, today, workouts, nutrition, messaging) through
`packages/core`. We need testable dependency injection, async state with caching, offline
awareness, and per-tenant/per-user scoping that resets cleanly on sign-out or tenant switch.

## Decision
`flutter_riverpod` 3 with `riverpod_annotation`/codegen. Providers live with their feature;
core exposes base providers (`core_providers.dart`: API client, database, sync engine, token
store, preferences, auth controller). Repositories are plain classes injected via providers.
Overrides power tests and white-label flavors.

## Alternatives considered
- **BLoC**: explicit but verbose; event/state boilerplate slows feature delivery across two apps.
- **Provider**: simpler, but no compile-time safety for families or auto-dispose semantics.
- **GetX / MobX**: global mutable patterns we do not want in a multi-tenant app.

## Consequences
- `ref.invalidate` on tenant switch/sign-out resets all tenant-scoped state.
- Build runner is part of CI (`mobile.yml` runs codegen before analyze/test).
- Contributors must learn Riverpod idioms (AsyncValue, families, keepAlive) — documented in core README.
