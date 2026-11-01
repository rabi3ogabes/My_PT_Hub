# Phase 3 — Nutrition, Habits, Check-ins, Progress & Compliance

> Paste `00-PROJECT-CONTEXT.md` first, then this file. Build on Phases 1–2.

## Goal
Make nutrition and accountability effortless for clients and fully visible to coaches: frictionless food logging (barcode + AI-ready photo hooks), regional food data, recipes, habits with streaks, automated check-ins, progress photos and a compliance dashboard that flags at-risk clients.

## Competitor complaints this phase must solve
- Foods added to a plan do not appear; nutrition logging is friction-heavy; clients stop using it.
- No preloaded recipes; meal plans are thin.
- MyFitnessPal sync unreliable (we make our own logging good enough to not need it).
- Hard to monitor whether clients complete daily habits.
- Progress tracking lacks conversation context.

## Backend

### 1. Food database
- `foods` (global + tenant custom), `food_translations` (ar/fr/en/es), `food_servings` (unit, grams), nutrients (kcal, protein, carbs, fat, fibre, sugar, sodium + micronutrients JSON), `barcodes`, source (USDA FDC, Open Food Facts, custom, regional).
- Importers: USDA FoodData Central, Open Food Facts (barcodes), and a curated **regional pack**: Gulf (machboos, harees, luqaimat, karak, dates by variety, laban), Levant, Maghreb (couscous, tajine, msemen, harira, chorba), French staples, common restaurant chains in Qatar/KSA/UAE.
- Meilisearch with language synonyms; barcode lookup endpoint with fallback to Open Food Facts API + cache.
- Verified flag; user-submitted foods moderated in admin.

### 2. Nutrition targets & plans
- Calculator service: BMR (Mifflin-St Jeor, Katch-McArdle), TDEE by activity, goal adjustments, macro splits (presets + custom), protein per kg; stored as `nutrition_targets` with effective dates; per-day-type targets (training vs rest day).
- `meal_plans` → `meal_plan_days` → `meals` (breakfast, lunch, dinner, snacks, custom names) → `meal_items` (food or recipe, quantity, unit).
- Plan types: fixed meal plan, flexible macros only, portion/hand-based, intermittent fasting window, **Ramadan mode** (suhoor/iftar meals, hydration targets between iftar and suhoor, adjusted timings by city).
- Diet tags & filters: halal, vegetarian, vegan, pescatarian, keto, dairy-free, gluten-free, allergens.
- Food swaps (same macros ± 10%), auto-generated grocery list grouped by aisle, printable PDF plan.

### 3. Recipes
- `recipes` (translations, photos, steps, prep/cook time, servings, tags) → `recipe_items`; macros auto-computed; scaling by servings.
- Seed 150+ recipes across cuisines (Gulf, Levantine, Maghreb, French, international) with macros in 4 languages.

### 4. Client food logging
- `food_logs` (meal slot, food/recipe, qty, unit, logged_at, source: search/barcode/quick-add/photo/plan), `water_logs`.
- "Log from plan" in one tap per meal; copy yesterday's meal; favourites and recents; quick-add macros.
- **Photo logging hook:** endpoint that accepts a photo and returns candidate foods + portions (implemented with AI in Phase 6; ship the UI and contract now with a manual fallback).
- Daily summary vs targets; weekly averages; adherence score.

### 5. Habits
- `habits` (templates: water, steps, sleep, protein, veggies, meditation, no sugar, custom), type (boolean, count, duration, numeric with target), schedule (daily, specific days, X per week), reminder times.
- `habit_assignments`, `habit_logs`; streaks, completion %, auto-complete from health data later (Phase 7 hook).

### 6. Check-ins
- `checkin_forms` (builder: short text, long text, scale 1–10, single/multi choice, number, photo, measurement, file), `checkin_schedules` (weekly on day X, every N days, on program week end), `checkin_responses` with answers JSON.
- Automation: reminder push + email; overdue escalation to coach; form auto-attaches last week's metrics, compliance and photos for context.
- Coach review screen: response + trend charts + photos side by side + comment thread on the check-in (fixes "progress without chat").

### 7. Progress tracking
- `body_metrics` (weight, body fat %, measurements: neck, chest, waist, hips, arms, thighs, calves, custom), unit conversion.
- `progress_photos` (front/side/back, pose overlay guide, private by default, encrypted storage, signed URLs), compare slider any two dates, privacy blur option for sharing, timelapse generation job.
- Progress report generator (PDF/web): weight trend, measurements, strength PRs, compliance, photos.

### 8. Compliance dashboard
- Per-client score from workouts, nutrition, habits, check-ins (weights configurable).
- Status: green / amber / red; "at risk" = 3+ days inactive or score drop > 25%; daily digest email/push to coach with who needs attention.

## Coach web
- Nutrition plan builder (drag foods/recipes into meals, live macro bars per meal/day, target comparison), recipe editor, food DB browser.
- Habit library and assignment; check-in form builder with preview; check-in inbox (unread, overdue, reviewed); client progress tab with charts and photo compare.
- **Per-client "Schedule" view** on the client profile home: calendar strip of assigned workouts, meals, habits, check-ins with quick-assign buttons (directly requested by competitor users).
- Compliance dashboard with filters and bulk message to at-risk clients.

## Client app
- **Nutrition tab:** day view with rings (kcal + macros), meals, quick log sheet (search, barcode scanner via camera, recent, favourites, from plan, quick-add, photo — Phase 6 AI), water tracker, Ramadan/fasting timer.
- Recipes browser with step-by-step cook mode.
- Habits tab: today's checklist with one-tap completion, streak flames, reminders.
- Check-in flow: friendly step-by-step form, photo capture with pose ghost overlay, measurement inputs with unit switch.
- Progress tab: weight chart, measurements, photo compare slider, PR board, monthly recap.
- Coach app: the same views for any client + quick assign + review check-ins.

## Tests
- Calculator unit tests vs reference values; macro totals for plans/recipes; barcode lookup with mocked API; habit streak edge cases (timezones, DST, skipped scheduled days); check-in scheduling; compliance score.
- Flutter: barcode flow, log-from-plan, offline food logging + sync.

## Acceptance checklist
- [ ] A food added to a plan always appears (regression test).
- [ ] Client logs a full day in under 60 seconds using plan + barcode.
- [ ] Arabic search finds "كبسة" and French finds "couscous royal" with correct macros.
- [ ] Ramadan mode shifts meal slots and reminders around iftar/suhoor.
- [ ] Coach sees at-risk clients on dashboard and can message them in bulk.
