<?php

namespace App\Domain\Workouts\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Workouts\Models\ProgramAssignment;
use App\Domain\Workouts\Models\ProgramDay;
use App\Domain\Workouts\Models\Workout;
use App\Domain\Workouts\Models\WorkoutBlock;
use App\Domain\Workouts\Models\WorkoutExercise;
use App\Domain\Workouts\Models\WorkoutSession;
use Carbon\CarbonImmutable;

/** The client's current program and the workout scheduled for a given day. */
class ClientProgram
{
    public function __construct(
        private readonly ProgramSchedule $schedule,
        private readonly ProgramAssigner $assigner,
    ) {}

    public function currentAssignment(User $client): ?ProgramAssignment
    {
        $assignment = ProgramAssignment::query()
            ->where('client_id', $client->id)
            ->whereIn('status', ['active', 'paused', 'scheduled'])
            ->whereHas('program')
            ->orderByRaw("case status when 'active' then 0 when 'paused' then 1 else 2 end")
            ->latest('start_date')->latest('id')
            ->first();
        if (! $assignment) {
            return null;
        }
        $assignment->setRelation('client', $client);

        return $this->assigner->refreshStatus($assignment);
    }

    /**
     * TodayWorkout payload for a date, or null when nothing is scheduled.
     *
     * @return array<string, mixed>|null
     */
    public function todayWorkout(User $client, CarbonImmutable $date): ?array
    {
        $assignment = $this->currentAssignment($client);
        if (! $assignment || in_array($assignment->status, ['completed', 'cancelled'], true)) {
            return null;
        }
        $day = $this->schedule->dayFor($assignment, $date);
        if (! $day || ! $day->workout_id || $day->is_rest || ! $day->workout) {
            return null;
        }
        $position = $this->schedule->position($assignment, $date);
        /** @var Workout $workout */
        $workout = $day->workout;
        $exerciseCount = WorkoutExercise::query()
            ->whereIn('workout_block_id', WorkoutBlock::query()->where('workout_id', $workout->id)->select('id'))->count();

        $tz = $client->timezone ?: 'UTC';
        $session = WorkoutSession::query()
            ->where('client_id', $client->id)
            ->where('program_day_id', $day->id)
            ->where('status', '!=', 'abandoned')
            ->where('started_at', '>=', $date->setTimezone($tz)->startOfDay()->utc())
            ->orderByRaw("case status when 'completed' then 0 else 1 end")
            ->latest('started_at')
            ->first();
        // A completed session for this program day (on any date) also counts as done.
        $session ??= WorkoutSession::query()->where('client_id', $client->id)->where('program_day_id', $day->id)
            ->where('program_assignment_id', $assignment->id)->where('status', 'completed')->latest('started_at')->first();

        return [
            'workout_id' => $workout->id,
            'program_day_id' => $day->id,
            'program_assignment_id' => $assignment->id,
            'name' => $day->title ?: $workout->name,
            'estimated_minutes' => (int) $workout->estimated_minutes,
            'exercise_count' => $exerciseCount,
            'week_number' => $position['week_number'] ?? null,
            'day_number' => $position['day_number'] ?? null,
            'status' => $session ? ($session->status === 'completed' ? 'completed' : 'in_progress') : 'pending',
            'session_id' => $session?->id,
        ];
    }

    /** Whether the client may open this workout (global, in one of their programs, or logged before). */
    public function canAccessWorkout(User $client, Workout $workout): bool
    {
        if ($workout->tenant_id === null) {
            return true;
        }
        $programIds = ProgramAssignment::query()->where('client_id', $client->id)
            ->whereNotIn('status', ['cancelled'])->pluck('program_id');
        if ($programIds->isNotEmpty() && ProgramDay::query()->where('workout_id', $workout->id)
            ->whereHas('week', fn ($q) => $q->whereIn('program_id', $programIds))->exists()) {
            return true;
        }

        return WorkoutSession::query()->where('client_id', $client->id)->where('workout_id', $workout->id)->exists();
    }

    /** The program day/week context in which the client will do this workout (for progression). */
    public function contextFor(User $client, Workout $workout, CarbonImmutable $today): array
    {
        $assignment = $this->currentAssignment($client);
        if (! $assignment) {
            return ['week_number' => 1, 'is_deload' => false];
        }
        $assignment->loadMissing('program.weeks');
        $week = $this->schedule->currentWeek($assignment, $today) ?: 1;
        $isDeload = (bool) $assignment->program?->weeks->firstWhere('number', $week)?->is_deload;

        return ['week_number' => $week, 'is_deload' => $isDeload];
    }
}
