<?php

use App\Domain\Calendar\Models\Event;
use App\Domain\Calendar\Services\AvailabilityService;
use App\Domain\Shared\Tenancy\TenantContext;
use Carbon\CarbonImmutable;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    [$this->tenant, $this->coach] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->coach);
});

function putAvailability($test, string $tz, array $weekly, array $overrides = [], array $settings = [])
{
    Sanctum::actingAs($test->coach, ['access']);
    app(TenantContext::class)->set(null);

    return $test->withHeader('X-Tenant', $test->tenant->id)->putJson('/api/v1/availability', [
        'timezone' => $tz,
        'weekly' => $weekly,
        'overrides' => $overrides,
        'settings' => $settings + ['slot_minutes' => 60, 'buffer_minutes' => 0, 'min_notice_hours' => 0, 'max_advance_days' => 400],
    ]);
}

function slotTimes($response, string $tz): array
{
    return array_map(fn ($s) => CarbonImmutable::parse($s['starts_at'])->setTimezone($tz)->format('H:i'), $response->json('data'));
}

it('produces DST-correct slots in Paris on the last Sundays of March and October', function () {
    $this->travelTo(CarbonImmutable::parse('2026-03-01 00:00', 'UTC'));
    putAvailability($this, 'Europe/Paris', [
        ['weekday' => 6, 'starts_at' => '09:00', 'ends_at' => '11:00'],
        ['weekday' => 7, 'starts_at' => '09:00', 'ends_at' => '11:00'],
    ])->assertOk()->assertJsonCount(2, 'data.weekly');

    $sat = $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/slots?date=2026-03-28')->assertOk();
    $sun = $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/slots?date=2026-03-29')->assertOk();
    expect(collect($sat->json('data'))->pluck('starts_at')->all())->toBe(['2026-03-28T08:00:00+00:00', '2026-03-28T09:00:00+00:00'])
        ->and(collect($sun->json('data'))->pluck('starts_at')->all())->toBe(['2026-03-29T07:00:00+00:00', '2026-03-29T08:00:00+00:00']);

    $octSat = $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/slots?date=2026-10-24')->assertOk();
    $octSun = $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/slots?date=2026-10-25')->assertOk();
    expect($octSat->json('data.0.starts_at'))->toBe('2026-10-24T07:00:00+00:00')
        ->and($octSun->json('data.0.starts_at'))->toBe('2026-10-25T08:00:00+00:00')
        ->and(slotTimes($octSun, 'Europe/Paris'))->toBe(['09:00', '10:00']);
});

it('counts real elapsed time inside a window spanning the DST jump', function () {
    $this->travelTo(CarbonImmutable::parse('2026-03-01 00:00', 'UTC'));
    putAvailability($this, 'Europe/Paris', [['weekday' => 7, 'starts_at' => '01:00', 'ends_at' => '04:00']])->assertOk();
    // 01:00–04:00 local on 29 March lasts only 2 real hours.
    $slots = $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/slots?date=2026-03-29')->assertOk();
    expect(slotTimes($slots, 'Europe/Paris'))->toBe(['01:00', '03:00']);
});

it('uses fixed offsets for Doha and the local offset for Casablanca', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-01 00:00', 'UTC'));
    putAvailability($this, 'Asia/Qatar', [['weekday' => 1, 'starts_at' => '06:00', 'ends_at' => '07:00']])->assertOk();
    expect($this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/slots?date=2026-06-08')->json('data.0.starts_at'))->toBe('2026-06-08T03:00:00+00:00');

    putAvailability($this, 'Africa/Casablanca', [['weekday' => 1, 'starts_at' => '18:00', 'ends_at' => '19:00']])->assertOk();
    $expected = CarbonImmutable::parse('2026-06-08 18:00', 'Africa/Casablanca')->utc()->toIso8601String();
    expect($this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/slots?date=2026-06-08')->json('data.0.starts_at'))->toBe($expected);
});

it('applies breaks, overrides, holidays, buffers, notice, max advance and busy events', function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-02 05:00', 'Asia/Qatar')); // Monday
    putAvailability($this, 'Asia/Qatar', [
        ['weekday' => 1, 'starts_at' => '08:00', 'ends_at' => '12:00'],
        ['weekday' => 1, 'starts_at' => '14:00', 'ends_at' => '16:00'], // lunch break 12–14
        ['weekday' => 2, 'starts_at' => '08:00', 'ends_at' => '10:00'],
    ], [
        ['date' => '2026-11-03', 'starts_at' => '17:00', 'ends_at' => '19:00'], // override replaces Tuesday hours
        ['date' => '2026-11-09', 'is_unavailable' => true],                      // holiday
        ['date' => '2026-11-16', 'starts_at' => '08:00', 'ends_at' => '10:00', 'is_unavailable' => true], // blocked morning
    ], ['buffer_minutes' => 30, 'min_notice_hours' => 4, 'max_advance_days' => 20])->assertOk();

    $mon = $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/slots?date=2026-11-02')->assertOk();
    // Notice of 4h from 05:00 → 09:00 earliest. Step = 60 + 30 buffer.
    expect(slotTimes($mon, 'Asia/Qatar'))->toBe(['09:30', '11:00', '14:00']);

    expect(slotTimes($this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/slots?date=2026-11-03'), 'Asia/Qatar'))->toBe(['17:00']);
    expect($this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/slots?date=2026-11-09')->json('data'))->toBe([]);
    expect(slotTimes($this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/slots?date=2026-11-16'), 'Asia/Qatar'))->toBe(['11:00', '14:00']);
    expect($this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/slots?date=2026-11-30')->json('data'))->toBe([]); // beyond max advance

    // A busy event (plus 30-min buffer) removes overlapping slots.
    $this->inTenant($this->tenant, fn () => Event::create([
        'coach_id' => $this->coach->id, 'type' => 'block', 'title' => 'Dentist', 'timezone' => 'Asia/Qatar',
        'starts_at' => CarbonImmutable::parse('2026-11-23 08:30', 'Asia/Qatar')->utc(), 'ends_at' => CarbonImmutable::parse('2026-11-23 09:00', 'Asia/Qatar')->utc(),
    ]));
    putAvailability($this, 'Asia/Qatar', [['weekday' => 1, 'starts_at' => '08:00', 'ends_at' => '12:00']], [], ['buffer_minutes' => 30, 'max_advance_days' => 60])->assertOk();
    $slots = app()->make(AvailabilityService::class);
    $times = $this->inTenant($this->tenant, fn () => array_map(fn ($s) => $s['starts_at']->setTimezone('Asia/Qatar')->format('H:i'), $slots->slots($this->coach, '2026-11-23', now(), $this->tenant)));
    // 08:00 overlaps 08:00–09:30 (event ± buffer); 09:30 and 11:00 start after the buffer.
    expect($times)->toBe(['09:30', '11:00']);
});

it('validates availability input and lets staff read it', function () {
    $this->actingAsIn($this->coach, $this->tenant)->putJson('/api/v1/availability', [
        'timezone' => 'Europe/Paris', 'weekly' => [['weekday' => 1, 'starts_at' => '12:00', 'ends_at' => '09:00']],
    ])->assertStatus(422)->assertJsonPath('errors.0.code', 'validation_failed');

    putAvailability($this, 'Europe/Paris', [['weekday' => 1, 'starts_at' => '09:00', 'ends_at' => '17:00']], [], ['cancel_window_hours' => 12])->assertOk();
    $this->actingAsIn($this->coach, $this->tenant)->getJson('/api/v1/availability')->assertOk()
        ->assertJsonPath('data.timezone', 'Europe/Paris')->assertJsonPath('data.weekly.0.starts_at', '09:00')->assertJsonPath('data.settings.cancel_window_hours', 12);

    expectApiError($this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/availability'), 403, 'forbidden');

    $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/locations', ['name' => 'Studio West Bay', 'timezone' => 'Asia/Qatar'])->assertCreated();
    $this->actingAsIn($this->coach, $this->tenant)->getJson('/api/v1/locations')->assertOk()->assertJsonPath('data.0.name', 'Studio West Bay');
});
