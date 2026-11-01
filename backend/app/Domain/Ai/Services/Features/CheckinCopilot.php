<?php

namespace App\Domain\Ai\Services\Features;

use App\Domain\Ai\Data\AiTask;
use App\Domain\Ai\Models\AiOutput;
use App\Domain\Ai\Services\AiGateway;
use App\Domain\Ai\Services\AiOutputService;
use App\Domain\Ai\Services\AiSettingsService;
use App\Domain\Ai\Services\ClientDataService;
use App\Domain\Ai\Services\SafetyGuard;
use App\Domain\Ai\Services\ToneEnforcer;
use App\Domain\Ai\Support\Schemas;
use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Identity\Models\User;
use App\Domain\Nutrition\Models\FoodLog;
use App\Domain\Nutrition\Models\NutritionTarget;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Models\WorkoutSession;

/**
 * E. Check-in copilot: wins / struggles / trends with numbers, risk flags,
 * suggested actions and a reply drafted in the coach's voice and the client's
 * language. Numeric trends are computed server-side; safety flags from the
 * answers are always added. The reply is a draft until the coach approves.
 */
final class CheckinCopilot
{
    public function __construct(
        private readonly AiGateway $gateway,
        private readonly AiOutputService $outputs,
        private readonly ClientDataService $clients,
        private readonly AiSettingsService $settings,
        private readonly ToneEnforcer $tone,
        private readonly SafetyGuard $guard,
    ) {}

    /** @param array<string, mixed> $input */
    public function summarize(Tenant $tenant, User $coach, CheckinResponse $response, array $input = []): AiOutput
    {
        if (! in_array($response->status, ['submitted', 'reviewed'], true)) {
            throw ApiException::invalid('checkin_not_submitted', __('ai.errors.checkin_not_submitted'));
        }
        $client = $this->clients->resolveClient($tenant, $response->client_id);
        $profile = $this->clients->profile($tenant, $client);
        $language = $this->clients->language($client, $input['language'] ?? null, $tenant);
        $voice = $this->settings->voiceFor($tenant, $coach, $profile?->coach_id);

        $answers = $this->answers($response);
        $metrics = $this->metrics($tenant, $client, $response);
        $previous = CheckinResponse::query()
            ->where('client_id', $client->id)
            ->whereIn('status', ['submitted', 'reviewed'])
            ->where('id', '!=', $response->id)
            ->orderByDesc('submitted_at')
            ->limit(3)
            ->get(['id', 'submitted_at', 'answers'])
            ->map(fn (CheckinResponse $r) => ['submitted_on' => $r->submitted_at?->toDateString(), 'answers' => $r->answers])
            ->all();

        $safety = $this->guard->screenText(collect($answers)->pluck('answer')->filter(fn ($a) => is_string($a))->implode("\n"))
            ->merge($this->guard->screenProfile($profile));

        $result = $this->gateway->run(new AiTask(
            feature: 'checkin_summary',
            tenant: $tenant,
            user: $coach,
            variables: [
                'first_name' => $client->firstName(),
                'language' => $language,
                'answers' => $answers,
                'metrics' => $metrics,
                'previous_checkins' => $previous,
                'safety_flags' => $safety->categories,
                ...$this->tone->variables($voice),
            ],
            context: ['first_name' => $client->firstName(), 'language' => $language, 'metrics' => $metrics, 'answers' => $answers, 'safety' => $safety->categories, 'sign_off' => $voice->sign_off],
            schema: Schemas::checkinSummary(),
            postProcess: function (array $data) use ($voice) {
                $banned = $this->tone->bannedWordsIn($data['reply_draft'], $voice);

                return [$data, $banned === [] ? [] : ['reply_draft contains banned words: '.implode(', ', $banned)]];
            },
        ));

        $data = $result->data;
        $data['reply_draft'] = $this->tone->polish($data['reply_draft'], $voice);
        foreach ($safety->toFlags() as $flag) {
            if (! collect($data['risk_flags'])->contains('type', $flag['type'])) {
                $data['risk_flags'][] = ['type' => $flag['type'], 'severity' => $flag['severity'], 'evidence' => __('ai.flags.detected_in_answers')];
            }
        }

        return $this->outputs->draft(
            'checkin_summary',
            $coach,
            $client,
            ['checkin_response_id' => $response->id],
            $data + ['metrics' => $metrics, 'checkin_response_id' => $response->id],
            $this->clients->grounding($tenant, $client),
            $safety->toFlags(),
            $result->requestId,
        );
    }

    /** @return list<array{question: string, type: string, answer: mixed}> */
    private function answers(CheckinResponse $response): array
    {
        $questions = collect($response->form()->withTrashed()->first()?->questions ?? [])->keyBy('id');
        $out = [];
        foreach ($response->answers ?? [] as $id => $answer) {
            $q = $questions->get($id);
            $label = $q['label'] ?? (string) $id;
            if (is_array($label)) {
                $label = $label[app()->getLocale()] ?? $label['en'] ?? (string) reset($label);
            }
            $out[] = ['question' => (string) $label, 'type' => (string) ($q['type'] ?? 'short_text'), 'answer' => $answer];
        }

        return $out;
    }

    /** @return array<string, mixed> */
    private function metrics(Tenant $tenant, User $client, CheckinResponse $response): array
    {
        $weights = $this->clients->weights($tenant, $client, 8);
        $first = $weights[0]['weight_kg'] ?? null;
        $last = $weights !== [] ? end($weights)['weight_kg'] : null;
        $now = $response->submitted_at ?? now();

        $sessions14 = WorkoutSession::query()->where('client_id', $client->id)->where('status', 'completed')->whereBetween('started_at', [$now->copy()->subDays(14), $now])->count();
        $sessionsPrev14 = WorkoutSession::query()->where('client_id', $client->id)->where('status', 'completed')->whereBetween('started_at', [$now->copy()->subDays(28), $now->copy()->subDays(14)])->count();

        $logs = FoodLog::query()->where('client_id', $client->id)->where('log_date', '>=', $now->copy()->subDays(7)->toDateString())->get(['log_date', 'kcal', 'protein_g']);
        $daysLogged = $logs->groupBy(fn (FoodLog $l) => $l->log_date->toDateString())->count();
        $avgKcal = $daysLogged > 0 ? round($logs->sum('kcal') / $daysLogged) : null;
        $target = NutritionTarget::query()->where('client_id', $client->id)->orderByDesc('effective_from')->first();

        return [
            'weights' => $weights,
            'weight_change_kg' => $first !== null && $last !== null ? round($last - $first, 1) : null,
            'sessions_last_14_days' => $sessions14,
            'sessions_previous_14_days' => $sessionsPrev14,
            'food_days_logged_last_7' => $daysLogged,
            'avg_kcal_logged' => $avgKcal,
            'kcal_target' => $target?->kcal,
            'snapshot' => $response->context,
        ];
    }
}
