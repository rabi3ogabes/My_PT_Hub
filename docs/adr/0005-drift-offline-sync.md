# 0005 — Drift local database + mutation queue for offline-first sync

- Status: Accepted · Date: 2026-01-07

## Context
Gyms have poor signal. Competitor reviews cite lost workouts and auto-paused sessions. Every
client write (sets, sessions, food, water, habits, body metrics, messages) must succeed offline,
survive app kills, and sync exactly once.

## Decision
- **Drift (SQLite)** in `packages/core/lib/src/offline`: `SyncQueue` (id ULID, entity, op,
  payload, attempts, status, lastError, nextAttemptAt), `SyncMeta` (last `server_time`),
  `CachedResponses`/`CachedEntities` (read cache), `LocalSessions`/`LocalSessionSets` (logger).
- **Client-generated ULIDs** for every offline-created row; the server upserts idempotently and
  records applied ids (`sync_mutations`).
- **Endpoints:** `POST /sync/push` (≤ 500 mutations, per-item `applied|duplicate|rejected`),
  `GET /sync/pull?since=` (changes + deletions since the last `server_time`).
- **SyncEngine** flushes on connectivity, resume, a 5-minute timer and 600 ms after enqueue;
  exponential backoff `2 s·2^(n−1)` capped at 10 min; rejected mutations are kept for the user
  to retry or discard — never silently dropped.
- **Conflicts:** server timestamps; last-write-wins per field; session sets, messages and ledger
  entries append-only.

## Alternatives considered
- **Hive/Isar**: fast KV stores, but relational queries (history, PRs) and migrations are weaker.
- **CRDTs / full sync frameworks (PowerSync, ElectricSQL)**: powerful but add infrastructure and
  licensing; our conflict surface is small and append-heavy.
- **Online-only with retries**: fails the core promise.

## Consequences
- Domain endpoints (`POST /me/sessions/{id}/sets`) and sync push share the same idempotent
  services, so both paths are safe.
- The server must tolerate out-of-order arrival (a set before its session): pushes are ordered by
  `client_time` and parents are upserted first within a batch.
- Local schema migrations are versioned with Drift; integration tests cover kill-and-resume.
