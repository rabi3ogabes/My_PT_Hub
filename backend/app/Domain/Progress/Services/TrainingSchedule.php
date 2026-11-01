<?php

namespace App\Domain\Progress\Services;

use App\Domain\Workouts\Models\ProgramAssignment;
use App\Domain\Workouts\Models\ProgramWeek;
use Carbon\CarbonImmutable;

/**
 * Maps a client's program assignments onto calendar dates: program day N of
 * week W falls on start_date + shift_days + (W−1)×7 + (N−1).
 */
final class TrainingSchedule
{
    /**
     * Planned workout days between two dates (inclusive): [date => program_day_id].
     *
     * @return array<string, list<string>>
     */
    public function plannedWorkouts(string $clientId, string $from, string $to): array
    {
        $assignments = ProgramAssignment::query()
            ->where('client_id', $clientId)
            ->whereIn('status', ['active', 'completed', 'paused'])
            ->whereDate('start_date', '<=', $to)
            ->get();
        if ($assignments->isEmpty()) {
            return [];
        }

        $weeks = ProgramWeek::query()
            ->whereIn('program_id', $assignments->pluck('program_id')->unique()->all())
            ->with('days')
            ->get()
            ->groupBy('program_id');

        $fromDate = CarbonImmutable::parse($from)->startOfDay();
        $toDate = CarbonImmutable::parse($to)->startOfDay();
        $out = [];
        foreach ($assignments as $assignment) {
            $start = CarbonImmutable::parse($assignment->start_date->toDateString())->addDays((int) $assignment->shift_days);
            $pausedAt = $assignment->paused_at ? CarbonImmutable::parse($assignment->paused_at->toDateString()) : null;
            foreach ($weeks[$assignment->program_id] ?? [] as $week) {
                foreach ($week->days as $day) {
                    if ($day->is_rest || ! $day->workout_id) {
                        continue;
                    }
                    $date = $start->addDays(((int) $week->number - 1) * 7 + ((int) $day->day_number - 1));
                    if ($date->lt($fromDate) || $date->gt($toDate)) {
                        continue;
                    }
                    if ($assignment->status === 'paused' && $pausedAt && $date->gte($pausedAt)) {
                        continue;
                    }
                    $out[$date->toDateString()][] = $day->id;
                }
            }
        }
        ksort($out);

        return $out;
    }

    public function isTrainingDay(string $clientId, string $date): bool
    {
        return $this->plannedWorkouts($clientId, $date, $date) !== [];
    }
}
