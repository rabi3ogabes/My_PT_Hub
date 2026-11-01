<?php

namespace App\Domain\Messaging\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Messaging\Events\ConversationUpdated;
use App\Domain\Messaging\Events\MessageDeleted;
use App\Domain\Messaging\Events\MessageSent;
use App\Domain\Messaging\Events\MessageUpdated;
use App\Domain\Messaging\Events\Typing;
use App\Domain\Messaging\Models\Conversation;
use App\Domain\Messaging\Models\ConversationParticipant;
use App\Domain\Messaging\Models\Message;
use App\Domain\Messaging\Models\MessageAttachment;
use App\Domain\Messaging\Models\MessageReaction;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Tenant;
use App\Http\Api\V1\Resources\Messaging\MessageResource;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Conversations and messages: idempotent sends (client ULIDs), 15-minute edit
 * window, delete-for-everyone, reactions, read state, realtime broadcasts and
 * push notifications to participants who are not looking at the thread.
 */
final class MessagingService
{
    public const EDIT_WINDOW_MINUTES = 15;

    public const TYPES = ['text', 'voice', 'image', 'video', 'file', 'share', 'system'];

    public const FILE_TYPES = ['image', 'video', 'voice', 'file'];

    public const ATTACHMENT_DISK = 'local';

    /** Seconds a participant counts as "in the thread" after reading/typing/fetching it. */
    private const PRESENCE_TTL = 60;

    public function __construct(private readonly TenantContext $context, private readonly Notifier $notifier) {}

    public function findOrCreateDirect(Tenant $tenant, User $userA, User $userB): Conversation
    {
        if ($userA->id === $userB->id) {
            throw ApiException::invalid('validation_failed', __('messaging.errors.self_conversation'), ['user_id' => [__('messaging.errors.self_conversation')]]);
        }
        $ids = [$userA->id, $userB->id];
        sort($ids);
        $key = $tenant->id.'|'.implode('|', $ids);

        return $this->context->run($tenant, function () use ($tenant, $userA, $ids, $key) {
            $existing = Conversation::query()->where('direct_key', $key)->first();
            if ($existing) {
                return $existing;
            }
            try {
                return DB::transaction(function () use ($tenant, $userA, $ids, $key) {
                    $conversation = Conversation::create([
                        'tenant_id' => $tenant->id,
                        'type' => 'direct',
                        'created_by' => $userA->id,
                        'direct_key' => $key,
                    ]);
                    foreach ($ids as $id) {
                        ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $id, 'role' => $id === $userA->id ? 'owner' : 'member']);
                    }

                    return $conversation;
                });
            } catch (UniqueConstraintViolationException) {
                return Conversation::query()->where('direct_key', $key)->firstOrFail();
            }
        });
    }

    /**
     * @param  list<string>  $userIds  other members (creator is added automatically)
     */
    public function createGroup(Tenant $tenant, User $creator, array $userIds, ?string $title = null): Conversation
    {
        return $this->context->run($tenant, fn () => DB::transaction(function () use ($tenant, $creator, $userIds, $title) {
            $conversation = Conversation::create(['tenant_id' => $tenant->id, 'type' => 'group', 'title' => $title, 'created_by' => $creator->id]);
            foreach (array_values(array_unique([$creator->id, ...$userIds])) as $id) {
                ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $id, 'role' => $id === $creator->id ? 'owner' : 'member']);
            }

            return $conversation;
        }));
    }

    public function participant(Conversation $conversation, User $user): ?ConversationParticipant
    {
        return ConversationParticipant::query()->where('conversation_id', $conversation->id)->where('user_id', $user->id)->first();
    }

    public function isParticipant(Conversation $conversation, User $user): bool
    {
        return ConversationParticipant::query()->where('conversation_id', $conversation->id)->where('user_id', $user->id)->exists();
    }

    /**
     * Sends a message. Idempotent on the client-generated ULID `id`: re-sending
     * the same id returns the stored message (wasRecentlyCreated = false).
     *
     * @param  array{id?: string|null, type?: string, body?: string|null, reply_to_id?: string|null, meta?: array<string, mixed>|null, channel?: string}  $payload
     */
    public function send(Conversation $conversation, User $sender, array $payload, ?UploadedFile $file = null): Message
    {
        $type = $payload['type'] ?? ($file ? 'file' : 'text');
        if (! in_array($type, self::TYPES, true)) {
            throw ApiException::invalid('validation_failed', __('messaging.errors.invalid_type'), ['type' => [__('messaging.errors.invalid_type')]]);
        }

        if (! empty($payload['id'])) {
            $existing = Message::withTrashed()->find($payload['id']);
            if ($existing) {
                if ($existing->conversation_id !== $conversation->id || $existing->sender_id !== $sender->id) {
                    throw ApiException::conflict('id_conflict', __('messaging.errors.id_conflict'));
                }

                return $this->loadForResource($existing);
            }
        }

        if ($type === 'text' && trim((string) ($payload['body'] ?? '')) === '') {
            throw ApiException::invalid('validation_failed', __('messaging.errors.body_required'), ['body' => [__('messaging.errors.body_required')]]);
        }
        if (in_array($type, self::FILE_TYPES, true) && $file === null) {
            throw ApiException::invalid('validation_failed', __('messaging.errors.file_required'), ['file' => [__('messaging.errors.file_required')]]);
        }

        $replyToId = $payload['reply_to_id'] ?? null;
        if ($replyToId !== null && ! Message::withTrashed()->where('id', $replyToId)->where('conversation_id', $conversation->id)->exists()) {
            throw ApiException::invalid('validation_failed', __('messaging.errors.invalid_reply'), ['reply_to_id' => [__('messaging.errors.invalid_reply')]]);
        }

        $meta = $payload['meta'] ?? [];
        $storedPath = null;

        try {
            $message = DB::transaction(function () use ($conversation, $sender, $payload, $type, $replyToId, $meta, $file, &$storedPath) {
                $attributes = [
                    'conversation_id' => $conversation->id,
                    'sender_id' => $sender->id,
                    'type' => $type,
                    'body' => isset($payload['body']) && $payload['body'] !== '' ? (string) $payload['body'] : null,
                    'meta' => $meta === [] ? null : $meta,
                    'reply_to_id' => $replyToId,
                    'channel' => $payload['channel'] ?? 'app',
                ];
                if (! empty($payload['id'])) {
                    $attributes['id'] = $payload['id'];
                }
                $message = Message::create($attributes);

                if ($file !== null) {
                    $extension = $file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'bin');
                    $storedPath = $file->storeAs(
                        'tenants/'.$conversation->tenant_id.'/messages/'.$conversation->id,
                        (string) Str::ulid().'.'.strtolower($extension),
                        self::ATTACHMENT_DISK,
                    );
                    if ($storedPath === false) {
                        throw new \RuntimeException('Attachment could not be stored.');
                    }
                    MessageAttachment::create([
                        'message_id' => $message->id,
                        'disk' => self::ATTACHMENT_DISK,
                        'path' => $storedPath,
                        'mime' => (string) ($file->getMimeType() ?: $file->getClientMimeType()),
                        'size' => (int) $file->getSize(),
                        'duration_ms' => isset($meta['duration_ms']) ? (int) $meta['duration_ms'] : null,
                        'waveform' => isset($meta['waveform']) && is_array($meta['waveform']) ? array_values($meta['waveform']) : null,
                    ]);
                }

                $conversation->forceFill(['last_message_at' => $message->created_at])->save();
                ConversationParticipant::query()->where('conversation_id', $conversation->id)->where('user_id', $sender->id)->update(['last_read_at' => $message->created_at]);

                return $message;
            });
        } catch (UniqueConstraintViolationException) {
            if ($storedPath) {
                Storage::disk(self::ATTACHMENT_DISK)->delete($storedPath);
            }
            $existing = Message::withTrashed()->find($payload['id'] ?? '');
            if ($existing && $existing->conversation_id === $conversation->id && $existing->sender_id === $sender->id) {
                return $this->loadForResource($existing);
            }
            throw ApiException::conflict('id_conflict', __('messaging.errors.id_conflict'));
        }

        $message->wasRecentlyCreated = true;
        $this->loadForResource($message);
        $this->touchPresence($conversation->id, $sender->id);

        broadcast(new MessageSent($conversation->id, MessageResource::forViewer($message, null)))->toOthers();
        $this->broadcastConversation($conversation, 'message', $message);
        $this->pushToOffline($conversation, $sender, $message);

        return $message;
    }

    public function edit(Message $message, User $user, string $body): Message
    {
        if ($message->sender_id !== $user->id) {
            throw ApiException::forbidden(__('messaging.errors.not_owner'));
        }
        if ($message->trashed() || $message->type !== 'text') {
            throw ApiException::conflict('not_editable', __('messaging.errors.not_editable'));
        }
        if ($message->created_at->lt(now()->subMinutes(self::EDIT_WINDOW_MINUTES))) {
            throw ApiException::conflict('edit_window_expired', __('messaging.errors.edit_window_expired', ['minutes' => self::EDIT_WINDOW_MINUTES]));
        }
        if (trim($body) === '') {
            throw ApiException::invalid('validation_failed', __('messaging.errors.body_required'), ['body' => [__('messaging.errors.body_required')]]);
        }

        $message->update(['body' => $body, 'edited_at' => now()]);
        $message->loadMissing('conversation');
        $this->loadForResource($message);
        broadcast(new MessageUpdated($message->conversation_id, MessageResource::forViewer($message, null)))->toOthers();
        $this->broadcastConversation($message->conversation, 'edited', $message);

        return $message;
    }

    /** Delete for everyone: the message stays as a tombstone, its content and files are removed. */
    public function delete(Message $message, User $user): Message
    {
        if ($message->sender_id !== $user->id) {
            throw ApiException::forbidden(__('messaging.errors.not_owner'));
        }
        if ($message->trashed()) {
            return $this->loadForResource($message);
        }

        $message->loadMissing(['conversation', 'attachments']);
        DB::transaction(function () use ($message) {
            foreach ($message->attachments as $attachment) {
                Storage::disk($attachment->disk)->delete($attachment->path);
                $attachment->delete();
            }
            $message->reactions()->delete();
            $message->forceFill(['body' => null, 'meta' => null])->save();
            $message->delete();
        });

        $message->unsetRelation('attachments')->unsetRelation('reactions');
        $this->loadForResource($message);
        broadcast(new MessageDeleted($message->conversation_id, MessageResource::forViewer($message, null)))->toOthers();
        $this->broadcastConversation($message->conversation, 'deleted', $message);

        return $message;
    }

    public function toggleReaction(Message $message, User $user, string $emoji): Message
    {
        if ($message->trashed()) {
            throw ApiException::conflict('not_editable', __('messaging.errors.not_editable'));
        }
        $existing = MessageReaction::query()->where('message_id', $message->id)->where('user_id', $user->id)->where('emoji', $emoji)->first();
        if ($existing) {
            $existing->delete();
        } else {
            try {
                MessageReaction::create(['message_id' => $message->id, 'user_id' => $user->id, 'emoji' => $emoji]);
            } catch (UniqueConstraintViolationException) {
                // Concurrent duplicate toggle: the reaction exists, which is the intended state.
            }
        }

        $message->unsetRelation('reactions');
        $this->loadForResource($message);
        broadcast(new MessageUpdated($message->conversation_id, MessageResource::forViewer($message, null)))->toOthers();

        return $message;
    }

    public function markRead(Conversation $conversation, User $user): ConversationParticipant
    {
        $participant = $this->participant($conversation, $user)
            ?? throw new ApiException('not_found', __('messaging.errors.conversation_not_found'), 404);
        $participant->update(['last_read_at' => now()]);
        $this->touchPresence($conversation->id, $user->id);

        $readBy = ['user_id' => $user->id, 'last_read_at' => $participant->last_read_at?->toIso8601String()];
        foreach ($conversation->participants()->pluck('user_id') as $userId) {
            broadcast(new ConversationUpdated($userId, [
                'conversation_id' => $conversation->id,
                'reason' => 'read',
                'unread_count' => $userId === $user->id ? 0 : $this->unreadCountFor($conversation->id, $userId),
                'read_by' => $readBy,
            ]));
        }

        return $participant;
    }

    public function unreadCount(Conversation $conversation, User $user): int
    {
        return $this->unreadCountFor($conversation->id, $user->id);
    }

    /** Total unread messages for a user across the conversations of a tenant. */
    public function totalUnread(User $user, ?Tenant $tenant = null): int
    {
        $tenantId = $tenant?->id ?? $this->context->id();

        return (int) $this->unreadQuery($user->id)
            ->join('conversations', 'conversations.id', '=', 'm.conversation_id')
            ->when($tenantId, fn ($q) => $q->where('conversations.tenant_id', $tenantId))
            ->count();
    }

    public function setMuted(Conversation $conversation, User $user, bool $muted): void
    {
        ConversationParticipant::query()->where('conversation_id', $conversation->id)->where('user_id', $user->id)->update(['is_muted' => $muted]);
    }

    public function typing(Conversation $conversation, User $user, bool $isTyping = true): void
    {
        $this->touchPresence($conversation->id, $user->id);
        broadcast(new Typing($conversation->id, $user->id, $user->name, $isTyping))->toOthers();
    }

    public function touchPresence(string $conversationId, string $userId): void
    {
        Cache::put($this->presenceKey($conversationId, $userId), true, self::PRESENCE_TTL);
    }

    public function isPresent(string $conversationId, string $userId): bool
    {
        return (bool) Cache::get($this->presenceKey($conversationId, $userId), false);
    }

    /**
     * Prepares conversations for ConversationResource: participants, last
     * message, the viewer's unread count and mute flag, participant roles.
     *
     * @param  EloquentCollection<int, Conversation>  $conversations
     */
    public function present(EloquentCollection $conversations, User $viewer): void
    {
        if ($conversations->isEmpty()) {
            return;
        }
        $conversations->load([
            'participants.user',
            'latestMessage' => fn ($q) => $q->with(['sender', 'attachments', 'reactions', 'replyTo' => fn ($r) => $r->withTrashed()->with('sender')]),
        ]);

        $ids = $conversations->modelKeys();
        $unread = $this->unreadQuery($viewer->id)->whereIn('m.conversation_id', $ids)
            ->groupBy('m.conversation_id')->selectRaw('m.conversation_id as cid, count(*) as n')->pluck('n', 'cid');

        $userIds = $conversations->flatMap(fn (Conversation $c) => $c->participants->pluck('user_id'))->unique()->values()->all();
        $tenantIds = $conversations->pluck('tenant_id')->unique()->all();
        $roles = Membership::withoutGlobalScopes()->whereIn('tenant_id', $tenantIds)->whereIn('user_id', $userIds)
            ->get(['tenant_id', 'user_id', 'role'])->groupBy('tenant_id')->map(fn ($g) => $g->pluck('role', 'user_id')->all());

        foreach ($conversations as $conversation) {
            $mine = $conversation->participants->firstWhere('user_id', $viewer->id);
            $conversation->presentation = [
                'viewer_id' => $viewer->id,
                'unread_count' => (int) ($unread[$conversation->id] ?? 0),
                'is_muted' => (bool) $mine?->is_muted,
                'roles' => $roles[$conversation->tenant_id] ?? [],
            ];
        }
    }

    public function loadForResource(Message $message): Message
    {
        $message->loadMissing(['sender', 'attachments', 'reactions', 'replyTo' => fn ($q) => $q->withTrashed()->with('sender')]);

        return $message;
    }

    private function unreadCountFor(string $conversationId, string $userId): int
    {
        return (int) $this->unreadQuery($userId)->where('m.conversation_id', $conversationId)->count();
    }

    /** Messages from others newer than the user's read marker, as a base query aliased "m". */
    private function unreadQuery(string $userId): Builder
    {
        return DB::table('messages as m')
            ->join('conversation_participants as cp', function ($join) use ($userId) {
                $join->on('cp.conversation_id', '=', 'm.conversation_id')->where('cp.user_id', '=', $userId);
            })
            ->whereNull('m.deleted_at')
            ->where(fn ($q) => $q->whereNull('m.sender_id')->orWhere('m.sender_id', '!=', $userId))
            ->where(fn ($q) => $q->whereNull('cp.last_read_at')->orWhereColumn('m.created_at', '>', 'cp.last_read_at'));
    }

    private function broadcastConversation(Conversation $conversation, string $reason, Message $message): void
    {
        $payloadMessage = MessageResource::forViewer($message, null);
        foreach ($conversation->participants()->pluck('user_id') as $userId) {
            broadcast(new ConversationUpdated($userId, [
                'conversation_id' => $conversation->id,
                'reason' => $reason,
                'unread_count' => $this->unreadCountFor($conversation->id, $userId),
                'last_message' => $payloadMessage,
            ]));
        }
    }

    /** Push to participants who are not muted and not currently in the thread. */
    private function pushToOffline(Conversation $conversation, User $sender, Message $message): void
    {
        $recipients = ConversationParticipant::query()->with('user')
            ->where('conversation_id', $conversation->id)
            ->where('user_id', '!=', $sender->id)
            ->where('is_muted', false)
            ->get();

        foreach ($recipients as $participant) {
            $user = $participant->user;
            if (! $user || $this->isPresent($conversation->id, $user->id)) {
                continue;
            }
            [$title, $body] = $this->notifier->inLocale($user, fn () => [
                $conversation->type === 'direct' || ! $conversation->title ? $sender->name : $sender->name.' · '.$conversation->title,
                $this->preview($message),
            ]);
            $this->notifier->sendText($user, 'messages', $title, $body, '/messages/'.$conversation->id, [
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
            ]);
        }
    }

    private function preview(Message $message): string
    {
        return match ($message->type) {
            'text', 'system' => Str::limit((string) $message->body, 140),
            'voice' => (string) __('messaging.preview.voice'),
            'image' => (string) __('messaging.preview.image'),
            'video' => (string) __('messaging.preview.video'),
            'file' => (string) __('messaging.preview.file'),
            'share' => (string) __('messaging.preview.share'),
            default => (string) __('messaging.preview.message'),
        };
    }

    private function presenceKey(string $conversationId, string $userId): string
    {
        return "messaging:presence:{$conversationId}:{$userId}";
    }
}
