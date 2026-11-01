<?php

namespace App\Domain\Habits\Services;

use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Habits\Models\HabitLog;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Streaks and completion rates for habit assignments.
 *
 * All arithmetic is done on local calendar dates (Y-m-d strings at UTC midnight),
 * so DST transitions in the user's timezone never add or drop a day. "Today" is
 * resolved in the user's timezone. Rules:
 *  - daily: every day counts.
 *  - days: only the listed ISO weekdays count; other days are skipped (never break a streak).
 *  - per_week: streak unit is ISO weeks; a week succeeds with ≥ per_week completions
 *    (pro-rated for the first partial week).
 *  - today (or the current week) still pending never breaks a streak.
 */
final class StreakService
{
    public function today(?string $timezone, ?CarbonInterface $now = null): string
    {
        $now ??= CarbonImmutable::now();

        return CarbonImmutable::instance($now)->setTimezone($timezone ?: 'UTC')->toDateString();
    }

    public function isScheduled(HabitAssignment $assignment, string $date): bool
    {
        if (! $this->inRange($assignment, $date)) {
            return false;
        }

        return match ($assignment->schedule) {
            'days' => in_array($this->d($date)->dayOfWeekIso, array_map('intval', $assignment->days ?? []), true),
            default => true,
        };
    }

    /**
     * Whether the habit should be shown as "to do" today.
     *
     * @param  list<string>  $completedDates
     */
    public function scheduledToday(HabitAssignment $assignment, array $completedDates, string $today): bool
    {
        if ($assignment->schedule !== 'per_week') {
            return $this->isScheduled($assignment, $today);
        }
        if (! $this->inRange($assignment, $today)) {
            return false;
        }
        $done = $this->completionsInWeek($assignment, $completedDates, $today);

        return in_array($today, $completedDates, true) || $done < (int) ($assignment->per_week ?: 1);
    }

    /**
     * @param  list<string>  $completedDates  local dates (Y-m-d) with a completed log
     * @return array{current: int, best: int}
     */
    public function streak(HabitAssignment $assignment, array $completedDates, string $today): array
    {
        return $assignment->schedule === 'per_week'
            ? $this->weeklyStreak($assignment, $completedDates, $today)
            : $this->dailyStreak($assignment, $completedDates, $today);
    }

    /**
     * Completion % over the last $days days (today only counts once completed).
     *
     * @param  list<string>  $completedDates
     */
    public function completionPct(HabitAssignment $assignment, array $completedDates, string $today, int $days = 30): int
    {
        $done = array_flip($completedDates);
        $end = $this->d($today);
        $start = $end->subDays($days - 1);
        $assignmentStart = $this->d($assignment->start_date->toDateString());
        if ($start->lt($assignmentStart)) {
            $start = $assignmentStart;
        }
        if ($assignment->end_date && $end->gt($this->d($assignment->end_date->toDateString()))) {
            $end = $this->d($assignment->end_date->toDateString());
        }
        if ($start->gt($end)) {
            return 0;
        }

        if ($assignment->schedule === 'per_week') {
            $count = 0;
            $windowDays = 0;
            for ($d = $start; $d->lte($end); $d = $d->addDay()) {
                $key = $d->toDateString();
                if ($key === $today && ! isset($done[$key])) {
                    continue;
                }
                $windowDays++;
                $count += isset($done[$key]) ? 1 : 0;
            }
            $expected = max(1, (int) round(((int) ($assignment->per_week ?: 1)) * $windowDays / 7));

            return $windowDays === 0 ? 0 : (int) min(100, round($count / $expected * 100));
        }

        $scheduled = 0;
        $completed = 0;
        for ($d = $start; $d->lte($end); $d = $d->addDay()) {
            $key = $d->toDateString();
            if (! $this->isScheduled($assignment, $key)) {
                continue;
            }
            if ($key === $today && ! isset($done[$key])) {
                continue; // still pending
            }
            $scheduled++;
            $completed += isset($done[$key]) ? 1 : 0;
        }

        return $scheduled === 0 ? 0 : (int) round($completed / $scheduled * 100);
    }

    /**
     * Full summary for an assignment (loads its completed logs).
     *
     * @return array{streak: array{current: int, best: int}, completion_pct_30d: int, scheduled_today: bool}
     */
    public function summary(HabitAssignment $assignment, string $today): array
    {
        $dates = $this->completedDates($assignment, $today);

        return [
            'streak' => $this->streak($assignment, $dates, $today),
            'completion_pct_30d' => $this->completionPct($assignment, $dates, $today),
            'scheduled_today' => $this->scheduledToday($assignment, $dates, $today),
        ];
    }

    /** @return list<string> */
    public function completedDates(HabitAssignment $assignment, string $today): array
    {
        $logs = $assignment->relationLoaded('logs')
            ? $assignment->logs
            : HabitLog::query()->where('habit_assignment_id', $assignment->id)->whereDate('log_date', '<=', $today)->get();

        return $logs->where('completed', true)
            ->map(fn (HabitLog $l) => $l->log_date->toDateString())
            ->filter(fn (string $d) => $d <= $today)
            ->unique()->sort()->values()->all();
    }

    /** @param list<string> $completedDates */
    private function dailyStreak(HabitAssignment $assignment, array $completedDates, string $today): array
    {
        $done = array_flip($completedDates);
        $start = $this->d($assignment->start_date->toDateString());
        $end = $this->d($today);
        if ($assignment->end_date && $end->gt($this->d($assignment->end_date->toDateString()))) {
            $end = $this->d($assignment->end_date->toDateString());
        }

        $run = 0;
        $best = 0;
        for ($d = $start; $d->lte($end); $d = $d->addDay()) {
            $key = $d->toDateString();
            if (! $this->isScheduled($assignment, $key)) {
                continue; // non-scheduled days are skipped
            }
            if (isset($done[$key])) {
                $run++;
                $best = max($best, $run);
            } elseif ($key !== $today) {
                $run = 0;
            }
        }

        return ['current' => $run, 'best' => $best];
    }

    /** @param list<string> $completedDates */
    private function weeklyStreak(HabitAssignment $assignment, array $completedDates, string $today): array
    {
        $perWeek = max(1, (int) ($assignment->per_week ?: 1));
        $start = $this->d($assignment->start_date->toDateString());
        $end = $this->d($today);
        if ($assignment->end_date && $end->gt($this->d($assignment->end_date->toDateString()))) {
            $end = $this->d($assignment->end_date->toDateString());
        }
        $currentWeek = $this->d($today)->startOfWeek(CarbonInterface::MONDAY);

        $run = 0;
        $best = 0;
        for ($week = $start->startOfWeek(CarbonInterface::MONDAY); $week->lte($end); $week = $week->addWeek()) {
            $from = $week->lt($start) ? $start : $week;
            $to = $week->addDays(6)->gt($end) ? $end : $week->addDays(6);
            $available = (int) $from->diffInDays($week->addDays(6)) + 1;
            $required = min($perWeek, $available);
            $count = 0;
            foreach ($completedDates as $date) {
                if ($date >= $from->toDateString() && $date <= $to->toDateString()) {
                    $count++;
                }
            }
            if ($count >= $required) {
                $run++;
                $best = max($best, $run);
            } elseif (! $week->equalTo($currentWeek)) {
                $run = 0;
            }
        }

        return ['current' => $run, 'best' => $best];
    }

    /** @param list<string> $completedDates */
    private function completionsInWeek(HabitAssignment $assignment, array $completedDates, string $date): int
    {
        $week = $this->d($date)->startOfWeek(CarbonInterface::MONDAY);
        $from = $week->toDateString();
        $to = $week->addDays(6)->toDateString();

        return count(array_filter($completedDates, fn ($d) => $d >= $from && $d <= $to));
    }

    private function inRange(HabitAssignment $assignment, string $date): bool
    {
        if ($date < $assignment->start_date->toDateString()) {
            return false;
        }

        return ! ($assignment->end_date && $date > $assignment->end_date->toDateString());
    }

    private function d(string $date): CarbonImmutable
    {
        return CarbonImmutable::parse($date, 'UTC')->startOfDay();
    }
}
