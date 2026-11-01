<?php

namespace App\Http\Api\V1\Resources\Workouts;

use App\Domain\Workouts\Models\Workout;
use App\Domain\Workouts\Models\WorkoutBlock;
use App\Domain\Workouts\Models\WorkoutExercise;
use App\Domain\Workouts\Models\WorkoutSetTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Workout → blocks → exercises → sets. Optional per-exercise extras (client
 * endpoint): last_performance and progression-adjusted set targets.
 *
 * @mixin Workout
 */
class WorkoutResource extends JsonResource
{
    /** @var array<string, mixed>|null exercise_id => LastPerformance */
    private ?array $lastPerformance = null;

    /** @var array<string, list<array<string, mixed>>> workout_exercise_id => adjusted sets */
    private array $prescribed = [];

    private array $extra = [];

    /**
     * @param  array<string, mixed>  $lastPerformance
     * @param  array<string, list<array<string, mixed>>>  $prescribed
     */
    public function forClient(array $lastPerformance, array $prescribed = [], array $extra = []): static
    {
        $this->lastPerformance = $lastPerformance;
        $this->prescribed = $prescribed;
        $this->extra = $extra;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var Workout $w */
        $w = $this->resource;

        return [
            'id' => $w->id,
            'name' => $w->name,
            'description' => $w->description,
            'difficulty' => $w->difficulty,
            'estimated_minutes' => (int) $w->estimated_minutes,
            'tags' => $w->tags ?? [],
            'equipment_summary' => $w->equipment_summary ?? [],
            'is_template' => (bool) $w->is_template,
            'is_global' => $w->tenant_id === null,
            'version' => (int) $w->version,
            'blocks' => $w->relationLoaded('blocks') ? $w->blocks->map(fn (WorkoutBlock $b) => $this->block($b, $request))->values()->all() : [],
            'updated_at' => $w->updated_at?->toIso8601String(),
            ...$this->extra,
        ];
    }

    /** @return array<string, mixed> */
    private function block(WorkoutBlock $b, Request $request): array
    {
        return [
            'id' => $b->id,
            'position' => (int) $b->position,
            'type' => $b->type,
            'name' => $b->name,
            'rounds' => (int) $b->rounds,
            'work_seconds' => $b->work_seconds !== null ? (int) $b->work_seconds : null,
            'rest_seconds' => $b->rest_seconds !== null ? (int) $b->rest_seconds : null,
            'rest_between_rounds' => $b->rest_between_rounds !== null ? (int) $b->rest_between_rounds : null,
            'time_cap_seconds' => $b->time_cap_seconds !== null ? (int) $b->time_cap_seconds : null,
            'notes' => $b->notes,
            'exercises' => $b->exercises->map(fn (WorkoutExercise $e) => $this->exercise($e, $request))->values()->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function exercise(WorkoutExercise $e, Request $request): array
    {
        $data = [
            'id' => $e->id,
            'position' => (int) $e->position,
            'exercise' => $e->exercise ? (new ExerciseResource($e->exercise))->toArray($request) : null,
            'rest_seconds' => $e->rest_seconds !== null ? (int) $e->rest_seconds : null,
            'tempo' => $e->tempo,
            'notes' => $e->notes,
            'alternative_ids' => $e->alternative_ids ?? [],
            'progression_rule' => $e->progression_rule,
            'sets' => $this->prescribed[$e->id] ?? $e->sets->map(fn (WorkoutSetTemplate $s) => SetTemplateResource::shape($s))->values()->all(),
        ];
        if ($this->lastPerformance !== null) {
            $data['last_performance'] = $this->lastPerformance[$e->exercise_id] ?? null;
        }

        return $data;
    }
}
