<?php

use App\Domain\Community\Models\UserBadge;
use App\Domain\Community\Services\BadgeService;
use App\Domain\Community\Services\ChallengeScorer;
use App\Domain\Workouts\Models\WorkoutSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

beforeEach(function () {
    [$this->tenant, $this->coach] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->coach);
    $this->rival = $this->addClient($this->tenant, $this->coach);
    $this->travelTo(now()->setDate(2026, 11, 10)->setTime(12, 0));
});

function stepsFor(string $userId, string $date, int $value): void
{
    DB::table('health_daily')->insert(['id' => (string) Str::ulid(), 'user_id' => $userId, 'date' => $date, 'type' => 'steps', 'value' => $value, 'source' => 'healthkit', 'created_at' => now(), 'updated_at' => now()]);
}

it('runs a steps challenge with a leaderboard and awards the badge on completion', function () {
    $challenge = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/challenges', [
        'name' => 'November steps', 'metric' => 'steps', 'goal' => 50000, 'starts_on' => '2026-11-01', 'ends_on' => '2026-11-30', 'badge_key' => 'nov_steps',
    ])->assertCreated()->json('data.id');
    expectApiError($this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/challenges', ['name' => 'x', 'metric' => 'steps', 'starts_on' => '2026-11-01', 'ends_on' => '2026-11-02']), 403, 'forbidden');

    stepsFor($this->client->id, '2026-11-02', 30000);
    stepsFor($this->client->id, '2026-11-03', 25000);
    stepsFor($this->client->id, '2026-10-30', 99999); // before the challenge
    stepsFor($this->rival->id, '2026-11-02', 12000);

    $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/challenges/{$challenge}/join")->assertOk()->assertJsonPath('data.joined', true);
    $this->actingAsIn($this->rival, $this->tenant)->postJson("/api/v1/challenges/{$challenge}/join")->assertOk();

    app(ChallengeScorer::class)->scoreActive();

    $show = $this->actingAsIn($this->rival, $this->tenant)->getJson("/api/v1/challenges/{$challenge}")->assertOk();
    expect($show->json('data.leaderboard.0.user.id'))->toBe($this->client->id)
        ->and((float) $show->json('data.leaderboard.0.score'))->toBe(55000.0)
        ->and($show->json('data.leaderboard.0.completed_at'))->not->toBeNull()
        ->and($show->json('data.my_entry.rank'))->toBe(2)
        ->and($show->json('data.participants_count'))->toBe(2);

    expect(UserBadge::query()->where('user_id', $this->client->id)->where('badge_key', 'nov_steps')->exists())->toBeTrue();
    $badges = $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/badges')->assertOk();
    expect(collect($badges->json('data'))->firstWhere('key', 'nov_steps')['earned'])->toBeTrue();
});

it('awards first workout and streak badges once', function () {
    $this->inTenant($this->tenant, function () {
        foreach (range(0, 6) as $d) {
            WorkoutSession::create(['client_id' => $this->client->id, 'status' => 'completed', 'started_at' => now()->subDays($d)->setTime(8, 0), 'ended_at' => now()->subDays($d)->setTime(9, 0)]);
        }
    });
    $service = app(BadgeService::class);
    $awarded = $service->evaluate($this->tenant, $this->client);
    expect($awarded)->toContain('first_workout')->toContain('streak_7')->not->toContain('streak_30');
    expect($service->evaluate($this->tenant, $this->client))->toBe([]);
    expect(UserBadge::query()->where('user_id', $this->client->id)->count())->toBe(2);

    $staffView = $this->actingAsIn($this->coach, $this->tenant)->getJson('/api/v1/badges?user_id='.$this->client->id)->assertOk();
    expect($staffView->json('meta.earned_count'))->toBe(2);
    expectApiError($this->actingAsIn($this->rival, $this->tenant)->getJson('/api/v1/badges?user_id='.$this->client->id), 403, 'forbidden');
});

it('keeps challenges tenant-isolated', function () {
    $challenge = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/challenges', [
        'name' => 'Workouts', 'metric' => 'workouts', 'goal' => 10, 'starts_on' => '2026-11-01', 'ends_on' => '2026-11-30',
    ])->json('data.id');
    [$otherTenant, $otherOwner] = $this->createCoachTenant();
    $this->actingAsIn($otherOwner, $otherTenant)->getJson("/api/v1/challenges/{$challenge}")->assertNotFound();
    $this->actingAsIn($otherOwner, $otherTenant)->postJson("/api/v1/challenges/{$challenge}/join")->assertNotFound();
    expect($this->actingAsIn($otherOwner, $otherTenant)->getJson('/api/v1/challenges')->json('data'))->toBe([]);
});
