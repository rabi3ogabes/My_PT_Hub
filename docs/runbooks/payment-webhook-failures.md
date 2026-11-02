# Runbook — Payment webhook failures

**Signals:** orders stuck in `pending` > 10 min after the gateway shows paid; spike of
`webhook_events.status = failed`; coach or client reports "paid but no access"; gateway dashboard
shows failing deliveries (4xx/5xx/timeouts); Sentry errors in webhook controllers.

Money rule: **never mark an order paid without confirming with the gateway API.** Fulfilment is
idempotent; replaying is always safe, double charging is not.

## 1. Scope
```sql
-- Stuck orders in the last 24 h, by gateway
SELECT pg.driver, count(*) FROM orders o JOIN payment_gateways pg ON pg.id = o.payment_gateway_id
WHERE o.status = 'pending' AND o.created_at > now() - interval '24 hours' AND o.created_at < now() - interval '10 minutes'
GROUP BY pg.driver;

-- Recent webhook processing failures
SELECT driver, type, status, error, created_at FROM webhook_events
WHERE status <> 'processed' AND created_at > now() - interval '6 hours' ORDER BY created_at DESC LIMIT 50;
```
SaaS billing (coach → Aurum) uses the same `webhook_events` table with `driver = 'stripe'` and `tenant_id` set on platform events.

## 2. Classify
| Symptom | Likely cause | Action |
|---|---|---|
| No webhook rows at all for a driver | Gateway can't reach us (DNS, WAF rule, URL changed) or gateway outage | Check gateway dashboard delivery log for `https://app.aurum.coach/webhooks/payments/{driver}/{gateway}` (SaaS: `/webhooks/stripe/saas`); Cloudflare firewall events; gateway status page |
| Rows with `error = invalid signature` | Secret rotated / wrong environment (test vs live) | Compare the endpoint secret in the gateway dashboard with `payment_gateways.credentials` (coach gateway) or `STRIPE_WEBHOOK_SECRET` (SaaS); update via Settings → Payments; replay |
| Rows `failed` with exception | Bug in parser/fulfilment | Sentry trace; hotfix; replay after deploy |
| Rows `processed` but order pending | Event type not mapped (e.g. new gateway event name) | Map the type in the driver; replay |
| Order paid but not fulfilled | `FulfilOrder` job failed or queue backlog | `php artisan horizon:status`; failed jobs on `payments` queue; see [queue-backlog.md](queue-backlog.md) |

## 3. Replay
- **Preferred:** resend from the gateway dashboard (Stripe "Resend", Tap/PayTabs retry) — signature intact.
- **From our store:** Filament (`/admin`) → Webhook events → view → *Replay* (`ReplayWebhookAction` re-runs processing on the stored payload; dedupe on `(driver, external_id)` and the order row lock prevent double fulfilment).
- **Manual reconciliation** for orders without any event (last resort, two people):
  1. Look up the order's `gateway_reference` in the gateway dashboard/API and confirm the payment is **captured/succeeded** for the right amount and currency.
  2. Apply it through the same service the webhooks use — idempotent, locks the order, triggers fulfilment once:
     ```bash
     php artisan tinker
     >>> $order = \App\Domain\Payments\Models\Order::withoutGlobalScopes()->findOrFail('<order ulid>');
     >>> app(\App\Domain\Payments\Services\PaymentService::class)->markPaid($order, ['driver' => '<driver>', 'gateway_payment_id' => '<gateway payment id>', 'reference' => 'manual-reconcile <ticket>']);
     ```
  3. Note the ticket number in the order's activity log entry.

## 4. Gateway outage
1. Ask affected coaches (or, with consent, do it for them in Filament) to set the gateway inactive (Settings → Payments → gateway → inactive, `payment_gateways.is_active = false`); checkout then uses their other gateway for that currency, or they share a manual payment link.
2. Notify affected coaches in-app (owner role) with the expected impact; clients see "Payment temporarily unavailable — try another method".
3. After recovery, reconcile all pending orders for that driver created during the window.

## 5. Client & coach communication
- Client: "Your payment was received. Your program will appear within a few minutes." Never ask clients to pay again until reconciliation confirms the first payment failed.
- Coach: list of affected orders with status after reconciliation (Payments → Orders filter `pending`).

## 6. Verify & close
- Pending orders older than 10 min back to zero; `webhook_events` failures cleared or explained.
- Spot-check 3 fulfilled orders: program assigned, credits granted, invoice issued, welcome message sent.
- Post-mortem if > 20 orders or > 1 hour affected.
