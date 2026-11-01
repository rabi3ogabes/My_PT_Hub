# 0002 — PostgreSQL 16 over MySQL 8

- Status: Accepted · Date: 2026-01-05

## Context
The stack allows "PostgreSQL 16 (or MySQL 8)". The schema is JSON-heavy (translatable columns,
check-in answers, automation configs), needs strong constraints for bookings and ledgers,
analytics queries (cohorts, MRR), trigram/accent-insensitive search fallbacks, and safe online
migrations for zero-downtime deploys.

## Decision
PostgreSQL 16 everywhere (local compose, CI services, staging, production). Extensions:
`pg_stat_statements`, `pg_trgm`, `unaccent`, `citext`. Tests run on SQLite in-memory for speed
locally and on Postgres in CI (`backend.yml`, `make test-pg`) for parity.

## Alternatives considered
- **MySQL 8**: familiar to the Laravel ecosystem and Forge; weaker JSON indexing ergonomics, no
  partial/exclusion constraints, less capable transactional DDL.
- **Managed NoSQL for logs**: unnecessary at our scale; adds a second consistency model.

## Consequences
- JSONB + GIN indexes for translatable search and answers; partial unique indexes (e.g. one
  active booking per client per event); exclusion constraints available for overlapping slots.
- Transactional DDL makes failed migrations roll back cleanly; we still follow expand/contract.
- Migrations must avoid MySQL-only syntax; code reviews and the Postgres CI job enforce it.
- Managed Postgres with PITR is the production target (RPO 15 min).
