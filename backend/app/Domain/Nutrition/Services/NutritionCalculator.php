<?php

namespace App\Domain\Nutrition\Services;

use InvalidArgumentException;

/**
 * Energy & macro targets.
 *
 * - BMR: Mifflin-St Jeor (default) or Katch-McArdle (when body fat is known).
 * - TDEE = BMR × activity factor; goal adjustment as a % of TDEE.
 * - Macros from a preset split or custom percentages; protein can be pinned in g/kg.
 * - Safety floors: never below 1200 kcal (women) / 1500 kcal (men) unless the
 *   coach explicitly overrides. Training/rest days carb-cycle around the base.
 */
final class NutritionCalculator
{
    public const ACTIVITY_FACTORS = [
        'sedentary' => 1.2,
        'light' => 1.375,
        'moderate' => 1.55,
        'active' => 1.725,
        'very_active' => 1.9,
    ];

    /** Fraction of TDEE added/removed per goal. */
    public const GOAL_ADJUSTMENTS = [
        'aggressive_fat_loss' => -0.25,
        'fat_loss' => -0.20,
        'recomp' => -0.10,
        'maintenance' => 0.0,
        'health' => 0.0,
        'performance' => 0.05,
        'strength' => 0.05,
        'lean_gain' => 0.10,
        'muscle_gain' => 0.15,
    ];

    /** protein / carbs / fat as % of kcal. */
    public const PRESETS = [
        'balanced' => [30, 40, 30],
        'high_protein' => [40, 35, 25],
        'low_carb' => [40, 20, 40],
        'keto' => [25, 5, 70],
        'performance' => [25, 55, 20],
    ];

    /** Default protein (g/kg body weight) per goal when the preset is pinned by weight. */
    public const PROTEIN_PER_KG = [
        'aggressive_fat_loss' => 2.4,
        'fat_loss' => 2.2,
        'recomp' => 2.2,
        'maintenance' => 1.8,
        'health' => 1.6,
        'performance' => 1.8,
        'strength' => 2.0,
        'lean_gain' => 2.0,
        'muscle_gain' => 2.0,
    ];

    public const FLOOR_FEMALE = 1200;

    public const FLOOR_MALE = 1500;

    public const KCAL_PER_G = ['protein' => 4, 'carbs' => 4, 'fat' => 9];

    public function bmrMifflinStJeor(string $sex, float $weightKg, float $heightCm, int $age): float
    {
        $base = 10 * $weightKg + 6.25 * $heightCm - 5 * $age;

        return round($base + ($this->isMale($sex) ? 5 : -161), 2);
    }

    public function bmrKatchMcArdle(float $weightKg, float $bodyFatPct): float
    {
        if ($bodyFatPct <= 0 || $bodyFatPct >= 70) {
            throw new InvalidArgumentException('Body fat % must be between 0 and 70.');
        }
        $leanMass = $weightKg * (1 - $bodyFatPct / 100);

        return round(370 + 21.6 * $leanMass, 2);
    }

    public function tdee(float $bmr, string $activity): float
    {
        $factor = self::ACTIVITY_FACTORS[$activity] ?? throw new InvalidArgumentException("Unknown activity level [$activity].");

        return round($bmr * $factor, 2);
    }

    public function goalAdjusted(float $tdee, string $goal, ?float $customAdjustmentPct = null): float
    {
        $adjustment = $customAdjustmentPct !== null ? $customAdjustmentPct / 100 : (self::GOAL_ADJUSTMENTS[$goal] ?? 0.0);

        return round($tdee * (1 + $adjustment), 2);
    }

    public function safetyFloor(?string $sex): int
    {
        return $this->isMale((string) $sex) ? self::FLOOR_MALE : self::FLOOR_FEMALE;
    }

    /** Applies the safety floor unless overridden; returns [kcal, floorApplied]. */
    public function applyFloor(float $kcal, ?string $sex, bool $override = false): array
    {
        $floor = $this->safetyFloor($sex);
        if (! $override && $kcal < $floor) {
            return [(float) $floor, true];
        }

        return [$kcal, false];
    }

    /**
     * Splits kcal into macro grams.
     *
     * @param  array{0: int|float, 1: int|float, 2: int|float}|null  $customPct  protein/carbs/fat percentages (sum 100)
     * @return array{kcal: int, protein_g: int, carbs_g: int, fat_g: int}
     */
    public function macros(float $kcal, string $preset = 'balanced', ?array $customPct = null, ?float $proteinPerKg = null, ?float $weightKg = null): array
    {
        [$p, $c, $f] = $this->split($preset, $customPct);

        if ($proteinPerKg !== null && $weightKg !== null && $weightKg > 0) {
            $proteinG = $proteinPerKg * $weightKg;
            $proteinKcal = min($proteinG * self::KCAL_PER_G['protein'], $kcal * 0.6);
            $proteinG = $proteinKcal / self::KCAL_PER_G['protein'];
            $rest = max(0, $kcal - $proteinKcal);
            $cf = max(1e-9, $c + $f);
            $carbsG = $rest * ($c / $cf) / self::KCAL_PER_G['carbs'];
            $fatG = $rest * ($f / $cf) / self::KCAL_PER_G['fat'];
        } else {
            $proteinG = $kcal * $p / 100 / self::KCAL_PER_G['protein'];
            $carbsG = $kcal * $c / 100 / self::KCAL_PER_G['carbs'];
            $fatG = $kcal * $f / 100 / self::KCAL_PER_G['fat'];
        }

        return [
            'kcal' => (int) round($kcal),
            'protein_g' => (int) round($proteinG),
            'carbs_g' => (int) round($carbsG),
            'fat_g' => (int) round($fatG),
        ];
    }

    /**
     * Full calculation used by the coach calculator and stored as targets.
     *
     * Input keys: sex (male|female), age, height_cm, weight_kg, body_fat_pct?, activity,
     * goal, formula? (mifflin|katch|auto), adjustment_pct?, preset?, custom_split? [p,c,f],
     * protein_per_kg?, override_safety?, cycle_pct? (training/rest delta, default 10).
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function calculate(array $input): array
    {
        $sex = (string) ($input['sex'] ?? 'female');
        $weight = (float) $input['weight_kg'];
        $height = (float) $input['height_cm'];
        $age = (int) $input['age'];
        $bodyFat = isset($input['body_fat_pct']) ? (float) $input['body_fat_pct'] : null;
        $activity = (string) ($input['activity'] ?? 'moderate');
        $goal = (string) ($input['goal'] ?? 'maintenance');
        $formula = (string) ($input['formula'] ?? 'auto');
        $preset = (string) ($input['preset'] ?? 'balanced');
        $override = (bool) ($input['override_safety'] ?? false);
        $cyclePct = (float) ($input['cycle_pct'] ?? 10);

        if ($formula === 'auto') {
            $formula = $bodyFat ? 'katch' : 'mifflin';
        }
        $bmr = $formula === 'katch' && $bodyFat
            ? $this->bmrKatchMcArdle($weight, $bodyFat)
            : $this->bmrMifflinStJeor($sex, $weight, $height, $age);
        $formula = $formula === 'katch' && $bodyFat ? 'katch_mcardle' : 'mifflin_st_jeor';

        $tdee = $this->tdee($bmr, $activity);
        $adjustmentPct = isset($input['adjustment_pct']) ? (float) $input['adjustment_pct'] : null;
        $target = $this->goalAdjusted($tdee, $goal, $adjustmentPct);
        [$kcal, $floorApplied] = $this->applyFloor($target, $sex, $override);

        $proteinPerKg = isset($input['protein_per_kg']) ? (float) $input['protein_per_kg'] : null;
        if ($proteinPerKg === null && ($input['pin_protein'] ?? true) && $preset !== 'custom') {
            $proteinPerKg = self::PROTEIN_PER_KG[$goal] ?? 1.8;
        }
        $custom = $preset === 'custom' ? ($input['custom_split'] ?? null) : null;
        $base = $this->macros($kcal, $preset, $custom, $proteinPerKg, $weight);

        $dayTypes = $this->dayTypes($base, $cyclePct, $sex, $override);

        return [
            'formula' => $formula,
            'bmr' => round($bmr),
            'tdee' => round($tdee),
            'goal' => $goal,
            'adjustment_pct' => round(($adjustmentPct ?? (self::GOAL_ADJUSTMENTS[$goal] ?? 0) * 100), 1),
            'preset' => $preset,
            'protein_per_kg' => $proteinPerKg,
            'safety_floor' => $this->safetyFloor($sex),
            'safety_floor_applied' => $floorApplied || $dayTypes['floor_applied'],
            'override_safety' => $override,
            ...$base,
            'fiber_g' => $this->fiber($base['kcal']),
            'water_ml' => $this->water($weight),
            'day_types' => ['training' => $dayTypes['training'], 'rest' => $dayTypes['rest']],
        ];
    }

    /**
     * Training days get +cycle% kcal, rest days −cycle%, absorbed by carbs (protein & fat constant).
     *
     * @param  array{kcal: int, protein_g: int, carbs_g: int, fat_g: int}  $base
     * @return array{training: array<string, int>, rest: array<string, int>, floor_applied: bool}
     */
    public function dayTypes(array $base, float $cyclePct, ?string $sex, bool $override = false): array
    {
        $floorApplied = false;
        $make = function (float $factor) use ($base, $sex, $override, &$floorApplied): array {
            [$kcal, $applied] = $this->applyFloor($base['kcal'] * $factor, $sex, $override);
            $floorApplied = $floorApplied || $applied;
            $fixed = $base['protein_g'] * 4 + $base['fat_g'] * 9;
            $carbs = max(0, ($kcal - $fixed) / 4);

            return ['kcal' => (int) round($kcal), 'protein_g' => $base['protein_g'], 'carbs_g' => (int) round($carbs), 'fat_g' => $base['fat_g']];
        };

        $training = $make(1 + $cyclePct / 100);
        $rest = $make(1 - $cyclePct / 100);

        return ['training' => $training, 'rest' => $rest, 'floor_applied' => $floorApplied];
    }

    /** 14 g fibre per 1000 kcal (dietary guidelines). */
    public function fiber(float $kcal): int
    {
        return (int) round($kcal / 1000 * 14);
    }

    /** 35 ml water per kg body weight. */
    public function water(float $weightKg): int
    {
        return (int) (round($weightKg * 35 / 50) * 50);
    }

    /** @return array{0: float, 1: float, 2: float} */
    private function split(string $preset, ?array $customPct): array
    {
        if ($customPct !== null) {
            $values = array_map('floatval', array_values($customPct));
            if (count($values) !== 3 || abs(array_sum($values) - 100) > 0.5) {
                throw new InvalidArgumentException('Custom macro split must have 3 percentages summing to 100.');
            }

            return [$values[0], $values[1], $values[2]];
        }
        $values = self::PRESETS[$preset] ?? self::PRESETS['balanced'];

        return [(float) $values[0], (float) $values[1], (float) $values[2]];
    }

    private function isMale(string $sex): bool
    {
        return in_array(strtolower($sex), ['male', 'm', 'man'], true);
    }
}
