<?php

use App\Domain\Identity\Models\User;
use App\Domain\Messaging\Models\Conversation;
use App\Domain\Messaging\Models\ConversationParticipant;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Storage;

/*
| presence-conversation.{id}: only participants who are still active members
| of the conversation's tenant may join. Returns the presence member info.
*/
Broadcast::channel('conversation.{conversationId}', function (User $user, string $conversationId) {
    $conversation = Conversation::withoutGlobalScopes()->find($conversationId);
    if (! $conversation || $user->membershipFor($conversation->tenant_id) === null) {
        return false;
    }
    $isParticipant = ConversationParticipant::query()
        ->where('conversation_id', $conversation->id)
        ->where('user_id', $user->id)
        ->exists();

    return $isParticipant ? [
        'id' => $user->id,
        'name' => $user->name,
        'avatar_url' => $user->avatar_path ? Storage::disk('public')->url($user->avatar_path) : null,
    ] : false;
});
