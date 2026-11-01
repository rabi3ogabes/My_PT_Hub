# Phase 8 — Marketing Website, SEO, Coach Profiles & Marketplace

> Paste `00-PROJECT-CONTEXT.md` first, then this file. Build on Phases 1–7. Load the `frontend-design` guidance if available.

## Goal
A luxury, lightning-fast multilingual website that ranks on Google for coach-software keywords AND consumer fitness keywords, converts coaches into trials, and turns every coach into an SEO page that brings clients — a growth loop competitors don't have.

## 1. Technical SEO foundation (Laravel, server-rendered)
- Blade + Livewire only where interactive; static pages fully cached (response cache + CDN); Core Web Vitals green: LCP < 2.0s, CLS < 0.05, INP < 200ms on mobile.
- URL structure: `/{locale}/...` with `ar`, `fr`, `en`, `es` (x-default → en); `hreflang` on every page; localized slugs (e.g. `/fr/logiciel-coach-sportif`, `/ar/برنامج-المدرب-الشخصي`).
- Sitemaps per locale and type (pages, exercises, templates, coaches, blog) with `lastmod`; sitemap index; robots.txt; IndexNow ping on publish.
- JSON-LD: Organization, WebSite + SearchAction, SoftwareApplication (with offers and aggregateRating from real reviews only), FAQPage, BreadcrumbList, Article, VideoObject (exercises), HowTo (exercise instructions), Person/LocalBusiness (coach profiles), Product (templates).
- Open Graph/Twitter cards with auto-generated branded OG images per page (queued render).
- Image pipeline: AVIF/WebP, responsive `srcset`, lazy loading, explicit dimensions; fonts self-hosted with `font-display: swap`, Arabic subset.
- Smart app banners (`apple-itunes-app` meta, Android equivalent), universal links / app links (`apple-app-site-association`, `assetlinks.json`), deferred deep links into the apps.
- Analytics: GA4 + server-side events, Google Search Console & Bing Webmaster verification, consent banner (GDPR-compliant, privacy-preserving default).

## 2. Marketing pages (design: dark luxury, bold type, motion)
- **Home:** hero with product video loop, social proof strip, "Why switch" (speed, AI included, Arabic/French, honest billing), feature sections with interactive demos (not static screenshots), testimonials, pricing teaser, final CTA.
- **Features:** one page per module (workouts, nutrition, AI copilot, check-ins, payments, bookings, community, white label, integrations).
- **Solutions:** personal trainers, online coaches, nutritionists, gyms & studios, physiotherapists, CrossFit/HYROX, women's coaches.
- **Pricing:** plan toggle monthly/annual, currency auto-detect, feature comparison table, "cancel in 2 clicks" promise, FAQ.
- **Compare pages:** vs My PT Hub, vs Trainerize, vs Everfit, vs PT Distinction, vs TrueCoach — factual tables, migration offer.
- **Migration landing page:** "Switch in 1 day" — CSV import + done-for-you migration.
- About (real founders, team, story, values), public roadmap with voting, changelog, status page link, careers, press kit, contact, legal (terms, privacy, DPA, cookie, refund policy).

## 3. Programmatic SEO content (high-volume, multilingual)
- **Exercise library pages** (`/exercises/{slug}`): video, muscles diagram, step-by-step HowTo, common mistakes, alternatives, related programs; 2,000+ pages × 4 languages.
- **Free templates** (`/templates/{slug}`): workout programs, meal plans (incl. Ramadan plans, Gulf/Maghreb cuisine plans), check-in forms, PAR-Q, client onboarding forms — gated download (email) → nurture to trial.
- **Calculators** (interactive, indexable): TDEE/BMR, macros, 1RM, body fat (Navy), BMI, protein, water, pace, calories burned, PT pricing calculator, "how much can I earn as an online coach".
- **Recipes** with nutrition facts (Recipe schema).
- **Blog/Academy** in 4 languages: business growth for coaches, programming science, nutrition, local market guides ("How to become a personal trainer in Qatar", "Devenir coach sportif en ligne").
- CMS: Filament-based editor with SEO fields (title, meta, canonical, OG, schema type, noindex), internal-link suggestions, content scoring, scheduling, translations workflow.

## 4. Coach profiles & marketplace (the growth loop)
- Every coach gets a public profile `/{locale}/coaches/{slug}` (and optional custom domain): photo/video intro, specialties, certifications (verified badge after document check), languages, location + online, packages with checkout (Phase 5), booking (Phase 4), transformation gallery (with client consent), reviews (verified clients only), FAQ, Instagram feed embed.
- Coach directory with filters (city, specialty, language, online/in-person, price, gender of coach, rating); city landing pages (`/coaches/doha`, `/coaches/riyadh`, `/coaches/paris`) with LocalBusiness schema.
- Client app "Find a coach" tab uses the same directory; standalone users get matched by a quiz → introduction request → lead in coach CRM.
- Reviews system: request after 30 days, moderation, response by coach, aggregate rating — only verified reviews in schema.
- Marketplace monetization options (config): free listing in Pro, featured placement (paid), or referral fee — default free at launch.

## 5. Conversion & growth
- Trial sign-up in 1 step (email or Google/Apple), onboarding wizard (Phase 1), product tour, sample client + sample program preloaded.
- Lifecycle emails (marketing skill: onboarding sequence days 0/1/3/7/14, trial ending, win-back) in 4 languages.
- Referral program for coaches (give 1 month / get 1 month) with tracking dashboard.
- Affiliate program for influencers/educators (commission, unique links, payouts).
- Exit-intent and lead magnets; A/B testing framework (feature flags) on hero and pricing.

## 6. Performance & SEO QA
- Lighthouse CI in GitHub Actions (budgets: performance ≥ 95, SEO 100, accessibility ≥ 95).
- Automated checks: broken links, missing hreflang pairs, duplicate titles/meta, missing alt text, schema validation.
- Keyword map document (`/docs/seo/keyword-map.md`): primary keyword per page per locale, search intent, internal links; start with: personal trainer app, personal training software, online coaching app, fitness coaching platform, برنامج مدرب شخصي, تطبيق مدرب رياضي, logiciel coach sportif, application coach sportif, app entrenador personal.

## Acceptance checklist
- [ ] All marketing pages in 4 languages with correct RTL and hreflang.
- [ ] Lighthouse mobile ≥ 95 performance on home, pricing, an exercise page.
- [ ] 2,000+ exercise pages and 30+ calculator/template pages indexed in sitemaps.
- [ ] Coach profile live with booking + checkout; directory filters by city and language.
- [ ] Rich results test passes for SoftwareApplication, FAQ, HowTo, VideoObject.
- [ ] Smart banner opens the app at the same content (deep link) when installed.
