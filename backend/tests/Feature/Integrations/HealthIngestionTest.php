<?php

use App\Domain\Habits\Models\Habit;
use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Habits\Models\HabitLog;
use App\Domain\Integrations\Data\SampleData;
use App\Domain\Integrations\Models\HealthDaily;
use App\Domain\Integrations\Models\HealthSample;
use App\Domain\Integrations\Models\Integration;
use App\Domain\Integrations\Services\HealthIngestionService;
use App\Domain\Integrations\Services\ReadinessService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Tests\Fixtures\Integrations\Fixture;

beforeEach(function () {
    Http::preventStrayRequests();
    [$this->tenant, $this->coach] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->coach, ['timezone' => 'Asia/Qatar']);
    $this->ingestion = app(HealthIngestionService::class);
});

function deviceSample(array $overrides = []): array
{
    return array_merge([
        'type' => 'steps', 'value' => 4200, 'unit' => 'count',
        'start_at' => '2026-10-31T05:00:00Z', 'end_at' => '2026-10-31T06:00:00Z',
        'source' => 'apple_health', 'external_id' => 'hk-1',
    ], $overrides);
}

it('ingests device batches idempotently and marks the device source connected', function () {
    $payload = ['samples' => [
        deviceSample(),
        deviceSample(['external_id' => 'hk-2', 'value' => 3800, 'start_at' => '2026-10-31T09:00:00Z', 'end_at' => '2026-10-31T10:00:00Z']),
        deviceSample(['type' => 'weight_kg', 'value' => 176.37, 'unit' => 'lb', 'external_id' => 'hk-w1']),
        deviceSample(['type' => 'water_ml', 'value' => 0.5, 'unit' => 'L', 'external_id' => null]),
    ]];

    $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/me/health/samples', $payload)
        ->assertOk()->assertJsonPath('data.created', 4)->assertJsonPath('data.duplicates', 0);
    $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/me/health/samples', $payload)
        ->assertOk()->assertJsonPath('data.created', 0)->assertJsonPath('data.duplicates', 4);

    expect(HealthSample::where('user_id', $this->client->id)->count())->toBe(4)
        ->and(round(HealthSample::where('type', 'weight_kg')->value('value'), 1))->toEqual(80.0)
        ->and(HealthSample::where('type', 'water_ml')->value('value'))->toEqual(500.0)
        ->and(HealthDaily::where('user_id', $this->client->id)->where('type', 'steps')->whereDate('date', '2026-10-31')->value('value'))->toEqual(8000.0);

    expect(Integration::where('user_id', $this->client->id)->where('provider', 'apple_health')->value('status'))->toBe('connected');
});

it('rejects server-side sources and unknown types from the device endpoint', function () {
    $this->actingAsIn($this->client, $this->tenant)
        ->postJson('/api/v1/me/health/samples', ['samples' => [deviceSample(['source' => 'garmin'])]])
        ->assertStatus(422)->assertJsonPath('errors.0.code', 'validation_failed');
    $this->actingAsIn($this->client, $this->tenant)
        ->postJson('/api/v1/me/health/samples', ['samples' => [deviceSample(['type' => 'mood'])]])
        ->assertStatus(422);
});

it('keeps one workout when the same run arrives from Garmin and Strava', function () {
    $garmin = SampleData::make('workout', 51, 'min', '2026-10-30T05:00:00Z', '2026-10-30T05:51:00Z', 'garmin', 'act-5544', ['distance_m' => 10050]);
    $strava = SampleData::make('workout', 50, 'min', '2026-10-30T05:02:00Z', '2026-10-30T05:54:00Z', 'strava', '9876543210', ['distance_m' => 10012]);
    $evening = SampleData::make('workout', 30, 'min', '2026-10-30T15:00:00Z', '2026-10-30T15:30:00Z', 'strava', '9876543299');

    $first = $this->ingestion->ingest($this->client, [$garmin]);
    $second = $this->ingestion->ingest($this->client, [$strava, $evening]);

    expect($first['created'])->toBe(1)->and($second['duplicates'])->toBe(1)->and($second['created'])->toBe(1);
    expect(HealthSample::where('user_id', $this->client->id)->primary()->where('type', 'workout')->count())->toBe(2);
    $dup = HealthSample::where('source', 'strava')->where('external_id', '9876543210')->first();
    expect($dup->duplicate_of_id)->toBe(HealthSample::where('source', 'garmin')->value('id'));
    // 51 (Garmin run) + 30 (evening ride); the Strava copy of the run is not double counted.
    expect(HealthDaily::where('user_id', $this->client->id)->where('type', 'workout')->whereDate('date', '2026-10-30')->value('value'))->toEqual(81.0);

    $this->actingAsIn($this->coach, $this->tenant)
        ->getJson("/api/v1/clients/{$this->client->id}/health?from=2026-10-30&to=2026-10-30&types[]=workout")
        ->assertOk()->assertJsonCount(2, 'data.timeline');
});

it('promotes the higher-priority source when it arrives second', function () {
    $this->ingestion->ingest($this->client, [SampleData::make('workout', 50, 'min', '2026-10-30T05:02:00Z', '2026-10-30T05:54:00Z', 'strava', 's-1')]);
    $this->ingestion->ingest($this->client, [SampleData::make('workout', 51, 'min', '2026-10-30T05:00:00Z', '2026-10-30T05:51:00Z', 'garmin', 'g-1')]);

    expect(HealthSample::primary()->where('type', 'workout')->pluck('source')->all())->toBe(['garmin']);

    // Revoking Garmin with data deletion promotes the Strava copy back.
    $this->ingestion->deleteSource($this->client, 'garmin');
    expect(HealthSample::primary()->where('type', 'workout')->pluck('source')->all())->toBe(['strava'])
        ->and(HealthDaily::where('type', 'workout')->value('source'))->toBe('strava');
});

it('applies the configurable source priority per metric per day', function () {
    $this->ingestion->ingest($this->client, [
        SampleData::make('steps', 11000, 'count', '2026-10-30T21:00:00Z', '2026-10-31T20:59:59Z', 'apple_health', 'hk-day'),
        SampleData::make('steps', 10400, 'count', '2026-10-30T21:00:00Z', '2026-10-31T20:59:59Z', 'garmin', 'g-day'),
    ]);
    $daily = HealthDaily::where('user_id', $this->client->id)->where('type', 'steps')->first();
    expect($daily->value)->toEqual(10400.0)->and($daily->source)->toBe('garmin')->and($daily->date->toDateString())->toBe('2026-10-31');

    config(['integrations.health.source_priority.steps' => ['apple_health', 'garmin']]);
    $this->ingestion->ingest($this->client, [SampleData::make('steps', 11200, 'count', '2026-10-30T21:00:00Z', '2026-10-31T20:59:59Z', 'apple_health', 'hk-day')]);
    expect(HealthDaily::where('user_id', $this->client->id)->where('type', 'steps')->first()->only(['value', 'source']))->toBe(['value' => 11200.0, 'source' => 'apple_health']);
});

it('auto-completes step and sleep habits from health data without overriding manual logs', function () {
    [$steps, $sleep, $water] = $this->inTenant($this->tenant, fn () => [
        Habit::create(['key' => 'steps', 'name' => ['en' => '10k steps'], 'type' => 'count', 'target' => 10000, 'unit' => 'steps', 'health_metric' => 'steps']),
        Habit::create(['key' => 'sleep', 'name' => ['en' => 'Sleep 8h'], 'type' => 'duration', 'target' => 480, 'unit' => 'min', 'health_metric' => 'sleep_minutes']),
        Habit::create(['key' => 'water', 'name' => ['en' => 'Water'], 'type' => 'numeric', 'target' => 2500, 'unit' => 'ml', 'health_metric' => 'water_ml']),
    ]);
    [$stepsA, $sleepA, $waterA] = $this->inTenant($this->tenant, fn () => array_map(fn (Habit $h) => HabitAssignment::create([
        'habit_id' => $h->id, 'client_id' => $this->client->id, 'schedule' => 'daily', 'start_date' => '2026-10-01', 'is_active' => true,
    ]), [$steps, $sleep, $water]));
    $sleepA->update(['target' => 420]); // assignment override: 7h
    HabitLog::create(['habit_assignment_id' => $waterA->id, 'log_date' => '2026-10-31', 'value' => 3000, 'completed' => true, 'source' => 'manual']);

    $this->ingestion->ingest($this->client, [
        SampleData::make('steps', 6000, 'count', '2026-10-31T04:00:00Z', '2026-10-31T08:00:00Z', 'apple_health', 'a'),
        SampleData::make('sleep_minutes', 440, 'min', '2026-10-30T20:00:00Z', '2026-10-31T03:20:00Z', 'apple_health', 'sl'),
        SampleData::make('water_ml', 1000, 'ml', '2026-10-31T06:00:00Z', '2026-10-31T06:00:00Z', 'apple_health', 'w'),
    ]);

    expect(HabitLog::where('habit_assignment_id', $stepsA->id)->exists())->toBeFalse();
    $sleepLog = HabitLog::where('habit_assignment_id', $sleepA->id)->firstOrFail();
    expect($sleepLog->completed)->toBeTrue()->and($sleepLog->source)->toBe('health')->and($sleepLog->log_date->toDateString())->toBe('2026-10-31');
    expect(HabitLog::where('habit_assignment_id', $waterA->id)->first()->only(['value', 'source']))->toBe(['value' => 3000.0, 'source' => 'manual']);

    $this->ingestion->ingest($this->client, [SampleData::make('steps', 4500, 'count', '2026-10-31T12:00:00Z', '2026-10-31T15:00:00Z', 'apple_health', 'b')]);

    $stepsLog = HabitLog::where('habit_assignment_id', $stepsA->id)->firstOrFail();
    expect($stepsLog->completed)->toBeTrue()->and($stepsLog->value)->toEqual(10500.0)->and($stepsLog->source)->toBe('health');
});

it('computes an explainable readiness score against the personal baseline', function () {
    $base = CarbonImmutable::parse('2026-10-31', 'Asia/Qatar');
    for ($i = 1; $i <= 14; $i++) {
        $date = $base->subDays($i)->toDateString();
        foreach (['sleep_minutes' => 450, 'hrv' => 60, 'resting_hr' => 55, 'workout' => 45] as $type => $value) {
            HealthDaily::create(['user_id' => $this->client->id, 'date' => $date, 'type' => $type, 'value' => $value, 'source' => 'garmin']);
        }
    }
    foreach (['sleep_minutes' => 360, 'hrv' => 48, 'resting_hr' => 60, 'workout' => 45] as $type => $value) {
        HealthDaily::create(['user_id' => $this->client->id, 'date' => '2026-10-31', 'type' => $type, 'value' => $value, 'source' => 'garmin']);
    }

    $readiness = app(ReadinessService::class)->score($this->client, $base->setTime(12, 0));
    $components = collect($readiness['components'])->keyBy('key');

    expect($readiness['score'])->toBeInt()->toBeLessThan(60)
        ->and($readiness['status'])->toBe('red')
        ->and($components->keys()->all())->toBe(['sleep', 'hrv', 'resting_hr', 'training_load'])
        ->and($components['sleep']['delta_pct'])->toEqual(-20.0)
        ->and($components['sleep']['score'])->toBe(50)
        ->and($components['hrv']['delta_pct'])->toEqual(-20.0)
        ->and($components['resting_hr']['score'])->toBe(35)
        ->and($components['sleep']['explanation'])->toContain('20');
});

it('shows staff a client timeline but never another tenant\'s client', function () {
    $this->ingestion->ingest($this->client, [SampleData::make('steps', 9000, 'count', '2026-10-31T05:00:00Z', '2026-10-31T06:00:00Z', 'garmin', 'x')]);

    $this->actingAsIn($this->coach, $this->tenant)->getJson("/api/v1/clients/{$this->client->id}/health?from=2026-10-25&to=2026-10-31")
        ->assertOk()
        ->assertJsonPath('data.daily.steps.0.value', 9000)
        ->assertJsonStructure(['data' => ['timeline', 'daily', 'readiness' => ['score', 'components'], 'sources']]);

    [$otherTenant, $otherCoach] = $this->createCoachTenant();
    expectApiError($this->actingAsIn($otherCoach, $otherTenant)->getJson("/api/v1/clients/{$this->client->id}/health"), 404, 'not_found');

    expectApiError($this->actingAsIn($this->client, $this->tenant)->getJson("/api/v1/clients/{$this->client->id}/health"), 403, 'forbidden');
});

it('returns the client summary with today values, series and sources', function () {
    Fixture::integration($this->client, 'garmin');
    $this->ingestion->ingest($this->client, [SampleData::make('steps', 7000, 'count', now()->subMinutes(30), now()->subMinutes(10), 'garmin', 'today')]);

    $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/me/health/summary?days=7')
        ->assertOk()
        ->assertJsonPath('data.today.steps', 7000)
        ->assertJsonPath('data.sources.0.provider', 'garmin');
});
