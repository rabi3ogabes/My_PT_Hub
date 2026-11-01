# Phase 6 — AI Coach Copilot

> Paste `00-PROJECT-CONTEXT.md` first, then this file. Build on Phases 1–5.

## Goal
Ship the most useful AI in the category, **included in Pro** (competitors charge extra): it builds programs and meal plans, logs food from photos, summarizes check-ins, drafts replies in the coach's voice, adapts training weekly, predicts churn, and gives form feedback — always with the coach in control.

## Principles (non-negotiable)
1. **Coach approves before anything reaches a client.** AI produces drafts; coach edits/approves/rejects. Exception: client-side tools the coach explicitly enables (e.g. photo food logging, AI chat for self-guided users).
2. **Grounded in real data.** Every prompt is built from structured client data (logs, targets, check-ins), never guesses. Cite which data was used in the UI ("Based on 4 check-ins and 12 sessions").
3. **Safe.** Health disclaimers; refuse/flag medical, injury-diagnosis, eating-disorder or extreme-deficit requests and route to coach; hard caps (no < 1,200 kcal women / < 1,500 kcal men targets without coach override, no > 1 kg/week loss suggestions); PAR-Q flags respected.
4. **Multilingual.** Output in the client's language (ar/fr/en/es); Arabic must be natural, not literal translation.
5. **Private.** Only the minimum data sent to the provider; no training on customer data (provider setting); PII redaction where possible; per-tenant opt-out.
6. **Metered.** Usage per tenant and per feature; fair-use limits per plan; graceful degradation.

## Backend

### 1. AI service layer
- `App\Domain\Ai\Contracts\AiProvider` with `complete()`, `stream()`, `structured(schema)`, `vision()`, `transcribe()`, `embed()`.
- Drivers: Anthropic (Claude), OpenAI, plus a `FakeProvider` for tests. Model per feature configurable in admin.
- **Structured outputs:** every generator returns JSON validated against a schema (Spatie Data objects) before saving; retry with error feedback on invalid output (max 2).
- Prompt templates versioned in `resources/prompts/{feature}/{version}.md` with variables; A/B prompt versions via feature flags.
- `ai_requests` (tenant, user, feature, model, tokens in/out, cost, latency, status), `ai_outputs` (draft payload, status: draft/approved/edited/rejected, edited_diff) — used to measure acceptance rate per feature.
- `ai_settings` per coach: tone of voice (motivational, technical, nurturing, tough love), sign-off, emoji use, language preferences, coaching philosophy notes, banned words.
- Queue + streaming over Reverb for long generations.

### 2. Features

**A. Program Generator**
- Inputs: client profile (goal, level, injuries/limitations, equipment, days/week, session length, preferences) OR free text/voice ("4-day upper lower for a busy dad with dumbbells only") OR photo of whiteboard OR PDF/spreadsheet upload.
- Output: full program using ONLY exercises from the library (retrieval over exercise index with embeddings; unknown exercises mapped to closest match or proposed as custom), with blocks/sets/progression rules → opens in the builder as an editable draft.

**B. Workout Progression Assistant**
- Weekly job analyses session logs (completion, RPE, PRs, missed sets) and proposes adjustments per exercise (load +/−, volume, swap). Coach reviews a diff view and applies with one click.

**C. Meal Plan Generator**
- Inputs: targets, diet tags, allergies, cuisine preferences (Gulf, Levantine, Maghreb, French…), budget, cooking time, meals/day, Ramadan mode.
- Output: plan built from the food/recipe DB with macros within ±5% of targets (verified server-side; regenerate if out of range) + grocery list.

**D. Photo Food Logging (client)**
- Vision model identifies foods + estimates portions → mapped to DB foods → client confirms/edits in one sheet → logged. Confidence shown; low confidence asks the client to adjust.

**E. Check-in Copilot**
- Summary (wins, struggles, trends with numbers), risk flags, suggested actions, drafted reply in coach's tone and client's language. Progress report generator (weekly/monthly) as shareable card + PDF.

**F. Smart Inbox**
- Drafted replies for common client messages, message summarization for long threads, voice-note transcription, priority sorting (urgent: injury/pain mentions, cancellation intent).

**G. Churn Prediction & Re-engagement**
- Features: activity recency/frequency, compliance trend, check-in sentiment, payment status, message response time. Start with a transparent rules + weighted score model; log outcomes to train a better model later.
- Daily "Clients to contact" list with suggested message.

**H. AI Form Check**
- Client uploads a set video (≤ 30s). Pipeline: extract frames → pose estimation (MediaPipe on device or server) → rule checks per exercise (depth, knee valgus, back rounding, bar path, tempo) + vision model commentary → coach sees annotated frames and draft feedback; coach can draw/annotate and record a voice reply.
- Clear disclaimer: feedback is not medical advice.

**I. Coach Business Assistant**
- Natural-language questions over the coach's own analytics ("Who hasn't paid this month?", "Which program has best retention?") → safe, tenant-scoped query tools (no raw SQL from the model; predefined tool functions).
- Content helper: Instagram captions, program descriptions, sales page copy in 4 languages.

**J. Self-guided AI Coach (client app standalone mode)**
- For clients without a coach: AI generates a starter plan within safe templates, answers training/nutrition questions with guardrails, and always offers "Work with a real coach" (marketplace upsell, Phase 8).

## UI

### Coach web & app
- Global "✦ Copilot" command bar (Cmd/Ctrl+K): "Create a program…", "Summarize Sara's last month", "Draft reply".
- Inline AI buttons inside builder, nutrition plan builder, check-in review, inbox, client profile.
- Draft review components: side-by-side diff, accept all / accept part / regenerate with instruction / discard.
- AI settings page (tone, sample replies to learn from, language, features on/off) and usage meter.

### Client app
- Snap-to-log camera in nutrition, form-check upload in exercise screen, AI weekly recap card, (standalone) AI coach chat with suggested prompts.

## Tests & evaluation
- Unit tests with `FakeProvider` for every feature (schema validation, retries, guardrails).
- **Eval suite** (`/backend/tests/AiEvals`): 50+ fixtures per feature with expected properties (macros within range, only library exercises, correct language, no unsafe advice, tone match). Run nightly; report acceptance metrics.
- Red-team fixtures: eating-disorder language, injury claims, minors, requests for extreme deficits → must be flagged/refused and routed to coach.
- Cost tests: average tokens per feature under budget.

## Acceptance checklist
- [ ] "4-day hypertrophy, dumbbells only, 45 min" → full editable program in < 20s using library exercises only.
- [ ] Meal plan generated in Arabic with Gulf dishes hits macros within ±5%.
- [ ] Photo of a plate is logged in < 10s after confirmation.
- [ ] Check-in summary + drafted reply in coach's tone; nothing sent without approval.
- [ ] Unsafe requests are refused and flagged in every eval fixture.
- [ ] Usage dashboard shows cost per tenant per feature.
