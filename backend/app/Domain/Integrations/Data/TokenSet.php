<?php

namespace App\Domain\Integrations\Data;

use Carbon\CarbonImmutable;

final class TokenSet
{
    /**
     * @param  list<string>  $scopes
     * @param  array<string, mixed>  $extra  provider-specific values to keep in integration settings
     */
    public function __construct(
        public readonly string $accessToken,
        public readonly ?string $refreshToken = null,
        public readonly ?CarbonImmutable $expiresAt = null,
        public readonly ?string $externalUserId = null,
        public readonly array $scopes = [],
        public readonly array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>  $json  standard OAuth2 token response
     */
    public static function fromOAuthResponse(array $json, ?string $externalUserId = null, string $scopeSeparator = ' '): self
    {
        $scope = $json['scope'] ?? [];
        $scopes = is_array($scope) ? array_values($scope) : array_values(array_filter(explode($scopeSeparator, (string) $scope)));
        $expiresAt = null;
        if (isset($json['expires_at']) && is_numeric($json['expires_at'])) {
            $expiresAt = CarbonImmutable::createFromTimestamp((int) $json['expires_at']);
        } elseif (isset($json['expires_in']) && is_numeric($json['expires_in'])) {
            $expiresAt = CarbonImmutable::now()->addSeconds((int) $json['expires_in']);
        }

        return new self(
            accessToken: (string) $json['access_token'],
            refreshToken: isset($json['refresh_token']) ? (string) $json['refresh_token'] : null,
            expiresAt: $expiresAt,
            externalUserId: $externalUserId,
            scopes: $scopes,
        );
    }
}
