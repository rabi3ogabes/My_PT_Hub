# Phase 5 — Client Payments, Packages, Business Analytics & CRM

> Paste `00-PROJECT-CONTEXT.md` first, then this file. Build on Phases 1–4.
> Note: Phase 1 handled the **coach paying the platform** (Cashier). This phase handles **clients paying the coach**.

## Goal
Let coaches sell anything (coaching packages, memberships, session credits, programs, products) through any regional payment gateway, with automatic content delivery, honest billing, clean invoicing and a business dashboard that shows MRR, churn and LTV.

## Competitor complaints this phase must solve
- Stripe-only; nothing for MENA/Africa local cards, wallets or bank transfer.
- Hard to manage packages paid outside the app (cash, bank transfer).
- Broken checkout links; fees in a foreign currency.

## Backend

### 1. Payment gateway driver layer
- Interface `PaymentGateway` with methods: `createCustomer`, `createCheckout`, `charge`, `createSubscription`, `cancelSubscription`, `refund`, `parseWebhook`, `supportedCurrencies`, `supportedMethods`.
- Drivers: **Stripe Connect** (Standard accounts, coach is merchant of record), **PayPal**, **Tap Payments** (Qatar/KSA/UAE/Kuwait — cards, mada, KNET, Apple Pay), **PayTabs**, **HyperPay** (mada), **MyFatoorah**, **Paymob** (Egypt/Pakistan/Gulf), **Flutterwave** (Africa, mobile money), **Manual** (cash, bank transfer, other).
- Coach connects one or more gateways in Settings (OAuth where available; encrypted credentials otherwise); choose default per currency.
- Webhooks: signature verification per driver, idempotent processing via `webhook_events` table, replay tool in admin.
- Platform fee (optional, per plan) configurable — default 0%.

### 2. Catalogue
- `products` types: coaching package (assigns program/meal plan/habits/check-in schedule + adds to group + starts conversation), membership (recurring), session credit pack, single session, digital program (self-guided), physical product (manual fulfilment), event ticket.
- `prices` (one-time / recurring interval; currency; tax inclusive flag; trial days; setup fee; installment plans e.g. 3 × monthly).
- `coupons` (percent/fixed, duration, max redemptions, expiry, product scope), referral codes per client.
- Public checkout page per product (branded, localized, RTL), embeddable widget/script, payment links shareable on WhatsApp/Instagram, QR code.

### 3. Orders, subscriptions, invoices
- `orders`, `order_items`, `payments`, `refunds`, `client_subscriptions` (status machine: trialing, active, past_due, paused, cancelled, expired), pause/resume, change plan with proration.
- **Fulfilment pipeline:** payment succeeded → event → `FulfilOrder` job → assign content, grant credits, add to group/community, send welcome sequence, notify coach. Expiry → revoke access (configurable grace period).
- Dunning: retries per gateway, reminder messages via automations (Phase 4), auto-pause after final failure.
- **Manual payments:** coach records cash/transfer with receipt photo; same fulfilment pipeline; reminders for due manual invoices.
- Invoices: sequential numbering per tenant, VAT/tax (Qatar none, KSA 15% ZATCA-ready fields, UAE 5%, France 20% TVA), coach's legal details, multilingual PDF (Arabic RTL layout), credit notes for refunds.
- Client self-service: payment methods, invoices, cancel subscription (two taps), pause request.

### 4. Business analytics
- Materialized daily snapshots table (`analytics_daily`) computed by scheduled job.
- Metrics: revenue (gross/net, by product, by gateway), MRR, ARR, new/expansion/churned MRR, active clients, client churn rate, average LTV, ARPU, refunds, outstanding manual invoices, credit liability (unused sessions), conversion from lead → client, retention cohorts by start month.
- Coach performance (for Studio): revenue and retention per coach, sessions delivered, commission calculation (`commission_rules`: % of package or per session) and payout report export (CSV).
- Exports: CSV/XLSX for accounting.

### 5. CRM & leads
- `leads` (source: profile page, form, referral, Instagram, WhatsApp, manual), `lead_stages` (customizable pipeline: New → Contacted → Consultation booked → Proposal → Won/Lost), tasks & follow-ups, notes, tags.
- Lead capture forms (embeddable, with consent checkbox) and consultation booking linked to Phase 4 calendar.
- Convert lead → client in one click (sends invite + optional checkout link).
- **Contracts & waivers:** template editor with variables, PAR-Q health questionnaire, e-signature (draw/type), signed PDF stored with audit trail (IP, timestamp, hash).

## Coach web
- Payments settings (connect gateways, currencies, tax settings, invoice branding).
- Products & pricing manager with live checkout preview; coupons; payment links generator with QR.
- Orders/subscriptions table with filters and actions (refund, pause, cancel, change plan, record manual payment).
- **Business dashboard:** KPI tiles (MRR, active clients, churn, revenue this month vs last), MRR movement waterfall, revenue by product, cohort retention heatmap, upcoming renewals, failed payments list.
- CRM kanban board, lead detail, contract templates, signed documents list.

## Apps
- Client app: shop (coach's products), checkout via gateway SDK/webview with Apple Pay / Google Pay where supported, my purchases, credits balance, invoices, manage subscription.
- Coach app: revenue snapshot widget, record manual payment with receipt photo, send payment link, lead pipeline (swipe between stages).
- **App Store compliance:** physical/real-world coaching services and 1:1 sessions may use external payment; digital-only content sold inside the iOS app must follow Apple guidelines — implement a config switch per product (`sell_in_app_ios`: hidden / external link (where allowed) / IAP) and document the decision.

## Tests
- Each gateway driver with mocked HTTP + webhook fixtures; idempotent webhook processing; fulfilment job end-to-end; proration math; tax calculations per country; invoice numbering under concurrency; credit ledger integrity; analytics snapshot correctness against fixtures.

## Acceptance checklist
- [ ] Coach in Qatar sells a 12-week package via Tap with Apple Pay; content auto-assigned within 10 seconds.
- [ ] Coach in Morocco/Egypt can use a local gateway; coach in France uses Stripe with TVA invoices.
- [ ] Cash payment recorded manually triggers the same fulfilment.
- [ ] Client cancels subscription in two taps; access ends at period end.
- [ ] Dashboard MRR equals the sum of active subscriptions (verified by test).
