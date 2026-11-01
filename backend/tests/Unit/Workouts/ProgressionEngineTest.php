<?php

use App\Domain\Workouts\Services\OneRepMax;
use App\Domain\Workouts\Services\ProgressionEngine;

function fiveByFive(float $kg = 100): array
{
    return array_fill(0, 3, ['set_type' => 'working', 'reps_min' => 5, 'reps_max' => 5, 'weight_kg' => $kg]);
}

beforeEach(fn () => $this->engine = new ProgressionEngine);

it('adds load in linear progression when every target rep was hit', function () {
    $done = array_fill(0, 3, ['reps' => 5, 'weight_kg' => 100]);
    $next = $this->engine->prescribe(['type' => 'linear_load', 'increment_kg' => 2.5], fiveByFive(), $done);

    expect(array_column($next, 'weight_kg'))->toBe([102.5, 102.5, 102.5]);
});

it('repeats the load when any set missed its reps', function () {
    $done = [['reps' => 5, 'weight_kg' => 100], ['reps' => 5, 'weight_kg' => 100], ['reps' => 4, 'weight_kg' => 100]];
    $next = $this->engine->prescribe(['type' => 'linear_load', 'increment_kg' => 2.5], fiveByFive(), $done);

    expect(array_column($next, 'weight_kg'))->toBe([100.0, 100.0, 100.0]);
});

it('keeps template targets without any history and leaves warm-ups untouched', function () {
    $targets = [['set_type' => 'warmup', 'reps_min' => 10, 'weight_kg' => 40], ...fiveByFive(80)];
    $next = $this->engine->prescribe(['type' => 'linear_load', 'increment_kg' => 5], $targets, []);

    expect(array_column($next, 'weight_kg'))->toBe([40, 80.0, 80.0, 80.0]);
});

it('raises %1RM each week and resolves loads from the 1RM', function () {
    $targets = fiveByFive(0);
    $rule = ['type' => 'percent_week', 'base_percent' => 70, 'step_percent' => 5, 'max_percent' => 85];

    $week1 = $this->engine->prescribe($rule, $targets, [], 1, false, 150);
    $week3 = $this->engine->prescribe($rule, $targets, [], 3, false, 150);
    $week9 = $this->engine->prescribe($rule, $targets, [], 9, false, 150);

    expect($week1[0]['percent_1rm'])->toEqual(70)->and($week1[0]['weight_kg'])->toEqual(105)
        ->and($week3[0]['percent_1rm'])->toEqual(80)->and($week3[0]['weight_kg'])->toEqual(120)
        ->and($week9[0]['percent_1rm'])->toEqual(85); // capped
});

it('progresses reps within the range, then adds load and resets reps', function () {
    $targets = array_fill(0, 3, ['set_type' => 'working', 'reps_min' => 8, 'reps_max' => 12, 'weight_kg' => 20]);
    $rule = ['type' => 'rep_progression', 'reps_min' => 8, 'reps_max' => 12, 'increment_kg' => 2];

    $mid = $this->engine->prescribe($rule, $targets, [['reps' => 10, 'weight_kg' => 20], ['reps' => 9, 'weight_kg' => 20], ['reps' => 12, 'weight_kg' => 20]]);
    expect(array_column($mid, 'reps_min'))->toBe([11, 10, 12])->and(array_column($mid, 'weight_kg'))->toBe([20.0, 20.0, 20.0]);

    $top = $this->engine->prescribe($rule, $targets, array_fill(0, 3, ['reps' => 12, 'weight_kg' => 20]));
    expect(array_column($top, 'reps_min'))->toBe([8, 8, 8])->and(array_column($top, 'weight_kg'))->toBe([22.5, 22.5, 22.5]); // rounded to 2.5
});

it('deloads by reducing load and halving working sets', function () {
    $done = array_fill(0, 3, ['reps' => 5, 'weight_kg' => 100]);
    $next = $this->engine->prescribe(['type' => 'linear_load', 'increment_kg' => 2.5, 'deload_percent' => 40], fiveByFive(), $done, 4, true);

    expect($next)->toHaveCount(2)->and(array_column($next, 'weight_kg'))->toBe([62.5, 62.5]);
});

it('rejects unknown rule types', function () {
    $this->engine->prescribe(['type' => 'magic'], fiveByFive());
})->throws(InvalidArgumentException::class);

it('estimates one-rep maxes with Epley and Brzycki', function () {
    expect(OneRepMax::epley(100, 5))->toEqual(116.67)
        ->and(OneRepMax::epley(100, 1))->toEqual(100)
        ->and(OneRepMax::epley(100, 20))->toBeNull()
        ->and(OneRepMax::epley(0, 5))->toBeNull()
        ->and(OneRepMax::brzycki(100, 5))->toEqual(112.5)
        ->and(OneRepMax::percentOf(140, 72.5))->toEqual(102.5);
});
