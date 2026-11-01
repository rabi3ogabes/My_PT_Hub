<?php

namespace App\Domain\Integrations\Providers;

use App\Domain\Identity\Models\User;
use App\Domain\Integrations\Contracts\OAuthProvider;
use App\Domain\Integrations\Data\TokenSet;
use App\Domain\Integrations\Exceptions\ProviderAuthException;
use App\Domain\Integrations\Exceptions\ProviderException;
use App\Domain\Integrations\Models\Integration;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Shared OAuth 2.0 authorization-code implementation. Drivers declare their
 * endpoints/scopes and override what differs (token auth, response shape).
 */
abstract class OAuth2Provider implements OAuthProvider
{
    protected string $authorizeUrl;

    protected string $tokenUrl;

    /** @var list<string> */
    protected array $scopes = [];

    protected string $scopeSeparator = ' ';

    /** 'body' sends client credentials in the form body, 'basic' in an Authorization header. */
    protected string $tokenAuth = 'body';

    /** Key of the credentials block in config/services.php. */
    protected string $servicesKey;

    /** @var array<string, string> extra query parameters for the authorization URL */
    protected array $authorizeParams = [];

    abstract public function key(): string;

    public function clientId(): ?string
    {
        $config = config('services.'.$this->servicesKey, []);

        return $config['client_id'] ?? $config['key'] ?? null;
    }

    public function clientSecret(): ?string
    {
        $config = config('services.'.$this->servicesKey, []);

        return $config['client_secret'] ?? $config['secret'] ?? null;
    }

    public function isConfigured(): bool
    {
        return filled($this->clientId()) && filled($this->clientSecret());
    }

    public function usesPkce(): bool
    {
        return false;
    }

    public function redirectUri(): string
    {
        return route('integrations.callback', ['provider' => $this->key()]);
    }

    public function authorizationUrl(User $user, string $state, array $context = []): string
    {
        $query = array_filter([
            'response_type' => 'code',
            'client_id' => $this->clientId(),
            'redirect_uri' => $context['redirect_uri'] ?? $this->redirectUri(),
            'scope' => implode($this->scopeSeparator, $this->scopes),
            'state' => $state,
        ] + $this->authorizeParams, fn ($v) => $v !== null && $v !== '');

        if ($this->usesPkce() && isset($context['code_challenge'])) {
            $query['code_challenge'] = $context['code_challenge'];
            $query['code_challenge_method'] = 'S256';
        }

        return $this->authorizeUrl.'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    public function connect(string $code, array $context = []): TokenSet
    {
        $params = array_filter([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $context['redirect_uri'] ?? $this->redirectUri(),
            'code_verifier' => $context['code_verifier'] ?? null,
        ]);

        $json = $this->tokenRequest($params);

        return $this->toTokenSet($json);
    }

    public function refresh(Integration $integration): TokenSet
    {
        if (! $integration->refresh_token) {
            throw new ProviderAuthException("{$this->key()}: no refresh token");
        }
        $json = $this->tokenRequest(['grant_type' => 'refresh_token', 'refresh_token' => $integration->refresh_token]);
        $tokens = $this->toTokenSet($json);

        // Some providers do not rotate refresh tokens; keep the existing one.
        return $tokens->refreshToken === null
            ? new TokenSet($tokens->accessToken, $integration->refresh_token, $tokens->expiresAt, $tokens->externalUserId ?? $integration->external_user_id, $tokens->scopes, $tokens->extra)
            : $tokens;
    }

    public function revoke(Integration $integration): void {}

    public function afterConnect(Integration $integration): void {}

    /**
     * @param  array<string, mixed>  $json
     */
    protected function toTokenSet(array $json): TokenSet
    {
        return TokenSet::fromOAuthResponse($json, $this->externalUserIdFrom($json), $this->scopeSeparator);
    }

    /** @param array<string, mixed> $json */
    protected function externalUserIdFrom(array $json): ?string
    {
        foreach (['user_id', 'x_user_id', 'userid', 'athlete.id'] as $key) {
            $value = data_get($json, $key);
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function tokenRequest(array $params): array
    {
        $request = Http::asForm()->acceptJson()->timeout(20);
        if ($this->tokenAuth === 'basic') {
            $request = $request->withBasicAuth((string) $this->clientId(), (string) $this->clientSecret());
        } else {
            $params += ['client_id' => $this->clientId(), 'client_secret' => $this->clientSecret()];
        }

        try {
            $response = $request->post($this->tokenUrl, $params);
        } catch (ConnectionException $e) {
            throw new ProviderException("{$this->key()}: token endpoint unreachable", 0, $e);
        }

        if (in_array($response->status(), [400, 401, 403], true)) {
            throw new ProviderAuthException("{$this->key()}: token request rejected ({$response->status()}) ".(string) ($response->json('error') ?? ''));
        }
        if ($response->failed() || ! is_array($response->json())) {
            throw new ProviderException("{$this->key()}: token request failed ({$response->status()})");
        }

        return $this->unwrapTokenResponse($response->json());
    }

    /**
     * @param  array<string, mixed>  $json
     * @return array<string, mixed>
     */
    protected function unwrapTokenResponse(array $json): array
    {
        if (! isset($json['access_token'])) {
            throw new ProviderException("{$this->key()}: token response without access_token");
        }

        return $json;
    }

    protected function api(Integration $integration): PendingRequest
    {
        return Http::withToken((string) $integration->access_token)->acceptJson()->timeout(20);
    }

    /**
     * Executes an authenticated call and maps auth/transport failures to provider exceptions.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function call(Integration $integration, string $method, string $url, array $payload = []): Response
    {
        try {
            $request = $this->api($integration);
            $response = match (strtolower($method)) {
                'get' => $request->get($url, $payload),
                'delete' => $request->delete($url, $payload),
                'patch' => $request->patch($url, $payload),
                'put' => $request->put($url, $payload),
                default => $request->post($url, $payload),
            };
        } catch (ConnectionException $e) {
            throw new ProviderException("{$this->key()}: {$url} unreachable", 0, $e);
        }

        if ($response->status() === 401) {
            throw new ProviderAuthException("{$this->key()}: access token rejected");
        }
        if ($response->serverError() || $response->status() === 429) {
            throw new ProviderException("{$this->key()}: {$url} failed ({$response->status()})");
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>
     */
    protected function getJson(Integration $integration, string $url, array $query = []): array
    {
        $response = $this->call($integration, 'get', $url, $query);
        if ($response->failed()) {
            throw new ProviderException("{$this->key()}: {$url} failed ({$response->status()})");
        }

        return (array) $response->json();
    }
}
