# Runbook — Queue backlog

**Signals:** Horizon "long wait" alerts (payments/notifications wait > 60 s; media > 5 min; ai > 2 min),
failed jobs > 10 in 10 min, coaches report delayed messages/AI drafts/videos, Redis memory rising.

Target queue layout: `payments` (fulfilment, webhooks), `notifications` (push/email/WhatsApp),
`sync`, `default`, `ai`, `media` (FFmpeg), `integrations` (wearable pulls, calendar sync).
Priority order when scaling: payments → notifications → sync → default → ai → integrations → media.
Today `config/horizon.php` defines a single `supervisor-1` on `default` (10 processes in
production, auto-balanced); split supervisors per queue as modules move jobs onto dedicated
queues, and keep this list in sync.

## 1. Look
```bash
docker exec aurum-<colour>-horizon-1 php artisan horizon:status
docker exec aurum-<colour>-horizon-1 php artisan queue:monitor redis:payments,redis:notifications,redis:sync,redis:default,redis:ai,redis:media,redis:integrations --max=500
docker exec aurum-<colour>-horizon-1 php artisan queue:failed | head -40
```
Horizon dashboard: `https://app.aurum.coach/horizon` (super-admin) → Metrics (throughput, runtime), Failed, Pending.

## 2. Diagnose
| Pattern | Likely cause | Action |
|---|---|---|
| All queues growing, workers idle | Horizon stopped / paused / supervisor crashed after deploy | `php artisan horizon:continue`; restart horizon container; confirm only one colour runs workers |
| One queue growing, runtime ↑ | Slow dependency (AI provider latency, gateway API, FFmpeg CPU) | Check provider status; raise `maxProcesses` for that supervisor or throttle producers |
| Failed jobs spike with same exception | Bug or third-party auth failure | Fix/rotate credentials; then retry selectively (below) |
| `media` saturating CPU and slowing others | Large upload burst | Move media to dedicated worker host; reduce media concurrency; managed `VideoDriver` for the tenant |
| Redis memory near max | Backlog + large payloads | Increase memory (never set eviction on the queue Redis); purge only clearly obsolete jobs |
| Jobs retrying forever | Missing `tries`/`backoff` | Ensure job defines `tries`, `backoff`, `retryUntil`; release fix |

## 3. Act
- Scale workers temporarily: raise `maxProcesses` for the supervisor in `config/horizon.php` (ship as a hotfix) and restart horizon, or start an extra worker container now:
  ```bash
  docker compose -p aurum-<colour> -f /opt/aurum/deploy/compose.prod.yml --profile workers run -d --name extra-worker horizon php artisan queue:work redis --queue=notifications,default --max-time=3600
  ```
- Pause a non-critical producer with a kill switch (`ai_copilot`, `integrations`, `automations` — see [outage.md §4](outage.md#4-kill-switches)) to protect payments and notifications.
- Retry failed jobs after the fix, queue by queue:
  ```bash
  php artisan queue:retry --queue=payments all
  php artisan queue:retry <uuid> <uuid>
  ```
  Payment fulfilment and notifications are idempotent (dedupe keys); AI jobs re-run only if their `ai_outputs` row is still `queued`.
- Never `queue:flush` the payments or sync queues.

## 4. Close
- Waits back under thresholds for 30 min; failed jobs triaged (retried or forgotten with a reason).
- Capture throughput numbers for capacity planning; add a scaling rule if the burst is recurring (e.g. Monday 06:00 check-in reminders).
