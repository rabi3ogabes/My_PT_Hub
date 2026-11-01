<?php

namespace App\Domain\Habits\Services;

use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Habits\Models\HabitLog;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Collection;

/** Builds the client habit checklist and records habit logs. */
final class HabitService
{
    public function __construct(private readonly StreakService $streaks) {}

    /** @return Collection<int, HabitAssignment> */
    public function activeFor(string $clientId, string $date): Collection
    {
        return HabitAssignment::query()
            ->where('client_id', $clientId)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $date)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $date))
            ->with(['habit', 'logs'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Contract item for GET /me/habits.
     *
     * @return array<string, mixed>
     */
    public function item(HabitAssignment $assignment, string $date, ?string $today = null): array
    {
        $today ??= $date;
        $reference = $date < $today ? $date : $today;
        $dates = $this->streaks->completedDates($assignment, $reference);
        $log = $assignment->logs->first(fn (HabitLog $l) => $l->log_date->toDateString() === $date);
        $habit = $assignment->habit;

        return [
            'assignment_id' => $assignment->id,
            'habit' => [
                'id' => $habit?->id,
                'name' => $habit?->translate('name'),
                'icon' => $habit?->icon,
                'type' => $habit?->type,
                'unit' => $habit?->unit,
            ],
            'target' => $this->target($assignment),
            'scheduled_today' => $this->streaks->scheduledToday($assignment, $dates, $date),
            'value' => $log ? (float) $log->value : null,
            'completed' => (bool) ($log?->completed),
            'streak' => $this->streaks->streak($assignment, $dates, $reference),
            'completion_pct_30d' => $this->streaks->completionPct($assignment, $dates, $reference),
            'reminder_times' => array_values($assignment->reminder_times ?? []),
        ];
    }

    public function target(HabitAssignment $assignment): ?float
    {
        $target = $assignment->target ?? $assignment->habit?->target;

        return $target !== null ? (float) $target : null;
    }

    /** Upserts the log of a date (unique per assignment/date). */
    public function log(HabitAssignment $assignment, string $date, ?float $value, ?bool $completed): HabitLog
    {
        $type = $assignment->habit?->type ?? 'boolean';
        $target = $this->target($assignment);
        if ($type === 'boolean') {
            $completed ??= $value === null ? true : $value > 0;
            $value ??= $completed ? 1.0 : 0.0;
        } else {
            $value ??= $completed ? ($target ?? 1.0) : 0.0;
            $completed ??= $target !== null ? $value >= $target : $value > 0;
        }

        $log = HabitLog::query()->where('habit_assignment_id', $assignment->id)->whereDate('log_date', $date)->first()
            ?? new HabitLog(['habit_assignment_id' => $assignment->id, 'log_date' => $date]);
        $log->fill(['value' => $value, 'completed' => $completed, 'source' => 'manual'])->save();

        return $log;
    }

    public function today(User $user): string
    {
        return $this->streaks->today($user->timezone);
    }
}
