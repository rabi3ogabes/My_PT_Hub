<?php

use App\Domain\Messaging\Events\ConversationUpdated;
use App\Domain\Messaging\Events\MessageSent;
use App\Domain\Messaging\Events\Typing;
use App\Domain\Messaging\Models\Conversation;
use App\Domain\Messaging\Models\Message;
use App\Domain\Messaging\Services\MessagingService;
use App\Notifications\GenericNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    [$this->tenant, $this->coach] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->coach);
});

it('lets a client open a direct conversation with their coach, idempotently', function () {
    $first = $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/conversations', ['user_id' => $this->coach->id])->assertCreated();
    $id = $first->json('data.id');
    expect($first->json('data.type'))->toBe('direct')
        ->and(collect($first->json('data.participants'))->pluck('role')->sort()->values()->all())->toBe(['client', 'owner']);

    $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/conversations', ['user_id' => $this->coach->id])->assertOk()->assertJsonPath('data.id', $id);
    $this->actingAsIn($this->coach, $this->tenant)->postJson('/api/v1/conversations', ['user_id' => $this->client->id])->assertOk()->assertJsonPath('data.id', $id);
    expect(Conversation::withoutGlobalScopes()->count())->toBe(1);
});

it('forbids clients from opening conversations with other clients or creating groups', function () {
    $other = $this->addClient($this->tenant, $this->coach);
    expectApiError($this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/conversations', ['user_id' => $other->id]), 403, 'forbidden');
    expectApiError($this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/conversations', ['type' => 'group', 'user_ids' => [$this->coach->id]]), 403, 'forbidden');
});

it('sends messages idempotently on the client ULID', function () {
    $conversationId = $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/conversations', ['user_id' => $this->coach->id])->json('data.id');
    $id = (string) Str::ulid();

    $this->actingAsIn($this->client, $this->tenant)
        ->postJson("/api/v1/conversations/{$conversationId}/messages", ['id' => $id, 'type' => 'text', 'body' => 'Hello coach'])
        ->assertCreated()->assertJsonPath('data.id', $id)->assertJsonPath('data.body', 'Hello coach');

    $this->actingAsIn($this->client, $this->tenant)
        ->postJson("/api/v1/conversations/{$conversationId}/messages", ['id' => $id, 'type' => 'text', 'body' => 'Hello coach'])
        ->assertOk()->assertJsonPath('data.id', $id);

    expect(Message::query()->count())->toBe(1);
    // Encrypted at rest.
    expect(DB::table('messages')->value('body'))->not->toContain('Hello coach');
});

it('rejects a ULID that belongs to another conversation', function () {
    $conversationId = $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/conversations', ['user_id' => $this->coach->id])->json('data.id');
    $id = (string) Str::ulid();
    $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/conversations/{$conversationId}/messages", ['id' => $id, 'body' => 'one'])->assertCreated();

    $assistant = $this->addStaff($this->tenant, 'assistant');
    $other = $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/conversations', ['user_id' => $assistant->id])->json('data.id');
    expectApiError($this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/conversations/{$other}/messages", ['id' => $id, 'body' => 'two']), 409, 'id_conflict');
});

it('tracks unread counts and read receipts', function () {
    $conversationId = $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/conversations', ['user_id' => $this->coach->id])->json('data.id');
    foreach (['a', 'b', 'c'] as $body) {
        $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/conversations/{$conversationId}/messages", ['body' => $body])->assertCreated();
    }

    $inbox = $this->actingAsIn($this->coach, $this->tenant)->getJson('/api/v1/conversations')->assertOk();
    expect($inbox->json('data.0.unread_count'))->toBe(3)
        ->and($inbox->json('data.0.last_message.body'))->toBe('c')
        ->and($inbox->json('meta.total_unread'))->toBe(3);
    expect($this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/conversations')->json('data.0.unread_count'))->toBe(0);

    $this->travel(2)->seconds();
    $this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/conversations/{$conversationId}/read")->assertOk()->assertJsonPath('data.unread_count', 0);
    expect($this->actingAsIn($this->coach, $this->tenant)->getJson("/api/v1/conversations/{$conversationId}")->json('data.unread_count'))->toBe(0);

    $this->travel(2)->seconds();
    $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/conversations/{$conversationId}/messages", ['body' => 'd'])->assertCreated();
    $conversation = Conversation::withoutGlobalScopes()->find($conversationId);
    expect(app(MessagingService::class)->unreadCount($conversation, $this->coach))->toBe(1);
});

it('paginates history newest first with a cursor', function () {
    $conversationId = $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/conversations', ['user_id' => $this->coach->id])->json('data.id');
    foreach (range(1, 5) as $i) {
        $this->travel(1)->seconds();
        $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/conversations/{$conversationId}/messages", ['body' => "m{$i}"]);
    }
    $page = $this->actingAsIn($this->coach, $this->tenant)->getJson("/api/v1/conversations/{$conversationId}/messages?per_page=2")->assertOk();
    expect(collect($page->json('data'))->pluck('body')->all())->toBe(['m5', 'm4']);
    $next = $this->actingAsIn($this->coach, $this->tenant)->getJson("/api/v1/conversations/{$conversationId}/messages?per_page=2&cursor=".$page->json('meta.next_cursor'))->assertOk();
    expect(collect($next->json('data'))->pluck('body')->all())->toBe(['m3', 'm2']);
});

it('allows editing own messages within 15 minutes only', function () {
    $conversationId = $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/conversations', ['user_id' => $this->coach->id])->json('data.id');
    $id = $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/conversations/{$conversationId}/messages", ['body' => 'typo'])->json('data.id');

    expectApiError($this->actingAsIn($this->coach, $this->tenant)->patchJson("/api/v1/messages/{$id}", ['body' => 'hijack']), 403, 'forbidden');

    $this->travel(10)->minutes();
    $this->actingAsIn($this->client, $this->tenant)->patchJson("/api/v1/messages/{$id}", ['body' => 'fixed'])
        ->assertOk()->assertJsonPath('data.body', 'fixed')->assertJsonPath('data.edited_at', fn ($v) => $v !== null);

    $this->travel(6)->minutes();
    expectApiError($this->actingAsIn($this->client, $this->tenant)->patchJson("/api/v1/messages/{$id}", ['body' => 'late']), 409, 'edit_window_expired');
});

it('deletes for everyone, leaving a tombstone', function () {
    $conversationId = $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/conversations', ['user_id' => $this->coach->id])->json('data.id');
    $id = $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/conversations/{$conversationId}/messages", ['body' => 'oops'])->json('data.id');
    $this->actingAsIn($this->client, $this->tenant)->deleteJson("/api/v1/messages/{$id}")->assertOk()->assertJsonPath('data.deleted', true)->assertJsonPath('data.body', null);

    $history = $this->actingAsIn($this->coach, $this->tenant)->getJson("/api/v1/conversations/{$conversationId}/messages")->assertOk();
    expect($history->json('data.0.deleted'))->toBeTrue()->and($history->json('data.0.body'))->toBeNull();
});

it('toggles reactions', function () {
    $conversationId = $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/conversations', ['user_id' => $this->coach->id])->json('data.id');
    $id = $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/conversations/{$conversationId}/messages", ['body' => 'PR today!'])->json('data.id');

    $this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/messages/{$id}/reactions", ['emoji' => '🔥'])
        ->assertOk()->assertJsonPath('data.reactions.0.emoji', '🔥')->assertJsonPath('data.reactions.0.count', 1)->assertJsonPath('data.reactions.0.mine', true);
    $this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/messages/{$id}/reactions", ['emoji' => '🔥'])
        ->assertOk()->assertJsonCount(0, 'data.reactions');
});

it('stores voice notes with duration and waveform and streams them to participants only', function () {
    Storage::fake('local');
    $conversationId = $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/conversations', ['user_id' => $this->coach->id])->json('data.id');

    $response = $this->actingAsIn($this->client, $this->tenant)->post("/api/v1/conversations/{$conversationId}/messages", [
        'type' => 'voice',
        'file' => UploadedFile::fake()->create('note.m4a', 120, 'audio/mp4'),
        'meta' => json_encode(['duration_ms' => 4200, 'waveform' => [0, 12, 40, 80, 33]]),
    ], ['Accept' => 'application/json'])->assertCreated();

    expect($response->json('data.type'))->toBe('voice')
        ->and($response->json('data.attachments.0.duration_ms'))->toBe(4200)
        ->and($response->json('data.attachments.0.waveform'))->toBe([0, 12, 40, 80, 33]);
    $messageId = $response->json('data.id');
    $attachmentId = $response->json('data.attachments.0.id');
    $url = "/api/v1/messages/{$messageId}/attachments/{$attachmentId}";

    $this->actingAsIn($this->coach, $this->tenant)->get($url, ['Accept' => 'application/json'])->assertOk();

    // Same tenant, not a participant → 404.
    $outsider = $this->addClient($this->tenant, $this->coach);
    $this->actingAsIn($outsider, $this->tenant)->getJson($url)->assertNotFound();

    // Other tenant (even its owner, using their own tenant header) → 404.
    [$otherTenant, $otherOwner] = $this->createCoachTenant();
    $this->actingAsIn($otherOwner, $otherTenant)->getJson($url)->assertNotFound();
    // Other tenant user forcing the victim tenant header → not a member.
    expectApiError($this->actingAsIn($otherOwner, $this->tenant)->getJson($url), 403, 'forbidden');
});

it('isolates conversations across tenants', function () {
    $conversationId = $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/conversations', ['user_id' => $this->coach->id])->json('data.id');
    $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/conversations/{$conversationId}/messages", ['body' => 'private']);
    $messageId = Message::query()->value('id');

    [$otherTenant, $otherOwner] = $this->createCoachTenant();
    $this->actingAsIn($otherOwner, $otherTenant)->getJson("/api/v1/conversations/{$conversationId}")->assertNotFound();
    $this->actingAsIn($otherOwner, $otherTenant)->getJson("/api/v1/conversations/{$conversationId}/messages")->assertNotFound();
    $this->actingAsIn($otherOwner, $otherTenant)->patchJson("/api/v1/messages/{$messageId}", ['body' => 'x'])->assertNotFound();
    $this->actingAsIn($otherOwner, $otherTenant)->deleteJson("/api/v1/messages/{$messageId}")->assertNotFound();
    expect($this->actingAsIn($otherOwner, $otherTenant)->getJson('/api/v1/conversations')->json('data'))->toBe([]);
});

it('authorizes the presence channel for participants only', function () {
    $conversationId = $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/conversations', ['user_id' => $this->coach->id])->json('data.id');
    $callback = Broadcast::getChannels()['conversation.{conversationId}'];
    $outsider = $this->addClient($this->tenant, $this->coach);
    [, $otherOwner] = $this->createCoachTenant();

    expect($callback($this->client, $conversationId))->toMatchArray(['id' => $this->client->id, 'name' => $this->client->name])
        ->and($callback($this->coach, $conversationId))->toBeArray()
        ->and($callback($outsider, $conversationId))->toBeFalse()
        ->and($callback($otherOwner, $conversationId))->toBeFalse()
        ->and($callback($this->client, (string) Str::ulid()))->toBeFalse();

    $userChannel = Broadcast::getChannels()['user.{id}'];
    expect($userChannel($this->client, $this->client->id))->toBeTrue()->and($userChannel($this->client, $this->coach->id))->toBeFalse();
});

it('broadcasts message and conversation events', function () {
    Event::fake([MessageSent::class, ConversationUpdated::class, Typing::class]);
    $conversationId = $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/conversations', ['user_id' => $this->coach->id])->json('data.id');
    $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/conversations/{$conversationId}/messages", ['body' => 'hi'])->assertCreated();
    $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/conversations/{$conversationId}/typing")->assertOk();

    Event::assertDispatched(MessageSent::class, fn ($e) => $e->broadcastOn()[0]->name === 'presence-conversation.'.$conversationId && $e->broadcastAs() === 'message.sent');
    Event::assertDispatched(ConversationUpdated::class, fn ($e) => $e->broadcastOn()[0]->name === 'private-user.'.$this->coach->id);
    Event::assertDispatched(Typing::class);
});

it('pushes to offline participants but respects mute and presence', function () {
    Notification::fake();
    $conversationId = $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/conversations', ['user_id' => $this->coach->id])->json('data.id');
    $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/conversations/{$conversationId}/messages", ['body' => 'ping'])->assertCreated();
    Notification::assertSentTo($this->coach, GenericNotification::class, fn ($n) => $n->category() === 'messages' && $n->pushData()['conversation_id'] === $conversationId);
    Notification::assertNotSentTo($this->client, GenericNotification::class);

    Notification::fake();
    $this->actingAsIn($this->coach, $this->tenant)->postJson("/api/v1/conversations/{$conversationId}/read");
    $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/conversations/{$conversationId}/messages", ['body' => 'while you look'])->assertCreated();
    Notification::assertNothingSentTo($this->coach);

    Notification::fake();
    Cache::flush();
    $conversation = Conversation::withoutGlobalScopes()->find($conversationId);
    app(MessagingService::class)->setMuted($conversation, $this->coach, true);
    $this->actingAsIn($this->client, $this->tenant)->postJson("/api/v1/conversations/{$conversationId}/messages", ['body' => 'muted'])->assertCreated();
    Notification::assertNothingSentTo($this->coach);
});
