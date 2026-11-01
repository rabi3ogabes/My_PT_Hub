<?php

namespace App\Domain\Calendar\Services;

use App\Domain\Calendar\Models\Booking;
use App\Domain\Calendar\Models\Event;
use App\Domain\Shared\Support\ApiException;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Recurring event series: expansion into occurrences for a date range,
 * materialization of single occurrences (so they can carry bookings or edits),
 * and "this | future | all" edits and deletions.
 *
 * Model: a series master has `rrule` (+ `exdates`). An edited or booked
 * occurrence becomes a child row (`series_id` = master, `recurrence_id` =
 * original start) and its start is added to the master's EXDATEs.
 */
final class EventSeriesService
{
    public function __construct(private readonly RecurrenceService $rrule) {}

    /**
     * Events (single, materialized children and expanded occurrences) overlapping [$from, $to).
     *
     * @param  Builder<Event>  $query  pre-filtered (coach, type, ...) and tenant-scoped
     * @return list<Event>
     */
    public function expand(Builder $query, CarbonInterface $from, CarbonInterface $to): array
    {
        $from = CarbonImmutable::instance($from)->utc();
        $to = CarbonImmutable::instance($to)->utc();

        $events = (clone $query)
            ->where(function (Builder $q) use ($from, $to) {
                $q->where(fn (Builder $s) => $s->whereNull('rrule')->where('starts_at', '<', $to)->where('ends_at', '>', $from))
                    ->orWhere(fn (Builder $m) => $m->whereNotNull('rrule')->whereNull('series_id')->where('starts_at', '<', $to));
            })
            ->get();

        $out = [];
        foreach ($events as $event) {
            if ($event->rrule === null) {
                $out[] = $event;

                continue;
            }
            $duration = $event->starts_at->diffInSeconds($event->ends_at);
            $starts = $this->rrule->occurrences($event->starts_at, $event->timezone, $event->rrule, $event->exdates, $from->subSeconds((int) $duration), $to);
            foreach ($starts as $start) {
                $out[] = $this->occurrence($event, $start);
            }
        }

        usort($out, fn (Event $a, Event $b) => [$a->starts_at->getTimestamp(), $a->id] <=> [$b->starts_at->getTimestamp(), $b->id]);

        return $out;
    }

    /** An unsaved in-memory copy of the master positioned at one occurrence. */
    public function occurrence(Event $master, CarbonInterface $start): Event
    {
        $duration = (int) $master->starts_at->diffInSeconds($master->ends_at);
        $copy = clone $master;
        $copy->starts_at = CarbonImmutable::instance($start)->utc();
        $copy->ends_at = CarbonImmutable::instance($start)->utc()->addSeconds($duration);
        $copy->isOccurrence = true;

        return $copy;
    }

    public function assertOccurrence(Event $master, CarbonInterface $start): void
    {
        if ($master->rrule === null || ! $this->rrule->isOccurrence($master->starts_at, $master->timezone, $master->rrule, $master->exdates, $start)) {
            throw ApiException::invalid('not_an_occurrence', __('calendar.errors.not_an_occurrence'), ['starts_at' => [__('calendar.errors.not_an_occurrence')]]);
        }
    }

    /** Turns one occurrence into a concrete child event (idempotent). Must be called inside a transaction. */
    public function materialize(Event $master, CarbonInterface $start): Event
    {
        $start = CarbonImmutable::instance($start)->utc();
        $existing = Event::query()->where('series_id', $master->id)->where('recurrence_id', $start)->first();
        if ($existing) {
            return $existing;
        }

        $master = Event::query()->whereKey($master->id)->lockForUpdate()->firstOrFail();
        $this->assertOccurrence($master, $start);
        $duration = (int) $master->starts_at->diffInSeconds($master->ends_at);

        try {
            $child = DB::transaction(fn () => Event::create([
                'tenant_id' => $master->tenant_id,
                'coach_id' => $master->coach_id,
                'location_id' => $master->location_id,
                'type' => $master->type,
                'title' => $master->title,
                'description' => $master->description,
                'starts_at' => $start,
                'ends_at' => $start->addSeconds($duration),
                'timezone' => $master->timezone,
                'capacity' => $master->capacity,
                'credits_cost' => $master->credits_cost,
                'meeting_url' => $master->meeting_url,
                'status' => $master->status,
                'series_id' => $master->id,
                'recurrence_id' => $start,
            ]));
        } catch (UniqueConstraintViolationException) {
            return Event::query()->where('series_id', $master->id)->where('recurrence_id', $start)->firstOrFail();
        }

        $this->addExdate($master, $start);

        return $child;
    }

    /**
     * @param  array<string, mixed>  $data  validated event attributes
     */
    public function update(Event $event, array $data, string $scope, ?CarbonInterface $occurrence = null): Event
    {
        return DB::transaction(function () use ($event, $data, $scope, $occurrence) {
            if (isset($data['rrule']) && $data['rrule'] !== null && ! $this->rrule->isValid($data['rrule'])) {
                throw ApiException::invalid('validation_failed', __('calendar.errors.invalid_rrule'), ['rrule' => [__('calendar.errors.invalid_rrule')]]);
            }

            // Non-recurring event or an already materialized child: plain update.
            if ($event->rrule === null) {
                $event->fill(array_diff_key($data, ['rrule' => true]));
                if (array_key_exists('rrule', $data) && $data['rrule'] !== null && $event->series_id === null) {
                    $event->rrule = $data['rrule'];
                }
                $this->assertTimes($event);
                $event->save();

                return $event;
            }

            if ($scope === 'all') {
                $event->fill($data);
                $this->assertTimes($event);
                $event->save();
                // Propagate non-time changes to materialized children.
                $shared = array_intersect_key($data, array_flip(['title', 'description', 'location_id', 'capacity', 'credits_cost', 'meeting_url', 'coach_id', 'type']));
                if ($shared !== []) {
                    Event::query()->where('series_id', $event->id)->get()->each(fn (Event $c) => $c->fill($shared)->save());
                }

                return $event;
            }

            if ($occurrence === null) {
                throw ApiException::invalid('validation_failed', __('calendar.errors.occurrence_required'), ['occurrence_starts_at' => [__('calendar.errors.occurrence_required')]]);
            }
            $occurrence = CarbonImmutable::instance($occurrence)->utc();

            if ($scope === 'this') {
                $child = $this->materialize($event, $occurrence);
                $child->fill(array_diff_key($data, ['rrule' => true]));
                $this->assertTimes($child);
                $child->save();

                return $child;
            }

            // future: end the current series before the occurrence and start a new series there.
            $this->assertOccurrence($event, $occurrence);
            $originalRule = $event->rrule;
            $parsed = $this->rrule->parse($originalRule);
            $newRule = $data['rrule'] ?? $originalRule;
            if (! isset($data['rrule']) && $parsed['count'] !== null) {
                $remaining = $parsed['count'] - $this->rrule->countBefore($event->starts_at, $event->timezone, $originalRule, $occurrence);
                $newRule = $this->rrule->replaceParts($originalRule, ['COUNT' => (string) max(1, $remaining)]);
            }
            $duration = (int) $event->starts_at->diffInSeconds($event->ends_at);
            $timeShift = isset($data['starts_at']) ? (int) $occurrence->diffInSeconds(CarbonImmutable::parse($data['starts_at'])->utc(), false) : 0;

            $new = $event->replicate(['slot_key', 'external_id']);
            $new->fill(array_diff_key($data, ['starts_at' => true, 'ends_at' => true]));
            $new->rrule = $newRule;
            $new->starts_at = $occurrence->addSeconds($timeShift);
            $new->ends_at = isset($data['ends_at']) ? CarbonImmutable::parse($data['ends_at'])->utc() : $new->starts_at->copy()->addSeconds($duration);
            $new->exdates = array_values(array_filter($event->exdates ?? [], fn (string $x) => CarbonImmutable::parse($x)->greaterThanOrEqualTo($occurrence)));
            $this->assertTimes($new);
            $new->save();

            $event->rrule = $this->rrule->truncateBefore($originalRule, $occurrence);
            $event->exdates = array_values(array_filter($event->exdates ?? [], fn (string $x) => CarbonImmutable::parse($x)->lessThan($occurrence)));
            $event->save();

            Event::query()->where('series_id', $event->id)->where('recurrence_id', '>=', $occurrence)->update(['series_id' => $new->id]);

            return $new;
        });
    }

    /**
     * Deletes an event or part of a series. Bookings on removed occurrences are cancelled.
     *
     * @return list<Booking> cancelled bookings
     */
    public function delete(Event $event, string $scope, ?CarbonInterface $occurrence = null): array
    {
        return DB::transaction(function () use ($event, $scope, $occurrence) {
            $removed = [];

            if ($event->rrule === null || $scope === 'all') {
                $removed[] = $event;
                if ($event->rrule !== null) {
                    array_push($removed, ...Event::query()->where('series_id', $event->id)->get()->all());
                }
            } else {
                if ($occurrence === null) {
                    throw ApiException::invalid('validation_failed', __('calendar.errors.occurrence_required'), ['occurrence_starts_at' => [__('calendar.errors.occurrence_required')]]);
                }
                $occurrence = CarbonImmutable::instance($occurrence)->utc();
                $this->assertOccurrence($event, $occurrence);
                if ($scope === 'this') {
                    $this->addExdate($event, $occurrence);
                } else {
                    array_push($removed, ...Event::query()->where('series_id', $event->id)->where('recurrence_id', '>=', $occurrence)->get()->all());
                    if ($this->rrule->countBefore($event->starts_at, $event->timezone, $event->rrule, $occurrence) === 0) {
                        $removed[] = $event;
                    } else {
                        $event->rrule = $this->rrule->truncateBefore($event->rrule, $occurrence);
                        $event->save();
                    }
                }
            }

            $cancelled = [];
            foreach ($removed as $e) {
                foreach (Booking::query()->where('event_id', $e->id)->whereIn('status', ['booked', 'waitlisted'])->get() as $booking) {
                    $booking->update(['status' => 'cancelled', 'cancelled_at' => now(), 'waitlist_position' => null]);
                    $cancelled[] = $booking;
                }
                $e->forceFill(['status' => 'cancelled', 'slot_key' => null])->save();
                $e->delete();
            }

            return $cancelled;
        });
    }

    private function addExdate(Event $master, CarbonInterface $start): void
    {
        $key = $this->rrule->key($start);
        $exdates = $master->exdates ?? [];
        if (! in_array($key, $exdates, true)) {
            $exdates[] = $key;
            sort($exdates);
            $master->exdates = $exdates;
            $master->save();
        }
    }

    private function assertTimes(Event $event): void
    {
        if ($event->ends_at === null || $event->starts_at === null || ! $event->ends_at->greaterThan($event->starts_at)) {
            throw ApiException::invalid('validation_failed', __('calendar.errors.invalid_times'), ['ends_at' => [__('calendar.errors.invalid_times')]]);
        }
    }
}
