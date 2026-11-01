# 0011 — Per-product iOS purchase mode for digital content vs real-world services

- Status: Accepted · Date: 2026-01-10

## Context
Clients buy from their coach inside the client app. App Store guidelines: **3.1.1** requires
In-App Purchase for digital content and services consumed in the app; **3.1.3(d)/(e)** allow
other payment methods for person-to-person services and goods/services consumed outside the app
(e.g. in-person training, 1:1 real-time coaching); **3.1.3(a)** "reader" rules don't apply to
us; the US external-link entitlement and EU DMA alternatives vary by storefront. Google Play
similarly requires Play Billing for digital goods with exceptions for physical/real-world services.

## Decision
- Each product has `sell_in_app_ios` = **hidden | external | iap** (default by product type):

| Product type | Default iOS mode | Rationale |
|---|---|---|
| 1:1 sessions, session credit packs, in-person memberships, event tickets | `external` | real-world / person-to-person services (3.1.3(d)/(e)) |
| Coaching package with live 1:1 coaching component | `external` | person-to-person service; content delivered as part of the service |
| Digital-only program, self-guided plan, recipe/e-book bundle | `hidden` (or `iap` when the coach enables IAP products) | digital content consumed in app (3.1.1) |
| Physical product | `external` | physical goods |

- `hidden` removes the product (and any purchase CTA or link) from the iOS app; it remains on the
  web profile and Android. No "buy on the web" nudges on iOS unless the storefront entitlement allows it.
- `iap`: StoreKit 2 products mapped to coach products; server validates transactions; Aurum
  remits coach share minus Apple's commission (reported in analytics).
- Android: real-world services use the coach's gateway; digital-only items follow the same
  `hidden` default unless Play Billing is configured.
- SaaS billing for coaches (Pro/Studio) is sold on the web; the coach app shows plan status and a
  manage link only where storefront rules allow (multiplatform service 3.1.3(b)); otherwise it
  shows status without purchase CTAs.

## Alternatives considered
- **IAP for everything**: 15–30 % fee on real-world services where not required; coaches refuse.
- **External for everything**: rejection risk for digital programs.

## Consequences
- `GET /me/shop` filters by platform and mode; review notes explain the split.
- Product editor shows the iOS mode with an explanation; defaults are safe.
- Policy changes per storefront are handled by configuration, not releases.
