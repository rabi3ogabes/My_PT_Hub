# AI eval suite

Fixture-driven evaluation of the AI Coach Copilot (Phase 6). Each JSON file in
`fixtures/{feature}/` describes one input and the properties the output must
have. `EvalFixturesTest.php` runs every fixture.

| Feature        | What is checked |
|----------------|-----------------|
| `safety`       | Red-team prompts (eating disorder, injury/medical diagnosis, minors, extreme deficits, self-harm) in en/ar/fr/es are flagged **and** refused; benign prompts are not flagged. |
| `priority`     | Inbox priority detection (pain/injury, cancellation intent, scheduling). |
| `program`      | Generated programs use **only library exercises**, respect days/week, goal and equipment. |
| `meal_plan`    | Every day is within **±5%** of kcal/protein/carbs/fat (recomputed from the food DB), language, Ramadan slots, diet tags, allergies, kcal floor (1,200 women / 1,500 men). |
| `business_ask` | The question is routed to the right predefined, tenant-scoped tool (never SQL). |
| `content`      | Output language matches the request. |
| `photo_food`   | Plate photo candidates are mapped to DB foods. |

Fixture shape:

```json
{
  "id": "meal_plan-gulf-ar",
  "feature": "meal_plan",
  "description": "Macros verified within ±5% from the food DB",
  "input": {"kcal": 2000, "protein_g": 150, "carbs_g": 200, "fat_g": 67, "cuisine": "gulf", "language": "ar", "days": 2},
  "expect": {"within_tolerance": 0.05, "language": "ar"}
}
```

## Running

CI / local (deterministic, Fake provider, no keys, no cost):

```bash
php artisan test tests/AiEvals
```

Nightly real-provider run (same fixtures, real model, real cost):

```bash
AI_EVALS_DRIVER=anthropic ANTHROPIC_API_KEY=... php artisan test tests/AiEvals
# or AI_EVALS_DRIVER=openai OPENAI_API_KEY=...
```

The nightly job should run on a schedule (e.g. GitHub Actions `cron: "17 2 * * *"`)
with the provider key from the repository secrets, then export acceptance metrics:

```bash
php artisan test tests/AiEvals --log-junit storage/logs/ai-evals.xml
```

Pass rate per feature (from the JUnit report) is the acceptance metric tracked
over time; the `ai_requests` table of the eval database gives tokens, cost and
latency per feature for the cost budget. A drop in pass rate after a prompt
change (`resources/prompts/{feature}/v*.md`) blocks the prompt rollout.

## Adding fixtures

Add a JSON file under the feature folder with a unique `id`. Keep red-team
fixtures balanced across the four languages and add a benign counterpart for
every new refusal pattern to catch over-blocking.
