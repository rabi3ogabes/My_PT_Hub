<?php

namespace App\Domain\Integrations\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Integrations\Models\HealthDaily;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Simple, explainable readiness score (0–100) from four components, each
 * compared with the user's own rolling baseline:
 *   sleep vs baseline (35%), HRV vs baseline (30%), resting HR vs baseline (20%),
 *   training load as acute:chronic workload ratio (15%).
 * Missing components are dropped and the weights re-normalized.
 */
final class ReadinessService
{
    public const WEIGHTS = ['sleep' => 0.35, 'hrv' => 0.30, 'resting_hr' => 0.20, 'training_load' => 0.15];

    private const MIN_BASELINE_POINTS = 3;

    /**
     * @return array{date: string, score: int|null, status: string|null, components: list<array<string, mixed>>, device_readiness: float|null}
     */
    public function score(User $user, ?CarbonInterface $date = null): array
    {
        $tz = $user->timezone ?: 'UTC';
        $day = CarbonImmutable::instance($date ?? now())->setTimezone($tz)->startOfDay();
        $baselineDays = (int) config('integrations.health.baseline_days', 28);

        $rows = HealthDaily::query()->where('user_id', $user->id)
            ->whereIn('type', ['sleep_minutes', 'hrv', 'resting_hr', 'workout', 'readiness'])
            ->whereDate('date', '>=', $day->subDays($baselineDays)->toDateString())
            ->whereDate('date', '<=', $day->toDateString())
            ->get(['date', 'type', 'value']);

        $today = $day->toDateString();
        $series = fn (string $type): Collection => $rows->where('type', $type)->mapWithKeys(fn (HealthDaily $r) => [$r->date->toDateString() => (float) $r->value]);

        $components = array_values(array_filter([
            $this->ratioComponent('sleep', $series('sleep_minutes'), $today, fn (float $r) => 100 - max(0, 1 - $r) * 250),
            $this->ratioComponent('hrv', $series('hrv'), $today, fn (float $r) => 70 + ($r - 1) * 300),
            $this->restingHrComponent($series('resting_hr'), $today),
            $this->trainingLoadComponent($series('workout'), $day),
        ]));

        $score = null;
        if ($components !== []) {
            $weightSum = array_sum(array_column($components, 'weight'));
            $score = (int) round(array_sum(array_map(fn (array $c) => $c['score'] * $c['weight'], $components)) / $weightSum);
        }

        return [
            'date' => $today,
            'score' => $score,
            'status' => $score === null ? null : ($score >= 70 ? 'green' : ($score >= 50 ? 'amber' : 'red')),
            'components' => $components,
            'device_readiness' => $series('readiness')->get($today),
        ];
    }

    /**
     * @param  Collection<string, float>  $values
     * @return array<string, mixed>|null
     */
    private function ratioComponent(string $key, Collection $values, string $today, \Closure $curve): ?array
    {
        $value = $values->get($today);
        $baseline = $values->except($today);
        if ($value === null || $baseline->count() < self::MIN_BASELINE_POINTS || $baseline->avg() <= 0) {
            return null;
        }
        $avg = (float) $baseline->avg();
        $ratio = $value / $avg;
        $deltaPct = round(($ratio - 1) * 100, 1);

        return $this->component($key, $value, $avg, $deltaPct, $curve($ratio), __("integrations.readiness.{$key}.".$this->direction($deltaPct, 5), ['pct' => abs($deltaPct)]));
    }

    /**
     * @param  Collection<string, float>  $values
     * @return array<string, mixed>|null
     */
    private function restingHrComponent(Collection $values, string $today): ?array
    {
        $value = $values->get($today);
        $baseline = $values->except($today);
        if ($value === null || $baseline->count() < self::MIN_BASELINE_POINTS) {
            return null;
        }
        $avg = (float) $baseline->avg();
        $delta = round($value - $avg, 1);
        $deltaPct = $avg > 0 ? round(($value / $avg - 1) * 100, 1) : 0.0;
        $direction = $delta > 2 ? 'above' : ($delta < -2 ? 'below' : 'normal');

        return $this->component('resting_hr', $value, $avg, $deltaPct, 75 - $delta * 8, __("integrations.readiness.resting_hr.{$direction}", ['bpm' => abs($delta)]));
    }

    /**
     * @param  Collection<string, float>  $minutes  daily workout minutes
     * @return array<string, mixed>|null
     */
    private function trainingLoadComponent(Collection $minutes, CarbonImmutable $day): ?array
    {
        $acuteFrom = $day->subDays(6)->toDateString();
        $chronic = $minutes->sum() / 28;
        if ($chronic <= 0) {
            return null;
        }
        $acute = $minutes->filter(fn ($v, string $d) => $d >= $acuteFrom)->sum() / 7;
        $acwr = round($acute / $chronic, 2);

        [$score, $band] = match (true) {
            $acwr > 1.3 => [100 - ($acwr - 1.3) * 150, 'high'],
            $acwr < 0.8 => [85, 'low'],
            default => [100, 'balanced'],
        };

        return $this->component('training_load', $acwr, 1.0, round(($acwr - 1) * 100, 1), $score, __("integrations.readiness.training_load.{$band}", ['ratio' => $acwr]));
    }

    /** @return array<string, mixed> */
    private function component(string $key, float $value, float $baseline, float $deltaPct, float $score, string $explanation): array
    {
        return [
            'key' => $key,
            'label' => __("integrations.readiness.{$key}.label"),
            'value' => round($value, 2),
            'baseline' => round($baseline, 2),
            'delta_pct' => $deltaPct,
            'score' => (int) round(max(0, min(100, $score))),
            'weight' => self::WEIGHTS[$key],
            'explanation' => $explanation,
        ];
    }

    private function direction(float $deltaPct, float $threshold): string
    {
        return $deltaPct > $threshold ? 'above' : ($deltaPct < -$threshold ? 'below' : 'normal');
    }
}
