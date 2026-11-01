<?php

use App\Domain\Checkins\Models\CheckinForm;
use App\Domain\Checkins\Models\CheckinResponse;
use App\Domain\Habits\Models\Habit;
use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Habits\Models\HabitLog;
use App\Domain\Messaging\Models\Conversation;
use App\Domain\Messaging\Models\Message;
use App\Domain\Nutrition\Models\FoodLog;
use App\Domain\Nutrition\Models\NutritionTarget;
use App\Domain\Progress\Services\ComplianceService;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\ProgramAssignment;
use App\Domain\Workouts\Models\ProgramDay;
use App\Domain\Workouts\Models\ProgramWeek;
use App\Domain\Workouts\Models\Workout;
use App\Domain\Workouts\Models\WorkoutSession;
use App\Notifications\GenericNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    [$this->tenant, $this->owner] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->owner);
    $this->travelTo(CarbonImmutable::parse('2026-11-07 12:00:00'));
});

afterEach(fn () => $this->travelBack());

function seedComplianceData($test, $client): void
{
    app(TenantContext::class)->run($test->tenant, function () use ($client) {
        $workout = Workout::create(['name' => 'Full body']);
        $program = Program::create(['name' => ['en' => 'Strength'], 'slug' => 'strength']);
        $week = ProgramWeek::create(['program_id' => $program->id, 'number' => 1]);
        foreach ([1, 3, 5] as $d) {
            ProgramDay::create(['program_week_id' => $week->id, 'day_number' => $d, 'workout_id' => $workout->id]);
        }
        ProgramDay::create(['program_week_id' => $week->id, 'day_number' => 2, 'is_rest' => true]);
        ProgramAssignment::create(['program_id' => $program->id, 'client_id' => $client->id, 'start_date' => '2026-11-01', 'status' => 'active']);
        foreach (['2026-11-01 07:00:00', '2026-11-03 07:00:00'] as $at) {
            WorkoutSession::create(['client_id' => $client->id, 'workout_id' => $workout->id, 'status' => 'completed', 'started_at' => $at, 'ended_at' => $at]);
        }

        NutritionTarget::create(['client_id' => $client->id, 'kcal' => 2000, 'protein_g' => 150, 'carbs_g' => 200, 'fat_g' => 67, 'effective_from' => '2026-10-01']);
        foreach (['2026-11-01' => 2000, '2026-11-02' => 2100, '2026-11-03' => 1900, '2026-11-04' => 1500, '2026-11-06' => 500] as $date => $kcal) {
            FoodLog::create(['client_id' => $client->id, 'log_date' => $date, 'slot' => 'lunch', 'name' => 'Meal', 'kcal' => $kcal, 'logged_at' => $date.' 12:00:00']);
        }

        $habit = Habit::create(['name' => ['en' => 'Walk'], 'type' => 'boolean']);
        $assignment = HabitAssignment::create(['habit_id' => $habit->id, 'client_id' => $client->id, 'schedule' => 'daily', 'start_date' => '2026-11-01']);
        foreach (['2026-11-01', '2026-11-02', '2026-11-03', '2026-11-04'] as $d) {
            HabitLog::create(['habit_assignment_id' => $assignment->id, 'log_date' => $d, 'completed' => true]);
        }

        $form = CheckinForm::query()->firstOrFail();
        CheckinResponse::create(['checkin_form_id' => $form->id, 'client_id' => $client->id, 'due_on' => '2026-11-02', 'status' => 'submitted', 'submitted_at' => '2026-11-02 10:00:00']);
        CheckinResponse::create(['checkin_form_id' => $form->id, 'client_id' => $client->id, 'due_on' => '2026-10-26', 'status' => 'overdue']);
    });
}

it('computes a weighted compliance score from workouts, nutrition, habits and check-ins', function () {
    seedComplianceData($this, $this->client);

    $result = $this->inTenant($this->tenant, fn () => app(ComplianceService::class)->refresh($this->client));
    expect($result['workouts_pct'])->toBe(67)      // 2 of 3 planned sessions
        ->and($result['nutrition_pct'])->toBe(50)  // 3 of 6 past days within ±10 % (today excluded)
        ->and($result['habits_pct'])->toBe(67)     // 4 of 6 scheduled days
        ->and($result['checkins_pct'])->toBe(50)   // 1 of 2 due
        ->and($result['score'])->toBe(60)          // (67×40 + 50×25 + 67×20 + 50×15) / 100
        ->and($result['risk_status'])->toBe('amber')
        ->and($result['at_risk'])->toBeFalse()
        ->and($result['days_inactive'])->toBe(1);

    $profile = ClientProfile::withoutGlobalScopes()->where('user_id', $this->client->id)->sole();
    expect($profile->compliance_score)->toBe(60)->and($profile->risk_status)->toBe('amber');

    // Tenant-configurable weights.
    $this->tenant->update(['settings' => array_merge($this->tenant->settings, ['compliance_weights' => ['workouts' => 100, 'nutrition' => 0, 'habits' => 0, 'checkins' => 0]])]);
    $weighted = $this->inTenant($this->tenant->fresh(), fn () => app(ComplianceService::class)->forClient($this->client));
    expect($weighted['score'])->toBe(67)->and($weighted['risk_status'])->toBe('amber');
});

it('flags inactive clients and score drops as at risk', function () {
    $this->inTenant($this->tenant, function () {
        NutritionTarget::create(['client_id' => $this->client->id, 'kcal' => 2000, 'protein_g' => 150, 'carbs_g' => 200, 'fat_g' => 67, 'effective_from' => '2026-10-01']);
        // Perfect previous week, nothing in the last 4 days of this week.
        foreach (range(25, 31) as $d) {
            FoodLog::create(['client_id' => $this->client->id, 'log_date' => "2026-10-$d", 'slot' => 'lunch', 'name' => 'Meal', 'kcal' => 2000, 'logged_at' => now()]);
        }
        foreach (['2026-11-01', '2026-11-02'] as $d) {
            FoodLog::create(['client_id' => $this->client->id, 'log_date' => $d, 'slot' => 'lunch', 'name' => 'Meal', 'kcal' => 2000, 'logged_at' => now()]);
        }
    });
    Membership::withoutGlobalScopes()->where('user_id', $this->client->id)->update(['joined_at' => '2026-09-01 00:00:00']);

    $result = $this->inTenant($this->tenant, fn () => app(ComplianceService::class)->forClient($this->client));
    expect($result['previous_score'])->toBe(100)
        ->and($result['score'])->toBe(33)
        ->and($result['days_inactive'])->toBe(5)
        ->and($result['reasons'])->toContain('inactive', 'score_drop', 'low_nutrition')
        ->and($result['at_risk'])->toBeTrue()
        ->and($result['risk_status'])->toBe('red');
});

it('lists compliance for coaches, sends the digest and bulk-messages at-risk clients', function () {
    Notification::fake();
    $good = $this->addClient($this->tenant, $this->owner);
    seedComplianceData($this, $good);
    Membership::withoutGlobalScopes()->where('user_id', $this->client->id)->update(['joined_at' => '2026-09-01 00:00:00']);

    $this->actingAsIn($this->owner, $this->tenant);
    $list = $this->getJson('/api/v1/compliance')->assertOk();
    $rows = collect($list->json('data'))->keyBy('client.id');
    expect($rows[$this->client->id]['risk_status'])->toBe('red')
        ->and($rows[$this->client->id]['reasons'])->toContain('inactive')
        ->and($rows[$good->id]['score'])->toBe(60);

    $atRisk = $this->getJson('/api/v1/compliance?at_risk=1')->assertOk();
    expect(collect($atRisk->json('data'))->pluck('client.id')->all())->toBe([$this->client->id]);

    expect(app(ComplianceService::class)->sendDigests(null, $this->tenant))->toBe(1);
    Notification::assertSentTo($this->owner, GenericNotification::class);

    $sent = $this->postJson('/api/v1/compliance/message', ['at_risk' => true, 'body' => 'Checking in — how are you doing this week?'])->assertCreated();
    expect($sent->json('data.sent'))->toBe(1);
    $this->postJson('/api/v1/compliance/message', ['client_ids' => [$this->client->id, $good->id], 'body' => 'New program next week!'])->assertCreated()->assertJsonPath('data.sent', 2);

    $conversations = Conversation::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->get();
    expect($conversations)->toHaveCount(2)
        ->and($conversations->every(fn ($c) => $c->type === 'direct'))->toBeTrue();
    $convo = Conversation::withoutGlobalScopes()->whereHas('participants', fn ($q) => $q->where('user_id', $this->client->id))->sole();
    $messages = Message::query()->where('conversation_id', $convo->id)->orderBy('created_at')->get();
    expect($messages)->toHaveCount(2)
        ->and($messages[0]->body)->toBe('Checking in — how are you doing this week?')
        ->and($messages[0]->sender_id)->toBe($this->owner->id);

    // Cross-tenant: another business can't message these clients.
    [$tenantB, $ownerB] = $this->createCoachTenant();
    expectApiError($this->actingAsIn($ownerB, $tenantB)->postJson('/api/v1/compliance/message', ['client_ids' => [$this->client->id], 'body' => 'hi']), 422, 'validation_failed');
    expect($this->getJson('/api/v1/compliance')->json('data'))->toBeEmpty();
});
