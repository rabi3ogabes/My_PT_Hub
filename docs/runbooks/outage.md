# Runbook — Outage / degraded service

**Signals:** uptime monitor on `https://aurum.coach/up` or `https://api.aurum.coach/up` failing,
5xx rate > 2 % for 5 min (Sentry/Cloudflare), p95 > 1 s, Reverb connection drops, user reports.

## 1. Triage (first 5 minutes)
1. Acknowledge the page; declare severity in #incidents; IC = on-call primary unless handed over.
2. Post status page "Investigating" (SEV1/2); for long incidents send an in-app notification to coach owners (super-admin broadcast).
3. Establish scope:
   ```bash
   curl -sS -o /dev/null -w '%{http_code} %{time_total}\n' https://api.aurum.coach/up
   ssh deploy@<host> 'cat /opt/aurum/shared/active_colour /opt/aurum/shared/active_tag; tail -5 /opt/aurum/shared/deploy_history'
   ssh deploy@<host> 'docker ps --format "table {{.Names}}\t{{.Status}}" | grep aurum'
   ```
4. Was there a deploy in the last 2 hours? If yes and symptoms started after it → **roll back first, debug later** (§3).

## 2. Diagnose by layer
| Layer | Check | Typical fix |
|---|---|---|
| Edge (Cloudflare/Caddy) | Cloudflare status; `docker compose -f /opt/aurum/deploy/edge/compose.edge.yml logs --tail=200 caddy` | Reload Caddy; certificate renewal issue → check DNS-01 credentials |
| nginx | `docker logs aurum-<colour>-web-1 --tail=200` (502 = PHP-FPM unreachable) | Restart `app`; check `PHP_UPSTREAM` |
| PHP-FPM | `docker exec aurum-<colour>-app-1 php artisan about`; FPM status `/fpm-status` via cgi-fcgi | Scale `pm.max_children`; restart; look for fatal errors in Sentry |
| Database | managed Postgres dashboard: connections, CPU, locks; `SELECT * FROM pg_stat_activity WHERE state <> 'idle' ORDER BY query_start;` | Kill runaway queries (`pg_terminate_backend`); failover to replica if primary unhealthy |
| Redis | `redis-cli -h <host> info memory`, `info clients` | Evictions (must be `noeviction` for queues) → raise memory; restart clients |
| Queues | `php artisan horizon:status`; see [queue-backlog.md](queue-backlog.md) | |
| Reverb | `docker logs aurum-<colour>-reverb-1`; connection count | Restart reverb; check `REVERB_SCALING_ENABLED` + Redis |
| Third party | Status pages: Stripe/Tap/FCM/Anthropic | Degrade gracefully (kill switch §4), see the specific runbook |

## 3. Rollback
Previous colour's containers are stopped, not removed — rollback is a proxy switch:
```bash
ssh deploy@<host> 'REGISTRY=ghcr.io/<org> bash /opt/aurum/deploy/rollback.sh'
```
Or redeploy a known-good tag: GitHub → Actions → **Deploy** → Run workflow → environment, `ref = v1.3.2`.

### Migrations
Releases follow **expand → deploy → contract**: a release only *adds* columns/tables or makes
backward-compatible changes; destructive changes ship one release later. Therefore rolling back
code does **not** require rolling back the schema. Never run `migrate:rollback` in production
during an incident. If a migration itself is broken (failed mid-way), Postgres transactional DDL
has already rolled it back — fix forward with a new migration.

## 4. Kill switches
A disabled feature flag is a global kill switch (`PlanGate::feature()` checks flags before plan
features; cached 60 s). Filament → Feature flags → set `enabled = false` for the plan feature key:
`ai_copilot`, `automations`, `community`, `integrations`, `api`, `zapier`, `payments`, `calendar`, `messaging`.
Maintenance mode for web only (API stays up for apps' offline sync):
`php artisan down --render="errors::503" --retry=60 --secret=<token>`.

## 5. Recovery & follow-up
1. Confirm `/up`, login, `GET /me/today`, checkout and a WebSocket message on production.
2. Status page "Monitoring" → "Resolved" after 30 min stable.
3. Check for data impact: failed jobs to retry (`php artisan queue:retry --queue=payments all` after review), webhook events in `failed` status, sync rejections spike.
4. Post-mortem: timeline, root cause, detection gap, action items with owners (template in the incident doc).
