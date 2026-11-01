<?php

namespace App\Domain\Calendar\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use InvalidArgumentException;

/**
 * Pure-PHP RFC 5545 RRULE expansion for the subset the product supports:
 * FREQ=DAILY|WEEKLY, INTERVAL, COUNT, UNTIL, BYDAY, WKST plus EXDATE exceptions.
 *
 * Occurrences are generated in the event's wall-clock timezone (a 09:00 class
 * in Paris stays at 09:00 local across DST changes) and returned in UTC.
 */
final class RecurrenceService
{
    public const MAX_COUNT = 1000;

    /** Hard stop on generated candidates, protects against pathological rules. */
    private const MAX_CANDIDATES = 20000;

    private const DAYS = ['MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6, 'SU' => 7];

    /**
     * @return array{freq: string, interval: int, count: int|null, until: CarbonImmutable|null, until_date: string|null, byday: list<int>, wkst: int}
     */
    public function parse(string $rrule): array
    {
        $rule = ['freq' => '', 'interval' => 1, 'count' => null, 'until' => null, 'until_date' => null, 'byday' => [], 'wkst' => 1];
        $body = preg_replace('/^RRULE:/i', '', trim($rrule)) ?? '';

        foreach (explode(';', strtoupper($body)) as $part) {
            if ($part === '') {
                continue;
            }
            [$key, $value] = array_pad(explode('=', $part, 2), 2, '');
            switch ($key) {
                case 'FREQ':
                    if (! in_array($value, ['DAILY', 'WEEKLY'], true)) {
                        throw new InvalidArgumentException("Unsupported FREQ [$value].");
                    }
                    $rule['freq'] = $value;
                    break;
                case 'INTERVAL':
                    if (! ctype_digit($value) || (int) $value < 1 || (int) $value > 365) {
                        throw new InvalidArgumentException('INTERVAL must be between 1 and 365.');
                    }
                    $rule['interval'] = (int) $value;
                    break;
                case 'COUNT':
                    if (! ctype_digit($value) || (int) $value < 1 || (int) $value > self::MAX_COUNT) {
                        throw new InvalidArgumentException('COUNT must be between 1 and '.self::MAX_COUNT.'.');
                    }
                    $rule['count'] = (int) $value;
                    break;
                case 'UNTIL':
                    if (preg_match('/^\d{8}$/', $value)) {
                        $rule['until_date'] = CarbonImmutable::createFromFormat('!Ymd', $value, 'UTC')->toDateString();
                    } elseif (preg_match('/^\d{8}T\d{6}Z?$/', $value)) {
                        $rule['until'] = CarbonImmutable::createFromFormat('Ymd\THis', rtrim($value, 'Z'), 'UTC');
                    } else {
                        throw new InvalidArgumentException('UNTIL must be YYYYMMDD or YYYYMMDDTHHMMSSZ.');
                    }
                    break;
                case 'BYDAY':
                    $days = [];
                    foreach (explode(',', $value) as $day) {
                        if (! isset(self::DAYS[$day])) {
                            throw new InvalidArgumentException("Unsupported BYDAY value [$day].");
                        }
                        $days[] = self::DAYS[$day];
                    }
                    $rule['byday'] = array_values(array_unique($days));
                    break;
                case 'WKST':
                    if (! isset(self::DAYS[$value])) {
                        throw new InvalidArgumentException("Invalid WKST [$value].");
                    }
                    $rule['wkst'] = self::DAYS[$value];
                    break;
                default:
                    throw new InvalidArgumentException("Unsupported RRULE part [$key].");
            }
        }

        if ($rule['freq'] === '') {
            throw new InvalidArgumentException('FREQ is required.');
        }
        if ($rule['count'] !== null && ($rule['until'] !== null || $rule['until_date'] !== null)) {
            throw new InvalidArgumentException('COUNT and UNTIL cannot be combined.');
        }

        return $rule;
    }

    public function isValid(string $rrule): bool
    {
        try {
            $this->parse($rrule);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /**
     * Occurrence starts (UTC) that fall in [$from, $to), excluding EXDATEs.
     *
     * @param  list<string>|null  $exdates  ISO 8601 instants of excluded occurrence starts
     * @return list<CarbonImmutable>
     */
    public function occurrences(CarbonInterface $dtstart, string $timezone, string $rrule, ?array $exdates, CarbonInterface $from, CarbonInterface $to): array
    {
        $excluded = [];
        foreach ($exdates ?? [] as $ex) {
            $excluded[$this->key(CarbonImmutable::parse($ex))] = true;
        }
        $fromUtc = CarbonImmutable::instance($from)->utc();
        $toUtc = CarbonImmutable::instance($to)->utc();

        $result = [];
        foreach ($this->iterate($dtstart, $timezone, $this->parse($rrule)) as $start) {
            if ($start->greaterThanOrEqualTo($toUtc)) {
                break;
            }
            if ($start->greaterThanOrEqualTo($fromUtc) && ! isset($excluded[$this->key($start)])) {
                $result[] = $start;
            }
        }

        return $result;
    }

    /** True when $instant is a (non-excluded) occurrence of the series. */
    public function isOccurrence(CarbonInterface $dtstart, string $timezone, string $rrule, ?array $exdates, CarbonInterface $instant): bool
    {
        $at = CarbonImmutable::instance($instant)->utc();

        return $this->occurrences($dtstart, $timezone, $rrule, $exdates, $at, $at->addSecond()) !== [];
    }

    /** Number of occurrences (EXDATEs included, per RFC COUNT semantics) strictly before $instant. */
    public function countBefore(CarbonInterface $dtstart, string $timezone, string $rrule, CarbonInterface $instant): int
    {
        $limit = CarbonImmutable::instance($instant)->utc();
        $n = 0;
        foreach ($this->iterate($dtstart, $timezone, $this->parse($rrule)) as $start) {
            if ($start->greaterThanOrEqualTo($limit)) {
                break;
            }
            $n++;
        }

        return $n;
    }

    /** Rewrites a rule so the series ends strictly before $instant (used to split "this and future"). */
    public function truncateBefore(string $rrule, CarbonInterface $instant): string
    {
        $until = CarbonImmutable::instance($instant)->utc()->subSecond()->format('Ymd\THis\Z');

        return $this->replaceParts($rrule, ['COUNT' => null, 'UNTIL' => $until]);
    }

    /** @param  array<string, string|null>  $changes  null removes a part */
    public function replaceParts(string $rrule, array $changes): string
    {
        $parts = [];
        foreach (explode(';', strtoupper(preg_replace('/^RRULE:/i', '', trim($rrule)) ?? '')) as $part) {
            if ($part === '') {
                continue;
            }
            [$k, $v] = array_pad(explode('=', $part, 2), 2, '');
            $parts[$k] = $v;
        }
        foreach ($changes as $k => $v) {
            if ($v === null) {
                unset($parts[$k]);
            } else {
                $parts[$k] = $v;
            }
        }

        return implode(';', array_map(fn ($k, $v) => "$k=$v", array_keys($parts), $parts));
    }

    public function key(CarbonInterface $instant): string
    {
        return CarbonImmutable::instance($instant)->utc()->format('Y-m-d\TH:i:s\Z');
    }

    /**
     * Lazily yields every occurrence of the rule in chronological order (UTC).
     *
     * @param  array{freq: string, interval: int, count: int|null, until: CarbonImmutable|null, until_date: string|null, byday: list<int>, wkst: int}  $rule
     * @return \Generator<int, CarbonImmutable>
     */
    private function iterate(CarbonInterface $dtstart, string $timezone, array $rule): \Generator
    {
        $localStart = CarbonImmutable::instance($dtstart)->setTimezone($timezone);
        $time = $localStart->format('H:i:s');
        $startDate = $localStart->startOfDay();
        $emitted = 0;
        $candidates = 0;

        foreach ($this->candidateDates($startDate, $rule) as $date) {
            if (++$candidates > self::MAX_CANDIDATES) {
                return;
            }
            if ($rule['until_date'] !== null && $date->toDateString() > $rule['until_date']) {
                return;
            }
            $start = CarbonImmutable::parse($date->toDateString().' '.$time, $timezone)->utc();
            if ($rule['until'] !== null && $start->greaterThan($rule['until'])) {
                return;
            }
            yield $start;
            $emitted++;
            if ($rule['count'] !== null && $emitted >= $rule['count']) {
                return;
            }
        }
    }

    /**
     * @param  array{freq: string, interval: int, count: int|null, until: CarbonImmutable|null, until_date: string|null, byday: list<int>, wkst: int}  $rule
     * @return \Generator<int, CarbonImmutable>
     */
    private function candidateDates(CarbonImmutable $startDate, array $rule): \Generator
    {
        $interval = $rule['interval'];

        if ($rule['freq'] === 'DAILY') {
            for ($i = 0; ; $i += $interval) {
                $date = $startDate->addDays($i);
                if ($rule['byday'] === [] || in_array($date->dayOfWeekIso, $rule['byday'], true)) {
                    yield $date;
                }
                if ($i > self::MAX_CANDIDATES * $interval) {
                    return;
                }
            }
        }

        // WEEKLY: walk weeks starting at WKST, every INTERVAL weeks.
        $days = $rule['byday'] !== [] ? $rule['byday'] : [$startDate->dayOfWeekIso];
        $wkst = $rule['wkst'];
        $offsets = array_map(fn (int $d) => ($d - $wkst + 7) % 7, $days);
        sort($offsets);
        $weekStart = $startDate->subDays(($startDate->dayOfWeekIso - $wkst + 7) % 7);

        for ($w = 0; ; $w += $interval) {
            $base = $weekStart->addWeeks($w);
            foreach ($offsets as $offset) {
                $date = $base->addDays($offset);
                if ($date->lessThan($startDate)) {
                    continue;
                }
                yield $date;
            }
            if ($w > self::MAX_CANDIDATES * $interval) {
                return;
            }
        }
    }
}
