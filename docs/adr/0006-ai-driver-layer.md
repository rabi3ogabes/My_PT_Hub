# 0006 — Provider-agnostic AI driver layer with a coach approval gate

- Status: Accepted · Date: 2026-01-08

## Context
AI is a core differentiator (included in Pro). Requirements: multiple providers (Anthropic by
default, OpenAI as alternative), structured outputs validated before saving, streaming,
per-tenant metering and caps, privacy (minimum data, no training), multilingual output, safety
guardrails, and — non-negotiable — coach approval before anything reaches a client.

## Decision
- `App\Domain\Ai\Contracts\AiProvider`: `complete`, `stream`, `structured(schema)`, `vision`,
  `transcribe`, `embed`. Drivers: **Anthropic** (default, `ANTHROPIC_MODEL`), **OpenAI**, **Fake**
  (deterministic; the only driver used in tests). Model per feature configurable in admin.
- Every generator returns JSON validated against a schema (Data objects); invalid → retry with
  error feedback (max 2) → fail visibly.
- Prompts versioned in `resources/prompts/{feature}/{version}.md`; A/B via feature flags.
- `ai_requests` (tokens, cost, latency, status) + `usage_meters` (monthly per tenant) enforce plan
  caps (`saas_plans.limits.ai_requests_month`, `AI_TENANT_MONTHLY_CAP_MICROS`).
- `ai_outputs` hold drafts with status draft/approved/edited/rejected and `grounding` (which data
  was used). Delivery to clients only from approved/edited outputs.
- Guardrails run server-side after generation (calorie floors, ≤ 1 kg/week loss, ED/injury/minor
  detection, PAR-Q flags) and flag or refuse.
- Eval suite `backend/tests/AiEvals` runs nightly against the real provider.

## Alternatives considered
- **Single-vendor SDK calls in services**: fastest start, but vendor lock-in and untestable.
- **Agent frameworks (LangChain-style) in PHP**: immature, opaque; our flows are deterministic pipelines.
- **Client-side AI calls**: impossible to meter, guard or keep keys secret.

## Consequences
- Switching or mixing providers is configuration, not code.
- Cost per tenant and acceptance rate per feature are measurable from day one.
- Extra latency from validation/retries is hidden by queueing + streaming over Reverb.
- Data processing agreements with each provider are required (subprocessors list in SECURITY.md).
