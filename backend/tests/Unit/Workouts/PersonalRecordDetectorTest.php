<?php

use App\Domain\Workouts\Models\SessionSet;
use App\Domain\Workouts\Services\PersonalRecordDetector;

function prSet(float $kg, int $reps, string $session = 's1', string $type = 'working'): SessionSet
{
    $set = new SessionSet(['weight_kg' => $kg, 'reps' => $reps, 'set_type' => $type, 'is_completed' => true, 'workout_session_id' => $session]);
    $set->id = bin2hex(random_bytes(13));

    return $set;
}

it('detects e1rm, weight, rep-at-weight and volume records against history', function () {
    $detector = new PersonalRecordDetector;
    $previous = collect([prSet(100, 5, 'a'), prSet(100, 5, 'a'), prSet(80, 10, 'b')]);
    $current = collect([prSet(105, 3), prSet(80, 12), prSet(80, 12)]);

    $found = collect($detector->candidates($current, $previous))->keyBy('type');

    // e1RM: 80×12 = 112 < 116.67 (100×5); 105×3 = 115.5 → no e1rm PR.
    expect($found->has('e1rm'))->toBeFalse()
        ->and($found['weight']['value'])->toEqual(105)->and($found['weight']['previous'])->toEqual(100)
        ->and($found['reps']['value'])->toEqual(12)->and($found['reps']['weight_kg'])->toEqual(80)->and($found['reps']['previous'])->toEqual(10)
        ->and($found['volume']['value'])->toEqual(105 * 3 + 80 * 12 * 2)->and($found['volume']['previous'])->toEqual(1000);
});

it('stores baselines (no previous value) the first time and ignores warm-ups', function () {
    $detector = new PersonalRecordDetector;
    $sets = $detector->qualifying(collect([prSet(60, 10, 's1', 'warmup'), prSet(100, 5)]));
    $found = collect($detector->candidates($sets, collect()))->keyBy('type');

    expect($found->keys()->sort()->values()->all())->toBe(['e1rm', 'reps', 'volume', 'weight'])
        ->and($found->pluck('previous')->filter()->all())->toBe([])
        ->and($found['e1rm']['value'])->toEqual(116.67)
        ->and($found['volume']['value'])->toEqual(500);
});
