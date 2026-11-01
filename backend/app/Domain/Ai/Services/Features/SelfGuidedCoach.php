<?php

namespace App\Domain\Ai\Services\Features;

use App\Domain\Ai\Data\AiTask;
use App\Domain\Ai\Models\AiOutput;
use App\Domain\Ai\Services\AiGateway;
use App\Domain\Ai\Services\AiOutputService;
use App\Domain\Ai\Services\ClientDataService;
use App\Domain\Ai\Services\SafetyGuard;
use App\Domain\Ai\Support\Schemas;
use App\Domain\Identity\Models\User;
use App\Domain\Nutrition\Models\FoodLog;
use App\Domain\Nutrition\Models\NutritionTarget;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Models\PersonalRecord;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\ProgramAssignment;
use App\Domain\Workouts\Models\WorkoutSession;
use Closure;

/**
 * J. Self-guided AI coach (client app): guarded chat that always offers a
 * real coach, a starter plan chosen among platform program templates (never
 * invented), and a weekly recap. Unsafe messages are refused before any
 * provider call and flagged for the coach (when the client has one).
 */
final class SelfGuidedCoach
{
    public function __construct(
        private readonly AiGateway $gateway,
        private readonly AiOutputService $outputs,
        private readonly ClientDataService $clients,
        private readonly SafetyGuard $guard,
    ) {}

    /**
     * @param  list<array{role: string, content: string}>  $history
     * @param  (Closure(string): void)|null  $onDelta  stream deltas when given
     * @return array<string, mixed>
     */
    public function chat(Tenant $tenant, User $client, string $message, array $history = [], ?Closure $onDelta = null): array
    {
        $this->gateway->authorize('coach_chat', $tenant, $client);
        $language = $this->clients->language($client, null, $tenant);
        $profile = $this->clients->profile($tenant, $client);
        $safety = $this->guard->screenText($message)->merge($this->guard->screenProfile($profile));
        $base = [
            'coach_offer' => ['text' => __('ai.coach_offer', [], $language), 'url' => rtrim((string) config('app.url'), '/').'/coaches'],
            'disclaimer' => $this->guard->disclaimer($language),
        ];

        if ($safety->refuse) {
            $this->gateway->logGuarded('coach_chat', $tenant, $client, implode(',', $safety->categories));
            $reply = $this->guard->refusalMessage($safety, $language);
            $output = $this->flagForCoach($client, $message, $reply, $safety->toFlags());
            $onDelta && $onDelta($reply);

            return ['reply' => $reply, 'refused' => true, 'flags' => $safety->categories, 'ai_output_id' => $output->id] + $base;
        }

        $history = $this->cleanHistory($history);
        $result = $this->gateway->run(new AiTask(
            feature: 'coach_chat',
            tenant: $tenant,
            user: $client,
            kind: $onDelta ? 'stream' : 'text',
            variables: [
                'first_name' => $client->firstName(),
                'language' => $language,
                'profile' => $this->clients->promptProfile($tenant, $client),
                'message' => $message,
                'safety_flags' => $safety->categories,
            ],
            context: ['message' => $message, 'language' => $language, 'first_name' => $client->firstName()],
            onDelta: $onDelta,
            history: $history,
        ));

        $reply = trim($result->text);
        $unsafe = $this->guard->unsafeAdvice($reply, $profile?->gender);
        $flags = $safety->categories;
        if ($unsafe !== null) {
            $reply = __('ai.refusal.unsafe_output', [], $language);
            $flags[] = 'unsafe_output';
            $this->flagForCoach($client, $message, $reply, [['type' => 'unsafe_output', 'severity' => 'high', 'source' => 'safety_guard', 'detail' => $unsafe]]);
        }

        return ['reply' => $reply, 'refused' => false, 'flags' => $flags, 'ai_output_id' => null, 'ai_request_id' => $result->requestId] + $base;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function starterPlan(Tenant $tenant, User $client, array $input): array
    {
        $this->gateway->authorize('starter_plan', $tenant, $client);
        $language = $this->clients->language($client, null, $tenant);
        $profile = $this->clients->profile($tenant, $client);
        $safety = $this->guard->screenProfile($profile)->merge($this->guard->screenText((string) ($input['notes'] ?? '')));
        if ($safety->refuse || $safety->has('parq')) {
            $this->gateway->logGuarded('starter_plan', $tenant, $client, implode(',', $safety->categories));
            $reply = $this->guard->refusalMessage($safety, $language);
            $output = $this->flagForCoach($client, (string) ($input['notes'] ?? ''), $reply, $safety->toFlags(), 'starter_plan');

            return ['refused' => true, 'message' => $reply, 'flags' => $safety->categories, 'template' => null, 'ai_output_id' => $output->id, 'coach_offer' => ['text' => __('ai.coach_offer', [], $language)]];
        }

        $templates = Program::withoutGlobalScopes()
            ->whereNull('tenant_id')
            ->whereNull('deleted_at')
            ->where('is_template', true)
            ->where('visibility', 'public')
            ->withCount('weeks')
            ->get()
            ->map(fn (Program $p) => [
                'id' => $p->id,
                'name' => $p->translate('name', $language),
                'goal' => $p->goal,
                'level' => $p->level,
                'days_per_week' => $p->days_per_week,
                'weeks' => $p->weeks_count,
                'equipment' => $p->equipment ?? [],
            ])->values()->all();
        if ($templates === []) {
            throw ApiException::invalid('no_templates', __('ai.errors.no_templates'));
        }
        $ids = array_column($templates, 'id');
        $request = [
            'goal' => $input['goal'] ?? $profile?->goal,
            'level' => $input['level'] ?? $profile?->level ?? 'beginner',
            'days_per_week' => $input['days_per_week'] ?? $profile?->days_per_week,
            'equipment' => $input['equipment'] ?? $profile?->equipment ?? [],
            'notes' => $input['notes'] ?? null,
        ];

        $result = $this->gateway->run(new AiTask(
            feature: 'starter_plan',
            tenant: $tenant,
            user: $client,
            variables: ['request' => $request, 'templates' => $templates, 'language' => $language],
            context: ['request' => $request, 'templates' => $templates, 'language' => $language],
            schema: Schemas::starterPlan(),
            postProcess: fn (array $data) => [$data, in_array($data['template_id'], $ids, true) ? [] : ["template_id '{$data['template_id']}' is not one of the provided templates"]],
        ));

        $template = collect($templates)->firstWhere('id', $result->data['template_id']);
        $assignmentId = null;
        if (($input['assign'] ?? false) && $tenant->isPersonal()) {
            $assignmentId = ProgramAssignment::query()->create([
                'program_id' => $template['id'],
                'client_id' => $client->id,
                'assigned_by' => $client->id,
                'start_date' => now()->setTimezone($client->timezone ?: 'UTC')->toDateString(),
                'status' => 'active',
                'unlock_mode' => 'all',
            ])->id;
        }

        $output = $this->outputs->draft('starter_plan', $client, $client, $request, ['template' => $template, 'reason' => $result->data['reason'], 'assignment_id' => $assignmentId], ['templates_considered' => count($templates)], [], $result->requestId);

        return [
            'refused' => false,
            'template' => $template,
            'reason' => $result->data['reason'],
            'assignment_id' => $assignmentId,
            'ai_output_id' => $output->id,
            'disclaimer' => $this->guard->disclaimer($language),
            'coach_offer' => ['text' => __('ai.coach_offer', [], $language)],
        ];
    }

    /** @return array<string, mixed> */
    public function weeklyRecap(Tenant $tenant, User $client): array
    {
        $existing = AiOutput::query()
            ->where('client_id', $client->id)
            ->where('feature', 'weekly_recap')
            ->where('created_at', '>=', now()->startOfWeek())
            ->latest()
            ->first();
        if ($existing) {
            return $existing->payload + ['ai_output_id' => $existing->id, 'cached' => true];
        }

        $this->gateway->authorize('weekly_recap', $tenant, $client);
        $language = $this->clients->language($client, null, $tenant);
        $stats = $this->weekStats($tenant, $client);

        $result = $this->gateway->run(new AiTask(
            feature: 'weekly_recap',
            tenant: $tenant,
            user: $client,
            variables: ['first_name' => $client->firstName(), 'language' => $language, 'stats' => $stats],
            context: ['first_name' => $client->firstName(), 'language' => $language, 'stats' => $stats],
            schema: Schemas::weeklyRecap(),
        ));

        $profile = $this->clients->profile($tenant, $client);
        $data = $result->data;
        if ($this->guard->unsafeAdvice($data['focus_next_week'].' '.implode(' ', $data['highlights']), $profile?->gender) !== null) {
            $data['focus_next_week'] = __('ai.recap.safe_focus', [], $language);
        }
        $payload = $data + ['stats' => $stats, 'week_start' => now()->startOfWeek()->toDateString(), 'language' => $language];
        $output = $this->outputs->draft('weekly_recap', $client, $client, ['week_start' => $payload['week_start']], $payload, $this->clients->grounding($tenant, $client), [], $result->requestId);

        return $payload + ['ai_output_id' => $output->id, 'cached' => false];
    }

    /** @return array<string, mixed> */
    private function weekStats(Tenant $tenant, User $client): array
    {
        $from = now()->subDays(7);
        $sessions = WorkoutSession::query()->where('client_id', $client->id)->where('status', 'completed')->where('started_at', '>=', $from)->get(['total_volume_kg', 'duration_seconds']);
        $logs = FoodLog::query()->where('client_id', $client->id)->where('log_date', '>=', $from->toDateString())->get(['log_date', 'kcal', 'protein_g']);
        $daysLogged = $logs->groupBy(fn (FoodLog $l) => $l->log_date->toDateString())->count();
        $target = NutritionTarget::query()->where('client_id', $client->id)->orderByDesc('effective_from')->first();
        $weights = $this->clients->weights($tenant, $client, 4);

        return [
            'sessions_completed' => $sessions->count(),
            'total_volume_kg' => round((float) $sessions->sum('total_volume_kg')),
            'training_minutes' => (int) round($sessions->sum('duration_seconds') / 60),
            'personal_records' => PersonalRecord::query()->where('client_id', $client->id)->where('achieved_at', '>=', $from)->count(),
            'food_days_logged' => $daysLogged,
            'avg_kcal' => $daysLogged ? (int) round($logs->sum('kcal') / $daysLogged) : null,
            'kcal_target' => $target?->kcal,
            'avg_protein_g' => $daysLogged ? (int) round($logs->sum('protein_g') / $daysLogged) : null,
            'protein_target_g' => $target?->protein_g,
            'weight_trend' => $weights,
        ];
    }

    /**
     * Safety events become a flagged draft the coach sees in the AI inbox.
     *
     * @param  list<array<string, mixed>>  $flags
     */
    private function flagForCoach(User $client, string $message, string $reply, array $flags, string $feature = 'coach_chat'): AiOutput
    {
        return $this->outputs->draft($feature, $client, $client, ['message' => $message], ['reply' => $reply, 'refused' => true], [], $flags);
    }

    /**
     * @param  list<array{role: string, content: string}>  $history
     * @return list<array{role: string, content: string}>
     */
    private function cleanHistory(array $history): array
    {
        $history = array_values(array_filter($history, fn ($m) => in_array($m['role'] ?? null, ['user', 'assistant'], true) && is_string($m['content'] ?? null) && $m['content'] !== ''));
        $history = array_slice($history, -10);
        while ($history !== [] && $history[0]['role'] !== 'user') {
            array_shift($history);
        }
        // Must alternate and end with an assistant turn (the new message is appended after).
        $clean = [];
        foreach ($history as $m) {
            if ($clean !== [] && end($clean)['role'] === $m['role']) {
                $clean[count($clean) - 1]['content'] .= "\n".$m['content'];
            } else {
                $clean[] = ['role' => $m['role'], 'content' => mb_substr($m['content'], 0, 4000)];
            }
        }
        if ($clean !== [] && end($clean)['role'] === 'user') {
            array_pop($clean);
        }

        return $clean;
    }
}
