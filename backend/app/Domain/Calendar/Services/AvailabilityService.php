<?php

namespace App\Domain\Calendar\Services;

use App\Domain\Calendar\Models\AvailabilityRule;
use App\Domain\Calendar\Models\BookingSetting;
use App\Domain\Calendar\Models\Event;
use App\Domain\Identity\Models\User;
use App\Domain\Tenancy\Models\Tenant;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Computes a coach's open 1:1 slots for a calendar day.
 *
 * Inputs: weekly rules (several windows per weekday = breaks), date overrides
 * (replace the weekly hours for that date), holidays (whole-day or partial
 * unavailable overrides), booking settings (slot length, buffer, minimum notice,
 * maximum advance) and the coach's existing events (incl. recurring series).
 *
 * All wall-clock times are interpreted in the availability timezone and the
 * arithmetic is done on absolute instants, so DST transitions are exact.
 */
final class AvailabilityService
{
    public function __construct(private readonly EventSeriesService $series) {}

    public function settingsFor(string $coachId): BookingSetting
    {
        return BookingSetting::query()->where('coach_id', $coachId)->first()
            ?? new BookingSetting([
                'coach_id' => $coachId,
                'slot_minutes' => 60,
                'buffer_minutes' => 0,
                'min_notice_hours' => 12,
                'max_advance_days' => 60,
                'cancel_window_hours' => 24,
                'no_show_policy' => 'forfeit',
                'waitlist_claim_minutes' => 120,
                'public_booking' => true,
            ]);
    }

    public function timezoneFor(User $coach, ?Tenant $tenant = null): string
    {
        $tz = AvailabilityRule::query()->where('coach_id', $coach->id)->value('timezone');

        return $tz ?: ($coach->timezone && $coach->timezone !== 'UTC' ? $coach->timezone : ($tenant->timezone ?? $coach->timezone ?? 'UTC'));
    }

    /**
     * Open slots for a coach-local calendar date.
     *
     * @return list<array{starts_at: CarbonImmutable, ends_at: CarbonImmutable}>
     */
    public function slots(User $coach, string $date, ?CarbonInterface $now = null, ?Tenant $tenant = null): array
    {
        $now = CarbonImmutable::instance($now ?? now())->utc();
        $settings = $this->settingsFor($coach->id);
        $tz = $this->timezoneFor($coach, $tenant);
        $day = CarbonImmutable::parse($date, $tz)->startOfDay();
        $dateString = $day->toDateString();

        $rules = AvailabilityRule::query()->where('coach_id', $coach->id)->get();
        $overrides = $rules->filter(fn (AvailabilityRule $r) => $r->date !== null && $r->date->toDateString() === $dateString);

        if ($overrides->contains(fn (AvailabilityRule $r) => $r->is_unavailable && $r->starts_at === null)) {
            return []; // whole-day holiday
        }

        $working = $overrides->filter(fn (AvailabilityRule $r) => ! $r->is_unavailable && $r->starts_at !== null);
        if ($working->isEmpty()) {
            $working = $rules->filter(fn (AvailabilityRule $r) => $r->date === null && ! $r->is_unavailable && (int) $r->weekday === $day->dayOfWeekIso && $r->starts_at !== null);
        }
        if ($working->isEmpty()) {
            return [];
        }

        $windows = [];
        foreach ($working as $rule) {
            [$start, $end] = $this->window($dateString, (string) $rule->starts_at, (string) $rule->ends_at, $tz);
            if ($end->greaterThan($start)) {
                $windows[] = [$start, $end];
            }
        }

        $buffer = (int) $settings->buffer_minutes;
        $busy = [];
        foreach ($overrides->filter(fn (AvailabilityRule $r) => $r->is_unavailable && $r->starts_at !== null) as $block) {
            $busy[] = $this->window($dateString, (string) $block->starts_at, (string) $block->ends_at, $tz);
        }
        $rangeStart = min(array_map(fn ($w) => $w[0], $windows))->subMinutes($buffer)->subDay();
        $rangeEnd = max(array_map(fn ($w) => $w[1], $windows))->addMinutes($buffer)->addDay();
        foreach ($this->busyEvents($coach->id, $rangeStart, $rangeEnd) as $event) {
            $busy[] = [CarbonImmutable::instance($event->starts_at)->subMinutes($buffer), CarbonImmutable::instance($event->ends_at)->addMinutes($buffer)];
        }

        $length = max(5, (int) $settings->slot_minutes);
        $step = $length + $buffer;
        $earliest = $now->addHours((int) $settings->min_notice_hours);
        $latest = $now->addDays((int) $settings->max_advance_days);

        $slots = [];
        foreach ($windows as [$wStart, $wEnd]) {
            for ($t = $wStart; $t->addMinutes($length)->lessThanOrEqualTo($wEnd); $t = $t->addMinutes($step)) {
                $end = $t->addMinutes($length);
                if ($t->lessThan($earliest) || $t->greaterThan($latest)) {
                    continue;
                }
                if ($this->overlapsAny($t, $end, $busy)) {
                    continue;
                }
                $slots[$t->getTimestamp()] = ['starts_at' => $t, 'ends_at' => $end];
            }
        }
        ksort($slots);

        return array_values($slots);
    }

    /** Whether $start is one of the coach's currently open slots. */
    public function isOpen(User $coach, CarbonInterface $start, ?CarbonInterface $now = null, ?Tenant $tenant = null): bool
    {
        $start = CarbonImmutable::instance($start)->utc();
        $localDate = $start->setTimezone($this->timezoneFor($coach, $tenant))->toDateString();
        foreach ($this->slots($coach, $localDate, $now, $tenant) as $slot) {
            if ($slot['starts_at']->equalTo($start)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<Event> */
    public function busyEvents(string $coachId, CarbonInterface $from, CarbonInterface $to): array
    {
        return array_values(array_filter(
            $this->series->expand(Event::query()->where('coach_id', $coachId)->where('status', '!=', 'cancelled'), $from, $to),
            fn (Event $e) => $e->status !== 'cancelled',
        ));
    }

    /**
     * @param  list<array{0: CarbonImmutable, 1: CarbonImmutable}>  $busy
     */
    private function overlapsAny(CarbonImmutable $start, CarbonImmutable $end, array $busy): bool
    {
        foreach ($busy as [$bStart, $bEnd]) {
            if ($start->lessThan($bEnd) && $end->greaterThan($bStart)) {
                return true;
            }
        }

        return false;
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} absolute UTC instants for local wall-clock times */
    private function window(string $date, string $from, string $to, string $tz): array
    {
        $start = CarbonImmutable::parse($date.' '.substr($from, 0, 8), $tz)->utc();
        $end = substr($to, 0, 5) === '24:00' || substr($to, 0, 5) === '00:00'
            ? CarbonImmutable::parse($date, $tz)->addDay()->startOfDay()->utc()
            : CarbonImmutable::parse($date.' '.substr($to, 0, 8), $tz)->utc();

        return [$start, $end];
    }
}
