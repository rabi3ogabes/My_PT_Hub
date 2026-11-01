<?php

use App\Domain\Progress\Models\ProgressPhoto;
use App\Domain\Workouts\Models\Exercise;
use App\Domain\Workouts\Models\PersonalRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    Storage::fake('private');
    [$this->tenant, $this->owner] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->owner);
});

it('stores progress photos privately and serves them through short-lived signed URLs', function () {
    $this->actingAsIn($this->client, $this->tenant);
    $res = $this->post('/api/v1/me/progress-photos', ['photo' => UploadedFile::fake()->image('side.jpg'), 'pose' => 'side', 'taken_on' => '2026-11-01'], ['Accept' => 'application/json'])->assertCreated();
    expect($res->json('data'))->toHaveKeys(['id', 'pose', 'taken_on', 'url'])
        ->and($res->json('data.url'))->toContain('expires=')->toContain('signature=');

    $photo = ProgressPhoto::withoutGlobalScopes()->sole();
    expect($photo->disk)->toBe('private')->and($photo->is_private)->toBeTrue();
    Storage::disk('private')->assertExists($photo->path);
    expect(config('filesystems.disks.private'))->not->toBeNull();

    $this->get($res->json('data.url'))->assertOk();
    expectApiError($this->getJson("/api/v1/me/progress-photos/{$photo->id}"), 403, 'forbidden');

    // Expired signature.
    $this->travel(11)->minutes();
    expectApiError($this->getJson($res->json('data.url')), 403, 'forbidden');
    $this->travelBack();
});

it('never shows a client another client\'s photos', function () {
    $this->actingAsIn($this->client, $this->tenant);
    $url = $this->post('/api/v1/me/progress-photos', ['photo' => UploadedFile::fake()->image('front.jpg'), 'pose' => 'front', 'taken_on' => '2026-11-01'], ['Accept' => 'application/json'])->json('data.url');

    $other = $this->addClient($this->tenant, $this->owner);
    $this->actingAsIn($other, $this->tenant);
    expectApiError($this->getJson($url), 404, 'not_found');
    expect($this->getJson('/api/v1/me/progress')->json('data.photos'))->toBeEmpty();

    [$tenantB, $ownerB] = $this->createCoachTenant();
    $photo = ProgressPhoto::withoutGlobalScopes()->sole();
    $this->actingAsIn($ownerB, $tenantB);
    $signed = URL::temporarySignedRoute('api.v1.clients.progress-photos.show', now()->addMinutes(5), ['client' => $this->client->id, 'photo' => $photo->id]);
    expectApiError($this->getJson($signed), 404, 'not_found');
});

it('records body metrics and returns the progress summary', function () {
    $this->actingAsIn($this->client, $this->tenant);
    $this->postJson('/api/v1/me/body-metrics', ['measured_on' => '2026-10-01', 'weight_kg' => 85, 'body_fat_pct' => 20, 'measurements' => ['waist' => 90, 'hips' => 100]])->assertCreated();
    $this->postJson('/api/v1/me/body-metrics', ['measured_on' => '2026-10-15', 'weight_kg' => 83.5])->assertCreated();
    $this->postJson('/api/v1/me/body-metrics', ['measured_on' => '2026-11-01', 'weight_kg' => 82, 'measurements' => ['waist' => 86]])->assertCreated();
    expectApiError($this->postJson('/api/v1/me/body-metrics', ['measured_on' => '2026-11-01', 'measurements' => ['tail' => 10]]), 422, 'validation_failed');

    $this->inTenant($this->tenant, function () {
        $exercise = Exercise::create(['slug' => 'bench', 'name' => ['en' => 'Bench press']]);
        PersonalRecord::create(['client_id' => $this->client->id, 'exercise_id' => $exercise->id, 'type' => 'weight', 'value' => 90, 'achieved_at' => now()->subWeek()]);
        PersonalRecord::create(['client_id' => $this->client->id, 'exercise_id' => $exercise->id, 'type' => 'weight', 'value' => 100, 'previous_value' => 90, 'achieved_at' => now()]);
    });

    $summary = $this->getJson('/api/v1/me/progress')->assertOk();
    expect($summary->json('data.weight'))->toBe([['date' => '2026-10-01', 'weight_kg' => 85], ['date' => '2026-10-15', 'weight_kg' => 83.5], ['date' => '2026-11-01', 'weight_kg' => 82]])
        ->and($summary->json('data.body_fat.0'))->toEqual(['date' => '2026-10-01', 'value' => 20])
        ->and($summary->json('data.measurements.1.values.waist'))->toEqual(86)
        ->and($summary->json('data.records'))->toHaveCount(1)
        ->and($summary->json('data.records.0.value'))->toEqual(100)
        ->and($summary->json('data.records.0.exercise_name'))->toBe('Bench press')
        ->and($summary->json('data.compliance'))->toHaveKeys(['score', 'workouts_pct', 'nutrition_pct', 'habits_pct', 'checkins_pct']);
});
