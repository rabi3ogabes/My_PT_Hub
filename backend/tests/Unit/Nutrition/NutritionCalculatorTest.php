<?php

use App\Domain\Nutrition\Services\NutritionCalculator;

beforeEach(fn () => $this->calc = new NutritionCalculator);

it('computes Mifflin-St Jeor BMR reference values', function () {
    // Men: 10w + 6.25h − 5a + 5 ; Women: … − 161
    expect($this->calc->bmrMifflinStJeor('male', 80, 180, 30))->toBe(1780.0)
        ->and($this->calc->bmrMifflinStJeor('female', 60, 165, 25))->toBe(1345.25)
        ->and($this->calc->bmrMifflinStJeor('female', 70, 170, 40))->toBe(1401.5);
});

it('computes Katch-McArdle BMR from lean mass', function () {
    // 80 kg @ 15 % → LBM 68 kg → 370 + 21.6 × 68 = 1838.8
    expect($this->calc->bmrKatchMcArdle(80, 15))->toBe(1838.8);
});

it('applies activity factors and goal adjustments', function () {
    expect($this->calc->tdee(1780, 'moderate'))->toBe(2759.0)
        ->and($this->calc->tdee(1780, 'sedentary'))->toBe(2136.0)
        ->and($this->calc->goalAdjusted(2759, 'fat_loss'))->toBe(2207.2)
        ->and($this->calc->goalAdjusted(2759, 'maintenance', 10))->toBe(3034.9);
});

it('splits macros from presets and pins protein per kg', function () {
    $balanced = $this->calc->macros(2000, 'balanced');
    expect($balanced)->toBe(['kcal' => 2000, 'protein_g' => 150, 'carbs_g' => 200, 'fat_g' => 67]);

    $pinned = $this->calc->macros(2000, 'balanced', null, 2.0, 80); // 160 g protein = 640 kcal; rest 1360 split 40:30
    expect($pinned['protein_g'])->toBe(160)
        ->and($pinned['carbs_g'])->toBe(194)
        ->and($pinned['fat_g'])->toBe(65);

    $custom = $this->calc->macros(2400, 'custom', [35, 40, 25]);
    expect($custom)->toBe(['kcal' => 2400, 'protein_g' => 210, 'carbs_g' => 240, 'fat_g' => 67]);
});

it('rejects custom splits that do not total 100', function () {
    $this->calc->macros(2000, 'custom', [50, 30, 30]);
})->throws(InvalidArgumentException::class);

it('never goes below the safety floor unless the coach overrides', function () {
    $input = ['sex' => 'female', 'age' => 45, 'height_cm' => 150, 'weight_kg' => 50, 'activity' => 'sedentary', 'goal' => 'aggressive_fat_loss'];
    $result = $this->calc->calculate($input);
    // BMR 1016.5 × 1.2 = 1219.8 × 0.75 = 914.85 → floored to 1200
    expect($result['kcal'])->toBe(1200)
        ->and($result['safety_floor_applied'])->toBeTrue()
        ->and($result['day_types']['rest']['kcal'])->toBeGreaterThanOrEqual(1200);

    $override = $this->calc->calculate($input + ['override_safety' => true]);
    expect($override['kcal'])->toBe(915)->and($override['safety_floor_applied'])->toBeFalse();

    $male = $this->calc->calculate(['sex' => 'male', 'age' => 60, 'height_cm' => 160, 'weight_kg' => 55, 'activity' => 'sedentary', 'goal' => 'aggressive_fat_loss']);
    expect($male['kcal'])->toBe(1500)->and($male['safety_floor'])->toBe(1500);
});

it('produces a full calculation with training and rest day targets', function () {
    $r = $this->calc->calculate(['sex' => 'male', 'age' => 30, 'height_cm' => 180, 'weight_kg' => 80, 'activity' => 'moderate', 'goal' => 'maintenance', 'preset' => 'balanced', 'protein_per_kg' => 2.0]);
    expect($r['formula'])->toBe('mifflin_st_jeor')
        ->and($r['bmr'])->toEqual(1780)
        ->and($r['tdee'])->toEqual(2759)
        ->and($r['kcal'])->toBe(2759)
        ->and($r['protein_g'])->toBe(160)
        ->and($r['water_ml'])->toBe(2800)
        ->and($r['fiber_g'])->toBe(39)
        ->and($r['day_types']['training']['kcal'])->toBe(3035)
        ->and($r['day_types']['rest']['kcal'])->toBe(2483)
        ->and($r['day_types']['training']['protein_g'])->toBe(160)
        ->and($r['day_types']['training']['carbs_g'])->toBeGreaterThan($r['day_types']['rest']['carbs_g']);

    $katch = $this->calc->calculate(['sex' => 'male', 'age' => 30, 'height_cm' => 180, 'weight_kg' => 80, 'body_fat_pct' => 15, 'activity' => 'sedentary', 'goal' => 'maintenance']);
    expect($katch['formula'])->toBe('katch_mcardle')->and($katch['bmr'])->toEqual(1839);
});
