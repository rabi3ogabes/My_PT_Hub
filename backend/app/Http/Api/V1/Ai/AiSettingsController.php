<?php

namespace App\Http\Api\V1\Ai;

use App\Domain\Ai\Models\AiOutput;
use App\Domain\Ai\Models\AiRequest;
use App\Domain\Ai\Services\AiSettingsService;
use App\Domain\Ai\Services\UsageMeterService;
use App\Domain\Ai\Support\AiFeatures;
use App\Domain\Ai\Support\CostCalculator;
use App\Domain\Ai\Support\Schemas;
use App\Domain\Billing\Services\PlanGate;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/** Coach voice/AI switches and the usage meter (cost per feature). */
class AiSettingsController extends ApiController
{
    public function __construct(private readonly AiSettingsService $settings) {}

    public function show(): JsonResponse
    {
        return $this->ok($this->payload());
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tone' => ['sometimes', Rule::in(AiSettingsService::TONES)],
            'sign_off' => ['sometimes', 'nullable', 'string', 'max:120'],
            'use_emoji' => ['sometimes', 'boolean'],
            'philosophy' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'language' => ['sometimes', 'nullable', Rule::in(Schemas::LANGUAGES)],
            'banned_words' => ['sometimes', 'array', 'max:50'],
            'banned_words.*' => ['string', 'max:60'],
            'sample_replies' => ['sometimes', 'array', 'max:10'],
            'sample_replies.*' => ['string', 'max:1000'],
            'disabled_features' => ['sometimes', 'array'],
            'disabled_features.*' => [Rule::in(AiFeatures::all())],
            'opt_out' => ['sometimes', 'boolean'],
            'client_tools' => ['sometimes', 'array'],
            'client_tools.*' => ['boolean'],
        ]);

        $tenant = $this->tenant();
        $user = $this->user();
        if (isset($data['client_tools'])) {
            if (! $user->isOwnerIn($tenant->id)) {
                throw ApiException::forbidden(__('ai.errors.owner_only'));
            }
            $allowed = array_unique(array_column(AiFeatures::CLIENT_TOOL_SETTINGS, 0));
            $this->settings->updateClientTools($tenant, array_intersect_key($data['client_tools'], array_flip($allowed)));
        }
        unset($data['client_tools']);

        $setting = $this->settings->forUser($tenant, $user);
        $setting->fill($data);
        $setting->tenant_id = $tenant->id;
        $setting->user_id = $user->id;
        $setting->save();

        return $this->ok($this->payload());
    }

    public function usage(Request $request, UsageMeterService $usage, PlanGate $plans): JsonResponse
    {
        $data = $request->validate(['period' => ['nullable', 'date_format:Y-m']]);
        $tenant = $this->tenant();
        $period = $data['period'] ?? $usage->period();
        $start = Carbon::createFromFormat('Y-m-d H:i:s', $period.'-01 00:00:00', 'UTC');
        $end = $start->copy()->endOfMonth();

        $requests = AiRequest::query()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("feature, COUNT(*) as calls, SUM(CASE WHEN status = 'ok' THEN 1 ELSE 0 END) as ok_calls, SUM(CASE WHEN status = 'refused' THEN 1 ELSE 0 END) as refused, SUM(tokens_in) as tokens_in, SUM(tokens_out) as tokens_out, SUM(cost_micros) as cost_micros, AVG(latency_ms) as avg_latency")
            ->groupBy('feature')
            ->get()
            ->keyBy('feature');

        $outputs = AiOutput::query()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('feature, status, COUNT(*) as n')
            ->groupBy('feature', 'status')
            ->get()
            ->groupBy('feature');

        $features = [];
        foreach (AiFeatures::all() as $feature) {
            $r = $requests[$feature] ?? null;
            $statuses = ($outputs[$feature] ?? collect())->pluck('n', 'status')->map(fn ($n) => (int) $n);
            $reviewed = $statuses->only(['approved', 'edited', 'applied', 'rejected'])->sum();
            $accepted = $statuses->only(['approved', 'edited', 'applied'])->sum();
            $cost = (int) ($r->cost_micros ?? 0);
            $features[] = [
                'feature' => $feature,
                'label' => __("ai.features.{$feature}"),
                'calls' => (int) ($r->calls ?? 0),
                'successful' => (int) ($r->ok_calls ?? 0),
                'refused' => (int) ($r->refused ?? 0),
                'tokens_in' => (int) ($r->tokens_in ?? 0),
                'tokens_out' => (int) ($r->tokens_out ?? 0),
                'cost_micros' => $cost,
                'cost_formatted' => CostCalculator::formatUsd($cost),
                'avg_latency_ms' => (int) round((float) ($r->avg_latency ?? 0)),
                'drafts' => (int) $statuses->sum(),
                'acceptance_rate' => $reviewed > 0 ? round($accepted / $reviewed, 2) : null,
            ];
        }

        $requestsMeter = $usage->meter($tenant, 'ai_requests', $period);
        $tokensMeter = $usage->meter($tenant, 'ai_tokens', $period);
        $totalCost = (int) ($requestsMeter?->cost_micros ?? 0);

        return $this->ok([
            'period' => $period,
            'totals' => [
                'requests' => (int) ($requestsMeter?->value ?? 0),
                'tokens' => (int) ($tokensMeter?->value ?? 0),
                'cost_micros' => $totalCost,
                'cost_formatted' => CostCalculator::formatUsd($totalCost),
            ],
            'limits' => [
                'requests_month' => $plans->limit($tenant, 'ai_requests_month'),
                'cost_cap_micros' => (int) config('services.ai.monthly_cap_micros', 0) ?: null,
                'plan_includes_copilot' => $plans->feature($tenant, 'ai_copilot'),
            ],
            'features' => $features,
        ]);
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        $tenant = $this->tenant();
        $user = $this->user();
        $s = $this->settings->forUser($tenant, $user);

        return [
            'tone' => $s->tone ?? 'motivational',
            'sign_off' => $s->sign_off,
            'use_emoji' => (bool) ($s->use_emoji ?? true),
            'philosophy' => $s->philosophy,
            'language' => $s->language,
            'banned_words' => array_values($s->banned_words ?? []),
            'sample_replies' => array_values($s->sample_replies ?? []),
            'disabled_features' => array_values($s->disabled_features ?? []),
            'opt_out' => (bool) $s->opt_out,
            'tenant_opted_out' => $this->settings->tenantOptedOut($tenant),
            'client_tools' => (object) $this->settings->clientTools($tenant),
            'can_manage_tenant' => $user->isOwnerIn($tenant->id),
            'tones' => AiSettingsService::TONES,
            'features' => array_map(fn (string $f) => ['key' => $f, 'label' => __("ai.features.{$f}"), 'audience' => AiFeatures::isCoach($f) ? 'coach' : 'client'], AiFeatures::all()),
            'disclaimer' => __('ai.disclaimer.health'),
        ];
    }
}
