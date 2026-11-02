# Runbook — Backup restore drill (quarterly) and real restore

Objectives: **RPO 15 min** (Postgres PITR via WAL archiving), **RTO 1 h** for the database,
4 h for full media. Drill every quarter and before launch (LAUNCH.md G1). Record results in the
drill log at the end of this file.

## What is backed up
| Asset | Method | Retention | Location |
|---|---|---|---|
| PostgreSQL | Managed PITR (continuous WAL) + nightly `pg_dump -Fc` (encrypted, `age`) | PITR 7 days; dumps 35 days | Provider + `aurum-backups/postgres/YYYY/MM/DD/` (separate account/region) |
| Object storage | Bucket versioning + daily replication | 35 days of versions | Secondary region bucket |
| Redis | Not backed up (cache/queues); queues are rebuilt from DB state (`webhook_events`, `checkin_responses`, …) | — | — |
| Secrets | Secret manager with version history | per provider | — |
| Config / infra | Git | ∞ | GitHub |

## Drill procedure (staging-isolated, ~90 minutes)
1. **Pick a target time** within the last 24 h (e.g. 25 minutes ago) and note it.
2. **Restore PITR** to a new instance `aurum-restore-<date>` via the provider console/CLI. Start the timer.
3. **Restore last nightly dump** too (verifies the independent path):
   ```bash
   aws s3 cp s3://aurum-backups/postgres/<yyyy>/<mm>/<dd>/aurum.dump.age - --endpoint-url <s3> | age -d -i ~/.age/restore.key > /tmp/aurum.dump
   createdb -h <restore-host> -U aurum aurum_restore
   pg_restore -h <restore-host> -U aurum -d aurum_restore --no-owner --jobs 4 /tmp/aurum.dump
   ```
4. **Verify integrity** on both restores:
   ```sql
   SELECT count(*) FROM tenants; SELECT count(*) FROM users; SELECT max(created_at) FROM session_sets;
   SELECT max(created_at) FROM orders;  -- should be close to the target time for PITR
   -- credit ledger invariant per client
   SELECT client_id FROM credit_ledger GROUP BY client_id HAVING sum(amount) < -1000;  -- expect none
   -- encrypted columns decrypt with the production APP_KEY (run in an isolated app container):
   ```
   ```bash
   docker run --rm --env-file restore.env ghcr.io/<org>/aurum-app:<active tag> php artisan tinker --execute="echo \App\Domain\Messaging\Models\Message::query()->latest()->first()?->body !== null ? 'decrypt ok' : 'no messages';"
   ```
5. **Boot the app** against the restored DB in an isolated network (`APP_ENV=staging`, mail to Mailpit, queues disabled): log in as a demo coach, open a client, view a progress photo (from the replicated bucket), open an invoice PDF.
6. **Media spot-check:** restore 10 random object versions from the secondary bucket; verify checksums.
7. **Stop the timer**; destroy the restore instance and temp files (`shred -u /tmp/aurum.dump`).
8. **Log results** below; open tickets for anything slower than RTO or any failed check.

## Real restore (production incident)
1. Declare SEV1 ([outage.md](outage.md)); put web in maintenance; stop workers (`horizon:terminate`) to prevent writes to the broken DB.
2. Decide the recovery point with the IC (just before the corrupting event; use audit/activity logs).
3. PITR to a new instance; verify (step 4 above); switch `DB_HOST` in `/opt/aurum/shared/.env`; redeploy current tag (deploy.sh re-runs `migrate`, no-op if schema matches).
4. Replay external events received after the recovery point: payment webhooks (gateway dashboards → resend), health webhooks (providers retry automatically), mobile clients re-push unsynced mutations automatically (idempotent).
5. Communicate data window lost (if any) to affected tenants; GDPR/PDPPL/PDPL breach assessment if integrity/availability of personal data was affected.

## Drill log
| Date | Operator | PITR RTO | Dump restore time | Checks | Issues / tickets |
|---|---|---|---|---|---|
| _first drill scheduled at T-6w (LAUNCH.md G1)_ | | | | | |
