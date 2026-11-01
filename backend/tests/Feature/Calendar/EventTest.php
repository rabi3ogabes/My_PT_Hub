<?php

use App\Domain\Calendar\Models\Booking;
use App\Domain\Calendar\Models\Event;
use App\Domain\Shared\Tenancy\TenantContext;
use Carbon\CarbonImmutable;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-01 06:00', 'UTC'));
    [$this->tenant, $this->coach] = $this->createCoachTenant([], ['timezone' => 'Europe/Paris']);
    $this->client = $this->addClient($this->tenant, $this->coach);
});

function createSeries($test, string $rrule = 'FREQ=WEEKLY;BYDAY=TU,SA;COUNT=8')
{
    Sanctum::actingAs($test->coach, ['access']);
    app(TenantContext::class)->set(null);

    return $test->withHeader('X-Tenant', $test->tenant->id)->postJson('/api/v1/events', [
        'type' => 'class', 'title' => 'Bootcamp', 'capacity' => 10, 'timezone' => 'Europe/Paris',
        'starts_at' => '2026-10-20T09:00:00+02:00', 'ends_at' => '2026-10-20T10:00:00+02:00', 'rrule' => $rrule,
    ])->assertCreated()->json('data.id');
}

function rangeStarts($test, string $from = '2026-10-19', string $to = '2026-11-20'): array
{
    Sanctum::actingAs($test->coach, ['access']);
    app(TenantContext::class)->set(null);

    return collect($test->withHeader('X-Tenant', $test->tenant->id)->getJson("/api/v1/events?from={$from}&to={$to}")->assertOk()->json('data'))
        ->map(fn ($e) => CarbonImmutable::parse($e['starts_at'])->setTimezone('Europe/Paris')->format('m-d H:i').($e['is_occurrence'] ? '' : '*'))->all();
}

it('expands a recurring series across DST in the requested range', function () {
    createSeries($this);
    expect(rangeStarts($this))->toBe(['10-20 09:00', '10-24 09:00', '10-27 09:00', '10-31 09:00', '11-03 09:00', '11-07 09:00', '11-10 09:00', '11-14 09:00']);
    $raw = $this->actingAsIn($this->coach, $this->tenant)->getJson('/api/v1/events?from=2026-10-26&to=2026-10-28')->json('data.0.starts_at');
    expect($raw)->toBe('2026-10-27T08:00:00+00:00'); // CET after the October change
});

it('rejects invalid recurrence rules and time ranges', function () {
    expectApiError($this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/events', [
        'type' => 'class', 'title' => 'X', 'starts_at' => '2026-10-20T09:00:00Z', 'ends_at' => '2026-10-20T10:00:00Z', 'rrule' => 'FREQ=YEARLY',
    ]), 422, 'validation_failed');
    expectApiError($this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/events', [
        'type' => 'class', 'title' => 'X', 'starts_at' => '2026-10-20T10:00:00Z', 'ends_at' => '2026-10-20T09:00:00Z',
    ]), 422, 'validation_failed');
    expectApiError($this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/events?from=2026-10-01&to=2026-10-31'), 403, 'forbidden');
});

it('edits one occurrence, this-and-future, or the whole series', function () {
    $id = createSeries($this);

    // this: move 24 Oct to 11:00 → becomes a concrete exception, original start excluded.
    $this->actingAsIn($this->coach, $this->tenant)->patchJson("/api/v1/events/{$id}", [
        'scope' => 'this', 'occurrence_starts_at' => '2026-10-24T07:00:00Z',
        'starts_at' => '2026-10-24T11:00:00+02:00', 'ends_at' => '2026-10-24T12:00:00+02:00',
    ])->assertOk()->assertJsonPath('data.series_id', $id);
    expect(rangeStarts($this))->toBe(['10-20 09:00', '10-24 11:00*', '10-27 09:00', '10-31 09:00', '11-03 09:00', '11-07 09:00', '11-10 09:00', '11-14 09:00']);

    // future: from 3 Nov the class moves to 18:00 and is renamed; remaining COUNT is preserved (4 left).
    $new = $this->actingAsIn($this->coach, $this->tenant)->patchJson("/api/v1/events/{$id}", [
        'scope' => 'future', 'occurrence_starts_at' => '2026-11-03T08:00:00Z',
        'starts_at' => '2026-11-03T18:00:00+01:00', 'ends_at' => '2026-11-03T19:00:00+01:00', 'title' => 'Evening bootcamp',
    ])->assertOk();
    expect($new->json('data.title'))->toBe('Evening bootcamp')->and($new->json('data.rrule'))->toContain('COUNT=4');
    expect(rangeStarts($this))->toBe(['10-20 09:00', '10-24 11:00*', '10-27 09:00', '10-31 09:00', '11-03 18:00', '11-07 18:00', '11-10 18:00', '11-14 18:00']);

    // all: rename the original series (and its exception).
    $this->actingAsIn($this->coach, $this->tenant)->patchJson("/api/v1/events/{$id}", ['scope' => 'all', 'title' => 'Morning bootcamp'])->assertOk();
    $titles = collect($this->actingAsIn($this->coach, $this->tenant)->getJson('/api/v1/events?from=2026-10-19&to=2026-11-01')->json('data'))->pluck('title')->unique()->values()->all();
    expect($titles)->toBe(['Morning bootcamp']);
});

it('deletes a single occurrence or the rest of the series and cancels bookings', function () {
    $id = createSeries($this);
    $booking = $this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/events/{$id}/bookings", ['client_id' => $this->client->id, 'starts_at' => '2026-10-27T08:00:00Z'])->assertCreated();

    $this->actingAsIn($this->coach, $this->tenant)->deleteJson("/api/v1/events/{$id}?scope=this&occurrence_starts_at=2026-10-20T07:00:00Z")->assertOk();
    expect(rangeStarts($this, '2026-10-19', '2026-10-25'))->toBe(['10-24 09:00']);

    $this->actingAsIn($this->coach, $this->tenant)->deleteJson("/api/v1/events/{$id}?scope=future&occurrence_starts_at=2026-10-27T08:00:00Z")
        ->assertOk()->assertJsonPath('data.cancelled_bookings.0.status', 'cancelled');
    expect(rangeStarts($this))->toBe(['10-24 09:00']);
    expect(Booking::query()->find($booking->json('data.id'))->status)->toBe('cancelled');

    $this->actingAsIn($this->coach, $this->tenant)->deleteJson("/api/v1/events/{$id}")->assertOk();
    expect(rangeStarts($this))->toBe([])->and(Event::query()->count())->toBe(0);
});

it('shows the attendance register for an event', function () {
    $event = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/events', [
        'type' => 'class', 'title' => 'Spin', 'capacity' => 1, 'starts_at' => '2026-10-02T17:00:00Z', 'ends_at' => '2026-10-02T18:00:00Z',
    ])->json('data.id');
    $other = $this->addClient($this->tenant, $this->coach);
    $this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/events/{$event}/bookings", ['client_id' => $this->client->id])->assertCreated()->assertJsonPath('data.status', 'booked');
    $this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/events/{$event}/bookings", ['client_id' => $other->id])->assertCreated()->assertJsonPath('data.status', 'waitlisted');

    $show = $this->actingAsIn($this->coach, $this->tenant)->getJson("/api/v1/events/{$event}")->assertOk();
    expect($show->json('data.booked_count'))->toBe(1)->and($show->json('data.waitlist_count'))->toBe(1)
        ->and(collect($show->json('data.bookings'))->pluck('status')->all())->toBe(['booked', 'waitlisted']);

    [$t2, $o2] = $this->createCoachTenant();
    $this->actingAsIn($o2, $t2)->getJson("/api/v1/events/{$event}")->assertNotFound();
    expectApiError($this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/events/{$event}/bookings", ['client_id' => $o2->id]), 404, 'not_found');
});
