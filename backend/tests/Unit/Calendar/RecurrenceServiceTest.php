<?php

use App\Domain\Calendar\Services\RecurrenceService;
use Carbon\CarbonImmutable;

beforeEach(fn () => $this->rrule = new RecurrenceService);

function utcList(array $instants): array
{
    return array_map(fn (CarbonImmutable $c) => $c->format('Y-m-d H:i'), $instants);
}

it('expands weekly BYDAY with COUNT and removes EXDATEs without changing the count', function () {
    $start = CarbonImmutable::parse('2026-03-02 09:00', 'Asia/Qatar'); // Monday, 06:00Z
    $all = $this->rrule->occurrences($start, 'Asia/Qatar', 'FREQ=WEEKLY;BYDAY=MO,WE,FR;COUNT=6', [], $start, $start->addMonths(2));
    expect(utcList($all))->toBe(['2026-03-02 06:00', '2026-03-04 06:00', '2026-03-06 06:00', '2026-03-09 06:00', '2026-03-11 06:00', '2026-03-13 06:00']);

    $withExdate = $this->rrule->occurrences($start, 'Asia/Qatar', 'FREQ=WEEKLY;BYDAY=MO,WE,FR;COUNT=6', ['2026-03-04T06:00:00Z'], $start, $start->addMonths(2));
    expect(utcList($withExdate))->toBe(['2026-03-02 06:00', '2026-03-06 06:00', '2026-03-09 06:00', '2026-03-11 06:00', '2026-03-13 06:00']);
});

it('keeps local wall-clock time across the Paris DST changes (last Sunday of March and October)', function () {
    $start = CarbonImmutable::parse('2026-03-27 09:00', 'Europe/Paris');
    $march = $this->rrule->occurrences($start, 'Europe/Paris', 'FREQ=DAILY;COUNT=4', null, $start, $start->addDays(10));
    expect(utcList($march))->toBe(['2026-03-27 08:00', '2026-03-28 08:00', '2026-03-29 07:00', '2026-03-30 07:00']);

    $oct = CarbonImmutable::parse('2026-10-24 09:00', 'Europe/Paris');
    $october = $this->rrule->occurrences($oct, 'Europe/Paris', 'FREQ=WEEKLY;BYDAY=SA,SU;UNTIL=20261101T235959Z', null, $oct, $oct->addDays(30));
    expect(utcList($october))->toBe(['2026-10-24 07:00', '2026-10-25 08:00', '2026-10-31 08:00', '2026-11-01 08:00']);
});

it('handles a nonexistent local time on the spring-forward night', function () {
    $start = CarbonImmutable::parse('2026-03-28 02:30', 'Europe/Paris'); // 01:30Z
    $occ = $this->rrule->occurrences($start, 'Europe/Paris', 'FREQ=DAILY;COUNT=3', null, $start, $start->addDays(5));
    // 02:30 does not exist on 29 March in Paris: it shifts forward to 03:30 CEST (01:30Z).
    expect(utcList($occ))->toBe(['2026-03-28 01:30', '2026-03-29 01:30', '2026-03-30 00:30']);
});

it('supports INTERVAL, UNTIL (date and date-time) and window filtering', function () {
    $start = CarbonImmutable::parse('2026-01-05 18:00', 'Africa/Casablanca'); // Monday
    $biweekly = $this->rrule->occurrences($start, 'Africa/Casablanca', 'FREQ=WEEKLY;INTERVAL=2;UNTIL=20260216', null, $start, $start->addYear());
    expect(count($biweekly))->toBe(4)
        ->and($biweekly[0]->setTimezone('Africa/Casablanca')->format('Y-m-d H:i'))->toBe('2026-01-05 18:00')
        ->and($biweekly[3]->setTimezone('Africa/Casablanca')->format('Y-m-d H:i'))->toBe('2026-02-16 18:00');

    $window = $this->rrule->occurrences($start, 'Africa/Casablanca', 'FREQ=DAILY;INTERVAL=3', null, CarbonImmutable::parse('2026-01-10', 'UTC'), CarbonImmutable::parse('2026-01-20', 'UTC'));
    expect(array_map(fn ($c) => $c->setTimezone('Africa/Casablanca')->format('m-d'), $window))->toBe(['01-11', '01-14', '01-17']);

    $daysOnly = $this->rrule->occurrences($start, 'Africa/Casablanca', 'FREQ=DAILY;BYDAY=TU,TH;COUNT=3', null, $start, $start->addMonth());
    expect(array_map(fn ($c) => $c->setTimezone('Africa/Casablanca')->format('D d'), $daysOnly))->toBe(['Tue 06', 'Thu 08', 'Tue 13']);
});

it('splits a series for "this and following" edits', function () {
    $start = CarbonImmutable::parse('2026-11-02 07:00', 'UTC');
    $cut = CarbonImmutable::parse('2026-11-05 07:00', 'UTC');
    expect($this->rrule->countBefore($start, 'UTC', 'FREQ=DAILY;COUNT=10', $cut))->toBe(3);
    $truncated = $this->rrule->truncateBefore('FREQ=DAILY;COUNT=10', $cut);
    expect($truncated)->toBe('FREQ=DAILY;UNTIL=20261105T065959Z')
        ->and(count($this->rrule->occurrences($start, 'UTC', $truncated, null, $start, $start->addMonth())))->toBe(3)
        ->and($this->rrule->isOccurrence($start, 'UTC', 'FREQ=DAILY;COUNT=10', ['2026-11-04T07:00:00Z'], CarbonImmutable::parse('2026-11-04 07:00', 'UTC')))->toBeFalse()
        ->and($this->rrule->isOccurrence($start, 'UTC', 'FREQ=DAILY;COUNT=10', null, CarbonImmutable::parse('2026-11-04 08:00', 'UTC')))->toBeFalse();
});

it('rejects unsupported or malformed rules', function (string $rule) {
    expect(fn () => $this->rrule->parse($rule))->toThrow(InvalidArgumentException::class);
})->with(['FREQ=MONTHLY', 'FREQ=DAILY;COUNT=0', 'FREQ=WEEKLY;BYDAY=XX', 'COUNT=3', 'FREQ=DAILY;COUNT=2;UNTIL=20260101', 'FREQ=DAILY;BYSETPOS=1', 'FREQ=DAILY;UNTIL=tomorrow']);
