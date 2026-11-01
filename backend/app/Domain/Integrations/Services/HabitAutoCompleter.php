<?php

namespace App\Domain\Integrations\Services;

use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Habits\Models\HabitLog;
use App\Domain\Identity\Models\User;
use Carbon\CarbonImmutable;

/**
 * Completes habits backed by a health metric ("10k steps", "8h sleep",
 * "2.5 L water") from the day's normalized values. Manual completions are
 * never overwritten; health-sourced logs follow the latest data.
 */
final class HabitAutoCompleter
{
    /** Habit health_metric → health_daily type. */
    public const METRICS = ['steps' => 'steps', 'sleep_minutes' => 'sleep_minutes', 'water_ml' => 'water_ml'];

    /**
     * @param  array<string, float>  $values  health_daily values for the date
     * @return int number of habit logs written
     */
    public function apply(User $user, string $date, array $values): int
    {
        $metrics = array_intersect_key(self::METRICS, array_filter($values, fn ($v, $type) => in_array($type, self::METRICS, true), ARRAY_FILTER_USE_BOTH));
        if ($metrics === []) {
            return 0;
        }

        $day = CarbonImmutable::parse($date);
        $assignments = HabitAssignment::withoutGlobalScopes()
            ->with(['habit' => fn ($q) => $q->withoutGlobalScopes()])
            ->where('client_id', $user->id)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $date)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $date))
            ->whereHas('habit', fn ($q) => $q->withoutGlobalScopes()->whereIn('health_metric', array_keys($metrics)))
            ->get();

        $written = 0;
        foreach ($assignments as $assignment) {
            $metric = (string) $assignment->habit->health_metric;
            $value = $values[self::METRICS[$metric]] ?? null;
            $target = $assignment->target ?? $assignment->habit->target;
            if ($value === null || $target === null || $target <= 0 || ! $this->scheduledOn($assignment, $day)) {
                continue;
            }

            $log = HabitLog::query()->where('habit_assignment_id', $assignment->id)->whereDate('log_date', $date)->first();
            if ($log && $log->source !== 'health' && $log->completed) {
                continue;
            }

            $completed = $value >= $target;
            if (! $log && ! $completed) {
                continue;
            }

            $log ??= new HabitLog(['habit_assignment_id' => $assignment->id, 'log_date' => $date]);
            $log->fill(['value' => round($value, 2), 'completed' => $completed, 'source' => 'health'])->save();
            $written++;
        }

        return $written;
    }

    private function scheduledOn(HabitAssignment $assignment, CarbonImmutable $day): bool
    {
        return match ($assignment->schedule) {
            'days' => in_array($day->isoWeekday(), array_map('intval', $assignment->days ?? []), true),
            default => true, // daily, per_week (any day counts toward the weekly target)
        };
    }
}
