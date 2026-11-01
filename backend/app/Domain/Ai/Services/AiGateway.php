<?php

namespace App\Domain\Ai\Services;

use App\Domain\Ai\Contracts\AiProvider;
use App\Domain\Ai\Data\AiPrompt;
use App\Domain\Ai\Data\AiResponse;
use App\Domain\Ai\Data\AiResult;
use App\Domain\Ai\Data\AiTask;
use App\Domain\Ai\Exceptions\AiProviderException;
use App\Domain\Ai\Models\AiRequest;
use App\Domain\Ai\Support\AiFeatures;
use App\Domain\Ai\Support\CostCalculator;
use App\Domain\Ai\Support\PiiRedactor;
use App\Domain\Ai\Support\PromptLibrary;
use App\Domain\Ai\Support\SchemaValidator;
use App\Domain\Billing\Services\PlanGate;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Tenant;

/**
 * The single entry point for every model call:
 *
 *  1. Gate: tenant/user opt-out, per-feature disable, plan ("ai_copilot" for
 *     coach features, tenant toggle for client tools), monthly request cap
 *     (plan limit ai_requests_month) and monthly cost cap.
 *  2. Render the versioned prompt template, redact PII.
 *  3. Call the provider; for structured output validate against the schema
 *     plus the feature's domain checks, retrying up to config('ai.max_retries')
 *     times with the validation errors fed back.
 *  4. Meter every provider call into ai_requests and the run into usage_meters.
 */
final class AiGateway
{
    public function __construct(
        private readonly AiProvider $provider,
        private readonly PromptLibrary $prompts,
        private readonly SchemaValidator $validator,
        private readonly PlanGate $plans,
        private readonly AiSettingsService $settings,
        private readonly UsageMeterService $usage,
    ) {}

    public function provider(): AiProvider
    {
        return $this->provider;
    }

    /** Throws an ApiException when the feature may not run for this tenant/user. */
    public function authorize(string $feature, Tenant $tenant, ?User $user): void
    {
        if ($this->settings->tenantOptedOut($tenant) || ($user && $user->isStaffIn($tenant->id) && $this->settings->userOptedOut($tenant, $user))) {
            throw new ApiException('ai_disabled', __('ai.errors.opted_out'), 403);
        }

        if (in_array($feature, $this->settings->disabledFeatures($tenant, $user), true)) {
            throw new ApiException('ai_feature_disabled', __('ai.errors.feature_disabled'), 403);
        }

        $monthlyLimit = $this->plans->limit($tenant, 'ai_requests_month');

        if (AiFeatures::isCoach($feature) && ! $this->plans->feature($tenant, 'ai_copilot') && ($monthlyLimit ?? 0) <= 0) {
            throw ApiException::limit(__('ai.errors.plan_required'));
        }

        if (AiFeatures::isClient($feature) && ! $this->settings->clientToolEnabled($tenant, $feature)) {
            throw new ApiException('ai_feature_disabled', __('ai.errors.client_tool_disabled'), 403);
        }

        if ($monthlyLimit !== null && $this->usage->requestsThisMonth($tenant) >= $monthlyLimit) {
            throw ApiException::limit(__('ai.errors.monthly_cap', ['limit' => $monthlyLimit]));
        }

        $costCap = (int) config('services.ai.monthly_cap_micros', 0);
        if ($costCap > 0 && $this->usage->costThisMonth($tenant) >= $costCap) {
            throw ApiException::limit(__('ai.errors.cost_cap'));
        }
    }

    public function run(AiTask $task): AiResult
    {
        $this->authorize($task->feature, $task->tenant, $task->user);

        $version = $this->promptVersion($task->feature, $task->tenant);
        $rendered = $this->prompts->render($task->feature, $version, $task->variables);
        $history = array_map(fn (array $m) => ['role' => $m['role'] === 'assistant' ? 'assistant' : 'user', 'content' => PiiRedactor::redact((string) $m['content'])], $task->history);

        $prompt = new AiPrompt(
            feature: $task->feature,
            system: PiiRedactor::redact($rendered['system']),
            messages: [...$history, ['role' => 'user', 'content' => PiiRedactor::redact($rendered['user'])]],
            model: config("ai.feature_models.{$task->feature}") ?: null,
            maxTokens: (int) (config("ai.max_tokens.{$task->feature}") ?? config('ai.max_tokens.default', 4096)),
            context: $task->context,
            promptVersion: $version,
        );

        $structured = $task->schema !== null;
        $maxAttempts = $structured ? 1 + (int) config('ai.max_retries', 2) : 1;
        $attempts = 0;
        $tokensIn = 0;
        $tokensOut = 0;
        $cost = 0;
        $requestId = null;
        $model = '';

        while (true) {
            $attempts++;
            $started = hrtime(true);
            try {
                $response = $this->call($task, $prompt);
            } catch (AiProviderException $e) {
                $latency = $this->elapsed($started);
                $model = $prompt->model ?? $this->defaultModel();
                $callCost = CostCalculator::micros($model, $e->tokensIn, $e->tokensOut);
                [$tokensIn, $tokensOut, $cost] = [$tokensIn + $e->tokensIn, $tokensOut + $e->tokensOut, $cost + $callCost];
                $status = match ($e->reason) {
                    'refused' => 'refused',
                    'invalid' => 'invalid',
                    default => 'error',
                };
                $requestId = $this->log($task, $model, $version, $e->tokensIn, $e->tokensOut, $callCost, $latency, $status, $e->getMessage());

                if ($e->reason === 'invalid' && $attempts < $maxAttempts) {
                    $prompt = $prompt->withMessage('assistant', '-')->withMessage('user', $this->feedback([$e->getMessage()]));

                    continue;
                }

                $this->usage->record($task->tenant, $tokensIn + $tokensOut, $cost);

                throw match ($e->reason) {
                    'refused' => new ApiException('ai_refused', __('ai.errors.refused'), 422),
                    'invalid' => new ApiException('ai_invalid_output', __('ai.errors.invalid_output'), 502),
                    default => new ApiException('ai_unavailable', __('ai.errors.unavailable'), 503),
                };
            }

            $latency = $this->elapsed($started);
            $model = $response->model;
            $callCost = CostCalculator::micros($model, $response->tokensIn, $response->tokensOut);
            [$tokensIn, $tokensOut, $cost] = [$tokensIn + $response->tokensIn, $tokensOut + $response->tokensOut, $cost + $callCost];

            $data = $response->data;
            $errors = [];
            if ($structured) {
                $errors = $this->validator->validate($data, $task->schema);
                if ($errors === [] && $task->postProcess !== null) {
                    [$data, $errors] = ($task->postProcess)($data);
                }
            }

            if ($errors !== []) {
                $requestId = $this->log($task, $model, $version, $response->tokensIn, $response->tokensOut, $callCost, $latency, 'invalid', mb_substr(implode('; ', $errors), 0, 250));
                if ($attempts < $maxAttempts) {
                    $prompt = $prompt
                        ->withMessage('assistant', (string) json_encode($response->data, JSON_UNESCAPED_UNICODE))
                        ->withMessage('user', $this->feedback($errors));

                    continue;
                }
                $this->usage->record($task->tenant, $tokensIn + $tokensOut, $cost);

                throw new ApiException('ai_invalid_output', __('ai.errors.invalid_output'), 502);
            }

            $requestId = $this->log($task, $model, $version, $response->tokensIn, $response->tokensOut, $callCost, $latency, 'ok', null);
            $this->usage->record($task->tenant, $tokensIn + $tokensOut, $cost);

            return new AiResult($response->text, $data, $requestId, $attempts, $tokensIn, $tokensOut, $cost, $model, $version);
        }
    }

    /** Logs a request that SafetyGuard refused before any provider call. */
    public function logGuarded(string $feature, Tenant $tenant, ?User $user, string $reason): string
    {
        return AiRequest::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user?->id,
            'feature' => $feature,
            'provider' => 'guard',
            'model' => 'safety_guard',
            'prompt_version' => null,
            'status' => 'refused',
            'error' => mb_substr($reason, 0, 250),
        ])->id;
    }

    public function promptVersion(string $feature, Tenant $tenant): string
    {
        if ($this->prompts->exists($feature, 'v2') && $this->plans->feature($tenant, "ai_prompt_{$feature}_v2")) {
            return 'v2';
        }

        return (string) (config("ai.prompt_versions.{$feature}") ?: 'v1');
    }

    private function call(AiTask $task, AiPrompt $prompt): AiResponse
    {
        return match ($task->kind) {
            'structured' => $this->provider->structured($prompt, $task->schema ?? [], $task->feature),
            'vision' => $this->provider->vision($prompt, $task->images, $task->schema, $task->feature),
            'stream' => $this->provider->stream($prompt, $task->onDelta ?? fn (string $d) => null),
            default => $this->provider->complete($prompt),
        };
    }

    /** @param list<string> $errors */
    private function feedback(array $errors): string
    {
        return "Your previous output failed validation:\n- ".implode("\n- ", array_slice($errors, 0, 25))
            ."\nReturn the complete corrected result. Use only the IDs provided in the prompt.";
    }

    private function log(AiTask $task, string $model, string $version, int $in, int $out, int $cost, int $latency, string $status, ?string $error): string
    {
        return AiRequest::query()->create([
            'tenant_id' => $task->tenant->id,
            'user_id' => $task->user?->id,
            'feature' => $task->feature,
            'provider' => $this->provider->name(),
            'model' => mb_substr($model, 0, 64),
            'prompt_version' => $version,
            'tokens_in' => $in,
            'tokens_out' => $out,
            'cost_micros' => $cost,
            'latency_ms' => $latency,
            'status' => $status,
            'error' => $error !== null ? mb_substr($error, 0, 250) : null,
        ])->id;
    }

    private function elapsed(int $started): int
    {
        return (int) round((hrtime(true) - $started) / 1_000_000);
    }

    private function defaultModel(): string
    {
        return match ($this->provider->name()) {
            'anthropic' => (string) config('services.ai.anthropic_model'),
            'openai' => (string) config('services.ai.openai_model'),
            default => 'fake',
        };
    }
}
