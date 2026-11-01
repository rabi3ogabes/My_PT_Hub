# Launch Plan — T-6 weeks → T+30 days

Launch markets: **Qatar first, then KSA → UAE → France → Morocco** (French beta runs in
parallel with Qatar). "T" = public launch day (both apps live in all four locales, web pricing
public, paid plans on). Owners: **PM** product, **ENG** engineering lead, **MOB** mobile lead,
**OPS** DevOps/SRE, **MKT** marketing/ASO, **CS** customer success, **LEG** legal/privacy.

## Gates (must be green to move to the next stage)

| Gate | Criteria | Evidence |
|---|---|---|
| G1 Beta ready (T-6w) | Phases 1–5 acceptance checklists pass on staging; crash-free ≥ 99.5 % internal; restore drill done | `docs/PHASES.md`, Sentry, drill log |
| G2 Store-ready (T-4w) | Listings validated (`node store/validate.mjs`), privacy labels/data safety entered, screenshots generated in 4 locales, review accounts seeded | Store consoles, `store/` |
| G3 Launch-ready (T-1w) | Pentest critical/high fixed; k6 load 10k VUs p95 < 200 ms; SSL Labs A+; on-call rota; runbooks rehearsed; DPA/terms/privacy live in 4 languages | Pentest report, k6 summary, runbook drill notes |
| G4 Full rollout (T+7d) | Crash-free sessions > 99.8 % for 72 h on the staged release; no P1 open; payment success > 97 % | Sentry/Crashlytics, payments dashboard |

## T-6 weeks — Closed beta

- [ ] **PM** Recruit 20 beta coaches: 12 Qatar (Arabic + English, mix of PT, online, nutritionist, 2 studios) and 8 France (online coaches, 1 studio). Each brings ≥ 5 real clients.
- [ ] **ENG** Staging → "beta" production tenant pool; feature flags: `ai_copilot` on for beta tenants (`feature_flags.tenants` allow-list), `marketplace` off.
- [ ] **MOB** TestFlight external group + Play closed testing track; in-app feedback widget routes to support.
- [ ] **CS** Weekly 30-minute feedback calls (Tue Doha / Thu Paris); shared issue board triaged daily; top-10 issues fixed per week.
- [ ] **OPS** Uptime monitors, Sentry alerts, Horizon backlog alerts, status page; first quarterly restore drill.
- [ ] **MKT** Keyword research per locale finalised ([seo/keyword-map.md](seo/keyword-map.md)); baseline rankings captured for 10 core ASO keywords per locale.
- [ ] **LEG** DPIA for health + AI processing signed off; subprocessors list published.

## T-4 weeks — Pre-registration & demand

- [ ] **MKT** Google Play **pre-registration** live for both apps (pre-registration reward: 1 month Pro for coaches); App Store **pre-order** for the client app (90-day max) — coach app goes live on launch day.
- [ ] **MKT** Store listings live in en/ar/fr/es from `store/{client,coach}/{locale}`; Custom Product Pages created (`store/custom_product_pages.md`); Play custom store listings per country.
- [ ] **MKT** Landing pages + waitlist in 4 languages; pricing page with "cancel in 2 clicks" promise; compare pages fact-checked by LEG.
- [ ] **MKT** Creator partnerships signed: 6 Arabic-speaking fitness coaches (Qatar/KSA/UAE) + 4 French coaches; disclosure rules (#ad / إعلان) in contracts.
- [ ] **ENG** Lifecycle emails (days 0/1/3/7/14, trial ending, win-back) in 4 languages, tested in Mailpit and staging.
- [ ] **MOB** Screenshot pipeline (`integration_test` captures → template compositor) produces all sizes in 4 locales; RTL review by native Arabic designer.
- [ ] **OPS** k6 load test at 2k VUs on staging; fix top 5 slow queries (`pg_stat_statements`).

## T-2 weeks — Launch kit

- [ ] **ENG** Migration service ready: CSV import for My PT Hub / Trainerize / Everfit exports (clients, programs, measurements) + done-for-you migration form (`/switch`).
- [ ] **MKT** Press kit (logos, screenshots, founder bios, fact sheet in 4 languages), Product Hunt page scheduled, launch video (`store/*/promo_video_storyboard.md`).
- [ ] **MKT** Apple featuring nomination (App Store Connect → Featuring Nominations) and Google Play "Suggest your app for featuring" submitted with the Arabic-first story and Ramadan/Gulf food angle.
- [ ] **ENG/OPS** External pentest complete; critical/high fixed and retested (gate G3).
- [ ] **OPS** k6 load at 10k concurrent clients on production-sized infra — p95 < 200 ms, error rate < 0.5 %.
- [ ] **MOB** Release candidates submitted for review (client + coach) with review notes and demo accounts (`store/categories_and_ratings.md`); manual release.
- [ ] **CS** Help centre: 40 articles × 4 languages; macros for top support questions; support hours Doha + Paris.

## T-1 week — Freeze

- [ ] **ENG** Code freeze except P0/P1 fixes; production deploy of the launch tag (`v1.0.0`) via blue/green; rollback rehearsed (`infra/deploy/rollback.sh`).
- [ ] **OPS** On-call rota (primary/secondary, Doha + Paris time zones), escalation tree, runbooks rehearsed (payment webhook failure, push outage, store rejection).
- [ ] **MKT** Apple Search Ads campaigns built (brand + 10 core keywords per locale, CPP per audience); Google App Campaigns (installs + in-app action "first workout logged" / "first client invited").
- [ ] **PM** KPI dashboard live: activation, client WAR, trial → paid, crash-free, store rating, review response time.

## Launch week (T → T+6d)

| Day | Actions |
|---|---|
| **T (Tue)** | Release apps (iOS phased release 1 % → automatic 7-day ramp; Play staged rollout **10 %**); flip `marketplace` flag for Qatar; Product Hunt launch 00:01 PT; creator posts go live 18:00 Doha; launch email to waitlist; community launch challenge ("Launch 30" — 30 workouts in 30 days) opens. |
| T+1 | Daily crash triage 09:00 Doha (Sentry + Crashlytics); daily review replies (target < 24 h, AI-drafted in reviewer's language, human-approved); ASA bid adjustments. |
| T+2 | **Crash gate** for Play 50 %: run `Mobile release → rollout percent 0.5` (crash-free ≥ 99.8 % via `infra/fastlane/scripts/crash_gate.sh`). |
| T+3 | Coach webinar (Arabic) "Your first program in 5 minutes"; fix-forward release if needed (benefit-led What's New). |
| T+4 | French webinar; KSA soft-open (Arabic listing + mada via Tap/HyperPay verified). |
| T+5 | **Crash gate** for Play 100 % (`percent 1.0`); iOS phased release continues. |
| T+6 | Launch retro; publish changelog; thank-you post to beta coaches. |

Daily during launch week: review replies, crash triage, payment success rate check, queue
backlog check, social listening in 4 languages, support SLA (first response < 2 h in business hours).

## T+30 days — Learn & iterate

- [ ] **MKT** Keyword rankings vs baseline per locale (AppTweak/AppFollow or the manual sheet); conversion rate per CPP; first screenshot A/B (Play experiments; App Store product page optimization) on frames 1–3.
- [ ] **MKT** Icon A/B test (gold mark vs monogram) on Play; ship winner to iOS.
- [ ] **CS** First 3 case studies (Qatar PT, French online coach, Moroccan studio) with real numbers and written consent.
- [ ] **PM** Referral push: "Give a month, get a month" for coaches; client referral codes on coach products.
- [ ] **PM** KPI review against targets ([PRODUCT_SPEC §7](PRODUCT_SPEC.md#7-kpis)); decide UAE timing and Morocco local gateway launch (Paymob/CMI evaluation).
- [ ] **ENG** Post-launch hardening backlog from SECURITY.md ⬜ items prioritised; performance review (top endpoints p95, Octane readiness).
- [ ] **MOB** `in_app_review` prompts tuned (only after 3rd workout, PR, or positive check-in reply; max 3/year; never after errors) based on rating trend.

## Communication plan

| Audience | Channel | Owner | When |
|---|---|---|---|
| Beta coaches | WhatsApp group + email | CS | weekly + launch day |
| Waitlist | Email (4 languages) | MKT | T-4w, T-1w, T |
| Press / creators | Press kit + embargo email | MKT | T-2w, T |
| Users during incidents | Status page + in-app banner | OPS | as needed |
| Internal | #launch channel, daily stand-up 09:30 Doha | PM | T-1w → T+7d |
