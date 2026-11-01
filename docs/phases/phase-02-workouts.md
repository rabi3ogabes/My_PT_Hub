# Phase 2 — Workouts, Exercise Library, Programs & Smart Logging

> Paste `00-PROJECT-CONTEXT.md` first, then this file. Build on Phase 1 (auth, tenancy, i18n, offline sync).

## Goal
The best workout experience in the category: a rich multilingual exercise library with fast video, a drag-and-drop builder that supports every training format, periodized programs, and the fastest, most forgiving workout logger on mobile. This is where competitors get the worst reviews — fix every one of them.

## Competitor complaints this phase must solve
- Logger does not prefill last weight/reps or auto-fill remaining sets.
- Sessions auto-pause and lose data after closing the app.
- Missing circuits, Tabata, EMOM, AMRAP; not calisthenics-friendly.
- Exercises missing; custom videos look bad; YouTube videos need third-party downloaders.
- Videos fail to play and burn mobile data.
- Trainers cannot build programs or upload videos from the phone.
- App pauses the user's music.

## Backend

### 1. Exercise library
- Tables: `exercises` (global or tenant-owned), `exercise_translations` (name, instructions, tips, common mistakes per locale), `muscle_groups` (primary/secondary), `equipment`, `exercise_tags`, `exercise_media`, `exercise_alternatives`.
- Attributes: movement pattern, mechanics (compound/isolation), force (push/pull), level, unilateral flag, metrics supported (weight, reps, time, distance, calories, RPE), bodyweight flag, calisthenics progression chain (e.g. incline push-up → push-up → archer → one-arm).
- Seeder + importer command (`php artisan exercises:import {csv}`) for 2,000+ exercises with 4-language names; admin UI in Filament to curate.
- Custom exercises per tenant: upload video from web/mobile (chunked/resumable upload via tus or S3 multipart), or paste YouTube/Vimeo URL (stored as embed — no download needed).
- **Video pipeline:** queued job → FFmpeg → HLS (240p/480p/720p/1080p) + poster + 3s preview loop (webp) → CDN; auto-rotate, normalize audio, max file size by plan.
- Meilisearch index with typo tolerance, synonyms per language ("DB bench" = "dumbbell bench press" = "ضغط صدر دمبل"), filters by muscle, equipment, level.

### 2. Workout builder
- `workouts` → `workout_blocks` (type: straight, superset, giant set, circuit, Tabata, EMOM, AMRAP, interval, warm-up, cool-down; rounds; work/rest seconds; time cap) → `workout_exercises` (order, notes, alternative ids, rest, tempo) → `workout_set_templates` (set type: warm-up, working, drop, failure, AMRAP; target reps/range, weight, %1RM, RPE/RIR, time, distance).
- Workout tags, estimated duration (auto-calculated), equipment summary, difficulty.
- Duplicate, version, share as template, import/export JSON.

### 3. Programs
- `programs` → `program_weeks` → `program_days` → workout refs + optional nutrition/habit refs (stubbed until Phase 3).
- Periodization helpers: progression rules per exercise (add X kg when all reps hit, % increase per week, rep progression), deload week, phase labels (hypertrophy/strength/peak).
- Assign to client or group with start date; shift schedule; pause program; auto-unlock days; drip by week.
- Template marketplace (tenant-private + platform-public) with 50+ seeded starter programs (beginner full body, PPL, upper/lower, 5/3/1-style, home no-equipment, calisthenics, HYROX, Ramadan-friendly low-volume).

### 4. Logging & history
- `workout_sessions` (status, started_at, ended_at, duration, perceived effort, notes, mood) and `session_sets` (append-only; actual reps, weight, time, RPE, completed_at, client mutation ULID for idempotent sync).
- Personal records detection (1RM est. Epley/Brzycki, rep PRs, volume PRs) on session complete → event → notification + celebration.
- Exercise history endpoint (last N sessions, best set, trend), volume per muscle per week.
- Compliance stats per client (planned vs completed).

## Coach web (Livewire)
- Exercise library browser (grid/list, filters, preview on hover, favourites, custom exercise form with upload progress).
- **Workout builder:** three-pane layout (library | canvas | inspector), drag & drop, keyboard shortcuts, block type switcher, bulk edit sets, autosave + undo/redo, live preview as client sees it.
- Program builder: calendar grid of weeks × days, drag workouts onto days, copy week, apply progression, bulk assign to clients/groups.
- Client workout tab: assigned program, upcoming, completed sessions with set-by-set detail, PRs, charts.

## Coach app (Flutter)
- Full parity: browse/search exercises, create custom exercise with in-app video recording and trim, build/edit workouts (simplified builder with the same block types), assign program to client, view client session logs.

## Client app (Flutter) — the smart logger
- **Today screen:** today's workout card, start button, streak, next session.
- **Workout preview:** blocks, equipment, duration, video thumbnails; download for offline (videos cached, size shown).
- **Logger:**
  - Each set row prefilled with last session's actual values (fallback: template targets); one tap "✓ same as last", swipe to edit.
  - Editing set 1 offers "apply to remaining sets".
  - Auto rest timer after completing a set; haptic + sound at end; lock-screen Live Activity (iOS) / ongoing notification (Android) showing rest countdown and next set.
  - Block modes: superset/circuit cycling, Tabata/EMOM/interval timers with audio cues, AMRAP round counter.
  - Plate calculator, 1RM calculator, exercise swap (alternatives), add extra set/exercise, notes, RPE picker.
  - **Never auto-pause; never lose data:** every change persisted to Drift instantly; session restored after app kill; background-safe timer.
  - Audio session configured to mix with other audio (does NOT stop Spotify/Apple Music).
  - Finish summary: duration, volume, PRs with Rive celebration, effort rating, notes, share card image.
- History, exercise progress charts, PR board.
- Video player: HLS adaptive, Wi-Fi-only HD option, data saver mode, offline cached playback.

## Tests
- Builder data integrity (blocks/sets ordering), progression rule engine unit tests, PR detection tests, sync idempotency (same mutation twice = one set), video pipeline job test with fixture.
- Flutter: logger widget tests (prefill, apply-to-all, timer), integration test for full offline workout → sync.
- Performance: logger renders 40 sets at 60fps on mid-range Android; exercise search < 100ms.

## Acceptance checklist
- [ ] Coach builds a 4-week program with supersets, a Tabata and an EMOM block on web AND phone.
- [ ] Client logs a full workout offline, kills the app mid-session, reopens — nothing lost; syncs later.
- [ ] Last-session values prefill; "apply to all sets" works.
- [ ] Music keeps playing during workouts; rest timer visible on lock screen.
- [ ] Custom video uploaded from phone plays as adaptive HLS within 2 minutes.
- [ ] Exercise search works in Arabic, French, English, Spanish.
