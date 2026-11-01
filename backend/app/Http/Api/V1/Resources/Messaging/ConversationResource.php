<?php

namespace App\Http\Api\V1\Resources\Messaging;

use App\Domain\Messaging\Models\Conversation;
use App\Domain\Messaging\Models\ConversationParticipant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Expects the conversation to be prepared by MessagingService::present() for the viewer.
 *
 * @mixin Conversation
 */
class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $p = $this->presentation;
        $roles = $p['roles'] ?? [];
        $viewer = $p['viewer_id'] ?? $request->user()?->id;
        $participants = $this->participants->filter(fn (ConversationParticipant $cp) => $cp->user !== null);

        $title = $this->title;
        if ($title === null && $this->type === 'direct') {
            $title = $participants->first(fn (ConversationParticipant $cp) => $cp->user_id !== $viewer)?->user?->name;
        }

        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $title,
            'participants' => $participants->map(fn (ConversationParticipant $cp) => UserSummary::make($cp->user) + [
                'role' => $roles[$cp->user_id] ?? null,
            ])->values()->all(),
            'last_message' => $this->latestMessage ? MessageResource::forViewer($this->latestMessage, $viewer) : null,
            'unread_count' => (int) ($p['unread_count'] ?? 0),
            'is_muted' => (bool) ($p['is_muted'] ?? false),
            'updated_at' => ($this->last_message_at ?? $this->updated_at)?->toIso8601String(),
        ];
    }
}
