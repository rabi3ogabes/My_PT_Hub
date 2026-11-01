<?php

namespace App\Domain\Integrations\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Integrations\Events\HealthDailyUpdated;
use App\Domain\Integrations\Models\HealthDaily;
use App\Domain\Integrations\Models\HealthSample;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Rolls primary samples into health_daily: one source of truth per metric
 * per day chosen by the configurable source priority. Workouts are merged
 * across sources (cross-source duplicates were already removed).
 */
final class HealthNormalizer
{
    public function __construct(private readonly HabitAutoCompleter $habits) {}

    /** Lower is better. Unknown sources rank last. */
    public function priority(string $type, string $source): int
    {
        $lists = (array) config('integrations.health.source_priority', []);
        $list = (array) ($lists[$type] ?? $lists['default'] ?? []);
        $index = array_search($source, $list, true);

        return $index === false ? count($list) + 100 : (int) $index;
    }

    /** Sleep counts toward the day you wake up; everything else toward the day it started. */
    public function localDate(string $type, CarbonImmutable $start, CarbonImmutable $end, string $timezone): string
    {
        return ($type === 'sleep_minutes' ? $end : $start)->setTimezone($timezone)->toDateString();
    }

    /**
     * @param  array<string, list<string>>  $days  local date => types
     * @return array<string, array<string, float>> date => type => value
     */
    public function rebuild(User $user, array $days): array
    {
        $tz = $user->timezone ?: 'UTC';
        $out = [];

        foreach ($days as $date => $types) {
            $dayStart = CarbonImmutable::parse($date, $tz)->startOfDay();
            $windowStart = $dayStart->utc();
            $windowEnd = $dayStart->addDay()->utc();

            foreach (array_unique($types) as $type) {
                $column = $type === 'sleep_minutes' ? 'end_at' : 'start_at';
                $samples = HealthSample::query()->primary()
                    ->where('user_id', $user->id)->where('type', $type)
                    ->where($column, '>=', $windowStart)->where($column, '<', $windowEnd)
                    ->orderBy('start_at')->get();

                if ($samples->isEmpty()) {
                    HealthDaily::query()->where('user_id', $user->id)->whereDate('date', $date)->where('type', $type)->delete();

                    continue;
                }

                [$value, $source] = $this->aggregate($type, $samples);
                $daily = HealthDaily::query()->where('user_id', $user->id)->whereDate('date', $date)->where('type', $type)->first()
                    ?? new HealthDaily(['user_id' => $user->id, 'date' => $date, 'type' => $type]);
                $daily->fill(['value' => $value, 'source' => $source])->save();
                $out[$date][$type] = $value;
            }

            $this->habits->apply($user, $date, $this->dailyValues($user, $date));
        }

        if ($out !== []) {
            event(new HealthDailyUpdated($user, array_keys($days)));
        }

        return $out;
    }

    /**
     * @param  Collection<int, HealthSample>  $samples
     * @return array{0: float, 1: string}
     */
    private function aggregate(string $type, Collection $samples): array
    {
        $method = (string) config('integrations.health.aggregation.'.$type, 'sum');

        if ($type === 'workout') {
            $sources = $samples->pluck('source')->unique();

            return [round((float) $samples->sum('value'), 3), $sources->count() === 1 ? (string) $sources->first() : 'merged'];
        }

        $bySource = $samples->groupBy('source');
        $source = (string) $bySource->keys()->sortBy(fn (string $s) => $this->priority($type, $s))->first();
        $rows = $bySource[$source];

        $value = match ($method) {
            'avg' => (float) $rows->avg('value'),
            'min' => (float) $rows->min('value'),
            'max' => (float) $rows->max('value'),
            'last' => (float) $rows->sortBy('start_at')->last()->value,
            default => (float) $rows->sum('value'),
        };

        return [round($value, 3), $source];
    }

    /** @return array<string, float> */
    public function dailyValues(User $user, string $date): array
    {
        return HealthDaily::query()->where('user_id', $user->id)->whereDate('date', $date)
            ->pluck('value', 'type')->map(fn ($v) => (float) $v)->all();
    }
}
