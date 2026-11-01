<?php

use App\Domain\Checkins\Models\CheckinForm;
use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Checkins\Models\CheckinSchedule;
use App\Domain\Checkins\Services\CheckinService;
use App\Domain\Progress\Models\BodyMetric;
use App\Domain\Progress\Models\ProgressPhoto;
use App\Notifications\GenericNotification;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('private');
    [$this->tenant, $this->owner] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->owner, ['timezone' => 'Asia/Qatar']);
    $this->form = $this->inTenant($this->tenant, fn () => CheckinForm::query()->firstOrFail());
});

afterEach(fn () => $this->travelBack());

it('validates the question builder', function () {
    $this->actingAsIn($this->owner, $this->tenant);
    $bad = $this->postJson('/api/v1/checkin-forms', ['name' => 'Bad', 'questions' => [
        ['id' => 'q1', 'type' => 'single_choice', 'label' => 'Pick', 'options' => ['only one']],
        ['id' => 'q1', 'type' => 'scale', 'label' => 'Mood', 'min' => 5, 'max' => 3],
        ['id' => 'q3', 'type' => 'teleport', 'label' => 'x'],
    ]]);
    expectApiError($bad, 422, 'validation_failed');
    expect(array_keys($bad->json('errors.0.field_errors')))->toContain('questions.0.options', 'questions.1.id', 'questions.1.max', 'questions.2.type');

    $good = $this->postJson('/api/v1/checkin-forms', ['name' => 'Monthly', 'intro' => 'Hi', 'questions' => [
        ['id' => 'mood', 'type' => 'scale', 'label' => ['en' => 'Mood', 'ar' => 'المزاج'], 'required' => true],
        ['id' => 'diet', 'type' => 'multi_choice', 'label' => 'Diet', 'options' => ['halal', 'vegan', 'keto']],
        ['id' => 'waist', 'type' => 'measurement', 'label' => 'Waist', 'unit' => 'cm'],
    ]])->assertCreated();
    expect($good->json('data.questions.0'))->toMatchArray(['id' => 'mood', 'min' => 1, 'max' => 10, 'label' => 'Mood']);

    $id = $good->json('data.id');
    $this->patchJson("/api/v1/checkin-forms/{$id}", ['name' => 'Monthly v2'])->assertOk()->assertJsonPath('data.name', 'Monthly v2');
    $this->getJson("/api/v1/checkin-forms/{$id}?lang=ar")->assertOk()->assertJsonPath('data.questions.0.label', 'المزاج');
    $this->deleteJson("/api/v1/checkin-forms/{$id}")->assertOk();
});

it('schedules check-ins, generates due responses, reminds and escalates overdue ones', function () {
    Notification::fake();
    $this->travelTo(CarbonImmutable::parse('2026-11-02 05:00:00')); // Monday 08:00 in Doha
    $this->actingAsIn($this->owner, $this->tenant);
    $schedule = $this->postJson('/api/v1/checkin-schedules', ['form_id' => $this->form->id, 'client_ids' => [$this->client->id], 'frequency' => 'weekly', 'weekday' => 1, 'due_time' => '09:00'])
        ->assertCreated()->json('data.0');
    expect($schedule['next_due_on'])->toBe('2026-11-02');

    $svc = app(CheckinService::class);
    expect($svc->generateDue($this->tenant))->toBe(1)->and($svc->generateDue($this->tenant))->toBe(0);
    $response = CheckinResponse::withoutGlobalScopes()->sole();
    expect($response->status)->toBe('pending')->and($response->due_on->toDateString())->toBe('2026-11-02');
    expect(CheckinSchedule::withoutGlobalScopes()->find($schedule['id'])->next_due_on->toDateString())->toBe('2026-11-09');
    Notification::assertSentTo($this->client, GenericNotification::class);

    // Before the due time (08:00 local) no reminder; after 09:00 local one reminder only.
    expect($svc->sendReminders($this->tenant))->toBe(0);
    $this->travelTo(CarbonImmutable::parse('2026-11-02 06:30:00'));
    expect($svc->sendReminders($this->tenant))->toBe(1)->and($svc->sendReminders($this->tenant))->toBe(0);

    // Next local day: overdue + escalated to the coach once.
    $this->travelTo(CarbonImmutable::parse('2026-11-03 06:00:00'));
    expect($svc->markOverdue($this->tenant))->toBe(1)->and($svc->markOverdue($this->tenant))->toBe(0);
    expect($response->fresh()->status)->toBe('overdue');
    Notification::assertSentTo($this->owner, GenericNotification::class);

    $inbox = $this->getJson('/api/v1/checkins?filter[status]=overdue')->assertOk();
    expect($inbox->json('data.0.id'))->toBe($response->id)->and($inbox->json('meta.counts.overdue'))->toBe(1);

    $this->deleteJson("/api/v1/checkin-schedules/{$schedule['id']}")->assertOk();
    expect(CheckinSchedule::withoutGlobalScopes()->find($schedule['id'])->is_active)->toBeFalse();
});

it('lets the client submit with metrics and photos, then the coach reviews with context and comments', function () {
    Notification::fake();
    $response = $this->inTenant($this->tenant, fn () => CheckinResponse::create(['checkin_form_id' => $this->form->id, 'client_id' => $this->client->id, 'due_on' => now('Asia/Qatar')->toDateString(), 'status' => 'pending']));

    $this->actingAsIn($this->client, $this->tenant);
    $list = $this->getJson('/api/v1/me/checkins')->assertOk();
    expect($list->json('data.0'))->toMatchArray(['id' => $response->id, 'status' => 'pending', 'form_name' => 'Weekly check-in']);
    $this->getJson("/api/v1/me/checkins/{$response->id}?lang=fr")->assertOk()->assertJsonPath('data.form.questions.0.label', 'Niveau d’énergie cette semaine');

    $photo = $this->post('/api/v1/me/progress-photos', ['photo' => UploadedFile::fake()->image('front.jpg', 600, 800), 'pose' => 'front', 'taken_on' => now()->toDateString()], ['Accept' => 'application/json'])->assertCreated();

    expectApiError($this->postJson("/api/v1/me/checkins/{$response->id}/submit", ['answers' => ['energy' => 11]]), 422, 'validation_failed');

    $submitted = $this->postJson("/api/v1/me/checkins/{$response->id}/submit", [
        'answers' => ['energy' => 7, 'sleep' => 8, 'stress' => 3, 'adherence' => 9, 'wins' => 'Hit every session'],
        'weight_kg' => 82.4, 'body_fat_pct' => 18.5, 'measurements' => ['waist' => 84],
        'photo_ids' => [$photo->json('data.id')],
    ])->assertOk();
    expect($submitted->json('data.status'))->toBe('submitted')->and($submitted->json('data.answers.energy'))->toBe(7);

    $metric = BodyMetric::withoutGlobalScopes()->sole();
    expect($metric->weight_kg)->toBe(82.4)->and($metric->measurements['waist'])->toEqual(84)->and($metric->source)->toBe('checkin');
    expect(ProgressPhoto::withoutGlobalScopes()->sole()->checkin_response_id)->toBe($response->id);
    $fresh = $response->fresh();
    expect($fresh->context['metrics'][0]['weight_kg'])->toEqual(82.4)
        ->and($fresh->context['photo_ids'])->toBe([$photo->json('data.id')])
        ->and($fresh->context)->toHaveKey('compliance');
    Notification::assertSentTo($this->owner, GenericNotification::class);

    $this->actingAsIn($this->owner, $this->tenant);
    $this->getJson('/api/v1/checkins?filter[status]=unread')->assertOk()->assertJsonPath('data.0.id', $response->id)->assertJsonPath('data.0.client.id', $this->client->id);
    $detail = $this->getJson("/api/v1/checkins/{$response->id}")->assertOk();
    expect($detail->json('data.context.metrics.0.weight_kg'))->toEqual(82.4)
        ->and($detail->json('data.photos.0.url'))->toContain('signature=')
        ->and($detail->json('data.context.compliance'))->toHaveKey('score');

    // Signed coach photo access works; unsigned is refused.
    $this->get($detail->json('data.photos.0.url'))->assertOk();
    expectApiError($this->getJson("/api/v1/clients/{$this->client->id}/progress-photos/{$photo->json('data.id')}"), 403, 'forbidden');

    $this->postJson("/api/v1/checkins/{$response->id}/comments", ['body' => 'Great week!'])->assertCreated()->assertJsonPath('data.user.id', $this->owner->id);
    $this->postJson("/api/v1/checkins/{$response->id}/review", ['feedback' => 'Keep protein high.'])->assertOk()->assertJsonPath('data.status', 'reviewed');

    $this->actingAsIn($this->client, $this->tenant);
    $mine = $this->getJson("/api/v1/me/checkins/{$response->id}")->assertOk();
    expect($mine->json('data.coach_feedback'))->toBe('Keep protein high.')
        ->and($mine->json('data.comments.0.body'))->toBe('Great week!');
    expectApiError($this->postJson("/api/v1/me/checkins/{$response->id}/submit", ['answers' => ['energy' => 5, 'sleep' => 5, 'stress' => 5, 'adherence' => 5]]), 409, 'checkin_already_reviewed');
});

it('isolates check-ins between clients and tenants', function () {
    $response = $this->inTenant($this->tenant, fn () => CheckinResponse::create(['checkin_form_id' => $this->form->id, 'client_id' => $this->client->id, 'due_on' => '2026-11-02']));
    $other = $this->addClient($this->tenant, $this->owner);
    $this->actingAsIn($other, $this->tenant);
    expectApiError($this->getJson("/api/v1/me/checkins/{$response->id}"), 404, 'not_found');
    expect($this->getJson('/api/v1/me/checkins')->json('data'))->toBeEmpty();
    expectApiError($this->getJson('/api/v1/checkins'), 403, 'forbidden');

    [$tenantB, $ownerB] = $this->createCoachTenant();
    $this->actingAsIn($ownerB, $tenantB);
    expectApiError($this->getJson("/api/v1/checkins/{$response->id}"), 404, 'not_found');
    expect(collect($this->getJson('/api/v1/checkins')->json('data'))->pluck('id'))->not->toContain($response->id);
    expectApiError($this->getJson("/api/v1/checkin-forms/{$this->form->id}"), 404, 'not_found');
});
