<?php

use App\Domain\Calendar\Models\Event;
use App\Domain\Integrations\Models\CalendarEventLink;
use App\Domain\Integrations\Services\CalendarSyncService;
use App\Domain\Integrations\Services\ExternalBusyService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\Fixtures\Integrations\Fixture;

beforeEach(function () {
    Http::preventStrayRequests();
    Fixture::configureProviders();
    [$this->tenant, $this->coach] = $this->createCoachTenant(['timezone' => 'Asia/Qatar']);
});

function makeSession($test, array $attributes = []): Event
{
    return (fn () => $this->inTenant($this->tenant, fn () => Event::create(array_merge([
        'coach_id' => $this->coach->id,
        'type' => 'session',
        'title' => '1:1 Online session',
        'starts_at' => '2026-11-05T15:00:00Z',
        'ends_at' => '2026-11-05T16:00:00Z',
        'timezone' => 'Asia/Qatar',
    ], $attributes))))->call($test);
}

it('merges busy blocks from Google freeBusy and Microsoft getSchedule', function () {
    Fixture::integration($this->coach, 'google_calendar');
    Fixture::integration($this->coach, 'outlook', ['settings' => ['email' => 'coach@example.com']]);
    Http::fake([
        'www.googleapis.com/calendar/v3/freeBusy' => Http::response(Fixture::json('google_freebusy')),
        'graph.microsoft.com/v1.0/me/calendar/getSchedule' => Http::response(Fixture::json('microsoft_schedule')),
    ]);

    $busy = app(ExternalBusyService::class)->busyBlocks($this->coach, CarbonImmutable::parse('2026-11-02T00:00:00Z'), CarbonImmutable::parse('2026-11-03T00:00:00Z'));

    expect(array_map(fn ($b) => [$b['start']->toIso8601String(), $b['end']->toIso8601String(), $b['source']], $busy))->toBe([
        ['2026-11-02T09:00:00+00:00', '2026-11-02T10:30:00+00:00', 'google_calendar'],
        ['2026-11-02T11:00:00+00:00', '2026-11-02T12:00:00+00:00', 'outlook'],
        ['2026-11-02T14:00:00+00:00', '2026-11-02T15:00:00+00:00', 'google_calendar'],
    ]);
    expect(app(ExternalBusyService::class)->isBusy($this->coach, CarbonImmutable::parse('2026-11-02T10:00:00Z'), CarbonImmutable::parse('2026-11-02T11:00:00Z')))->toBeTrue()
        ->and(app(ExternalBusyService::class)->isBusy($this->coach, CarbonImmutable::parse('2026-11-02T12:00:00Z'), CarbonImmutable::parse('2026-11-02T13:00:00Z')))->toBeFalse();

    Http::assertSent(fn (HttpRequest $r) => str_contains($r->url(), 'getSchedule') && $r['schedules'] === ['coach@example.com']);

    // Cached per window until a push notification invalidates it.
    $sent = count(Http::recorded());
    app(ExternalBusyService::class)->busyBlocks($this->coach, CarbonImmutable::parse('2026-11-02T00:00:00Z'), CarbonImmutable::parse('2026-11-03T00:00:00Z'));
    expect(count(Http::recorded()))->toBe($sent);
    app(ExternalBusyService::class)->invalidate(\App\Domain\Integrations\Models\Integration::where('provider', 'google_calendar')->first());
    app(ExternalBusyService::class)->busyBlocks($this->coach, CarbonImmutable::parse('2026-11-02T00:00:00Z'), CarbonImmutable::parse('2026-11-03T00:00:00Z'));
    expect(count(Http::recorded()))->toBe($sent + 1);
});

it('degrades gracefully when a calendar provider fails', function () {
    Fixture::integration($this->coach, 'google_calendar');
    Http::fake(['www.googleapis.com/*' => Http::response('boom', 500)]);

    expect(app(ExternalBusyService::class)->busyBlocks($this->coach, now(), now()->addDay()))->toBe([]);
});

it('pushes platform events to Google Calendar and removes them when cancelled', function () {
    $google = Fixture::integration($this->coach, 'google_calendar');
    Http::fake([
        'www.googleapis.com/calendar/v3/calendars/primary/events' => Http::response(['id' => 'gcal-evt-1']),
        'www.googleapis.com/calendar/v3/calendars/primary/events/gcal-evt-1' => Http::response(['id' => 'gcal-evt-1']),
    ]);

    $event = makeSession($this, ['location_id' => null]);

    expect(CalendarEventLink::where('event_id', $event->id)->where('integration_id', $google->id)->value('external_id'))->toBe('gcal-evt-1');
    Http::assertSent(fn (HttpRequest $r) => $r->method() === 'POST' && str_ends_with($r->url(), '/calendars/primary/events')
        && $r['summary'] === '1:1 Online session'
        && $r['start']['timeZone'] === 'Asia/Qatar'
        && $r['start']['dateTime'] === '2026-11-05T18:00:00+03:00'
        && $r['extendedProperties']['private']['aurum_event_id'] === $event->id);

    $this->inTenant($this->tenant, fn () => $event->update(['title' => 'Moved session', 'starts_at' => '2026-11-05T16:00:00Z', 'ends_at' => '2026-11-05T17:00:00Z']));
    Http::assertSent(fn (HttpRequest $r) => $r->method() === 'PATCH' && $r['summary'] === 'Moved session');

    $this->inTenant($this->tenant, fn () => $event->update(['status' => 'cancelled']));
    Http::assertSent(fn (HttpRequest $r) => $r->method() === 'DELETE' && str_ends_with($r->url(), '/events/gcal-evt-1'));
    expect(CalendarEventLink::count())->toBe(0);
});

it('creates a Zoom meeting for online sessions when Zoom is connected', function () {
    Fixture::integration($this->coach, 'zoom');
    Http::fake(['api.zoom.us/v2/users/me/meetings' => Http::response(Fixture::json('zoom_meeting'), 201)]);

    $event = makeSession($this);

    expect($event->refresh()->meeting_url)->toBe('https://us02web.zoom.us/j/85746065432?pwd=abc');
    Http::assertSent(fn (HttpRequest $r) => $r['type'] === 2 && $r['duration'] === 60 && $r['start_time'] === '2026-11-05T15:00:00Z');
});

it('does nothing for coaches without connected calendars', function () {
    Http::fake();
    makeSession($this);
    Http::assertNothingSent();
});

it('starts a Google watch channel after connecting and renews expiring channels', function () {
    $google = Fixture::integration($this->coach, 'google_calendar', ['settings' => ['watch_expires_at' => now()->addHours(2)->toIso8601String()]]);
    Http::fake([
        'www.googleapis.com/calendar/v3/calendars/primary/events/watch' => Http::response(Fixture::json('google_watch')),
        'www.googleapis.com/calendar/v3/channels/stop' => Http::response(null, 204),
    ]);

    expect(app(CalendarSyncService::class)->renewWatches())->toBe(1);

    $google->refresh();
    expect($google->setting('resource_id'))->toBe('o3hgv1538sdjfh')
        ->and($google->setting('channel_token'))->toHaveLength(40)
        ->and($google->setting('watch_expires_at'))->toStartWith('2030-01-01');
    Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/events/watch') && $r['address'] === route('integrations.webhook', ['provider' => 'google_calendar']) && $r['type'] === 'web_hook');
});
