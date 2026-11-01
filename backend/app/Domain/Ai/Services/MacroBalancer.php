<?php

namespace App\Domain\Ai\Services;

/**
 * Deterministic portion scaling that brings a day of meal items to within a
 * tolerance (default ±5%) of kcal / protein / carbs / fat targets.
 *
 * Items are grouped by their dominant macro (protein, carb, fat sources); one
 * scale factor per group is solved by weighted least squares on the four
 * targets, quantities are rounded to friendly steps (5 g / 1 g / 0.25
 * serving) and a greedy pass nudges single items to close the residual.
 *
 * Item shape: ['quantity' => float, 'unit' => 'g'|'serving',
 *              'per' => ['kcal'=>, 'protein_g'=>, 'carbs_g'=>, 'fat_g'=>] per gram or per serving, ...]
 */
final class MacroBalancer
{
    public const METRICS = ['kcal', 'protein_g', 'carbs_g', 'fat_g'];

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  array{kcal: float|int, protein_g: float|int, carbs_g: float|int, fat_g: float|int}  $target
     * @return array{items: list<array<string, mixed>>, totals: array<string, float>, ok: bool, deviations: array<string, float>}
     */
    public function balance(array $items, array $target, float $tolerance = 0.05): array
    {
        if ($items === []) {
            return ['items' => [], 'totals' => $this->totals([]), 'ok' => false, 'deviations' => array_fill_keys(self::METRICS, -1.0)];
        }

        $groups = array_values(array_unique(array_map(fn (array $i) => self::group($i['per']), $items)));

        for ($iter = 0; $iter < 8; $iter++) {
            $scales = $this->solve($items, $groups, $target);
            $converged = true;
            foreach ($items as &$item) {
                $s = $scales[self::group($item['per'])] ?? 1.0;
                if (abs($s - 1) > 0.002) {
                    $converged = false;
                }
                $item['quantity'] = max($this->minQty($item), $item['quantity'] * $s);
            }
            unset($item);
            if ($converged) {
                break;
            }
        }

        foreach ($items as &$item) {
            $item['quantity'] = $this->round($item, $item['quantity']);
        }
        unset($item);

        $items = $this->refine($items, $target);
        $totals = $this->totals($items);
        $deviations = $this->deviations($totals, $target);
        $ok = collect($deviations)->every(fn (float $d) => abs($d) <= $tolerance);

        return ['items' => $items, 'totals' => $totals, 'ok' => $ok, 'deviations' => $deviations];
    }

    /** @param array<string, float|int> $per */
    public static function group(array $per): string
    {
        $p = (float) ($per['protein_g'] ?? 0) * 4;
        $c = (float) ($per['carbs_g'] ?? 0) * 4;
        $f = (float) ($per['fat_g'] ?? 0) * 9;

        return match (true) {
            $p >= $c && $p >= $f => 'protein',
            $f >= $c => 'fat',
            default => 'carb',
        };
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, float>
     */
    public function totals(array $items): array
    {
        $totals = array_fill_keys(self::METRICS, 0.0);
        foreach ($items as $item) {
            foreach (self::METRICS as $m) {
                $totals[$m] += (float) ($item['per'][$m] ?? 0) * (float) $item['quantity'];
            }
        }

        return array_map(fn (float $v) => round($v, 1), $totals);
    }

    /**
     * @param  array<string, float>  $totals
     * @param  array<string, float|int>  $target
     * @return array<string, float>
     */
    public function deviations(array $totals, array $target): array
    {
        $out = [];
        foreach (self::METRICS as $m) {
            $t = (float) ($target[$m] ?? 0);
            $out[$m] = $t > 0 ? round(($totals[$m] - $t) / $t, 4) : 0.0;
        }

        return $out;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  list<string>  $groups
     * @param  array<string, float|int>  $target
     * @return array<string, float>
     */
    private function solve(array $items, array $groups, array $target): array
    {
        $k = count($groups);
        $rows = [];
        $b = [];
        foreach (self::METRICS as $m) {
            $t = (float) ($target[$m] ?? 0);
            if ($t <= 0) {
                continue;
            }
            $row = array_fill(0, $k, 0.0);
            foreach ($items as $item) {
                $g = array_search(self::group($item['per']), $groups, true);
                $row[$g] += (float) ($item['per'][$m] ?? 0) * (float) $item['quantity'] / $t;
            }
            $rows[] = $row;
            $b[] = 1.0;
        }

        // Normal equations (AᵀA + λI) s = Aᵀb.
        $ata = array_fill(0, $k, array_fill(0, $k, 0.0));
        $atb = array_fill(0, $k, 0.0);
        foreach ($rows as $r => $row) {
            for ($i = 0; $i < $k; $i++) {
                $atb[$i] += $row[$i] * $b[$r];
                for ($j = 0; $j < $k; $j++) {
                    $ata[$i][$j] += $row[$i] * $row[$j];
                }
            }
        }
        for ($i = 0; $i < $k; $i++) {
            $ata[$i][$i] += 1e-6;
        }

        $s = $this->gauss($ata, $atb);
        $out = [];
        foreach ($groups as $i => $g) {
            $out[$g] = is_finite($s[$i]) ? min(3.0, max(0.3, $s[$i])) : 1.0;
        }

        return $out;
    }

    /**
     * @param  list<list<float>>  $a
     * @param  list<float>  $b
     * @return list<float>
     */
    private function gauss(array $a, array $b): array
    {
        $n = count($b);
        for ($col = 0; $col < $n; $col++) {
            $pivot = $col;
            for ($r = $col + 1; $r < $n; $r++) {
                if (abs($a[$r][$col]) > abs($a[$pivot][$col])) {
                    $pivot = $r;
                }
            }
            [$a[$col], $a[$pivot]] = [$a[$pivot], $a[$col]];
            [$b[$col], $b[$pivot]] = [$b[$pivot], $b[$col]];
            if (abs($a[$col][$col]) < 1e-12) {
                continue;
            }
            for ($r = $col + 1; $r < $n; $r++) {
                $f = $a[$r][$col] / $a[$col][$col];
                for ($c = $col; $c < $n; $c++) {
                    $a[$r][$c] -= $f * $a[$col][$c];
                }
                $b[$r] -= $f * $b[$col];
            }
        }
        $x = array_fill(0, $n, 1.0);
        for ($r = $n - 1; $r >= 0; $r--) {
            if (abs($a[$r][$r]) < 1e-12) {
                $x[$r] = 1.0;

                continue;
            }
            $sum = $b[$r];
            for ($c = $r + 1; $c < $n; $c++) {
                $sum -= $a[$r][$c] * $x[$c];
            }
            $x[$r] = $sum / $a[$r][$r];
        }

        return $x;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  array<string, float|int>  $target
     * @return list<array<string, mixed>>
     */
    private function refine(array $items, array $target): array
    {
        $score = fn (array $list) => array_sum(array_map(fn (float $d) => $d * $d, $this->deviations($this->totals($list), $target)));
        $current = $score($items);

        for ($iter = 0; $iter < 400; $iter++) {
            $best = null;
            $bestScore = $current;
            foreach ($items as $i => $item) {
                $step = $this->step($item);
                foreach ([$step, -$step] as $delta) {
                    $q = round($item['quantity'] + $delta, 2);
                    if ($q < $this->minQty($item)) {
                        continue;
                    }
                    $trial = $items;
                    $trial[$i]['quantity'] = $q;
                    $sc = $score($trial);
                    if ($sc < $bestScore - 1e-9) {
                        [$best, $bestScore] = [[$i, $q], $sc];
                    }
                }
            }
            if ($best === null) {
                break;
            }
            $items[$best[0]]['quantity'] = $best[1];
            $current = $bestScore;
        }

        return $items;
    }

    /** @param array<string, mixed> $item */
    private function step(array $item): float
    {
        if (($item['unit'] ?? 'g') === 'serving') {
            return 0.25;
        }

        return $item['quantity'] >= 40 ? 5.0 : 1.0;
    }

    /** @param array<string, mixed> $item */
    private function round(array $item, float $q): float
    {
        if (($item['unit'] ?? 'g') === 'serving') {
            return max(0.25, round($q * 4) / 4);
        }

        return $q >= 40 ? max(5.0, round($q / 5) * 5) : max(1.0, round($q));
    }

    /** @param array<string, mixed> $item */
    private function minQty(array $item): float
    {
        return ($item['unit'] ?? 'g') === 'serving' ? 0.25 : 1.0;
    }
}
