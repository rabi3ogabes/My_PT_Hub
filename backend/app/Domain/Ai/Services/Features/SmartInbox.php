<?php

namespace App\Domain\Ai\Services\Features;

use App\Domain\Ai\Data\AiTask;
use App\Domain\Ai\Models\AiOutput;
use App\Domain\Ai\Services\AiGateway;
use App\Domain\Ai\Services\AiOutputService;
use App\Domain\Ai\Services\AiSettingsService;
use App\Domain\Ai\Services\ClientDataService;
use App\Domain\Ai\Services\PriorityDetector;
use App\Domain\Ai\Services\ToneEnforcer;
use App\Domain\Ai\Support\Schemas;
use App\Domain\Identity\Models\User;
use App\Domain\Messaging\Models\Conversation;
use App\Domain\Messaging\Models\Message;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Support\Collection;

/**
 * F. Smart inbox: reply drafts in the coach's voice and client's language,
 * thread summaries, and deterministic priority detection (pain/injury,
 * medical, cancellation intent). Drafts are never sent automatically.
 */
final class SmartInbox
{
    public function __construct(
        private readonly AiGateway $gateway,
        private readonly AiOutputService $outputs,
        private readonly ClientDataService $clients,
        private readonly AiSettingsService $settings,
        private readonly ToneEnforcer $tone,
        private readonly PriorityDetector $priority,
    ) {}

    /** @param array<string, mixed> $input */
    public function replyDraft(Tenant $tenant, User $coach, array $input): AiOutput
    {
        [$conversation, $replyTo] = $this->resolve($input);
        $messages = $this->messages($conversation, 20);
        $client = $this->clientOf($tenant, $conversation);
        $profile = $client ? $this->clients->profile($tenant, $client) : null;
        $voice = $this->settings->voiceFor($tenant, $coach, $profile?->coach_id);
        $language = $this->clients->language($client, $input['language'] ?? null, $tenant);
        $clientText = $this->clientText($messages, $client);
        $priority = $this->priority->detect($replyTo?->body ?? $clientText);

        $result = $this->gateway->run(new AiTask(
            feature: 'reply_draft',
            tenant: $tenant,
            user: $coach,
            variables: [
                'first_name' => $client?->firstName() ?? '-',
                'language' => $language,
                'thread' => $this->transcript($messages, $client),
                'reply_to' => $replyTo?->body ?? '-',
                'instruction' => $input['instruction'] ?? '-',
                'priority' => $priority,
                ...$this->tone->variables($voice),
            ],
            context: ['first_name' => $client?->firstName(), 'language' => $language, 'last_client_message' => $replyTo?->body ?? $clientText, 'priority' => $priority],
            schema: Schemas::replyDraft(),
            postProcess: function (array $data) use ($voice) {
                $banned = $this->tone->bannedWordsIn($data['reply'], $voice);

                return [$data, $banned === [] ? [] : ['reply contains banned words: '.implode(', ', $banned)]];
            },
        ));

        $payload = [
            'reply' => $this->tone->polish($result->data['reply'], $voice),
            'language' => $result->data['language'],
            'priority' => $priority['priority'],
            'priority_reasons' => $priority['reasons'],
            'conversation_id' => $conversation->id,
            'reply_to_message_id' => $replyTo?->id,
        ];

        return $this->outputs->draft(
            'reply_draft',
            $coach,
            $client,
            array_filter(['conversation_id' => $conversation->id, 'message_id' => $replyTo?->id, 'instruction' => $input['instruction'] ?? null]),
            $payload,
            ['messages' => $messages->count(), 'label' => __('ai.grounding_messages', ['count' => $messages->count()])],
            $priority['priority'] === 'urgent' ? array_map(fn ($r) => ['type' => $r, 'severity' => 'high', 'source' => 'priority_detector'], $priority['reasons']) : [],
            $result->requestId,
        );
    }

    public function summarize(Tenant $tenant, User $coach, Conversation $conversation): AiOutput
    {
        $messages = $this->messages($conversation, 100);
        if ($messages->isEmpty()) {
            throw ApiException::invalid('conversation_empty', __('ai.errors.conversation_empty'));
        }
        $client = $this->clientOf($tenant, $conversation);
        $priority = $this->priority->detect($this->clientText($messages, $client));
        $language = $this->clients->language($coach, null, $tenant);

        $result = $this->gateway->run(new AiTask(
            feature: 'thread_summary',
            tenant: $tenant,
            user: $coach,
            variables: ['thread' => $this->transcript($messages, $client), 'language' => $language, 'priority' => $priority],
            context: ['messages' => $messages->count(), 'language' => $language, 'priority' => $priority, 'first_name' => $client?->firstName()],
            schema: Schemas::threadSummary(),
        ));

        return $this->outputs->draft(
            'thread_summary',
            $coach,
            $client,
            ['conversation_id' => $conversation->id],
            $result->data + ['priority' => $priority['priority'], 'priority_reasons' => $priority['reasons'], 'conversation_id' => $conversation->id],
            ['messages' => $messages->count(), 'label' => __('ai.grounding_messages', ['count' => $messages->count()])],
            [],
            $result->requestId,
        );
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{0: Conversation, 1: Message|null}
     */
    private function resolve(array $input): array
    {
        $replyTo = null;
        if (! empty($input['message_id'])) {
            $replyTo = Message::query()->find($input['message_id']);
            $conversation = $replyTo ? Conversation::query()->find($replyTo->conversation_id) : null;
            if (! $conversation) {
                throw new ApiException('not_found', __('api.not_found'), 404);
            }

            return [$conversation, $replyTo];
        }
        $conversation = Conversation::query()->find($input['conversation_id'] ?? null);
        if (! $conversation) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }

        return [$conversation, null];
    }

    /** @return Collection<int, Message> */
    private function messages(Conversation $conversation, int $limit): Collection
    {
        return Message::query()
            ->with('sender:id,name')
            ->where('conversation_id', $conversation->id)
            ->whereIn('type', ['text', 'voice'])
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    private function clientOf(Tenant $tenant, Conversation $conversation): ?User
    {
        $participantIds = $conversation->participants()->pluck('user_id');
        $clientId = Membership::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('role', 'client')->whereIn('user_id', $participantIds)->value('user_id');

        return $clientId ? User::query()->find($clientId) : null;
    }

    /**
     * Thread as role-labelled lines with first names only.
     *
     * @param  Collection<int, Message>  $messages
     * @return list<string>
     */
    private function transcript(Collection $messages, ?User $client): array
    {
        return $messages->map(function (Message $m) use ($client) {
            $role = $m->sender_id && $client && $m->sender_id === $client->id ? 'client' : 'coach';
            $name = $m->sender ? $m->sender->firstName() : 'system';
            $body = $m->type === 'voice' ? ($m->meta['transcript'] ?? '[voice note]') : (string) $m->body;

            return '['.$m->created_at?->format('Y-m-d H:i')."] {$role} ({$name}): ".mb_substr($body, 0, 1500);
        })->all();
    }

    /** @param Collection<int, Message> $messages */
    private function clientText(Collection $messages, ?User $client): string
    {
        return $messages->filter(fn (Message $m) => $client && $m->sender_id === $client->id)->take(-5)->map(fn (Message $m) => (string) $m->body)->implode("\n");
    }
}
