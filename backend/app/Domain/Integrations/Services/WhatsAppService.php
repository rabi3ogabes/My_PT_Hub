<?php

namespace App\Domain\Integrations\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Integrations\Exceptions\InvalidWebhookSignature;
use App\Domain\Integrations\Exceptions\ProviderException;
use App\Domain\Integrations\Models\Integration;
use App\Domain\Messaging\Models\Conversation;
use App\Domain\Messaging\Models\ConversationParticipant;
use App\Domain\Messaging\Models\Message;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Tenant;
use App\Notifications\GenericNotification;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

/**
 * WhatsApp Business Cloud API: opt-in tracking, approved template messages
 * (ar/fr/en/es) and inbound replies routed into the client's direct
 * conversation with their coach (channel "whatsapp").
 */
final class WhatsAppService
{
    public const PROVIDER = 'whatsapp';

    /** Inbound keywords that withdraw consent. */
    private const STOP_WORDS = ['stop', 'unsubscribe', 'arret', 'arrêt', 'baja', 'parar', 'توقف', 'الغاء', 'إلغاء'];

    public function __construct(private readonly TenantContext $context) {}

    public function isConfigured(): bool
    {
        return filled(config('services.whatsapp.token')) && filled(config('services.whatsapp.phone_number_id'));
    }

    public static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === null || $digits === '') {
            return null;
        }
        $digits = ltrim(str_starts_with($digits, '00') ? substr($digits, 2) : $digits, '0');

        return strlen($digits) >= 8 && strlen($digits) <= 15 ? $digits : null;
    }

    public function optIn(User $user, string $phone, ?Tenant $tenant = null): Integration
    {
        $digits = self::normalizePhone($phone) ?? throw ApiException::invalid('validation_failed', __('integrations.errors.invalid_phone'), ['phone' => [__('integrations.errors.invalid_phone')]]);

        $integration = Integration::query()->firstOrNew(['user_id' => $user->id, 'provider' => self::PROVIDER]);
        $integration->fill(['tenant_id' => $tenant?->id ?? $integration->tenant_id, 'status' => 'connected', 'external_user_id' => $digits, 'last_error' => null]);
        $integration->mergeSettings(['phone' => $digits, 'opted_in_at' => now()->toIso8601String(), 'opted_out_at' => null]);
        $integration->save();

        return $integration;
    }

    public function optOut(User $user): void
    {
        $integration = Integration::query()->where('user_id', $user->id)->where('provider', self::PROVIDER)->first();
        if ($integration) {
            $integration->forceFill(['status' => 'revoked'])->mergeSettings(['opted_out_at' => now()->toIso8601String()])->save();
        }
    }

    public function isOptedIn(User $user): bool
    {
        return Integration::query()->where('user_id', $user->id)->where('provider', self::PROVIDER)
            ->where('status', 'connected')->whereNotNull('external_user_id')->exists();
    }

    public function languageCode(?string $locale): string
    {
        $languages = (array) config('integrations.whatsapp.languages', []);

        return (string) ($languages[$locale] ?? $languages['en'] ?? 'en');
    }

    /**
     * Ordered body parameters for an approved template.
     *
     * @param  array<string, scalar|null>  $params
     * @return list<string>
     */
    public function orderedParams(string $template, array $params): array
    {
        $names = config('integrations.whatsapp.templates.'.$template);
        if (! is_array($names)) {
            throw new \InvalidArgumentException("Unknown WhatsApp template [{$template}]");
        }

        return array_map(fn (string $name) => (string) ($params[$name] ?? ''), $names);
    }

    /**
     * Human-readable text of the template in the given locale (mirrors the approved Meta template).
     *
     * @param  array<string, scalar|null>  $params
     */
    public function renderTemplate(string $template, array $params, string $locale): string
    {
        $names = config('integrations.whatsapp.templates.'.$template, []);
        $replacements = [];
        foreach ($names as $name) {
            $replacements[$name] = (string) ($params[$name] ?? '');
        }

        return __('integrations.whatsapp.templates.'.$template, $replacements, $locale);
    }

    /**
     * Cloud API request body for a template message.
     *
     * @param  array<string, scalar|null>  $params
     * @return array<string, mixed>
     */
    public function templatePayload(string $toPhone, string $template, array $params, string $locale): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $toPhone,
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => ['code' => $this->languageCode($locale)],
                'components' => [[
                    'type' => 'body',
                    'parameters' => array_map(fn (string $text) => ['type' => 'text', 'text' => $text], $this->orderedParams($template, $params)),
                ]],
            ],
        ];
    }

    /**
     * Sends an approved template to an opted-in user. Returns the WhatsApp
     * message id, or null when the user has not opted in / WhatsApp is not configured.
     *
     * @param  array<string, scalar|null>  $params
     */
    public function sendTemplate(User $to, string $template, array $params = [], ?string $locale = null): ?string
    {
        $integration = Integration::query()->where('user_id', $to->id)->where('provider', self::PROVIDER)->where('status', 'connected')->first();
        if (! $integration?->external_user_id || ! $this->isConfigured()) {
            return null;
        }

        $locale = in_array($locale, ['ar', 'fr', 'en', 'es'], true) ? $locale : $to->preferredLocale();
        $url = sprintf('https://graph.facebook.com/%s/%s/messages', config('integrations.whatsapp.graph_version', 'v21.0'), config('services.whatsapp.phone_number_id'));

        try {
            $response = Http::withToken((string) config('services.whatsapp.token'))->acceptJson()->timeout(15)
                ->post($url, $this->templatePayload($integration->external_user_id, $template, $params, $locale));
        } catch (ConnectionException $e) {
            throw new ProviderException('whatsapp: API unreachable', 0, $e);
        }

        if ($response->failed()) {
            $code = (int) $response->json('error.code');
            // 131047/131026: re-engagement window / undeliverable — not retryable.
            $integration->forceFill(['last_error' => 'send_failed:'.$code])->save();
            if ($response->serverError()) {
                throw new ProviderException("whatsapp: send failed ({$response->status()})");
            }

            return null;
        }

        return (string) $response->json('messages.0.id');
    }

    public function verifyEndpoint(Request $request): Response
    {
        $expected = (string) config('services.whatsapp.verify_token');
        if ($request->query('hub_mode') !== 'subscribe' || $expected === '' || ! hash_equals($expected, (string) $request->query('hub_verify_token'))) {
            return response('forbidden', 403);
        }

        return response((string) $request->query('hub_challenge'), 200, ['Content-Type' => 'text/plain']);
    }

    public function verifySignature(Request $request): void
    {
        $secret = (string) config('services.whatsapp.app_secret');
        $given = (string) $request->header('X-Hub-Signature-256');
        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);
        if ($secret === '' || $given === '' || ! hash_equals($expected, $given)) {
            throw new InvalidWebhookSignature('whatsapp: invalid X-Hub-Signature-256');
        }
    }

    /**
     * Verifies and routes inbound messages. Returns the created Message models.
     *
     * @return list<Message>
     */
    public function handleInbound(Request $request): array
    {
        $this->verifySignature($request);

        $created = [];
        foreach ((array) $request->json('entry', []) as $entry) {
            foreach ((array) ($entry['changes'] ?? []) as $change) {
                foreach ((array) ($change['value']['messages'] ?? []) as $incoming) {
                    $message = $this->routeInbound((array) $incoming);
                    if ($message) {
                        $created[] = $message;
                    }
                }
            }
        }

        return $created;
    }

    /** @param array<string, mixed> $incoming */
    private function routeInbound(array $incoming): ?Message
    {
        $from = self::normalizePhone((string) ($incoming['from'] ?? ''));
        $waId = (string) ($incoming['id'] ?? '');
        if ($from === null || $waId === '') {
            return null;
        }

        $integration = Integration::query()->where('provider', self::PROVIDER)->where('external_user_id', $from)->latest('updated_at')->first();
        $user = $integration?->user()->first() ?? User::query()->whereIn('phone', ['+'.$from, $from, '00'.$from])->first();
        if (! $user) {
            return null;
        }

        $tenant = $this->clientTenant($user, $integration?->tenant_id);
        if (! $tenant) {
            return null;
        }

        $body = $this->bodyOf($incoming);
        if (in_array(mb_strtolower(trim($body)), self::STOP_WORDS, true)) {
            $this->optOut($user);
        }

        return $this->context->run($tenant, function () use ($tenant, $user, $body, $waId, $incoming, $from) {
            $coachId = ClientProfile::query()->where('user_id', $user->id)->value('coach_id')
                ?? Membership::query()->where('role', 'owner')->where('status', 'active')->value('user_id');
            if (! $coachId) {
                return null;
            }

            return DB::transaction(function () use ($tenant, $user, $coachId, $body, $waId, $incoming, $from) {
                $conversation = $this->directConversation($user->id, (string) $coachId);
                $existing = Message::query()->where('conversation_id', $conversation->id)->where('meta->whatsapp_id', $waId)->first();
                if ($existing) {
                    return null;
                }

                $message = Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $user->id,
                    'type' => 'text',
                    'body' => $body,
                    'channel' => 'whatsapp',
                    'meta' => array_filter(['whatsapp_id' => $waId, 'from' => $from, 'whatsapp_type' => $incoming['type'] ?? 'text']),
                ]);
                $conversation->forceFill(['last_message_at' => now()])->save();
                ConversationParticipant::query()->where('conversation_id', $conversation->id)->where('user_id', $user->id)->update(['last_read_at' => now()]);

                if ($coach = User::query()->find($coachId)) {
                    $coach->notify(new GenericNotification(
                        'messages',
                        __('integrations.whatsapp.inbound_title', ['name' => $user->name], $coach->preferredLocale()),
                        mb_strimwidth($body, 0, 140, '…'),
                        '/conversations/'.$conversation->id,
                        ['conversation_id' => $conversation->id, 'tenant_id' => $tenant->id, 'channel' => 'whatsapp'],
                    ));
                }

                return $message;
            });
        });
    }

    private function clientTenant(User $user, ?string $preferredTenantId): ?Tenant
    {
        $memberships = Membership::withoutGlobalScopes()->where('user_id', $user->id)->where('role', 'client')->where('status', 'active')->orderByDesc('joined_at')->get();
        $membership = $memberships->firstWhere('tenant_id', $preferredTenantId)
            ?? $memberships->firstWhere('tenant_id', $user->current_tenant_id)
            ?? $memberships->first();

        return $membership ? Tenant::query()->find($membership->tenant_id) : null;
    }

    private function directConversation(string $clientId, string $coachId): Conversation
    {
        $conversation = Conversation::query()->where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $clientId))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $coachId))
            ->orderByDesc('last_message_at')->first();
        if ($conversation) {
            return $conversation;
        }

        $conversation = Conversation::create(['type' => 'direct', 'created_by' => $clientId]);
        foreach ([$coachId => 'owner', $clientId => 'member'] as $userId => $role) {
            ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $userId, 'role' => $role]);
        }

        return $conversation;
    }

    /** @param array<string, mixed> $incoming */
    private function bodyOf(array $incoming): string
    {
        return match ($incoming['type'] ?? 'text') {
            'text' => (string) ($incoming['text']['body'] ?? ''),
            'button' => (string) ($incoming['button']['text'] ?? ''),
            'interactive' => (string) ($incoming['interactive']['button_reply']['title'] ?? $incoming['interactive']['list_reply']['title'] ?? ''),
            'image', 'video', 'document' => (string) ($incoming[$incoming['type']]['caption'] ?? __('integrations.whatsapp.media_received')),
            default => __('integrations.whatsapp.media_received'),
        };
    }
}
