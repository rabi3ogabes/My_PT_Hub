# Phase 0 — Discovery, Product Spec & Architecture

> Paste `00-PROJECT-CONTEXT.md` first, then this file. **No application code in this phase** — only documents, diagrams and config that every later phase builds on.

## Goal
Turn the vision into an unambiguous blueprint: product spec, information architecture, user flows, database ERD, API contract, design tokens and repo scaffold. When this phase ends, any engineer could start Phase 1 without asking questions.

## Before you start — ask me (max 5 questions)
1. Final brand name and domain.
2. Launch markets in order (e.g. Qatar → KSA → UAE → France → Morocco).
3. Accent color choice (electric lime or champagne gold) or my own hex.
4. AI provider preference (Anthropic Claude, OpenAI, or both via driver).
5. Hosting target (Forge + DigitalOcean/Hetzner, AWS, or my own VPS).

## Deliverables (all inside `/docs`)

### 1. `PRODUCT_SPEC.md`
- Vision, positioning statement, target personas (solo PT, online coach, nutritionist, studio owner, client).
- Problem → solution table built from competitor gaps (slow apps, logging friction, English-only, billing lock-in, weak nutrition, no client discovery).
- Feature list grouped by module, each with: user story, acceptance criteria, priority (P0 launch / P1 / P2), plan tier (Free / Pro / Studio / Enterprise).
- Pricing tiers: Free (3 clients) · Pro $39 unlimited + AI · Studio $99 (5 coaches + branded app) · Enterprise custom.
- Non-functional requirements (performance, security, accessibility, localization, uptime 99.9%).
- KPIs: activation (coach creates first program + invites client within 24h), client weekly active rate, coach churn, NPS.

### 2. `INFORMATION_ARCHITECTURE.md`
Sitemaps for:
- Marketing site (home, features/*, pricing, AI, for-personal-trainers, for-nutritionists, for-gyms, coaches directory, exercise library, templates, calculators, blog, compare/*, help, legal).
- Coach dashboard (Dashboard, Clients, Programs, Workouts, Exercises, Nutrition, Habits, Check-ins, Calendar, Messages, Community, Payments, Packages, Analytics, CRM/Leads, Marketing, Team, Settings, Billing).
- Client portal / app (Today, Workouts, Nutrition, Habits, Progress, Messages, Calendar, Community, Shop, Profile).
- Super-admin (Tenants, Plans, Subscriptions, Exercise/food master data, Translations, Feature flags, Support impersonation with audit, System health).

### 3. `USER_FLOWS.md` (Mermaid diagrams)
- Coach sign-up → onboarding wizard → first client invite → first program assigned (target < 5 min).
- Client invite acceptance → onboarding questionnaire → first workout logged (target < 2 min).
- Standalone client: install → goal quiz → free plan → "find a coach" upsell.
- Workout logging (offline) → sync → coach notified → PR celebration.
- Weekly check-in → AI summary → coach approves reply → client notified.
- Client buys package → payment → auto-assign content → welcome message.
- Booking a session → credit deduction → reminders → attendance → no-show rule.
- Coach cancels subscription (two taps) → confirmation → data retention → export.

### 4. `ERD.md` + `erd.mmd`
Full Mermaid ER diagram and table descriptions. Minimum entities:
`tenants, users, tenant_user (role), coach_profiles, client_profiles, invitations, teams, locations,`
`exercises, exercise_translations, exercise_media, muscle_groups, equipment, workouts, workout_blocks, workout_exercises, workout_sets_template, programs, program_weeks, program_days, assignments, workout_sessions, session_sets, personal_records,`
`foods, food_translations, recipes, recipe_items, meal_plans, meal_plan_days, meals, meal_items, food_logs, water_logs, nutrition_targets,`
`habits, habit_assignments, habit_logs, checkin_forms, checkin_questions, checkin_schedules, checkin_responses, body_metrics, progress_photos,`
`conversations, conversation_participants, messages, message_attachments, scheduled_messages, automations, community_spaces, posts, comments, reactions, challenges, challenge_entries,`
`calendars, availability_rules, events, event_attendees, bookings, credit_ledgers, waitlists,`
`products, packages, package_items, prices, coupons, orders, order_items, payments, refunds, invoices, payment_gateways, payouts, client_subscriptions,`
`saas_plans, saas_subscriptions (Cashier), usage_meters, leads, lead_stages, contracts, signatures,`
`ai_requests, ai_outputs, ai_settings (tone of voice),`
`integrations, integration_tokens, health_samples, webhooks, webhook_deliveries,`
`notifications, devices (FCM tokens), media (spatie), activity_log, translations, feature_flags, settings.`
For each table: columns, types, indexes, FKs, soft deletes, tenant scoping, PII/encryption flags.

### 5. `API_CONTRACT.md` + `openapi.yaml`
- Auth endpoints (register, login, refresh, logout, social login Apple/Google, magic link, 2FA).
- Resource endpoints for every module (CRUD + actions like `POST /workout-sessions/{id}/complete`).
- Sync endpoints for offline mobile: `GET /sync/pull?since=`, `POST /sync/push` (batched mutations with client-generated ULIDs).
- Conventions: envelope, errors (`code`, `message`, `field_errors`), pagination, filtering (`filter[status]=`), sorting, includes, localization via `Accept-Language`, idempotency header.
- WebSocket channels (Reverb): `private-tenant.{id}`, `private-user.{id}`, `presence-conversation.{id}`.

### 6. `DESIGN_SYSTEM.md` + `/packages/design_tokens/tokens.json`
- Tokens (color light/dark, typography scale Latin + Arabic, spacing, radius, shadows, motion).
- Component inventory: buttons, inputs, selects, steppers, cards, stat tiles, charts, list rows, tabs, bottom sheet, modals, toasts, empty states, skeletons, avatars, badges, chips, timers, set-logger row.
- Script to generate `tailwind.config.js` theme and Dart `app_tokens.dart` from `tokens.json`.
- Key screen wireframes described in text (or HTML prototypes): coach dashboard home, client "Today" screen, workout logger, program builder, nutrition day, check-in review.

### 7. `ARCHITECTURE.md`
- System diagram (Mermaid): clients → CDN → Laravel (Octane) → Postgres/Redis/Meilisearch/S3 → queues (Horizon) → Reverb → FCM/APNs → third parties.
- Module boundaries (Laravel domain folders: `app/Domain/{Workouts,Nutrition,...}`), Flutter feature-first structure.
- ADRs (`/docs/adr/0001-*.md`) for: monorepo, Postgres vs MySQL, Livewire vs Inertia, Riverpod, Drift offline sync, AI driver layer, payment driver layer, video pipeline.

### 8. Repo scaffold
- Create the monorepo folders, root `README.md`, `CLAUDE.md` (copy of project context), `.editorconfig`, `.gitignore`, `docker-compose.yml` (php, postgres, redis, meilisearch, mailpit, minio), `Makefile` with `make up / test / lint`.
- `/docs/CHANGELOG.md` and `/docs/PHASES.md` checklist with all phases.

## Acceptance checklist
- [ ] Every module in the context file has user stories with acceptance criteria.
- [ ] ERD covers every entity above; tenant scoping marked on each table.
- [ ] OpenAPI file validates (`npx @redocly/cli lint`).
- [ ] Token generator script produces Tailwind + Dart outputs.
- [ ] `docker compose up` starts all services.
- [ ] ADRs written for all major decisions.

## End of phase
Summarise decisions, list open questions, and wait for my "go" before Phase 1.
