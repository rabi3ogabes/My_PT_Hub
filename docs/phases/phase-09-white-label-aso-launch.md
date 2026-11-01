# Phase 9 — White Label, Store Listings & ASO, CI/CD, Hardening & Launch

> Paste `00-PROJECT-CONTEXT.md` first, then this file. Build on Phases 1–8.

## Goal
Ship to production and the stores with everything needed to climb the rankings: automated builds, white-label apps for Studio coaches, fully optimized and localized store listings, security/performance hardening, monitoring, and a launch plan.

## 1. White-label app pipeline
- Flutter flavors generated from a `whitelabel.json` per tenant: app name, bundle/package id, icon set, splash, colors, fonts, default locale, feature toggles, deep-link domain.
- Generator script (`infra/whitelabel/generate.dart`): creates flavor config, icons (flutter_launcher_icons), splash (flutter_native_splash), Firebase config per app, store metadata skeleton.
- Fastlane lanes: `build_whitelabel tenant:<slug>` → iOS (match signing) + Android (upload key) → TestFlight / Play internal track.
- Two publishing modes:
  1. **Under our developer account** (fast, one-click; app name "Coach Name — by Aurum") — included in Studio.
  2. **Under coach's own developer account** (full white label) — guided checklist (Apple account, D-U-N-S, privacy policy URL auto-hosted on their profile domain), we handle the build.
- Over-the-air content/config updates via Remote Config; app binaries updated by pipeline whenever core releases.
- Admin dashboard: white-label apps list, build status, store review status, version per tenant.
- Comply with Apple guideline 4.2.6 (template apps): prefer the container model (one app, coach branding after login via invite code) as default; separate binaries only where policy allows. Document the decision in an ADR.

## 2. Store listings & ASO (Coach app + Client app, 4 locales each)
Generate `/store/{app}/{locale}/` with:
- **Title (30 chars)** — brand + primary keyword. Examples to refine with keyword research:
  - Client app EN: "Aurum: Workout & Meal Planner"
  - Coach app EN: "Aurum Coach: PT Client Manager"
  - AR: "Aurum: مدرب شخصي وبرنامج تمارين"
  - FR: "Aurum : Coach sportif & Muscu"
- **iOS subtitle (30)** and **Play short description (80)** with secondary keywords.
- **iOS keyword field (100 chars)** — comma-separated, no spaces, no title words repeated, no competitor trademarks.
- **Play full description (4,000)** — hook in first 2 lines, benefit headings, keywords 3–5 times naturally, social proof, feature bullets, CTA.
- **Screenshots:** 8 portrait per locale (6.9", 6.5", 5.5" iPhone; iPad 13"; Android phone + 7"/10" tablet). Frames 1–3 sell the top benefits in large type (e.g. "Log workouts in seconds", "AI builds your plan", "Your coach in your pocket"); RTL mirrored for Arabic; generated from real app screens via automated screenshot tests (`integration_test` + `screenshots` or Fastlane snapshot/screengrab) composited with a template.
- **App preview video** (15–30s) storyboard + Play promo video.
- **Icon** variants for A/B testing.
- **What's New** template: specific, benefit-led release notes every release (never "bug fixes").
- Category: Health & Fitness (client), Health & Fitness or Business (coach — test both); age rating questionnaire answers; privacy nutrition labels / Play Data Safety mapped from actual data flows.
- Apple Custom Product Pages (one per ad audience: trainers, nutritionists, gyms, Arabic market) and Play custom store listings per country.
- In-app events (App Store) and Play promotional content for launches/challenges.

## 3. Ratings & retention engine
- `in_app_review` prompt only after positive moments (3rd completed workout, PR, positive check-in reply), max 3 times/year, never after errors.
- In-app feedback widget routes unhappy users to support before they rate.
- Review reply workflow: App Store Connect + Play Console APIs pulled into admin; AI-drafted replies (Phase 6) in reviewer's language; target response < 24h.
- Track funnel in Firebase: store visit → install → sign up → first workout → day-7 retention; keyword rankings tracked weekly (AppFollow/AppTweak API or manual sheet).

## 4. CI/CD & environments
- GitHub Actions:
  - Backend: lint, Larastan, Pest (parallel), build Docker image, deploy to staging on `main`, production on tag with zero-downtime (Envoyer/Forge or blue-green), run migrations safely, Horizon restart, cache warm.
  - Mobile: analyze, test, golden tests, build, Fastlane deploy to TestFlight + Play internal on `main`; staged rollout to production on tag (10% → 50% → 100% with crash-rate gate).
  - Web: Lighthouse CI, broken-link check.
- Feature flags for every risky feature; kill switches.
- Secrets in GitHub OIDC + cloud secret manager; no secrets in repo.

## 5. Hardening
- Security: OWASP ASVS L2 review checklist, dependency audit (composer audit, `dart pub outdated`, Dependabot), rate limits review, CSP and security headers, 2FA enforced for owners/admins, encrypted backups tested by restore drill, pentest (external) before public launch, SSL Labs A+.
- Privacy: GDPR + Qatar PDPPL + KSA PDPL alignment, DPA template, data retention policy, subprocessors list, cookie policy; health data handling statement.
- Performance: load test (k6) for 10k concurrent clients — API p95 < 200ms; DB indexes reviewed (pg_stat_statements); N+1 detector in staging; Octane memory leak check; CDN cache hit ratio > 90%.
- Reliability: uptime monitoring, Sentry alerts, queue backlog alerts, status page, on-call runbook (`/docs/runbooks/*.md` for outages, payment webhook failures, push failures, store rejection).
- Accessibility audit (WCAG 2.2 AA) on web and both apps, VoiceOver/TalkBack walkthrough in English and Arabic.

## 6. Launch plan (`/docs/LAUNCH.md`)
- **T-6 weeks:** closed beta with 20 coaches (Qatar + France), weekly feedback calls, fix top issues.
- **T-4 weeks:** store listings live in pre-registration (Play) / pre-order (App Store); landing pages and waitlist; creator partnerships signed (Arabic + French fitness coaches).
- **T-2 weeks:** migration service ready (import from My PT Hub/Trainerize CSV); press kit; Product Hunt page; Apple/Google featuring nomination forms submitted.
- **Launch week:** staged rollout; Apple Search Ads + Google App Campaigns on brand + 10 core keywords per locale; launch challenge in community; daily review replies; daily crash triage.
- **T+30 days:** analyze keyword rankings and conversion, iterate screenshots (A/B), first case studies, referral push.

## Acceptance checklist
- [ ] Production deploy is one command/tag with zero downtime and automatic rollback.
- [ ] Both apps live on App Store and Google Play in ar, fr, en, es with complete localized metadata and screenshots.
- [ ] A Studio tenant's branded app builds and reaches TestFlight via one pipeline run.
- [ ] Crash-free sessions > 99.8% during staged rollout before going to 100%.
- [ ] Security headers A+, backups restored successfully in drill, pentest critical/high findings fixed.
- [ ] Launch checklist completed and KPIs dashboard live.
