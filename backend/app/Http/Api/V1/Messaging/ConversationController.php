<?php

namespace App\Http\Api\V1\Messaging;

use App\Domain\Identity\Models\User;
use App\Domain\Messaging\Models\Conversation;
use App\Domain\Messaging\Services\MessagingService;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Membership;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Resources\Messaging\ConversationResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConversationController extends ApiController
{
    public function __construct(private readonly MessagingService $messaging) {}

    /** Inbox: the viewer's conversations, most recent activity first, with unread counts and last message. */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['filter.type' => ['nullable', Rule::in(['direct', 'group', 'broadcast', 'checkin'])]]);
        $user = $this->user();

        $paginator = Conversation::query()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->when($request->input('filter.type'), fn ($q, $type) => $q->where('type', $type))
            ->orderByDesc('updated_at')->orderByDesc('id')
            ->cursorPaginate($this->perPage(30));

        $this->messaging->present(new Collection($paginator->items()), $user);

        return $this->paginated($paginator, ConversationResource::class, ['total_unread' => $this->messaging->totalUnread($user, $this->tenant())]);
    }

    /**
     * Opens a direct conversation (idempotent) or creates a group.
     * Clients may only open direct conversations with staff of their tenant.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['nullable', Rule::in(['direct', 'group'])],
            'user_id' => ['required_without:user_ids', 'nullable', 'string', 'size:26'],
            'user_ids' => ['required_if:type,group', 'nullable', 'array', 'min:1', 'max:100'],
            'user_ids.*' => ['string', 'size:26'],
            'title' => ['nullable', 'string', 'max:120'],
        ]);
        $user = $this->user();
        $tenant = $this->tenant();
        $isStaff = $user->isStaffIn($tenant->id);
        $type = $data['type'] ?? (isset($data['user_ids']) ? 'group' : 'direct');

        if ($type === 'group') {
            if (! $isStaff) {
                throw ApiException::forbidden(__('messaging.errors.clients_cannot_create_groups'));
            }
            $ids = array_values(array_diff(array_unique($data['user_ids'] ?? []), [$user->id]));
            $this->assertMembers($ids);
            $conversation = $this->messaging->createGroup($tenant, $user, $ids, $data['title'] ?? null);

            return $this->created($this->present($conversation));
        }

        $otherId = $data['user_id'] ?? ($data['user_ids'][0] ?? null);
        $membership = $otherId ? Membership::query()->where('user_id', $otherId)->where('status', 'active')->first() : null;
        if (! $membership) {
            throw ApiException::invalid('validation_failed', __('messaging.errors.not_a_member'), ['user_id' => [__('messaging.errors.not_a_member')]]);
        }
        if (! $isStaff && ! in_array($membership->role, ['owner', 'coach', 'assistant'], true)) {
            throw ApiException::forbidden(__('messaging.errors.clients_message_staff_only'));
        }
        $other = User::query()->findOrFail($otherId);
        $conversation = $this->messaging->findOrCreateDirect($tenant, $user, $other);

        return $conversation->wasRecentlyCreated ? $this->created($this->present($conversation)) : $this->ok($this->present($conversation));
    }

    public function show(Conversation $conversation): JsonResponse
    {
        $this->authorizeParticipant($conversation);

        return $this->ok($this->present($conversation));
    }

    public function read(Conversation $conversation): JsonResponse
    {
        $this->authorizeParticipant($conversation);
        $participant = $this->messaging->markRead($conversation, $this->user());

        return $this->ok([
            'conversation_id' => $conversation->id,
            'unread_count' => 0,
            'last_read_at' => $participant->last_read_at?->toIso8601String(),
        ]);
    }

    public function typing(Request $request, Conversation $conversation): JsonResponse
    {
        $data = $request->validate(['typing' => ['nullable', 'boolean']]);
        $this->authorizeParticipant($conversation);
        $this->messaging->typing($conversation, $this->user(), (bool) ($data['typing'] ?? true));

        return $this->noContent();
    }

    private function authorizeParticipant(Conversation $conversation): void
    {
        if (! $this->messaging->isParticipant($conversation, $this->user())) {
            throw new ApiException('not_found', __('messaging.errors.conversation_not_found'), 404);
        }
    }

    /** @return array<string, mixed> */
    private function present(Conversation $conversation): array
    {
        $this->messaging->present(new Collection([$conversation]), $this->user());

        return (new ConversationResource($conversation))->resolve(request());
    }

    /** @param  list<string>  $ids */
    private function assertMembers(array $ids): void
    {
        $found = Membership::query()->whereIn('user_id', $ids)->where('status', 'active')->count();
        if ($ids === [] || $found !== count($ids)) {
            throw ApiException::invalid('validation_failed', __('messaging.errors.not_a_member'), ['user_ids' => [__('messaging.errors.not_a_member')]]);
        }
    }
}
