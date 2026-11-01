<?php

namespace App\Domain\Ai\Services\Features;

use App\Domain\Ai\Data\AiTask;
use App\Domain\Ai\Models\AiOutput;
use App\Domain\Ai\Services\AiGateway;
use App\Domain\Ai\Services\AiOutputService;
use App\Domain\Ai\Services\ClientDataService;
use App\Domain\Ai\Services\ExerciseRetriever;
use App\Domain\Ai\Support\Schemas;
use App\Domain\Ai\Support\SchemaValidator;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Workouts\Models\ProgramAssignment;
use App\Domain\Workouts\Models\ProgramDay;
use App\Domain\Workouts\Models\SessionSet;
use App\Domain\Workouts\Models\WorkoutExercise;
use App\Domain\Workouts\Models\WorkoutSession;
use App\Domain\Workouts\Models\WorkoutSetTemplate;
use Illuminate\Support\Collection;

/**
 * B. Progression assistant: analyses the last 4 weeks of logged sessions per
 * exercise (top sets, e1RM trend, RPE, completion) and proposes per-exercise
 * load/volume changes as a diff. A deterministic double-progression baseline
 * is computed server-side; the model refines and explains it, and load
 * changes are capped at ±10% per step.
 */
final class ProgressionAssistant
{
    public const WINDOW_DAYS = 28;

    public const MAX_LOAD_CHANGE = 0.10;

    public function __construct(
        private readonly AiGateway $gateway,
        private readonly AiOutputService $outputs,
        private readonly ClientDataService $clients,
        private readonly ExerciseRetriever $exercises,
        private readonly SchemaValidator $validator,
    ) {}

    /** @param array<string, mixed> $input */
    public function generate(Tenant $tenant, User $coach, User $client, array $input = []): AiOutput
    {
        $analysis = $this->analyse($tenant, $client);
        if ($analysis === []) {
            throw ApiException::invalid('not_enough_data', __('ai.errors.not_enough_sessions'));
        }
        $baseline = array_map(fn (array $a) => $this->baseline($a), $analysis);
        $language = $this->clients->language($client, $input['language'] ?? null, $tenant);
        $ids = array_column($analysis, 'exercise_id');

        $result = $this->gateway->run(new AiTask(
            feature: 'progression',
            tenant: $tenant,
            user: $coach,
            variables: [
                'client' => $this->clients->promptProfile($tenant, $client),
                'analysis' => $analysis,
                'baseline' => $baseline,
                'language' => $language,
                'window_days' => self::WINDOW_DAYS,
            ],
            context: ['baseline' => $baseline, 'language' => $language],
            schema: Schemas::progression(),
            postProcess: fn (array $data) => $this->normalize($data, $ids),
        ));

        return $this->outputs->draft(
            'progression',
            $coach,
            $client,
            ['client_id' => $client->id, 'window_days' => self::WINDOW_DAYS],
            $result->data + ['analysis' => $analysis, 'language' => $language],
            $this->clients->grounding($tenant, $client, ['exercises_analysed' => count($analysis)]),
            [],
            $result->requestId,
        );
    }

    /**
     * Per-exercise stats over the window.
     *
     * @return list<array<string, mixed>>
     */
    public function analyse(Tenant $tenant, User $client): array
    {
        $sessions = WorkoutSession::query()
            ->where('tenant_id', $tenant->id)
            ->where('client_id', $client->id)
            ->where('status', 'completed')
            ->where('started_at', '>=', now()->subDays(self::WINDOW_DAYS))
            ->orderBy('started_at')
            ->get(['id', 'started_at']);
        if ($sessions->isEmpty()) {
            return [];
        }
        $dates = $sessions->pluck('started_at', 'id');

        $sets = SessionSet::query()
            ->whereIn('workout_session_id', $sessions->pluck('id'))
            ->where('set_type', '!=', 'warmup')
            ->get(['workout_session_id', 'exercise_id', 'set_number', 'reps', 'weight_kg', 'rpe', 'is_completed']);

        $out = [];
        foreach ($sets->groupBy('exercise_id') as $exerciseId => $rows) {
            $exercise = $this->exercises->find((string) $exerciseId);
            $bySession = $rows->groupBy('workout_session_id')->sortBy(fn ($r, $sid) => $dates[$sid] ?? now());
            $tops = $bySession->map(fn (Collection $s) => $this->topSet($s))->values();
            $first = $tops->first();
            $last = $tops->last();
            $e1rmFirst = $this->e1rm($first);
            $e1rmLast = $this->e1rm($last);
            $rpes = $rows->pluck('rpe')->filter(fn ($v) => $v !== null);

            $out[] = [
                'exercise_id' => (string) $exerciseId,
                'exercise_name' => $exercise ? (string) $exercise->translate('name', 'en') : (string) $exerciseId,
                'sessions' => $bySession->count(),
                'sets' => $rows->count(),
                'working_sets_last_session' => $bySession->last()?->count() ?? 0,
                'completion_rate' => round($rows->where('is_completed', true)->count() / max(1, $rows->count()), 2),
                'avg_rpe' => $rpes->isNotEmpty() ? round((float) $rpes->avg(), 1) : null,
                'first_top_set' => $first,
                'last_top_set' => $last,
                'e1rm_change_pct' => $e1rmFirst > 0 ? round(($e1rmLast - $e1rmFirst) / $e1rmFirst * 100, 1) : null,
            ];
        }

        return $out;
    }

    /**
     * Double-progression baseline for one exercise.
     *
     * @param  array<string, mixed>  $a
     * @return array<string, mixed>
     */
    public function baseline(array $a): array
    {
        $weight = $a['last_top_set']['weight_kg'];
        $reps = $a['last_top_set']['reps'];
        $sets = max(1, (int) $a['working_sets_last_session']);
        $rpe = $a['avg_rpe'];
        $current = ['weight_kg' => $weight, 'reps' => $reps, 'sets' => $sets];

        [$action, $proposed, $reason] = match (true) {
            $a['completion_rate'] < 0.75 || ($rpe !== null && $rpe >= 9.5) => [
                $weight ? 'decrease_load' : 'decrease_volume',
                $weight ? ['weight_kg' => $this->roundLoad($weight * 0.95), 'reps' => $reps, 'sets' => $sets] : ['weight_kg' => null, 'reps' => $reps, 'sets' => max(1, $sets - 1)],
                'fatigue',
            ],
            $a['sessions'] >= 3 && $a['e1rm_change_pct'] !== null && abs($a['e1rm_change_pct']) < 1 && ($rpe === null || $rpe <= 8.5) => [
                'increase_volume', ['weight_kg' => $weight, 'reps' => $reps, 'sets' => min(10, $sets + 1)], 'plateau',
            ],
            $a['completion_rate'] >= 0.9 && ($rpe === null || $rpe <= 8.5) && $weight => [
                'increase_load', ['weight_kg' => $this->roundLoad($weight + max(1.0, $weight * 0.025)), 'reps' => $reps, 'sets' => $sets], 'ready',
            ],
            $a['completion_rate'] >= 0.9 && ! $weight && $reps => [
                'increase_volume', ['weight_kg' => null, 'reps' => min(100, $reps + 2), 'sets' => $sets], 'ready',
            ],
            default => ['hold', $current, 'consolidate'],
        };

        return [
            'exercise_id' => $a['exercise_id'],
            'exercise_name' => $a['exercise_name'],
            'action' => $action,
            'current' => $current,
            'proposed' => $proposed,
            'reason' => $reason,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $ids
     * @return array{0: array<string, mixed>, 1: list<string>}
     */
    public function normalize(array $data, array $ids): array
    {
        $errors = [];
        foreach ($data['suggestions'] as $i => &$s) {
            if (! in_array($s['exercise_id'], $ids, true)) {
                $errors[] = "suggestions[{$i}].exercise_id '{$s['exercise_id']}' was not analysed; use only analysed exercise ids";

                continue;
            }
            if ($s['action'] === 'swap') {
                if (! $s['swap_exercise_id'] || ! $this->exercises->find($s['swap_exercise_id'])) {
                    $errors[] = "suggestions[{$i}].swap_exercise_id must be a library exercise id";
                }
            } else {
                $s['swap_exercise_id'] = null;
            }
            $from = $s['current']['weight_kg'];
            $to = $s['proposed']['weight_kg'];
            if ($from && $to && abs($to - $from) / $from > self::MAX_LOAD_CHANGE) {
                $s['proposed']['weight_kg'] = $this->roundLoad($from * (1 + ($to > $from ? 1 : -1) * self::MAX_LOAD_CHANGE));
                $s['capped'] = true;
            }
        }
        unset($s);

        return [$data, $errors];
    }

    /**
     * @param  array<string, mixed>  $edited
     * @return list<string>
     */
    public function validateEdited(AiOutput $output, array $edited): array
    {
        $payload = $edited;
        unset($payload['analysis'], $payload['language']);
        foreach ($payload['suggestions'] ?? [] as $i => $s) {
            unset($payload['suggestions'][$i]['capped']);
        }
        $errors = $this->validator->validate($payload, Schemas::progression());
        if ($errors !== []) {
            return $errors;
        }
        [, $errors] = $this->normalize($payload, array_column($output->payload['analysis'] ?? [], 'exercise_id'));

        return $errors;
    }

    /**
     * Applies the approved suggestions to the client's active program.
     *
     * @return ProgramAssignment the assignment whose program was updated
     */
    public function apply(AiOutput $output, User $reviewer): ProgramAssignment
    {
        $assignment = ProgramAssignment::query()
            ->where('client_id', $output->client_id)
            ->where('status', 'active')
            ->latest('start_date')
            ->first();
        if (! $assignment) {
            throw ApiException::invalid('no_active_program', __('ai.errors.no_active_program'));
        }
        $program = $assignment->program()->first();
        $sharedWithOthers = ProgramAssignment::query()
            ->where('program_id', $assignment->program_id)
            ->where('status', 'active')
            ->where('client_id', '!=', $output->client_id)
            ->exists();
        if (! $program || $program->tenant_id !== $output->tenant_id || $sharedWithOthers) {
            throw ApiException::conflict('program_shared', __('ai.errors.program_shared'));
        }

        $workoutIds = ProgramDay::query()
            ->whereIn('program_week_id', $program->weeks()->pluck('id'))
            ->whereNotNull('workout_id')
            ->pluck('workout_id')
            ->unique();

        foreach ($output->effectivePayload()['suggestions'] ?? [] as $s) {
            if ($s['action'] === 'hold') {
                continue;
            }
            $workoutExercises = WorkoutExercise::query()
                ->whereHas('block', fn ($q) => $q->whereIn('workout_id', $workoutIds))
                ->where('exercise_id', $s['exercise_id'])
                ->get();

            foreach ($workoutExercises as $we) {
                if ($s['action'] === 'swap') {
                    $we->update(['exercise_id' => $s['swap_exercise_id']]);

                    continue;
                }
                $working = WorkoutSetTemplate::query()->where('workout_exercise_id', $we->id)->where('set_type', '!=', 'warmup')->orderBy('position')->get();
                if (in_array($s['action'], ['increase_load', 'decrease_load'], true) && $s['proposed']['weight_kg'] !== null) {
                    WorkoutSetTemplate::query()->whereIn('id', $working->pluck('id'))->update(['weight_kg' => $s['proposed']['weight_kg']]);
                }
                if ($s['proposed']['reps'] !== null && in_array($s['action'], ['increase_volume', 'decrease_volume'], true)) {
                    WorkoutSetTemplate::query()->whereIn('id', $working->pluck('id'))->update(['reps_min' => $s['proposed']['reps'], 'reps_max' => $s['proposed']['reps']]);
                }
                $targetSets = $s['proposed']['sets'];
                if ($targetSets !== null && $working->isNotEmpty() && $targetSets !== $working->count()) {
                    if ($targetSets > $working->count()) {
                        $last = $working->last();
                        $position = (int) WorkoutSetTemplate::query()->where('workout_exercise_id', $we->id)->max('position');
                        for ($i = $working->count(); $i < $targetSets; $i++) {
                            $copy = $last->replicate();
                            $copy->position = ++$position;
                            $copy->save();
                        }
                    } else {
                        WorkoutSetTemplate::query()->whereIn('id', $working->slice($targetSets)->pluck('id'))->delete();
                    }
                }
            }
        }

        return $assignment;
    }

    /**
     * @param  Collection<int, SessionSet>  $sets
     * @return array{weight_kg: float|null, reps: int|null, rpe: float|null}
     */
    private function topSet(Collection $sets): array
    {
        $top = $sets->sortByDesc(fn (SessionSet $s) => [(float) $s->weight_kg, (int) $s->reps])->first();

        return ['weight_kg' => $top?->weight_kg !== null ? (float) $top->weight_kg : null, 'reps' => $top?->reps, 'rpe' => $top?->rpe];
    }

    /** @param array<string, mixed>|null $set */
    private function e1rm(?array $set): float
    {
        if (! $set || ! $set['reps']) {
            return 0.0;
        }
        $w = (float) ($set['weight_kg'] ?? 0);

        return $w > 0 ? $w * (1 + $set['reps'] / 30) : (float) $set['reps'];
    }

    private function roundLoad(float $kg): float
    {
        return round($kg * 2) / 2;
    }
}
