<?php

namespace App\Domain\Integrations\Providers\Calendar;

use App\Domain\Calendar\Models\Event;
use App\Domain\Integrations\Contracts\CalendarProvider;
use App\Domain\Integrations\Data\BusyBlock;
use App\Domain\Integrations\Data\TokenSet;
use App\Domain\Integrations\Exceptions\InvalidWebhookSignature;
use App\Domain\Integrations\Exceptions\ProviderException;
use App\Domain\Integrations\Models\Integration;
use App\Domain\Integrations\Providers\OAuth2Provider;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/** Microsoft Graph (Outlook / Microsoft 365): events, getSchedule and change subscriptions. */
class MicrosoftCalendarProvider extends OAuth2Provider implements CalendarProvider
{
    public const API = 'https://graph.microsoft.com/v1.0';

    /** Graph caps event subscriptions at 4230 minutes. */
    private const MAX_SUBSCRIPTION_MINUTES = 4200;

    protected string $authorizeUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';

    protected string $tokenUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';

    protected string $servicesKey = 'microsoft';

    protected array $scopes = ['offline_access', 'User.Read', 'Calendars.ReadWrite'];

    public function key(): string
    {
        return 'outlook';
    }

    public function connect(string $code, array $context = []): TokenSet
    {
        $tokens = parent::connect($code, $context);
        $me = Http::withToken($tokens->accessToken)->acceptJson()->timeout(20)->get(self::API.'/me')->json() ?? [];

        return new TokenSet($tokens->accessToken, $tokens->refreshToken, $tokens->expiresAt, isset($me['id']) ? (string) $me['id'] : null, $tokens->scopes,
            array_filter(['email' => $me['mail'] ?? $me['userPrincipalName'] ?? null]));
    }

    public function revoke(Integration $integration): void
    {
        $this->stopWatch($integration);
    }

    public function busyBlocks(Integration $integration, CarbonInterface $from, CarbonInterface $to): array
    {
        $email = (string) $integration->setting('email');
        $response = $this->call($integration, 'post', self::API.'/me/calendar/getSchedule', [
            'schedules' => [$email],
            'startTime' => ['dateTime' => CarbonImmutable::instance($from)->utc()->format('Y-m-d\TH:i:s'), 'timeZone' => 'UTC'],
            'endTime' => ['dateTime' => CarbonImmutable::instance($to)->utc()->format('Y-m-d\TH:i:s'), 'timeZone' => 'UTC'],
            'availabilityViewInterval' => 15,
        ]);
        if ($response->failed()) {
            throw new ProviderException("outlook: getSchedule failed ({$response->status()})");
        }

        $blocks = [];
        foreach ((array) $response->json('value.0.scheduleItems', []) as $item) {
            if (! in_array($item['status'] ?? 'busy', ['busy', 'tentative', 'oof'], true)) {
                continue;
            }
            $blocks[] = new BusyBlock(
                CarbonImmutable::parse($item['start']['dateTime'], $item['start']['timeZone'] ?? 'UTC')->utc(),
                CarbonImmutable::parse($item['end']['dateTime'], $item['end']['timeZone'] ?? 'UTC')->utc(),
                'outlook',
            );
        }

        return $blocks;
    }

    public function upsertEvent(Integration $integration, Event $event, ?string $externalId = null): string
    {
        $body = array_filter([
            'subject' => $event->title,
            'body' => ['contentType' => 'text', 'content' => trim(($event->description ?? '')."\n".($event->meeting_url ?? ''))],
            'start' => ['dateTime' => $event->starts_at->copy()->utc()->format('Y-m-d\TH:i:s'), 'timeZone' => 'UTC'],
            'end' => ['dateTime' => $event->ends_at->copy()->utc()->format('Y-m-d\TH:i:s'), 'timeZone' => 'UTC'],
            'location' => $event->meeting_url ? ['displayName' => $event->meeting_url] : null,
            'transactionId' => $externalId ? null : $event->id,
        ]);

        if ($externalId) {
            $response = $this->call($integration, 'patch', self::API.'/me/events/'.rawurlencode($externalId), $body);
            if ($response->successful()) {
                return $externalId;
            }
            if ($response->status() !== 404) {
                throw new ProviderException("outlook: update failed ({$response->status()})");
            }
        }

        $response = $this->call($integration, 'post', self::API.'/me/events', $body);
        if ($response->failed() || ! $response->json('id')) {
            throw new ProviderException("outlook: create failed ({$response->status()})");
        }

        return (string) $response->json('id');
    }

    public function deleteEvent(Integration $integration, string $externalId): void
    {
        $response = $this->call($integration, 'delete', self::API.'/me/events/'.rawurlencode($externalId));
        if ($response->failed() && $response->status() !== 404) {
            throw new ProviderException("outlook: delete failed ({$response->status()})");
        }
    }

    public function watch(Integration $integration, string $callbackUrl): array
    {
        $clientState = Str::random(40);
        $expires = CarbonImmutable::now()->addMinutes(min(self::MAX_SUBSCRIPTION_MINUTES, config('integrations.calendar.watch_ttl_hours', 168) * 60));
        $response = $this->call($integration, 'post', self::API.'/subscriptions', [
            'changeType' => 'created,updated,deleted',
            'notificationUrl' => $callbackUrl,
            'resource' => 'me/events',
            'expirationDateTime' => $expires->utc()->toIso8601ZuluString(),
            'clientState' => $clientState,
        ]);
        if ($response->failed()) {
            throw new ProviderException("outlook: subscription failed ({$response->status()})");
        }

        return [
            'subscription_id' => (string) $response->json('id'),
            'client_state' => $clientState,
            'watch_expires_at' => $expires->toIso8601String(),
        ];
    }

    public function stopWatch(Integration $integration): void
    {
        if ($id = $integration->setting('subscription_id')) {
            $this->call($integration, 'delete', self::API.'/subscriptions/'.rawurlencode((string) $id));
        }
    }

    public function verifyWebhookEndpoint(Request $request): ?Response
    {
        $token = $request->query('validationToken');

        return $token === null ? null : response((string) $token, 200, ['Content-Type' => 'text/plain']);
    }

    public function handleWebhook(Request $request): array
    {
        $ids = [];
        foreach ((array) $request->json('value', []) as $notification) {
            $integration = Integration::query()->where('provider', $this->key())
                ->where('settings->subscription_id', (string) ($notification['subscriptionId'] ?? ''))->first();
            if (! $integration || ! hash_equals((string) $integration->setting('client_state'), (string) ($notification['clientState'] ?? ''))) {
                throw new InvalidWebhookSignature('outlook: clientState mismatch');
            }
            $ids[] = $integration->id;
        }

        return array_values(array_unique($ids));
    }
}
