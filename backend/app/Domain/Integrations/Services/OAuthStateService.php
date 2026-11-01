<?php

namespace App\Domain\Integrations\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Signed, single-use OAuth state. The state string is "{nonce}.{hmac}"; the
 * flow context (user, tenant, PKCE verifier, return URL) stays server-side.
 */
final class OAuthStateService
{
    /**
     * @return array{state: string, code_challenge: string|null, expires_at: CarbonImmutable}
     */
    public function issue(User $user, string $provider, ?Tenant $tenant, ?string $returnTo, bool $pkce): array
    {
        $nonce = Str::random(40);
        $verifier = $pkce ? rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=') : null;
        $ttl = (int) config('integrations.state_ttl_minutes', 15);
        $expiresAt = now()->toImmutable()->addMinutes($ttl);

        Cache::put($this->cacheKey($nonce), [
            'user_id' => $user->id,
            'tenant_id' => $tenant?->id,
            'provider' => $provider,
            'code_verifier' => $verifier,
            'return_to' => $this->safeReturn($returnTo),
        ], $expiresAt);

        return [
            'state' => $nonce.'.'.$this->sign($nonce, $provider),
            'code_challenge' => $verifier ? rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=') : null,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Verifies and consumes the state (single use).
     *
     * @return array{user_id: string, tenant_id: string|null, provider: string, code_verifier: string|null, return_to: string|null}
     */
    public function consume(string $provider, string $state): array
    {
        [$nonce, $signature] = array_pad(explode('.', $state, 2), 2, '');
        if ($nonce === '' || ! hash_equals($this->sign($nonce, $provider), $signature)) {
            throw new ApiException('invalid_state', __('integrations.errors.invalid_state'), 400);
        }
        $payload = Cache::pull($this->cacheKey($nonce));
        if (! is_array($payload) || $payload['provider'] !== $provider) {
            throw new ApiException('invalid_state', __('integrations.errors.invalid_state'), 400);
        }

        return $payload;
    }

    public function safeReturn(?string $returnTo): ?string
    {
        if ($returnTo === null || $returnTo === '' || str_starts_with($returnTo, '//')) {
            return null;
        }
        foreach ((array) config('integrations.allowed_return_prefixes', []) as $prefix) {
            if ($prefix !== '' && str_starts_with($returnTo, $prefix)) {
                return $returnTo;
            }
        }

        return null;
    }

    private function sign(string $nonce, string $provider): string
    {
        return hash_hmac('sha256', $nonce.'|'.$provider, (string) config('app.key'));
    }

    private function cacheKey(string $nonce): string
    {
        return 'integrations:oauth-state:'.$nonce;
    }
}
