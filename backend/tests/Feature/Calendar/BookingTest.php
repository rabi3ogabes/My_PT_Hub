<?php

use App\Domain\Calendar\Models\Booking;
use App\Domain\Calendar\Models\BookingSetting;
use App\Domain\Calendar\Models\CreditLedgerEntry;
use App\Domain\Calendar\Models\Event;
use App\Domain\Calendar\Notifications\BookingReminder;
use App\Domain\Calendar\Services\BookingService;
use App\Domain\Calendar\Services\ReminderService;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Notifications\GenericNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-02 06:00', 'UTC'));
    [$this->tenant, $this->coach] = $this->createCoachTenant([], ['timezone' => 'Asia/Qatar']);
    $this->clients = collect(range(1, 4))->map(fn () => $this->addClient($this->tenant, $this->coach));
});

function makeClass($test, array $attrs = []): Event
{
    return app(TenantContext::class)->run($test->tenant, fn () => Event::create($attrs + [
        'coach_id' => $test->coach->id, 'type' => 'class', 'title' => 'HIIT', 'timezone' => 'Asia/Qatar',
        'starts_at' => now()->addDays(2)->setTime(15, 0), 'ends_at' => now()->addDays(2)->setTime(16, 0), 'capacity' => 2, 'credits_cost' => 1,
    ]));
}

function settings($test, array $values): void
{
    app(TenantContext::class)->run($test->tenant, fn () => BookingSetting::query()->updateOrCreate(['coach_id' => $test->coach->id], $values));
    app(BookingService::class)->forgetSettings();
}

it('enforces capacity, puts extra clients on the waitlist and prevents double booking', function () {
    $class = makeClass($this);
    [$a, $b, $c, $d] = $this->clients->all();

    $this->actingAsIn($a, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $class->id])->assertCreated()->assertJsonPath('data.status', 'booked')->assertJsonPath('data.event.booked_count', 1);
    expectApiError($this->actingAsIn($a, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $class->id]), 409, 'already_booked');
    $this->actingAsIn($b, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $class->id])->assertCreated()->assertJsonPath('data.status', 'booked');
    $this->actingAsIn($c, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $class->id])->assertCreated()->assertJsonPath('data.status', 'waitlisted')->assertJsonPath('data.waitlist_position', 1);
    $this->actingAsIn($d, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $class->id])->assertCreated()->assertJsonPath('data.waitlist_position', 2);

    expect(Booking::query()->where('event_id', $class->id)->whereIn('status', ['booked'])->count())->toBe(2);

    // Overlapping class at the same time → double booking for client A.
    $overlap = makeClass($this, ['title' => 'Yoga', 'starts_at' => $class->starts_at->copy()->addMinutes(30), 'ends_at' => $class->ends_at->copy()->addMinutes(30)]);
    expectApiError($this->actingAsIn($a, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $overlap->id]), 409, 'double_booking');
});

it('auto-promotes the waitlist with a claim window and notification', function () {
    Notification::fake();
    $class = makeClass($this, ['capacity' => 1]);
    [$a, $b, $c] = $this->clients->all();
    $ba = $this->actingAsIn($a, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $class->id])->json('data.id');
    $bb = $this->actingAsIn($b, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $class->id])->json('data.id');
    $bc = $this->actingAsIn($c, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $class->id])->json('data.id');

    $this->actingAsIn($a, $this->tenant)->postJson("/api/v1/me/bookings/{$ba}/cancel")->assertOk()->assertJsonPath('data.status', 'cancelled');
    Notification::assertSentTo($b, GenericNotification::class, fn ($n) => $n->category() === 'bookings');

    // The offered seat is held: C cannot jump the queue and nobody else gets booked.
    expect(Booking::query()->find($bb)->waitlist_offered_at)->not->toBeNull();
    expectApiError($this->actingAsIn($c, $this->tenant)->postJson("/api/v1/me/bookings/{$bc}/claim"), 409, 'no_offer');

    $this->actingAsIn($b, $this->tenant)->postJson("/api/v1/me/bookings/{$bb}/claim")->assertOk()->assertJsonPath('data.status', 'booked');
    expect(Booking::query()->find($bc)->waitlist_position)->toBe(1);
});

it('expires unclaimed offers and offers the seat to the next person', function () {
    $class = makeClass($this, ['capacity' => 1]);
    [$a, $b, $c] = $this->clients->all();
    $ba = $this->actingAsIn($a, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $class->id])->json('data.id');
    $bb = $this->actingAsIn($b, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $class->id])->json('data.id');
    $bc = $this->actingAsIn($c, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $class->id])->json('data.id');
    $this->actingAsIn($a, $this->tenant)->postJson("/api/v1/me/bookings/{$ba}/cancel")->assertOk();

    $this->travel(121)->minutes();
    expectApiError($this->actingAsIn($b, $this->tenant)->postJson("/api/v1/me/bookings/{$bb}/claim"), 409, 'offer_expired');
    expect(app(BookingService::class)->expireOffers())->toBe(1);
    expect(Booking::query()->find($bb)->status)->toBe('cancelled')
        ->and(Booking::query()->find($bc)->waitlist_offered_at)->not->toBeNull();
});

it('books directly from the waitlist when the claim window is zero', function () {
    settings($this, ['waitlist_claim_minutes' => 0]);
    $class = makeClass($this, ['capacity' => 1]);
    [$a, $b] = $this->clients->all();
    $ba = $this->actingAsIn($a, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $class->id])->json('data.id');
    $bb = $this->actingAsIn($b, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $class->id])->json('data.id');
    $this->actingAsIn($a, $this->tenant)->postJson("/api/v1/me/bookings/{$ba}/cancel")->assertOk();
    expect(Booking::query()->find($bb)->status)->toBe('booked');
});

it('forfeits the credit on a late cancellation but not on an early one', function () {
    settings($this, ['cancel_window_hours' => 24]);
    [$a, $b] = $this->clients->all();
    $soon = makeClass($this, ['starts_at' => now()->addHours(5), 'ends_at' => now()->addHours(6)]);
    $later = makeClass($this, ['starts_at' => now()->addDays(3), 'ends_at' => now()->addDays(3)->addHour()]);

    $late = $this->actingAsIn($a, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $soon->id])->json('data.id');
    $early = $this->actingAsIn($a, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $later->id])->json('data.id');

    $this->actingAsIn($a, $this->tenant)->postJson("/api/v1/me/bookings/{$late}/cancel")->assertOk()->assertJsonPath('data.status', 'late_cancel');
    $this->actingAsIn($a, $this->tenant)->postJson("/api/v1/me/bookings/{$early}/cancel")->assertOk()->assertJsonPath('data.status', 'cancelled');

    $credits = $this->actingAsIn($a, $this->tenant)->getJson('/api/v1/me/credits')->assertOk();
    expect($credits->json('data.balance'))->toBe(-1)
        ->and($credits->json('data.entries.0.reason'))->toBe('late_cancel');
});

it('marks last week\'s 10 sessions attended in one call with a consistent credit ledger', function () {
    $client = $this->clients->first();
    $this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/clients/{$client->id}/credits", ['amount' => 12, 'note' => 'Pack of 12'])->assertCreated()->assertJsonPath('data.balance', 12);

    $ids = [];
    foreach (range(1, 10) as $i) {
        $event = makeClass($this, ['type' => 'session', 'capacity' => 1, 'title' => "PT {$i}", 'starts_at' => now()->subDays(8)->addHours($i * 3), 'ends_at' => now()->subDays(8)->addHours($i * 3 + 1)]);
        $ids[] = $this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/events/{$event->id}/bookings", ['client_id' => $client->id])->assertCreated()->json('data.id');
    }

    $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/bookings/bulk-status', ['booking_ids' => $ids, 'status' => 'attended'])
        ->assertOk()->assertJsonPath('meta.updated', 10)->assertJsonPath('data.0.status', 'attended');
    // Re-sending is idempotent.
    $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/bookings/bulk-status', ['booking_ids' => $ids, 'status' => 'attended'])->assertOk();

    $sum = fn () => (int) $this->inTenant($this->tenant, fn () => CreditLedgerEntry::query()->where('client_id', $client->id)->sum('amount'));
    $balance = fn () => $this->actingAsIn($this->coach, $this->tenant)->getJson("/api/v1/clients/{$client->id}/credits")->json('data.balance');
    expect($balance())->toBe(2)->and($sum())->toBe(2);

    // Retroactive correction: one was actually a no-show, another cancelled by the coach.
    $this->actingAsIn($this->coach, $this->tenant)->patchJson("/api/v1/bookings/{$ids[0]}", ['status' => 'no_show'])->assertOk();
    $this->actingAsIn($this->coach, $this->tenant)->patchJson("/api/v1/bookings/{$ids[1]}", ['status' => 'cancelled'])->assertOk();
    $this->actingAsIn($this->coach, $this->tenant)->patchJson("/api/v1/bookings/{$ids[1]}", ['status' => 'attended'])->assertOk();
    $this->actingAsIn($this->coach, $this->tenant)->patchJson("/api/v1/bookings/{$ids[2]}", ['status' => 'cancelled'])->assertOk();
    expect($balance())->toBe(3)->and($sum())->toBe(3);

    // Bulk is atomic: an unknown id rejects the whole batch.
    expectApiError($this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/bookings/bulk-status', ['booking_ids' => [$ids[3], '01JAAAAAAAAAAAAAAAAAAAAAAA'], 'status' => 'no_show']), 404, 'not_found');
    expect(Booking::query()->find($ids[3])->status)->toBe('attended');

    // The ledger is append-only.
    expect(fn () => $this->inTenant($this->tenant, fn () => CreditLedgerEntry::query()->first()->update(['amount' => 99])))->toThrow(LogicException::class);
});

it('books a 1:1 slot once and frees it on cancellation', function () {
    Sanctum::actingAs($this->coach, ['access']);
    $this->withHeader('X-Tenant', $this->tenant->id)->putJson('/api/v1/availability', [
        'timezone' => 'Asia/Qatar', 'weekly' => [['weekday' => 3, 'starts_at' => '09:00', 'ends_at' => '11:00']],
        'settings' => ['min_notice_hours' => 2, 'cancel_window_hours' => 12],
    ])->assertOk();
    [$a, $b] = $this->clients->all();
    $start = CarbonImmutable::parse('2026-11-04 09:00', 'Asia/Qatar')->utc()->toIso8601String();

    $booking = $this->actingAsIn($a, $this->tenant)->postJson('/api/v1/me/bookings', ['coach_id' => $this->coach->id, 'starts_at' => $start])
        ->assertCreated()->assertJsonPath('data.status', 'booked')->assertJsonPath('data.event.type', 'session');
    expectApiError($this->actingAsIn($b, $this->tenant)->postJson('/api/v1/me/bookings', ['coach_id' => $this->coach->id, 'starts_at' => $start]), 409, 'slot_unavailable');
    expect(collect($this->actingAsIn($b, $this->tenant)->getJson('/api/v1/me/slots?date=2026-11-04')->json('data'))->pluck('starts_at')->all())->not->toContain($start);

    // Off-grid start → not an open slot.
    expectApiError($this->actingAsIn($b, $this->tenant)->postJson('/api/v1/me/bookings', ['coach_id' => $this->coach->id, 'starts_at' => CarbonImmutable::parse('2026-11-04 09:20', 'Asia/Qatar')->toIso8601String()]), 409, 'slot_unavailable');

    $this->actingAsIn($a, $this->tenant)->postJson('/api/v1/me/bookings/'.$booking->json('data.id').'/cancel')->assertOk()->assertJsonPath('data.status', 'cancelled');
    $this->actingAsIn($b, $this->tenant)->postJson('/api/v1/me/bookings', ['coach_id' => $this->coach->id, 'starts_at' => $start])->assertCreated();
});

it('checks in by QR code and deducts the credit once', function () {
    $class = makeClass($this, ['starts_at' => now()->addMinutes(30), 'ends_at' => now()->addMinutes(90)]);
    $client = $this->clients->first();
    $code = $this->actingAsIn($client, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $class->id])->json('data.checkin_code');

    $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/bookings/check-in', ['code' => $code])->assertOk()->assertJsonPath('data.status', 'attended')->assertJsonPath('data.checked_in_at', fn ($v) => $v !== null);
    $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/bookings/check-in', ['code' => $code])->assertOk();
    expect($this->actingAsIn($client, $this->tenant)->getJson('/api/v1/me/credits')->json('data.balance'))->toBe(-1);
    expectApiError($this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/bookings/check-in', ['code' => 'NOPE']), 404, 'not_found');
});

it('books an occurrence of a recurring class by materializing it', function () {
    $series = makeClass($this, ['rrule' => 'FREQ=WEEKLY;BYDAY=MO,WE;COUNT=6', 'starts_at' => CarbonImmutable::parse('2026-11-02 18:00', 'Asia/Qatar')->utc(), 'ends_at' => CarbonImmutable::parse('2026-11-02 19:00', 'Asia/Qatar')->utc()]);
    $client = $this->clients->first();

    $classes = $this->actingAsIn($client, $this->tenant)->getJson('/api/v1/me/classes?from=2026-11-02T00:00:00Z&to=2026-11-10T00:00:00Z')->assertOk();
    expect(collect($classes->json('data'))->pluck('starts_at')->all())->toBe(['2026-11-02T15:00:00+00:00', '2026-11-04T15:00:00+00:00', '2026-11-09T15:00:00+00:00']);

    $booking = $this->actingAsIn($client, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $series->id, 'starts_at' => '2026-11-04T15:00:00Z'])->assertCreated();
    expect($booking->json('data.event.id'))->not->toBe($series->id)->and($booking->json('data.event.series_id'))->toBe($series->id);
    expectApiError($this->actingAsIn($client, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $series->id, 'starts_at' => '2026-11-05T15:00:00Z']), 422, 'not_an_occurrence');

    $after = $this->actingAsIn($client, $this->tenant)->getJson('/api/v1/me/classes?from=2026-11-02T00:00:00Z&to=2026-11-10T00:00:00Z')->assertOk();
    expect(collect($after->json('data'))->firstWhere('starts_at', '2026-11-04T15:00:00+00:00')['booked_count'])->toBe(1)
        ->and(count($after->json('data')))->toBe(3);
});

it('sends 24h and 2h reminders exactly once', function () {
    Notification::fake();
    $class = makeClass($this, ['starts_at' => now()->addHours(20), 'ends_at' => now()->addHours(21)]);
    $client = $this->clients->first();
    $this->actingAsIn($client, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $class->id])->assertCreated();

    $service = app(ReminderService::class);
    expect($service->sendDue(CarbonImmutable::now()))->toBe(1)->and($service->sendDue(CarbonImmutable::now()))->toBe(0);
    expect($service->sendDue(CarbonImmutable::now()->addHours(19)))->toBe(1)->and($service->sendDue(CarbonImmutable::now()->addHours(19)))->toBe(0);
    Notification::assertSentToTimes($client, BookingReminder::class, 2);
    Notification::assertSentTo($client, BookingReminder::class, fn ($n) => $n->window === '2h' && str_contains($n->toMail($client)->rawAttachments[0]['data'], 'BEGIN:VCALENDAR'));
});

it('isolates bookings and credits across tenants', function () {
    $class = makeClass($this);
    $client = $this->clients->first();
    $bookingId = $this->actingAsIn($client, $this->tenant)->postJson('/api/v1/me/bookings', ['event_id' => $class->id])->json('data.id');

    [$otherTenant, $otherOwner] = $this->createCoachTenant();
    $intruder = $this->addClient($otherTenant, $otherOwner);
    expectApiError($this->actingAsIn($intruder, $otherTenant)->postJson('/api/v1/me/bookings', ['event_id' => $class->id]), 404, 'not_found');
    $this->actingAsIn($intruder, $otherTenant)->postJson("/api/v1/me/bookings/{$bookingId}/cancel")->assertNotFound();
    $this->actingAsIn($intruder, $otherTenant)->getJson("/api/v1/me/bookings/{$bookingId}/ics")->assertNotFound();
    $this->actingAsIn($otherOwner, $otherTenant)->patchJson("/api/v1/bookings/{$bookingId}", ['status' => 'attended'])->assertNotFound();
    $this->actingAsIn($otherOwner, $otherTenant)->getJson("/api/v1/clients/{$client->id}/credits")->assertNotFound();
    expect($this->actingAsIn($otherOwner, $otherTenant)->getJson('/api/v1/events?from=2026-11-01&to=2026-11-30')->json('data'))->toBe([]);

    // Another client of the same tenant cannot touch the booking either.
    $peer = $this->clients->last();
    $this->actingAsIn($peer, $this->tenant)->postJson("/api/v1/me/bookings/{$bookingId}/cancel")->assertNotFound();
    $this->actingAsIn($client, $this->tenant)->get("/api/v1/me/bookings/{$bookingId}/ics")->assertOk()->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
});
