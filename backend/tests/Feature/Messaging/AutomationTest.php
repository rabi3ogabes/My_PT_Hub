<?php

use App\Domain\Habits\Models\Habit;
use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Messaging\Models\Automation;
use App\Domain\Messaging\Models\AutomationRun;
use App\Domain\Messaging\Models\Message;
use App\Domain\Messaging\Services\AutomationEngine;
use App\Domain\Tenancy\Events\ClientJoined;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Workouts\Events\PersonalRecordAchieved;
use App\Domain\Workouts\Models\PersonalRecord;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\ProgramAssignment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

beforeEach(function () {
    [$this->tenant, $this->coach] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->coach, ['name' => 'Omar Haddad', 'locale' => 'fr']);
});

it('validates and stores automations', function () {
    $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/automations', [
        'name' => 'Bad', 'trigger' => 'nope', 'actions' => [],
    ])->assertStatus(422)->assertJsonPath('errors.0.code', 'validation_failed');

    $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/automations', [
        'name' => 'Missing template', 'trigger' => 'client_joined', 'actions' => [['type' => 'send_message']],
    ])->assertStatus(422)->assertJsonPath('errors.0.field_errors', fn ($f) => isset($f['actions.0.body']));

    $response = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/automations', [
        'name' => 'Welcome',
        'trigger' => 'client_joined',
        'conditions' => [['field' => 'goal', 'op' => 'eq', 'value' => 'fat_loss']],
        'actions' => [['type' => 'send_message', 'body' => ['en' => 'Welcome {first_name}!', 'fr' => 'Bienvenue {first_name} !']], ['type' => 'add_tag', 'tag' => 'onboarding']],
    ])->assertCreated()->assertJsonPath('data.trigger', 'client_joined');

    $id = $response->json('data.id');
    $this->actingAsIn($this->coach, $this->tenant)->getJson('/api/v1/automations')->assertOk()->assertJsonPath('data.0.id', $id);
    $this->actingAsIn($this->coach, $this->tenant)->patchJson("/api/v1/automations/{$id}", ['is_active' => false])->assertOk()->assertJsonPath('data.is_active', false);
    $this->actingAsIn($this->coach, $this->tenant)->deleteJson("/api/v1/automations/{$id}")->assertOk();

    $assistant = $this->addStaff($this->tenant, 'assistant');
    expectApiError($this->actingAsIn($assistant, $this->tenant)->postJson('/api/v1/automations', ['name' => 'x', 'trigger' => 'client_joined', 'actions' => [['type' => 'add_tag', 'tag' => 'x']]]), 403, 'forbidden');
    expectApiError($this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/automations'), 403, 'forbidden');
});

it('fires client_joined once, sends the template in the client locale, and logs the run', function () {
    $this->inTenant($this->tenant, fn () => Automation::create([
        'name' => 'Welcome', 'trigger' => 'client_joined',
        'actions' => [['type' => 'send_message', 'body' => ['en' => 'Welcome {first_name}!', 'fr' => 'Bienvenue {first_name} !']], ['type' => 'add_tag', 'tag' => 'new']],
    ]));

    event(new ClientJoined($this->tenant, $this->client));
    event(new ClientJoined($this->tenant, $this->client));
    app(AutomationEngine::class)->fire('client_joined', $this->tenant, $this->client);

    expect(AutomationRun::query()->count())->toBe(1);
    $run = AutomationRun::query()->first();
    expect($run->status)->toBe('done')->and($run->client_id)->toBe($this->client->id)->and($run->result['actions'][0]['ok'])->toBeTrue();
    $messages = Message::query()->get();
    expect($messages)->toHaveCount(1)->and($messages->first()->body)->toBe('Bienvenue Omar !')->and($messages->first()->sender_id)->toBe($this->coach->id);
    $profile = $this->inTenant($this->tenant, fn () => ClientProfile::query()->where('user_id', $this->client->id)->first());
    expect($profile->tags)->toBe(['new']);
});

it('evaluates conditions before acting', function () {
    $this->inTenant($this->tenant, function () {
        ClientProfile::query()->where('user_id', $this->client->id)->update(['goal' => 'strength', 'tags' => json_encode(['vip'])]);
        Automation::create(['name' => 'Fat loss only', 'trigger' => 'payment_failed', 'conditions' => [['field' => 'goal', 'op' => 'eq', 'value' => 'fat_loss']], 'actions' => [['type' => 'add_tag', 'tag' => 'a']]]);
        Automation::create(['name' => 'VIP', 'trigger' => 'payment_failed', 'conditions' => [['field' => 'tags', 'op' => 'contains', 'value' => 'VIP'], ['field' => 'goal', 'op' => 'in', 'value' => ['strength', 'performance']]], 'actions' => [['type' => 'add_tag', 'tag' => 'b']]]);
    });

    $runs = app(AutomationEngine::class)->fire('payment_failed', $this->tenant, $this->client, ['dedupe' => 'inv_1']);
    expect($runs)->toHaveCount(1);
    $profile = $this->inTenant($this->tenant, fn () => ClientProfile::query()->where('user_id', $this->client->id)->first());
    expect($profile->tags)->toBe(['vip', 'b']);

    // Same occurrence → deduplicated; new occurrence → fires again.
    expect(app(AutomationEngine::class)->fire('payment_failed', $this->tenant, $this->client, ['dedupe' => 'inv_1']))->toBe([]);
    expect(app(AutomationEngine::class)->fire('payment_failed', $this->tenant, $this->client, ['dedupe' => 'inv_2']))->toHaveCount(1);
});

it('fires "missed 3 days → send message" exactly once from the scheduled scan', function () {
    CarbonImmutable::setTestNow('2026-11-01 10:00:00');
    $this->inTenant($this->tenant, function () {
        $program = Program::create(['name' => ['en' => 'Shred 8'], 'tenant_id' => $this->tenant->id]);
        ProgramAssignment::create(['program_id' => $program->id, 'client_id' => $this->client->id, 'start_date' => '2026-10-20', 'status' => 'active']);
        $habit = Habit::create(['name' => ['en' => 'Walk'], 'tenant_id' => $this->tenant->id]);
        Automation::create([
            'name' => 'Missed 3 days', 'trigger' => 'workout_missed', 'trigger_config' => ['days' => 3],
            'actions' => [['type' => 'send_message', 'body' => ['en' => 'Hey {first_name}, ready to get back to {program}?']], ['type' => 'assign_habit', 'habit_id' => $habit->id], ['type' => 'notify_coach']],
        ]);
    });
    $this->client->update(['locale' => 'en']);

    $engine = app(AutomationEngine::class);
    expect($engine->scan(CarbonImmutable::now()))->toBe(1);
    expect($engine->scan(CarbonImmutable::now()->addHours(5)))->toBe(0);

    expect(AutomationRun::query()->count())->toBe(1)
        ->and(Message::query()->first()->body)->toBe('Hey Omar, ready to get back to Shred 8?')
        ->and($this->inTenant($this->tenant, fn () => HabitAssignment::query()->where('client_id', $this->client->id)->count()))->toBe(1);
    CarbonImmutable::setTestNow();
});

it('never fires automations of another tenant', function () {
    [$otherTenant] = $this->createCoachTenant();
    $this->inTenant($otherTenant, fn () => Automation::create(['name' => 'Other', 'trigger' => 'client_joined', 'actions' => [['type' => 'add_tag', 'tag' => 'x']]]));

    app(AutomationEngine::class)->fire('client_joined', $this->tenant, $this->client);
    expect(AutomationRun::query()->count())->toBe(0);

    [$t2, $o2] = $this->createCoachTenant();
    $id = $this->inTenant($otherTenant, fn () => Automation::query()->value('id'));
    $this->actingAsIn($o2, $t2)->getJson("/api/v1/automations/{$id}")->assertNotFound();
});

it('fires pr_hit from the workouts PersonalRecordAchieved event exactly once and can assign a program', function () {
    $program = $this->inTenant($this->tenant, fn () => Program::create(['name' => ['en' => 'Peak'], 'tenant_id' => $this->tenant->id]));
    $this->inTenant($this->tenant, fn () => Automation::create([
        'name' => 'PR celebration', 'trigger' => 'pr_hit',
        'actions' => [['type' => 'send_message', 'body' => ['en' => 'New PR, {first_name}!', 'fr' => 'Nouveau record, {first_name} !']], ['type' => 'assign_program', 'program_id' => $program->id]],
    ]));
    $record = new PersonalRecord(['tenant_id' => $this->tenant->id, 'client_id' => $this->client->id, 'type' => 'e1rm', 'value' => 120]);
    $record->id = (string) Str::ulid();

    event(new PersonalRecordAchieved($record));
    event(new PersonalRecordAchieved($record));

    $runs = AutomationRun::query()->get();
    expect($runs)->toHaveCount(1)->and($runs->first()->status)->toBe('done');
    expect(Message::query()->first()->body)->toBe('Nouveau record, Omar !');
    expect($this->inTenant($this->tenant, fn () => ProgramAssignment::query()->where('client_id', $this->client->id)->where('program_id', $program->id)->count()))->toBe(1);
});
