<?php

namespace App\Domain\Messaging\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/** Broadcast on presence-conversation.{id} as "typing". */
class Typing implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public readonly string $conversationId,
        public readonly string $userId,
        public readonly string $userName,
        public readonly bool $isTyping = true,
    ) {}

    /** @return list<PresenceChannel> */
    public function broadcastOn(): array
    {
        return [new PresenceChannel('conversation.'.$this->conversationId)];
    }

    public function broadcastAs(): string
    {
        return 'typing';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return ['conversation_id' => $this->conversationId, 'user' => ['id' => $this->userId, 'name' => $this->userName], 'typing' => $this->isTyping];
    }
}
