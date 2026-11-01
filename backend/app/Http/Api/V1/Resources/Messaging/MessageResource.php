<?php

namespace App\Http\Api\V1\Resources\Messaging;

use App\Domain\Messaging\Models\Message;
use App\Domain\Messaging\Models\MessageAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Message */
class MessageResource extends JsonResource
{
    /** Viewer used for `reactions[].mine`; defaults to the authenticated user. */
    public ?string $viewerId = null;

    public static function forViewer(Message $message, ?string $viewerId): array
    {
        $resource = new self($message);
        $resource->viewerId = $viewerId;

        return $resource->resolve(request());
    }

    public function toArray(Request $request): array
    {
        $viewer = $this->viewerId ?? $request->user()?->id;
        $deleted = $this->trashed();

        $reactions = [];
        if (! $deleted) {
            foreach ($this->reactions->groupBy('emoji') as $emoji => $group) {
                $reactions[] = ['emoji' => (string) $emoji, 'count' => $group->count(), 'mine' => $viewer !== null && $group->contains('user_id', $viewer)];
            }
        }

        $reply = $this->replyTo;

        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender' => UserSummary::make($this->sender),
            'type' => $this->type,
            'body' => $deleted ? null : $this->body,
            'meta' => (object) ($deleted ? [] : ($this->meta ?? [])),
            'attachments' => $deleted ? [] : $this->attachments->map(fn (MessageAttachment $a) => [
                'id' => $a->id,
                'mime' => $a->mime,
                'size' => (int) $a->size,
                'url' => route('api.v1.messages.attachment', ['message' => $this->id, 'attachment' => $a->id]),
                'duration_ms' => $a->duration_ms !== null ? (int) $a->duration_ms : null,
                'waveform' => $a->waveform,
            ])->values()->all(),
            'reply_to' => $reply ? [
                'id' => $reply->id,
                'body' => $reply->trashed() ? null : $reply->body,
                'sender_name' => $reply->sender?->name,
            ] : null,
            'reactions' => $reactions,
            'channel' => $this->channel,
            'edited_at' => $this->edited_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'deleted' => $deleted,
        ];
    }
}
