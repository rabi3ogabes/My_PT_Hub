<?php

use App\Notifications\GenericNotification;

beforeEach(function () {
    [$this->tenant, $this->coach] = $this->createCoachTenant();
    $this->client = $this->addClient($this->tenant, $this->coach);
});

it('lists notifications newest first with unread count and marks them read', function () {
    foreach (['One', 'Two', 'Three'] as $i => $title) {
        $this->travel(1)->seconds();
        $this->client->notify(new GenericNotification($i === 2 ? 'bookings' : 'messages', $title, "Body {$title}", '/x', ['conversation_id' => 'c1']));
    }
    $this->coach->notify(new GenericNotification('messages', 'Not yours', 'x'));

    $res = $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/notifications?per_page=2')->assertOk();
    expect(collect($res->json('data'))->pluck('title')->all())->toBe(['Three', 'Two'])
        ->and($res->json('meta.unread_count'))->toBe(3)
        ->and($res->json('data.0.category'))->toBe('bookings')
        ->and($res->json('data.1.data.conversation_id'))->toBe('c1');
    $next = $this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/notifications?per_page=2&cursor='.$res->json('meta.next_cursor'))->assertOk();
    expect(collect($next->json('data'))->pluck('title')->all())->toBe(['One']);

    expect(collect($this->actingAsIn($this->client, $this->tenant)->getJson('/api/v1/notifications?filter[category]=bookings')->json('data'))->pluck('title')->all())->toBe(['Three']);

    $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/notifications/read', ['ids' => [$res->json('data.0.id')]])
        ->assertOk()->assertJsonPath('data.marked', 1)->assertJsonPath('meta.unread_count', 2);
    // Another user's ids are ignored.
    $foreign = $this->coach->notifications()->value('id');
    $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/notifications/read', ['ids' => [$foreign]])->assertJsonPath('data.marked', 0);
    $this->actingAsIn($this->client, $this->tenant)->postJson('/api/v1/notifications/read')->assertOk()->assertJsonPath('meta.unread_count', 0);
    expect($this->coach->unreadNotifications()->count())->toBe(1);
});
