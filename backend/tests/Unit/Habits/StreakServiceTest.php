<?php

use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Habits\Services\StreakService;
use Carbon\CarbonImmutable;

function assignment(array $attrs = []): HabitAssignment
{
    return new HabitAssignment(array_merge(['schedule' => 'daily', 'start_date' => '2026-03-01', 'is_active' => true], $attrs));
}

beforeEach(fn () => $this->svc = new StreakService);

it('counts consecutive daily completions and keeps today pending', function () {
    $a = assignment();
    $done = ['2026-03-01', '2026-03-02', '2026-03-03', '2026-03-04'];
    // Today (03-05) not yet done: streak still 4.
    expect($this->svc->streak($a, $done, '2026-03-05'))->toBe(['current' => 4, 'best' => 4]);
    // A missed past day resets the current streak but keeps the best.
    expect($this->svc->streak($a, $done, '2026-03-06'))->toBe(['current' => 0, 'best' => 4]);
});

it('skips non-scheduled weekdays for specific-day schedules', function () {
    // Mon/Wed/Fri. 2026-03-02 is a Monday.
    $a = assignment(['schedule' => 'days', 'days' => [1, 3, 5], 'start_date' => '2026-03-02']);
    $done = ['2026-03-02', '2026-03-04', '2026-03-06', '2026-03-09'];
    expect($this->svc->streak($a, $done, '2026-03-10'))->toBe(['current' => 4, 'best' => 4])
        ->and($this->svc->isScheduled($a, '2026-03-03'))->toBeFalse()
        ->and($this->svc->isScheduled($a, '2026-03-04'))->toBeTrue();
    // Missing Wednesday 03-11 breaks it once Friday arrives.
    expect($this->svc->streak($a, $done, '2026-03-12')['current'])->toBe(0);
    // Completion % counts only scheduled days: 4 done / 5 scheduled past days (02,04,06,09,11).
    expect($this->svc->completionPct($a, $done, '2026-03-12'))->toBe(80);
});

it('counts weekly streaks for X-per-week schedules', function () {
    $a = assignment(['schedule' => 'per_week', 'per_week' => 3, 'start_date' => '2026-03-02']);
    $done = ['2026-03-02', '2026-03-04', '2026-03-06', '2026-03-09', '2026-03-10', '2026-03-13', '2026-03-16'];
    // Weeks of 03-02 and 03-09 met (3 each); current week (03-16) in progress → not broken.
    expect($this->svc->streak($a, $done, '2026-03-17'))->toBe(['current' => 2, 'best' => 2]);
    // Week of 03-16 ends with 1 completion → broken by 03-23.
    expect($this->svc->streak($a, $done, '2026-03-24')['current'])->toBe(0);
    expect($this->svc->scheduledToday($a, $done, '2026-03-17'))->toBeTrue();
});

it('resolves today in the user timezone', function () {
    $now = CarbonImmutable::parse('2026-03-01 22:30:00', 'UTC');
    expect($this->svc->today('Asia/Qatar', $now))->toBe('2026-03-02')
        ->and($this->svc->today('America/New_York', $now))->toBe('2026-03-01')
        ->and($this->svc->today(null, $now))->toBe('2026-03-01');
});

it('is not affected by DST transitions', function () {
    // Europe/Paris switches to summer time on 2026-03-29 (a 23-hour day).
    $a = assignment(['start_date' => '2026-03-25']);
    $done = ['2026-03-25', '2026-03-26', '2026-03-27', '2026-03-28', '2026-03-29', '2026-03-30', '2026-03-31'];
    $today = (new StreakService)->today('Europe/Paris', CarbonImmutable::parse('2026-03-31 21:30:00', 'UTC'));
    expect($today)->toBe('2026-03-31')
        ->and($this->svc->streak($a, $done, $today))->toBe(['current' => 7, 'best' => 7])
        ->and($this->svc->completionPct($a, $done, $today, 7))->toBe(100);

    // Autumn fallback (2026-10-25, a 25-hour day) does not double count.
    $b = assignment(['start_date' => '2026-10-23']);
    $done = ['2026-10-23', '2026-10-24', '2026-10-25', '2026-10-26'];
    expect($this->svc->streak($b, $done, '2026-10-26'))->toBe(['current' => 4, 'best' => 4]);
});

it('ignores days before the start date and after the end date', function () {
    $a = assignment(['start_date' => '2026-03-05', 'end_date' => '2026-03-07']);
    $done = ['2026-03-03', '2026-03-05', '2026-03-06', '2026-03-07'];
    expect($this->svc->streak($a, $done, '2026-03-20'))->toBe(['current' => 3, 'best' => 3])
        ->and($this->svc->isScheduled($a, '2026-03-08'))->toBeFalse()
        ->and($this->svc->completionPct($a, $done, '2026-03-20'))->toBe(100);
});
