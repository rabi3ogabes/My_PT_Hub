<?php

namespace App\Domain\Workouts\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Workouts\Models\ProgramAssignment;
use App\Domain\Workouts\Models\ProgramDay;
use App\Domain\Workouts\Models\ProgramWeek;
use Carbon\CarbonImmutable;

/**
 * Calendar maths for program assignments. Week N, day D falls on
 *   start_date + shift_days + (N − 1) × 7 + (D − 1)
 * While paused, the schedule is frozen on paused_at (resuming adds the paused
 * days to shift_days).
 */
class ProgramSchedule
{
    /** The client's local calendar date. */
    public static function today(?User $user = null): CarbonImmutable
    {
        $tz = $user?->timezone ?: (app(TenantContext::class)->get()?->timezone ?: 'UTC');

        return CarbonImmutable::now($tz)->startOfDay();
    }

    /**
     * Zero-based day offset into the program for a date (negative = not started yet).
     */
    public function dayIndex(ProgramAssignment $assignment, CarbonImmutable|string $date): int
    {
        $date = $date instanceof CarbonImmutable ? $date->startOfDay() : CarbonImmutable::parse($date)->startOfDay();
        if ($assignment->status === 'paused' && $assignment->paused_at !== null) {
            $paused = CarbonImmutable::parse($assignment->paused_at->toDateString());
            if ($date->greaterThan($paused)) {
                $date = $paused;
            }
        }
        $start = CarbonImmutable::parse($assignment->start_date->toDateString())->addDays((int) $assignment->shift_days);

        return (int) $start->diffInDays($date, false);
    }

    /**
     * @return array{week_number: int, day_number: int}|null null when outside the program
     */
    public function position(ProgramAssignment $assignment, CarbonImmutable|string $date, ?int $weeksCount = null): ?array
    {
        $index = $this->dayIndex($assignment, $date);
        if ($index < 0) {
            return null;
        }
        $week = intdiv($index, 7) + 1;
        $weeksCount ??= $this->weeksCount($assignment);
        if ($week > $weeksCount) {
            return null;
        }

        return ['week_number' => $week, 'day_number' => ($index % 7) + 1];
    }

    /** Calendar date of a program week/day for this assignment. */
    public function dateFor(ProgramAssignment $assignment, int $weekNumber, int $dayNumber): CarbonImmutable
    {
        return CarbonImmutable::parse($assignment->start_date->toDateString())
            ->addDays((int) $assignment->shift_days + ($weekNumber - 1) * 7 + ($dayNumber - 1));
    }

    /** The program day scheduled for a date (first non-rest day with a workout, else first day row). */
    public function dayFor(ProgramAssignment $assignment, CarbonImmutable|string $date): ?ProgramDay
    {
        $pos = $this->position($assignment, $date);
        if ($pos === null) {
            return null;
        }
        $days = ProgramDay::query()
            ->whereHas('week', fn ($q) => $q->where('program_id', $assignment->program_id)->where('number', $pos['week_number']))
            ->where('day_number', $pos['day_number'])
            ->with('workout')
            ->get();

        return $days->first(fn (ProgramDay $d) => $d->workout_id && ! $d->is_rest) ?? $days->first();
    }

    /** Last date the client may open content for (unlock mode). */
    public function unlockedThrough(ProgramAssignment $assignment, CarbonImmutable|string $today): CarbonImmutable
    {
        $today = $today instanceof CarbonImmutable ? $today : CarbonImmutable::parse($today);
        $weeks = max(1, $this->weeksCount($assignment));
        $programEnd = $this->dateFor($assignment, $weeks, 7);
        $index = $this->dayIndex($assignment, $today);

        return match ($assignment->unlock_mode) {
            'daily' => $index < 0 ? $this->dateFor($assignment, 1, 1)->subDay() : $this->dateFor($assignment, 1, 1)->addDays(min($index, $weeks * 7 - 1)),
            'weekly_drip' => $index < 0 ? $this->dateFor($assignment, 1, 1)->subDay() : $this->dateFor($assignment, min(intdiv($index, 7) + 1, $weeks), 7),
            default => $programEnd,
        };
    }

    public function isUnlocked(ProgramAssignment $assignment, int $weekNumber, int $dayNumber, CarbonImmutable|string $today): bool
    {
        return $this->dateFor($assignment, $weekNumber, $dayNumber)->lessThanOrEqualTo($this->unlockedThrough($assignment, $today));
    }

    /** Current week (1-based, clamped to the program length; 0 before start). */
    public function currentWeek(ProgramAssignment $assignment, CarbonImmutable|string $today): int
    {
        $index = $this->dayIndex($assignment, $today);
        if ($index < 0) {
            return 0;
        }

        return min(intdiv($index, 7) + 1, max(1, $this->weeksCount($assignment)));
    }

    /** Percentage of scheduled workouts the client has completed. */
    public function progressPct(ProgramAssignment $assignment): int
    {
        $planned = ProgramDay::query()
            ->whereHas('week', fn ($q) => $q->where('program_id', $assignment->program_id))
            ->whereNotNull('workout_id')->where('is_rest', false)->count();
        if ($planned === 0) {
            return 0;
        }
        $done = \App\Domain\Workouts\Models\WorkoutSession::query()->withoutGlobalScopes()
            ->where('program_assignment_id', $assignment->id)->where('status', 'completed')
            ->whereNull('deleted_at')->whereNotNull('program_day_id')
            ->distinct()->count('program_day_id');

        return (int) min(100, round($done / $planned * 100));
    }

    public function weeksCount(ProgramAssignment $assignment): int
    {
        if ($assignment->relationLoaded('program') && $assignment->program?->relationLoaded('weeks')) {
            return $assignment->program->weeks->count();
        }

        return ProgramWeek::query()->where('program_id', $assignment->program_id)->count();
    }
}
