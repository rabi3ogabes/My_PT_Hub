<?php

use App\Domain\Community\Models\CommunitySpace;
use App\Domain\Community\Models\Post;
use App\Domain\Tenancy\Models\ClientGroup;
use App\Notifications\GenericNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    [$this->tenant, $this->coach] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->coach);
    $this->space = $this->inTenant($this->tenant, fn () => CommunitySpace::query()->first());
});

it('lists visible spaces and restricts group spaces to members', function () {
    $group = $this->inTenant($this->tenant, fn () => ClientGroup::create(['name' => 'Morning crew']));
    $groupSpace = $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/community/spaces', ['name' => 'Crew', 'client_group_id' => $group->id])->assertCreated()->json('data.id');

    expect(collect($this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/community/spaces')->json('data'))->pluck('id'))->not->toContain($groupSpace);
    $this->actingAsIn($this->client, $this->tenant)->getJson("/api/v1/community/spaces/{$groupSpace}/posts")->assertNotFound();

    $group->members()->attach($this->client->id);
    expect(collect($this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/community/spaces')->json('data'))->pluck('id'))->toContain($groupSpace);
    expectApiError($this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/community/spaces', ['name' => 'Mine']), 403, 'forbidden');
});

it('shows pinned posts first and supports reactions and polls', function () {
    $first = $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/community/spaces/{$this->space->id}/posts", ['body' => 'First!'])->assertCreated()->json('data.id');
    $this->travel(1)->minutes();
    $poll = $this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/community/spaces/{$this->space->id}/posts", [
        'type' => 'poll', 'body' => 'Best time?', 'poll' => ['options' => ['Morning', 'Evening']],
    ])->assertCreated()->json('data.id');

    $this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/community/posts/{$first}/pin")->assertOk()->assertJsonPath('data.is_pinned', true);
    $feed = $this->actingAsIn($this->client, $this->tenant)->getJson("/api/v1/community/spaces/{$this->space->id}/posts")->assertOk();
    expect(collect($feed->json('data'))->pluck('id')->all())->toBe([$first, $poll]);

    $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/community/posts/{$poll}/vote", ['option' => 1])
        ->assertOk()->assertJsonPath('data.poll.my_vote', 1)->assertJsonPath('data.poll.options.1.votes', 1);
    $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/community/posts/{$poll}/vote", ['option' => 0])
        ->assertOk()->assertJsonPath('data.poll.total_votes', 1)->assertJsonPath('data.poll.options.0.votes', 1);
    expectApiError($this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/community/posts/{$poll}/vote", ['option' => 5]), 422, 'validation_failed');

    $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/community/posts/{$first}/react")->assertOk()
        ->assertJsonPath('data.reactions_count', 1)->assertJsonPath('data.my_reaction', '🔥');
    $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/community/posts/{$first}/react")->assertOk()->assertJsonPath('data.reactions_count', 0);

    expectApiError($this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/community/posts/{$first}/pin"), 403, 'forbidden');
});

it('only lets staff post in announcement-only spaces', function () {
    $this->actingAsIn($this->coach, $this->tenant)->patchJson("/api/v1/community/spaces/{$this->space->id}", ['announcement_only' => true])->assertOk();
    expectApiError($this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/community/spaces/{$this->space->id}/posts", ['body' => 'hi']), 403, 'forbidden');
    $this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/community/spaces/{$this->space->id}/posts", ['body' => 'Announcement'])->assertCreated();
});

it('uploads media posts', function () {
    Storage::fake('public');
    $this->actingAsIn($this->client, $this->tenant)->post("/api/v1/community/spaces/{$this->space->id}/posts", [
        'media' => [UploadedFile::fake()->image('pr.jpg')],
        'body' => 'New PR',
    ], ['Accept' => 'application/json'])->assertCreated()->assertJsonPath('data.type', 'media')->assertJsonPath('data.media.0.mime', 'image/jpeg');
});

it('handles comments, hiding, deletion and moderation reports', function () {
    Notification::fake();
    $postId = $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/community/spaces/{$this->space->id}/posts", ['body' => 'Leg day'])->json('data.id');
    $this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/community/posts/{$postId}/comments", ['body' => 'Great work'])->assertCreated();
    Notification::assertSentTo($this->client, GenericNotification::class, fn ($n) => $n->category() === 'community');
    $this->actingAsIn($this->client, $this->tenant)->getJson("/api/v1/community/posts/{$postId}/comments")->assertOk()->assertJsonPath('data.0.body', 'Great work');
    expect(Post::query()->find($postId)->comments_count)->toBe(1);

    $other = $this->addClient($this->tenant, $this->coach);
    expectApiError($this->actingAsIn($other, $this->tenant)->deleteJson("/api/v1/community/posts/{$postId}"), 403, 'forbidden');
    $this->actingAsIn($other, $this->tenant)->postJson('/api/v1/community/reports', ['type' => 'post', 'id' => $postId, 'reason' => 'spam'])->assertCreated();

    $reports = $this->actingAsIn($this->coach, $this->tenant)->getJson('/api/v1/community/reports')->assertOk();
    expect($reports->json('data.0.content.body'))->toBe('Leg day');
    $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/community/reports/'.$reports->json('data.0.id').'/resolve', ['action' => 'hide'])
        ->assertOk()->assertJsonPath('data.status', 'resolved')->assertJsonPath('data.content.is_hidden', true);

    expect($this->actingAsIn($other, $this->tenant)->getJson("/api/v1/community/spaces/{$this->space->id}/posts")->json('data'))->toBe([]);
    $this->actingAsIn($other, $this->tenant)->getJson("/api/v1/community/posts/{$postId}/comments")->assertNotFound();

    $this->actingAsIn($this->client, $this->tenant)->deleteJson("/api/v1/community/posts/{$postId}")->assertNotFound(); // hidden for its author too
    $this->actingAsIn($this->coach, $this->tenant)->deleteJson("/api/v1/community/posts/{$postId}")->assertOk();
    expect(Post::query()->find($postId))->toBeNull();
});

it('isolates community content across tenants', function () {
    $postId = $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/community/spaces/{$this->space->id}/posts", ['body' => 'internal'])->json('data.id');
    [$otherTenant, $otherOwner] = $this->createCoachTenant();

    $this->actingAsIn($otherOwner, $otherTenant)->getJson("/api/v1/community/spaces/{$this->space->id}/posts")->assertNotFound();
    $this->actingAsIn($otherOwner, $otherTenant)->getJson("/api/v1/community/posts/{$postId}/comments")->assertNotFound();
    $this->actingAsIn($otherOwner, $otherTenant)->postJson("/api/v1/community/posts/{$postId}/react")->assertNotFound();
    $this->actingAsIn($otherOwner, $otherTenant)->deleteJson("/api/v1/community/posts/{$postId}")->assertNotFound();
    $this->actingAsIn($otherOwner, $otherTenant)->postJson('/api/v1/community/reports', ['type' => 'post', 'id' => $postId, 'reason' => 'spam'])->assertNotFound();
    expect(collect($this->actingAsIn($otherOwner, $otherTenant)->getJson('/api/v1/community/spaces')->json('data'))->pluck('id'))->not->toContain($this->space->id);
});
