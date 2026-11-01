<?php

namespace App\Domain\Messaging\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/** Broadcast on presence-conversation.{id} as "message.deleted". Payload is the Message resource. */
class MessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    /**
     * @param  array<string, mixed>  $message
     */
    public function __construct(public readonly string $conversationId, public readonly array $message) {}

    /** @return list<PresenceChannel> */
    public function broadcastOn(): array
    {
        return [new PresenceChannel('conversation.'.$this->conversationId)];
    }

    public function broadcastAs(): string
    {
        return 'message.deleted';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['message' => $this->message];
    }
}
