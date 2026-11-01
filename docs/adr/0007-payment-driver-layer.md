# 0007 — Payment gateway driver layer; coaches are merchant of record

- Status: Accepted · Date: 2026-01-08

## Context
Competitors are Stripe-only; our launch markets need Tap (Qatar/KSA/UAE/Kuwait: cards, mada,
KNET, Apple Pay), HyperPay/MyFatoorah/PayTabs (Gulf), Paymob (Egypt/Gulf), Flutterwave (Africa),
PayPal, plus cash/bank transfer. Money must be correct (minor units), webhooks idempotent,
invoices compliant (ZATCA fields in KSA, TVA in France).

## Decision
- **Two layers.** SaaS billing (coach → Aurum) via `SaasGateway` (Stripe + Local); client
  payments (client → coach) via `PaymentGateway` drivers listed above + Manual.
- **Coach is merchant of record** through their own gateway account (Stripe Connect Standard /
  OAuth where available; encrypted credentials otherwise). Aurum takes an optional platform fee
  per plan (default 0 %).
- Interface: `createCustomer`, `createCheckout`, `charge`, `createSubscription`,
  `cancelSubscription`, `refund`, `parseWebhook`, `supportedCurrencies`, `supportedMethods`.
- Webhooks verified per driver and deduplicated in `webhook_events (driver, external_id)`;
  replay from admin. Payment POSTs require `Idempotency-Key`.
- One fulfilment pipeline (`FulfilOrder`) for every source, including manual payments.

## Alternatives considered
- **Aurum as merchant of record (marketplace payouts)**: simpler UX, but licensing (payment
  facilitation in GCC), tax liability and payout complexity — rejected for v1.
- **Stripe-only**: excludes mada/KNET and many local cards; fails our markets.
- **Aggregator (e.g. single PSP covering MENA)**: fewer drivers, but coaches often already have
  a specific local acquirer.

## Consequences
- Each driver needs fixtures and contract tests (mocked HTTP + webhook samples).
- Gateway outages affect only tenants using that gateway; runbook covers failover to payment links.
- Coaches handle their own chargebacks in their gateway dashboards; Aurum mirrors status.
