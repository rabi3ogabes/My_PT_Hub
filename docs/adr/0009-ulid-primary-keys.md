# 0009 — ULID primary keys on every domain table

- Status: Accepted · Date: 2026-01-05

## Context
Mobile apps create rows offline (sessions, sets, food logs, messages) and must reference them
before the server sees them. Public ids appear in URLs and must not leak counts or be guessable.
Index locality matters for write-heavy tables.

## Decision
Every domain table uses a **26-character ULID string primary key** (`$table->ulid('id')->primary()`,
`HasUlids` in `BaseModel`); the public id *is* the primary key; foreign keys use `foreignUlid`.
Clients may supply the id for offline-created rows; the server validates format and ownership and
upserts idempotently. Framework tables (jobs, permissions, roles) keep bigint ids.

## Alternatives considered
- **Bigint + separate public UUID**: two ids to keep in sync; offline creation still needs a client id.
- **UUIDv4**: random → poor B-tree locality and larger indexes than time-ordered ids.
- **UUIDv7**: comparable to ULID; ULID chosen for Laravel first-class support (`HasUlids`) and
  readable Crockford base32 in URLs.

## Consequences
- Offline sync needs no id remapping; idempotency is natural (`id` = idempotency key).
- Time-ordered ids give cursor pagination and recent-first scans good locality.
- 26-char strings are larger than bigint; acceptable at our scale. Dart uses `ulid.dart` in core.
- Spatie permission morph keys are 26-char strings (`model_id` length 26).
