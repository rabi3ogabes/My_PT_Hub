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

/** Google Calendar API v3: events, freeBusy and push notification channels. */
class GoogleCalendarProvider extends OAuth2Provider implements CalendarProvider
{
    public const API = 'https://www.googleapis.com/calendar/v3';

    protected string $authorizeUrl = 'https://accounts.google.com/o/oauth2/v2/auth';

    protected string $tokenUrl = 'https://oauth2.googleapis.com/token';

    protected string $servicesKey = 'google_calendar';

    protected array $scopes = ['openid', 'email', 'https://www.googleapis.com/auth/calendar.events', 'https://www.googleapis.com/auth/calendar.freebusy'];

    protected array $authorizeParams = ['access_type' => 'offline', 'prompt' => 'consent', 'include_granted_scopes' => 'true'];

    public function key(): string
    {
        return 'google_calendar';
    }

    protected function toTokenSet(array $json): TokenSet
    {
        $tokens = parent::toTokenSet($json);
        $claims = [];
        if (isset($json['id_token']) && substr_count((string) $json['id_token'], '.') === 2) {
            // Received directly from Google's token endpoint over TLS: claims are trusted without re-verifying the signature.
            $claims = (array) json_decode((string) base64_decode(strtr(explode('.', (string) $json['id_token'])[1], '-_', '+/')), true);
        }

        return new TokenSet($tokens->accessToken, $tokens->refreshToken, $tokens->expiresAt, isset($claims['sub']) ? (string) $claims['sub'] : null, $tokens->scopes, array_filter(['email' => $claims['email'] ?? null]));
    }

    public function revoke(Integration $integration): void
    {
        $this->stopWatch($integration);
        Http::asForm()->timeout(10)->post('https://oauth2.googleapis.com/revoke', ['token' => $integration->refresh_token ?: $integration->access_token]);
    }

    public function busyBlocks(Integration $integration, CarbonInterface $from, CarbonInterface $to): array
    {
        $response = $this->call($integration, 'post', self::API.'/freeBusy', [
            'timeMin' => CarbonImmutable::instance($from)->utc()->toRfc3339String(),
            'timeMax' => CarbonImmutable::instance($to)->utc()->toRfc3339String(),
            'items' => [['id' => 'primary']],
        ]);
        if ($response->failed()) {
            throw new ProviderException("google_calendar: freeBusy failed ({$response->status()})");
        }

        return array_map(fn (array $b) => new BusyBlock(CarbonImmutable::parse($b['start'])->utc(), CarbonImmutable::parse($b['end'])->utc(), 'google_calendar'),
            (array) $response->json('calendars.primary.busy', []));
    }

    public function upsertEvent(Integration $integration, Event $event, ?string $externalId = null): string
    {
        $tz = $event->timezone ?: 'UTC';
        $body = array_filter([
            'summary' => $event->title,
            'description' => trim(($event->description ?? '')."\n".($event->meeting_url ?? '')) ?: null,
            'start' => ['dateTime' => $event->starts_at->copy()->setTimezone($tz)->toRfc3339String(), 'timeZone' => $tz],
            'end' => ['dateTime' => $event->ends_at->copy()->setTimezone($tz)->toRfc3339String(), 'timeZone' => $tz],
            'location' => $event->meeting_url,
            'status' => $event->status === 'cancelled' ? 'cancelled' : 'confirmed',
            'extendedProperties' => ['private' => ['aurum_event_id' => $event->id]],
        ]);

        if ($externalId) {
            $response = $this->call($integration, 'patch', self::API.'/calendars/primary/events/'.rawurlencode($externalId), $body);
            if ($response->successful()) {
                return $externalId;
            }
            if (! in_array($response->status(), [404, 410], true)) {
                throw new ProviderException("google_calendar: update failed ({$response->status()})");
            }
        }

        $response = $this->call($integration, 'post', self::API.'/calendars/primary/events', $body);
        if ($response->failed() || ! $response->json('id')) {
            throw new ProviderException("google_calendar: create failed ({$response->status()})");
        }

        return (string) $response->json('id');
    }

    public function deleteEvent(Integration $integration, string $externalId): void
    {
        $response = $this->call($integration, 'delete', self::API.'/calendars/primary/events/'.rawurlencode($externalId));
        if ($response->failed() && ! in_array($response->status(), [404, 410], true)) {
            throw new ProviderException("google_calendar: delete failed ({$response->status()})");
        }
    }

    public function watch(Integration $integration, string $callbackUrl): array
    {
        $channelId = (string) Str::uuid();
        $token = Str::random(40);
        $response = $this->call($integration, 'post', self::API.'/calendars/primary/events/watch', [
            'id' => $channelId,
            'type' => 'web_hook',
            'address' => $callbackUrl,
            'token' => $token,
            'params' => ['ttl' => (string) (config('integrations.calendar.watch_ttl_hours', 168) * 3600)],
        ]);
        if ($response->failed()) {
            throw new ProviderException("google_calendar: watch failed ({$response->status()})");
        }
        $expiration = $response->json('expiration');

        return [
            'channel_id' => $channelId,
            'channel_token' => $token,
            'resource_id' => (string) $response->json('resourceId'),
            'watch_expires_at' => $expiration ? CarbonImmutable::createFromTimestampMs((int) $expiration)->toIso8601String() : null,
        ];
    }

    public function stopWatch(Integration $integration): void
    {
        if ($integration->setting('channel_id') && $integration->setting('resource_id')) {
            $this->call($integration, 'post', self::API.'/channels/stop', ['id' => $integration->setting('channel_id'), 'resourceId' => $integration->setting('resource_id')]);
        }
    }

    public function verifyWebhookEndpoint(Request $request): ?Response
    {
        return null;
    }

    public function handleWebhook(Request $request): array
    {
        $channelId = (string) $request->header('X-Goog-Channel-ID');
        $integration = $channelId === '' ? null : Integration::query()->where('provider', $this->key())->where('settings->channel_id', $channelId)->first();
        if (! $integration || ! hash_equals((string) $integration->setting('channel_token'), (string) $request->header('X-Goog-Channel-Token'))) {
            throw new InvalidWebhookSignature('google_calendar: unknown channel');
        }

        return $request->header('X-Goog-Resource-State') === 'sync' ? [] : [$integration->id];
    }
}
