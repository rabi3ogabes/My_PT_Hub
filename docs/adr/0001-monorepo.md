# 0001 — Monorepo

- Status: Accepted · Date: 2026-01-05 · Deciders: architecture, mobile, backend leads

## Context
Aurum ships a Laravel backend, two Flutter apps, a shared Dart package, design tokens consumed
by both Tailwind and Flutter, white-label tooling, store metadata and docs. The API contract
(`docs/API_CONTRACT.md`) must change atomically with the backend resources and the Dart models.
The team is small and many contributors (human and AI agents) work concurrently.

## Decision
One Git repository with top-level `backend/`, `apps/client`, `apps/coach`, `packages/core`,
`packages/design_tokens`, `infra/`, `store/`, `docs/`. Path-filtered CI workflows
(`backend.yml`, `mobile.yml`, `web-quality.yml`) keep feedback fast. Generated artefacts
(tokens, OpenAPI, ERD) are produced by scripts in the repo and checked in CI.

## Alternatives considered
- **Polyrepo** (backend / apps / packages): cleaner permissions, but contract changes need
  coordinated PRs and version pinning of `core`; drift between API and models is the #1 source of
  mobile bugs in comparable products.
- **Monorepo with Melos/Nx orchestration**: useful later; today a Makefile and path filters suffice.

## Consequences
- A single PR can change a migration, the resource, the OpenAPI file, the Dart model and the screen.
- CI must stay path-aware to avoid running Flutter jobs for backend-only changes (done).
- Repository size grows with assets; large media stays out of Git (store screenshots are generated).
- Ownership is expressed by directory (CODEOWNERS) rather than repository.
