# Architecture Decision Records

Format: Context → Decision → Alternatives considered → Consequences. Status values: Proposed,
Accepted, Superseded by NNNN. Add a new file `NNNN-kebab-title.md`; never rewrite an accepted
ADR — supersede it.

| # | Decision | Status |
|---|---|---|
| [0001](0001-monorepo.md) | Single monorepo for backend, apps, shared packages, infra and docs | Accepted |
| [0002](0002-postgresql-over-mysql.md) | PostgreSQL 16 as the primary database | Accepted |
| [0003](0003-livewire-over-inertia.md) | Blade + Livewire for the web UI instead of Inertia/SPA | Accepted |
| [0004](0004-riverpod-state-management.md) | Riverpod (codegen) for Flutter state | Accepted |
| [0005](0005-drift-offline-sync.md) | Drift + mutation queue for offline-first sync | Accepted |
| [0006](0006-ai-driver-layer.md) | Provider-agnostic AI driver layer with coach approval gate | Accepted |
| [0007](0007-payment-driver-layer.md) | Payment gateway driver layer; coach is merchant of record | Accepted |
| [0008](0008-video-pipeline.md) | Self-hosted FFmpeg → HLS pipeline behind a driver | Accepted |
| [0009](0009-ulid-primary-keys.md) | ULID primary keys on every domain table | Accepted |
| [0010](0010-ios-template-apps-container-model.md) | Container app by default; separate white-label binaries only where policy allows (Apple 4.2.6) | Accepted |
| [0011](0011-in-app-purchase-digital-content.md) | Per-product iOS purchase mode for digital content vs real-world services | Accepted |
