<?php

namespace App\Http\Api\V1\Client;

use App\Domain\Workouts\Models\Workout;
use App\Domain\Workouts\Models\WorkoutBlock;
use App\Domain\Workouts\Models\WorkoutExercise;
use App\Domain\Workouts\Models\WorkoutSetTemplate;
use App\Domain\Workouts\Services\ClientProgram;
use App\Domain\Workouts\Services\OneRepMax;
use App\Domain\Workouts\Services\PerformanceHistory;
use App\Domain\Workouts\Services\ProgramBuilder;
use App\Domain\Workouts\Services\ProgramSchedule;
use App\Domain\Workouts\Services\ProgressionEngine;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Workouts\ProgramAssignmentResource;
use App\Http\Api\V1\Resources\Workouts\ProgramResource;
use App\Http\Api\V1\Resources\Workouts\SetTemplateResource;
use App\Http\Api\V1\Resources\Workouts\WorkoutResource;
use App\Domain\Workouts\Models\PersonalRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientWorkoutController extends ApiController
{
    public function __construct(
        private readonly ClientProgram $program,
        private readonly ProgramSchedule $schedule,
        private readonly PerformanceHistory $history,
        private readonly ProgressionEngine $progression,
    ) {}

    /** GET /me/program → {assignment, program, unlocked_through} | null */
    public function program(Request $request): JsonResponse
    {
        $user = $this->user();
        $assignment = $this->program->currentAssignment($user);
        if (! $assignment) {
            return $this->ok(null);
        }
        $assignment->load(['program' => fn ($q) => $q->withTrashed()->with(ProgramBuilder::WITH)]);
        $today = ProgramSchedule::today($user);

        return $this->ok([
            'assignment' => (new ProgramAssignmentResource($assignment))->toArray($request),
            'program' => (new ProgramResource($assignment->program))->toArray($request),
            'unlocked_through' => $this->schedule->unlockedThrough($assignment, $today)->toDateString(),
        ]);
    }

    /** GET /me/workouts/{id} → Workout + download_size_bytes, last_performance per exercise and progressed targets. */
    public function workout(Request $request, Workout $workout): JsonResponse
    {
        $user = $this->user();
        if (! $this->program->canAccessWorkout($user, $workout)) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException;
        }
        $workout->load(['blocks.exercises.sets', 'blocks.exercises.exercise' => fn ($q) => $q->withTrashed()]);

        $exercises = $workout->blocks->flatMap(fn (WorkoutBlock $b) => $b->exercises);
        $exerciseIds = $exercises->pluck('exercise_id')->unique()->values()->all();
        $last = $this->history->lastMany($user, $exerciseIds);

        $context = $this->program->contextFor($user, $workout, ProgramSchedule::today($user));
        $oneRms = PersonalRecord::query()->where('client_id', $user->id)->where('type', 'e1rm')
            ->whereIn('exercise_id', $exerciseIds)->selectRaw('exercise_id, max(value) as best')->groupBy('exercise_id')->pluck('best', 'exercise_id');

        $prescribed = [];
        foreach ($exercises as $we) {
            /** @var WorkoutExercise $we */
            if (! $we->progression_rule || empty($we->progression_rule['type'])) {
                continue;
            }
            $targets = $we->sets->map(fn (WorkoutSetTemplate $s) => SetTemplateResource::shape($s))->values()->all();
            $performed = $last[$we->exercise_id]['sets'] ?? [];
            $oneRm = isset($oneRms[$we->exercise_id]) ? (float) $oneRms[$we->exercise_id] : null;
            $prescribed[$we->id] = $this->progression->prescribe($we->progression_rule, $targets, $performed, $context['week_number'], $context['is_deload'], $oneRm);
        }
        // Resolve %1RM targets to loads where the client has an estimated max.
        foreach ($exercises as $we) {
            $oneRm = isset($oneRms[$we->exercise_id]) ? (float) $oneRms[$we->exercise_id] : null;
            if ($oneRm === null) {
                continue;
            }
            $sets = $prescribed[$we->id] ?? $we->sets->map(fn (WorkoutSetTemplate $s) => SetTemplateResource::shape($s))->values()->all();
            $changed = false;
            foreach ($sets as $i => $s) {
                if (($s['weight_kg'] ?? null) === null && ($s['percent_1rm'] ?? null) !== null) {
                    $sets[$i]['weight_kg'] = OneRepMax::percentOf($oneRm, (float) $s['percent_1rm']);
                    $changed = true;
                }
            }
            if ($changed) {
                $prescribed[$we->id] = $sets;
            }
        }

        $size = (int) $exercises->pluck('exercise')->filter()->unique('id')
            ->sum(fn ($e) => in_array($e->video_provider, ['hls', 'upload'], true) ? (int) $e->video_size_bytes : 0);

        return $this->ok((new WorkoutResource($workout))->forClient($last, $prescribed, ['download_size_bytes' => $size])->toArray($request));
    }
}
