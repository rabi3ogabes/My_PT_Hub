<?php

namespace App\Domain\Workouts\Services;

use App\Domain\Shared\Support\ApiException;
use App\Domain\Workouts\Models\Exercise;
use App\Domain\Workouts\Models\Workout;
use App\Domain\Workouts\Models\WorkoutBlock;
use App\Domain\Workouts\Models\WorkoutExercise;
use App\Domain\Workouts\Models\WorkoutSetTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Workout builder: nested blocks → exercises → set templates written in one
 * transactional payload. Updates fully replace the tree (order = array order),
 * re-using ids the client sends back so logged sets keep their references.
 */
class WorkoutBuilder
{
    public const BLOCK_TYPES = ['straight', 'superset', 'giant', 'circuit', 'tabata', 'emom', 'amrap', 'interval', 'warmup', 'cooldown'];

    public const SET_TYPES = ['warmup', 'working', 'drop', 'failure', 'amrap'];

    public const DIFFICULTIES = ['beginner', 'intermediate', 'advanced'];

    /** Relations needed to render a full Workout. */
    public const WITH = ['blocks.exercises.sets', 'blocks.exercises.exercise'];

    /** @param array<string, mixed> $data */
    public function create(array $data, ?string $createdBy = null, ?string $tenantId = null, bool $global = false): Workout
    {
        return DB::transaction(function () use ($data, $createdBy, $tenantId, $global) {
            $workout = new Workout;
            if ($global) {
                $workout->tenant_id = null;
            } elseif ($tenantId !== null) {
                $workout->tenant_id = $tenantId;
            }
            $workout->created_by = $createdBy;
            $this->fillAttributes($workout, $data);
            $workout->save();
            $this->writeBlocks($workout, $data['blocks'] ?? [], [], $global);

            return $this->refreshSummary($workout);
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Workout $workout, array $data): Workout
    {
        $this->assertEditable($workout);

        return $this->replace($workout, $data);
    }

    /**
     * Applies attributes and (when present) fully replaces the block tree, without the
     * tenant ownership check — for platform maintenance (template seeders) and update().
     *
     * @param  array<string, mixed>  $data
     */
    public function replace(Workout $workout, array $data): Workout
    {
        return DB::transaction(function () use ($workout, $data) {
            $this->fillAttributes($workout, $data);
            if (array_key_exists('blocks', $data)) {
                $existing = $this->existingIds($workout);
                $workout->blocks()->delete(); // cascades to exercises and set templates
                $this->writeBlocks($workout, $data['blocks'] ?? [], $existing, $workout->tenant_id === null);
                $workout->version = (int) $workout->version + 1;
            }
            $workout->save();

            return $this->refreshSummary($workout);
        });
    }

    public function duplicate(Workout $source, ?string $createdBy = null, ?string $name = null): Workout
    {
        $payload = $this->export($source);
        $payload['name'] = $name ?? __('workouts.copy_of', ['name' => $source->name]);

        $copy = $this->create($payload, $createdBy);
        $copy->forceFill(['source_id' => $source->id])->save();

        return $copy;
    }

    public function assertEditable(Workout $workout): void
    {
        if (! $workout->isOwnedByCurrentTenant()) {
            throw ApiException::forbidden(__('workouts.global_workout_read_only'));
        }
    }

    /**
     * Portable JSON (exercise slugs + ids so it can be imported in another workspace).
     *
     * @return array<string, mixed>
     */
    public function export(Workout $workout): array
    {
        $workout->loadMissing(['blocks.exercises.sets', 'blocks.exercises.exercise' => fn ($q) => $q->withTrashed()]);

        return [
            'format' => 'aurum.workout.v1',
            'name' => $workout->name,
            'description' => $workout->description,
            'difficulty' => $workout->difficulty,
            'tags' => $workout->tags ?? [],
            'blocks' => $workout->blocks->map(fn (WorkoutBlock $b) => [
                'type' => $b->type,
                'name' => $b->name,
                'rounds' => $b->rounds,
                'work_seconds' => $b->work_seconds,
                'rest_seconds' => $b->rest_seconds,
                'rest_between_rounds' => $b->rest_between_rounds,
                'time_cap_seconds' => $b->time_cap_seconds,
                'notes' => $b->notes,
                'exercises' => $b->exercises->map(fn (WorkoutExercise $e) => [
                    'exercise_id' => $e->exercise_id,
                    'exercise_slug' => $e->exercise?->slug,
                    'rest_seconds' => $e->rest_seconds,
                    'tempo' => $e->tempo,
                    'notes' => $e->notes,
                    'alternative_ids' => $e->alternative_ids ?? [],
                    'progression_rule' => $e->progression_rule,
                    'sets' => $e->sets->map(fn (WorkoutSetTemplate $s) => [
                        'set_type' => $s->set_type,
                        'reps_min' => $s->reps_min,
                        'reps_max' => $s->reps_max,
                        'weight_kg' => $s->weight_kg,
                        'percent_1rm' => $s->percent_1rm,
                        'rpe' => $s->rpe,
                        'rir' => $s->rir,
                        'duration_seconds' => $s->duration_seconds,
                        'distance_m' => $s->distance_m,
                        'rest_seconds' => $s->rest_seconds,
                    ])->values()->all(),
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    /**
     * Resolves exercise references in an exported payload (id when visible, else slug).
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function resolveImport(array $payload): array
    {
        $missing = [];
        foreach ($payload['blocks'] ?? [] as $bi => $block) {
            foreach ($block['exercises'] ?? [] as $ei => $ex) {
                $id = $ex['exercise_id'] ?? null;
                $found = $id ? Exercise::query()->whereKey($id)->value('id') : null;
                if (! $found && ! empty($ex['exercise_slug'])) {
                    $found = Exercise::query()->where('slug', $ex['exercise_slug'])
                        ->orderByRaw('tenant_id is null')->value('id');
                }
                if (! $found) {
                    $missing["blocks.$bi.exercises.$ei.exercise_id"] = [__('workouts.exercise_not_found')];
                }
                $payload['blocks'][$bi]['exercises'][$ei]['exercise_id'] = $found;
                unset($payload['blocks'][$bi]['exercises'][$ei]['exercise_slug']);
            }
        }
        if ($missing !== []) {
            throw ApiException::invalid('validation_failed', __('api.validation_failed'), $missing);
        }

        return $payload;
    }

    /** Recomputes estimated minutes and equipment summary from the stored tree. */
    public function refreshSummary(Workout $workout): Workout
    {
        $workout->unsetRelation('blocks');
        $workout->load(['blocks.exercises.sets', 'blocks.exercises.exercise' => fn ($q) => $q->withTrashed()]);
        $workout->estimated_minutes = $this->estimateMinutes($workout);
        $workout->equipment_summary = $this->equipmentSummary($workout);
        $workout->save();

        return $workout;
    }

    public function estimateMinutes(Workout $workout): int
    {
        $seconds = 0;
        foreach ($workout->blocks as $block) {
            $seconds += $this->blockSeconds($block) + 30; // transition / setup between blocks
        }

        return $workout->blocks->isEmpty() ? 0 : max(1, (int) ceil($seconds / 60));
    }

    /** @return list<string> */
    public function equipmentSummary(Workout $workout): array
    {
        return $workout->blocks
            ->flatMap(fn (WorkoutBlock $b) => $b->exercises->flatMap(fn (WorkoutExercise $e) => $e->exercise?->equipment ?? []))
            ->filter(fn ($k) => is_string($k) && $k !== '' && $k !== 'bodyweight')
            ->unique()->values()->all();
    }

    public function blockSeconds(WorkoutBlock $block): int
    {
        $exercises = $block->exercises;
        $count = max(1, $exercises->count());

        switch ($block->type) {
            case 'tabata':
                return max(1, (int) $block->rounds ?: 8) * ((int) ($block->work_seconds ?? 20) + (int) ($block->rest_seconds ?? 10)) + (int) ($block->rest_between_rounds ?? 0);
            case 'emom':
                return max(1, (int) $block->rounds) * (int) ($block->work_seconds ?: 60);
            case 'amrap':
                return (int) ($block->time_cap_seconds ?: ($block->work_seconds ?: 600));
            case 'interval':
                return max(1, (int) $block->rounds) * ((int) ($block->work_seconds ?? 30) + (int) ($block->rest_seconds ?? 30)) * ($exercises->count() > 1 ? $count : 1);
            case 'superset':
            case 'giant':
            case 'circuit':
                $rounds = max((int) $block->rounds, (int) $exercises->max(fn (WorkoutExercise $e) => $e->sets->count()));
                $rounds = max(1, $rounds);
                $total = 0;
                for ($r = 0; $r < $rounds; $r++) {
                    foreach ($exercises as $e) {
                        $set = $e->sets->get($r) ?? $e->sets->last();
                        $total += $this->setWorkSeconds($set, $block->work_seconds);
                    }
                    $total += (int) ($block->rest_seconds ?? 15) * ($count - 1);
                    if ($r < $rounds - 1) {
                        $total += (int) ($block->rest_between_rounds ?? 90);
                    }
                }

                return $total;
            default: // straight, warmup, cooldown
                $default = in_array($block->type, ['warmup', 'cooldown'], true) ? 20 : 90;
                $total = 0;
                foreach ($exercises as $e) {
                    $sets = $e->sets->isEmpty() ? collect([null]) : $e->sets;
                    $rounds = max(1, (int) $block->rounds);
                    foreach ($sets as $i => $set) {
                        $rest = (int) ($set?->rest_seconds ?? $e->rest_seconds ?? $block->rest_seconds ?? $default);
                        $total += ($this->setWorkSeconds($set, $block->work_seconds) + $rest) * $rounds;
                    }
                }

                return $total;
        }
    }

    private function setWorkSeconds(?WorkoutSetTemplate $set, ?int $blockWork): int
    {
        if ($set?->duration_seconds) {
            return (int) $set->duration_seconds;
        }
        if ($set?->distance_m) {
            return (int) ceil($set->distance_m / 3); // ~3 m/s mixed effort
        }
        if ($blockWork) {
            return (int) $blockWork;
        }
        $reps = $set ? (int) round((($set->reps_min ?? $set->reps_max ?? 10) + ($set->reps_max ?? $set->reps_min ?? 10)) / 2) : 10;

        return 10 + $reps * 3; // setup + ~3 s per rep
    }

    /** @param array<string, mixed> $data */
    private function fillAttributes(Workout $workout, array $data): void
    {
        foreach (['name', 'description', 'difficulty', 'tags', 'is_template'] as $field) {
            if (array_key_exists($field, $data)) {
                $workout->{$field} = $data[$field];
            }
        }
        $workout->difficulty ??= 'intermediate';
        $workout->tags ??= [];
    }

    /** @return array{blocks: array<string, true>, exercises: array<string, true>, sets: array<string, true>} */
    private function existingIds(Workout $workout): array
    {
        $blockIds = WorkoutBlock::query()->where('workout_id', $workout->id)->pluck('id');
        $exerciseIds = WorkoutExercise::query()->whereIn('workout_block_id', $blockIds)->pluck('id');
        $setIds = WorkoutSetTemplate::query()->whereIn('workout_exercise_id', $exerciseIds)->pluck('id');

        return [
            'blocks' => array_fill_keys($blockIds->all(), true),
            'exercises' => array_fill_keys($exerciseIds->all(), true),
            'sets' => array_fill_keys($setIds->all(), true),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $blocks
     * @param  array<string, array<string, true>>  $existing
     */
    private function writeBlocks(Workout $workout, array $blocks, array $existing, bool $global = false): void
    {
        $exerciseIds = collect($blocks)->flatMap(fn ($b) => collect($b['exercises'] ?? [])->pluck('exercise_id'))->filter()->unique()->values();
        $query = Exercise::query()->withTrashed()->whereIn('id', $exerciseIds);
        if ($global) {
            $query->withoutGlobalScopes()->whereNull('tenant_id');
        }
        $visible = $query->pluck('id')->flip();
        foreach ($blocks as $bi => $block) {
            foreach ($block['exercises'] ?? [] as $ei => $ex) {
                if (! isset($visible[$ex['exercise_id'] ?? ''])) {
                    throw ApiException::invalid('validation_failed', __('api.validation_failed'), [
                        "blocks.$bi.exercises.$ei.exercise_id" => [__('workouts.exercise_not_found')],
                    ]);
                }
            }
        }

        $reuse = fn (string $level, mixed $id) => is_string($id) && isset($existing[$level][$id]) ? $id : strtolower((string) Str::ulid());

        $setRows = [];
        foreach (array_values($blocks) as $bi => $block) {
            $b = new WorkoutBlock([
                'workout_id' => $workout->id,
                'position' => $bi + 1,
                'type' => $block['type'] ?? 'straight',
                'name' => $block['name'] ?? null,
                'rounds' => max(1, (int) ($block['rounds'] ?? 1)),
                'work_seconds' => $block['work_seconds'] ?? null,
                'rest_seconds' => $block['rest_seconds'] ?? null,
                'rest_between_rounds' => $block['rest_between_rounds'] ?? null,
                'time_cap_seconds' => $block['time_cap_seconds'] ?? null,
                'notes' => $block['notes'] ?? null,
            ]);
            $b->id = $reuse('blocks', $block['id'] ?? null);
            $b->save();

            foreach (array_values($block['exercises'] ?? []) as $ei => $ex) {
                $e = new WorkoutExercise([
                    'workout_block_id' => $b->id,
                    'exercise_id' => $ex['exercise_id'],
                    'position' => $ei + 1,
                    'rest_seconds' => $ex['rest_seconds'] ?? null,
                    'tempo' => $ex['tempo'] ?? null,
                    'notes' => $ex['notes'] ?? null,
                    'alternative_ids' => array_values($ex['alternative_ids'] ?? []),
                    'progression_rule' => $ex['progression_rule'] ?? null,
                ]);
                $e->id = $reuse('exercises', $ex['id'] ?? null);
                $e->save();

                foreach (array_values($ex['sets'] ?? []) as $si => $set) {
                    $min = $set['reps_min'] ?? null;
                    $max = $set['reps_max'] ?? $min;
                    if ($min !== null && $max !== null && $max < $min) {
                        [$min, $max] = [$max, $min];
                    }
                    $setRows[] = [
                        'id' => $reuse('sets', $set['id'] ?? null),
                        'workout_exercise_id' => $e->id,
                        'position' => $si + 1,
                        'set_type' => $set['set_type'] ?? 'working',
                        'reps_min' => $min,
                        'reps_max' => $max,
                        'weight_kg' => $set['weight_kg'] ?? null,
                        'percent_1rm' => $set['percent_1rm'] ?? null,
                        'rpe' => $set['rpe'] ?? null,
                        'rir' => $set['rir'] ?? null,
                        'duration_seconds' => $set['duration_seconds'] ?? null,
                        'distance_m' => $set['distance_m'] ?? null,
                        'rest_seconds' => $set['rest_seconds'] ?? null,
                    ];
                }
            }
        }
        foreach (array_chunk($setRows, 200) as $chunk) {
            WorkoutSetTemplate::query()->insert($chunk);
        }
    }
}
