<?php

namespace App\Domain\Workouts\Services;

use InvalidArgumentException;

/**
 * Pure progression rule engine. Given an exercise's rule, its planned set
 * targets and the client's last performance, returns the next targets.
 *
 * Rules (stored on workout_exercises.progression_rule):
 * - linear_load:     {type, increment_kg: 2.5, round_to?: 2.5}
 *                    +increment when every working set hit its top target reps; otherwise repeat.
 * - percent_week:    {type, base_percent: 70, step_percent: 2.5, max_percent?: 95, round_to?: 2.5}
 *                    %1RM rises each week (week 1 = base); needs a 1RM to resolve a load.
 * - rep_progression: {type, reps_min: 8, reps_max: 12, increment_kg: 2.5, round_to?: 2.5}
 *                    +1 rep per set until all sets reach reps_max, then +load and back to reps_min.
 * Any rule may add {deload_percent: 40} (default 40): on deload weeks loads drop by that
 * percentage and sets are halved (rounded up).
 *
 * Set targets / performed sets are arrays:
 *   target    {set_type?, reps_min?, reps_max?, weight_kg?, percent_1rm?}
 *   performed {reps?, weight_kg?, set_type?}
 */
final class ProgressionEngine
{
    public const TYPES = ['linear_load', 'percent_week', 'rep_progression'];

    public const DEFAULT_DELOAD_PERCENT = 40.0;

    /**
     * @param  array<string, mixed>  $rule
     * @param  list<array<string, mixed>>  $targets
     * @param  list<array<string, mixed>>  $lastPerformance
     * @return list<array<string, mixed>>
     */
    public function prescribe(array $rule, array $targets, array $lastPerformance = [], int $weekNumber = 1, bool $isDeload = false, ?float $oneRepMaxKg = null): array
    {
        $type = $rule['type'] ?? null;
        if (! in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException("Unknown progression rule [{$type}]");
        }
        $roundTo = (float) ($rule['round_to'] ?? 2.5);

        $next = match ($type) {
            'linear_load' => $this->linearLoad($rule, $targets, $lastPerformance, $roundTo),
            'percent_week' => $this->percentWeek($rule, $targets, max(1, $weekNumber), $oneRepMaxKg, $roundTo),
            'rep_progression' => $this->repProgression($rule, $targets, $lastPerformance, $roundTo),
        };

        return $isDeload ? $this->deload($next, (float) ($rule['deload_percent'] ?? self::DEFAULT_DELOAD_PERCENT), $roundTo) : $next;
    }

    /**
     * True when every working set reached its target top reps at (at least) the target weight.
     *
     * @param  list<array<string, mixed>>  $targets
     * @param  list<array<string, mixed>>  $performed
     */
    public function allTargetsHit(array $targets, array $performed, ?float $atWeightKg = null): bool
    {
        $working = $this->working($targets);
        $done = $this->working($performed);
        if ($working === [] || count($done) < count($working)) {
            return false;
        }
        foreach (array_values($working) as $i => $target) {
            $set = array_values($done)[$i];
            $goal = (int) ($target['reps_max'] ?? $target['reps_min'] ?? 0);
            if ((int) ($set['reps'] ?? 0) < $goal) {
                return false;
            }
            $weight = $atWeightKg ?? ($target['weight_kg'] ?? null);
            if ($weight !== null && (float) ($set['weight_kg'] ?? 0) < (float) $weight) {
                return false;
            }
        }

        return true;
    }

    /** @return list<array<string, mixed>> */
    private function linearLoad(array $rule, array $targets, array $last, float $roundTo): array
    {
        $increment = (float) ($rule['increment_kg'] ?? 2.5);
        $lastWeight = $this->topWorkingWeight($last);
        $base = $lastWeight ?? $this->topWorkingWeight($targets, 'weight_kg');
        if ($base === null) {
            return $targets;
        }
        $hit = $last !== [] && $this->allTargetsHit($targets, $last, $lastWeight);
        $weight = OneRepMax::round($hit ? $base + $increment : $base, $roundTo);

        return $this->withWorkingWeight($targets, $weight);
    }

    /** @return list<array<string, mixed>> */
    private function percentWeek(array $rule, array $targets, int $week, ?float $oneRepMax, float $roundTo): array
    {
        $percent = (float) ($rule['base_percent'] ?? 70) + (float) ($rule['step_percent'] ?? 2.5) * ($week - 1);
        $percent = min($percent, (float) ($rule['max_percent'] ?? 95));

        return array_map(function (array $t) use ($percent, $oneRepMax, $roundTo) {
            if ($this->isWorking($t)) {
                $t['percent_1rm'] = round($percent, 2);
                if ($oneRepMax !== null && $oneRepMax > 0) {
                    $t['weight_kg'] = OneRepMax::percentOf($oneRepMax, $percent, $roundTo);
                }
            }

            return $t;
        }, $targets);
    }

    /** @return list<array<string, mixed>> */
    private function repProgression(array $rule, array $targets, array $last, float $roundTo): array
    {
        $min = (int) ($rule['reps_min'] ?? 8);
        $max = (int) ($rule['reps_max'] ?? 12);
        $increment = (float) ($rule['increment_kg'] ?? 2.5);
        $done = array_values($this->working($last));
        $lastWeight = $this->topWorkingWeight($last);
        $weight = $lastWeight ?? $this->topWorkingWeight($targets, 'weight_kg');

        $allAtMax = $done !== [] && count($done) >= count($this->working($targets))
            && collect($done)->every(fn ($s) => (int) ($s['reps'] ?? 0) >= $max);

        $i = 0;

        return array_map(function (array $t) use ($min, $max, $increment, $done, $weight, $allAtMax, $roundTo, &$i) {
            if (! $this->isWorking($t)) {
                return $t;
            }
            if ($allAtMax) {
                $t['reps_min'] = $min;
                $t['reps_max'] = $min;
                if ($weight !== null) {
                    $t['weight_kg'] = OneRepMax::round($weight + $increment, $roundTo);
                }
            } else {
                $prev = isset($done[$i]) ? (int) ($done[$i]['reps'] ?? 0) : null;
                $goal = $prev === null ? $min : max($min, min($max, $prev + 1));
                $t['reps_min'] = $goal;
                $t['reps_max'] = $goal;
                if ($weight !== null) {
                    $t['weight_kg'] = OneRepMax::round($weight, $roundTo);
                }
            }
            $i++;

            return $t;
        }, $targets);
    }

    /** @return list<array<string, mixed>> */
    private function deload(array $targets, float $percent, float $roundTo): array
    {
        $working = array_keys($this->working($targets));
        $keep = array_slice($working, 0, (int) ceil(count($working) / 2));
        $out = [];
        foreach ($targets as $i => $t) {
            if ($this->isWorking($t)) {
                if (! in_array($i, $keep, true)) {
                    continue;
                }
                if (isset($t['weight_kg']) && $t['weight_kg'] !== null) {
                    $t['weight_kg'] = OneRepMax::round((float) $t['weight_kg'] * (1 - $percent / 100), $roundTo);
                }
                if (isset($t['percent_1rm']) && $t['percent_1rm'] !== null) {
                    $t['percent_1rm'] = round((float) $t['percent_1rm'] * (1 - $percent / 100), 2);
                }
            }
            $out[] = $t;
        }

        return $out;
    }

    /** @return array<int, array<string, mixed>> */
    private function working(array $sets): array
    {
        return array_filter($sets, fn (array $s) => $this->isWorking($s));
    }

    private function isWorking(array $set): bool
    {
        return ! in_array($set['set_type'] ?? 'working', ['warmup'], true);
    }

    private function topWorkingWeight(array $sets, string $key = 'weight_kg'): ?float
    {
        $weights = array_filter(array_map(fn ($s) => isset($s[$key]) ? (float) $s[$key] : null, $this->working($sets)), fn ($w) => $w !== null && $w > 0);

        return $weights === [] ? null : max($weights);
    }

    /** @return list<array<string, mixed>> */
    private function withWorkingWeight(array $targets, float $weight): array
    {
        return array_map(function (array $t) use ($weight) {
            if ($this->isWorking($t)) {
                $t['weight_kg'] = $weight;
            }

            return $t;
        }, array_values($targets));
    }
}
