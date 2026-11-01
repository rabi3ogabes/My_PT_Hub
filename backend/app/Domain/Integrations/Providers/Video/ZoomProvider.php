<?php

namespace App\Domain\Integrations\Providers\Video;

use App\Domain\Calendar\Models\Event;
use App\Domain\Integrations\Contracts\VideoProvider;
use App\Domain\Integrations\Data\TokenSet;
use App\Domain\Integrations\Exceptions\ProviderException;
use App\Domain\Integrations\Models\Integration;
use App\Domain\Integrations\Providers\OAuth2Provider;
use Illuminate\Support\Facades\Http;

/** Zoom (user-level OAuth app): creates scheduled meetings for online sessions and live classes. */
class ZoomProvider extends OAuth2Provider implements VideoProvider
{
    public const API = 'https://api.zoom.us/v2';

    protected string $authorizeUrl = 'https://zoom.us/oauth/authorize';

    protected string $tokenUrl = 'https://zoom.us/oauth/token';

    protected string $servicesKey = 'zoom';

    protected string $tokenAuth = 'basic';

    public function key(): string
    {
        return 'zoom';
    }

    public function connect(string $code, array $context = []): TokenSet
    {
        $tokens = parent::connect($code, $context);
        $me = Http::withToken($tokens->accessToken)->acceptJson()->timeout(20)->get(self::API.'/users/me')->json() ?? [];

        return new TokenSet($tokens->accessToken, $tokens->refreshToken, $tokens->expiresAt, isset($me['id']) ? (string) $me['id'] : null, $tokens->scopes, array_filter(['email' => $me['email'] ?? null]));
    }

    public function revoke(Integration $integration): void
    {
        Http::asForm()->withBasicAuth((string) $this->clientId(), (string) $this->clientSecret())->timeout(10)
            ->post('https://zoom.us/oauth/revoke', ['token' => $integration->access_token]);
    }

    public function createMeeting(Integration $integration, Event $event): array
    {
        $response = $this->call($integration, 'post', self::API.'/users/me/meetings', [
            'topic' => $event->title,
            'type' => 2,
            'start_time' => $event->starts_at->copy()->utc()->format('Y-m-d\TH:i:s\Z'),
            'duration' => max(15, (int) $event->starts_at->diffInMinutes($event->ends_at, true)),
            'timezone' => $event->timezone ?: 'UTC',
            'agenda' => (string) ($event->description ?? ''),
            'settings' => ['join_before_host' => false, 'waiting_room' => true, 'auto_recording' => 'cloud'],
        ]);
        if ($response->failed() || ! $response->json('join_url')) {
            throw new ProviderException("zoom: meeting creation failed ({$response->status()})");
        }

        return [
            'id' => (string) $response->json('id'),
            'join_url' => (string) $response->json('join_url'),
            'start_url' => $response->json('start_url'),
        ];
    }
}
