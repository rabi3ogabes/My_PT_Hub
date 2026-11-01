<?php

namespace App\Http\Api\V1\Coach;

use App\Domain\Identity\Models\User;
use App\Domain\Messaging\Models\Conversation;
use App\Domain\Messaging\Models\ConversationParticipant;
use App\Domain\Messaging\Models\Message;
use App\Domain\Progress\Services\ComplianceService;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Support\TenantClients;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ComplianceController extends ApiController
{
    public function __construct(private readonly ComplianceService $compliance) {}

    /**
     * GET /compliance?status=green|amber|red&at_risk=1&coach_id=&q=&cursor=
     * Scores of the listed page are recomputed live and persisted.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['green', 'amber', 'red'])],
            'at_risk' => ['nullable', 'boolean'],
            'coach_id' => ['nullable', 'string', 'size:26'],
            'q' => ['nullable', 'string', 'max:120'],
        ]);
        $clientIds = TenantClients::allIds();
        $query = ClientProfile::query()
            ->whereIn('user_id', $clientIds)
            ->with(['user', 'coach'])
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('risk_status', $v))
            ->when(! empty($filters['at_risk']), fn ($q) => $q->where('risk_status', 'red'))
            ->when($filters['coach_id'] ?? null, fn ($q, $v) => $q->where('coach_id', $v))
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', '%'.str_replace(['%', '_'], '', $v).'%')))
            ->orderBy('id');

        $page = $query->cursorPaginate($this->perPage());
        $items = collect($page->items())->filter(fn (ClientProfile $p) => $p->user)->map(function (ClientProfile $profile) {
            $result = $this->compliance->refresh($profile->user);

            return [
                'client' => ['id' => $profile->user->id, 'name' => $profile->user->name, 'avatar_url' => null],
                'coach' => $profile->coach ? ['id' => $profile->coach->id, 'name' => $profile->coach->name] : null,
                'score' => $result['score'],
                'risk_status' => $result['risk_status'],
                'at_risk' => $result['at_risk'],
                'workouts_pct' => $result['workouts_pct'],
                'nutrition_pct' => $result['nutrition_pct'],
                'habits_pct' => $result['habits_pct'],
                'checkins_pct' => $result['checkins_pct'],
                'previous_score' => $result['previous_score'],
                'days_inactive' => $result['days_inactive'],
                'last_active_on' => $result['last_active_on'],
                'reasons' => $result['reasons'],
            ];
        })->values()->all();

        $counts = ClientProfile::query()->whereIn('user_id', $clientIds)->selectRaw('risk_status, count(*) as aggregate')->groupBy('risk_status')->pluck('aggregate', 'risk_status');

        return response()->json([
            'data' => $items,
            'meta' => [
                'per_page' => $page->perPage(),
                'next_cursor' => $page->nextCursor()?->encode(),
                'prev_cursor' => $page->previousCursor()?->encode(),
                'counts' => ['green' => (int) ($counts['green'] ?? 0), 'amber' => (int) ($counts['amber'] ?? 0), 'red' => (int) ($counts['red'] ?? 0)],
                'weights' => $this->compliance->weights(),
            ],
            'links' => ['next' => $page->nextPageUrl(), 'prev' => $page->previousPageUrl()],
            'errors' => [],
        ]);
    }

    /**
     * POST /compliance/message {body, client_ids?: [], at_risk?: true}
     * Sends the message in a direct conversation with each client (created when missing).
     */
    public function bulkMessage(Request $request): JsonResponse
    {
        if (! $this->user()->can('messages.send')) {
            throw ApiException::forbidden();
        }
        $data = $request->validate([
            'body' => ['required', 'string', 'max:4000'],
            'client_ids' => ['required_without:at_risk', 'array', 'max:500'],
            'client_ids.*' => ['string', 'size:26'],
            'at_risk' => ['nullable', 'boolean'],
        ]);

        if (! empty($data['client_ids'])) {
            $clients = TenantClients::find($data['client_ids']);
        } else {
            if (empty($data['at_risk'])) {
                throw ApiException::invalid('validation_failed', __('api.validation_failed'), ['client_ids' => [__('progress.errors.no_recipients')]]);
            }
            $results = $this->compliance->refreshTenant($this->tenant());
            $ids = array_keys(array_filter($results, fn (array $r) => $r['at_risk'] || $r['risk_status'] === 'red'));
            $clients = User::query()->whereIn('id', $ids)->get()->all();
        }
        if ($clients === []) {
            throw ApiException::invalid('validation_failed', __('api.validation_failed'), ['client_ids' => [__('progress.errors.no_recipients')]]);
        }

        $sender = $this->user();
        $conversationIds = DB::transaction(function () use ($clients, $sender, $data) {
            $ids = [];
            foreach ($clients as $client) {
                $conversation = $this->directConversation($sender, $client);
                $message = Message::create([
                    'id' => (string) Str::ulid(),
                    'conversation_id' => $conversation->id,
                    'sender_id' => $sender->id,
                    'type' => 'text',
                    'body' => $data['body'],
                    'meta' => ['origin' => 'compliance_bulk'],
                    'channel' => 'app',
                ]);
                $conversation->forceFill(['last_message_at' => $message->created_at])->save();
                ConversationParticipant::query()->where('conversation_id', $conversation->id)->where('user_id', $sender->id)
                    ->update(['last_read_at' => $message->created_at]);
                $ids[] = $conversation->id;
            }

            return $ids;
        });

        return $this->created(['sent' => count($conversationIds), 'conversation_ids' => $conversationIds]);
    }

    private function directConversation(User $sender, User $client): Conversation
    {
        $existing = Conversation::query()->where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $sender->id))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $client->id))
            ->withCount('participants')
            ->get()
            ->first(fn (Conversation $c) => (int) $c->participants_count === 2);
        if ($existing) {
            return $existing;
        }

        $conversation = Conversation::create(['type' => 'direct', 'created_by' => $sender->id]);
        foreach ([[$sender->id, 'owner'], [$client->id, 'member']] as [$userId, $role]) {
            ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $userId, 'role' => $role]);
        }

        return $conversation;
    }
}
