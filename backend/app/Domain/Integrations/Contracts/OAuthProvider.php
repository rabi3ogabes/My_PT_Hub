<?php

namespace App\Domain\Integrations\Contracts;

use App\Domain\Identity\Models\User;
use App\Domain\Integrations\Data\TokenSet;
use App\Domain\Integrations\Exceptions\ProviderAuthException;
use App\Domain\Integrations\Models\Integration;

/** A provider the user connects through an OAuth authorization-code flow. */
interface OAuthProvider
{
    public function key(): string;

    /** True when client credentials are present in config/services.php. */
    public function isConfigured(): bool;

    /** Whether the flow needs a PKCE code_verifier/challenge pair. */
    public function usesPkce(): bool;

    /**
     * @param  array{redirect_uri?: string, code_challenge?: string}  $context
     */
    public function authorizationUrl(User $user, string $state, array $context = []): string;

    /**
     * Exchanges an authorization code for tokens.
     *
     * @param  array{redirect_uri?: string, code_verifier?: string}  $context
     */
    public function connect(string $code, array $context = []): TokenSet;

    /** @throws ProviderAuthException when the grant is no longer valid */
    public function refresh(Integration $integration): TokenSet;

    /** Best-effort revocation at the provider (deauthorize / delete subscriptions). */
    public function revoke(Integration $integration): void;

    /** Provider-side setup after the tokens are stored (subscriptions, user registration). */
    public function afterConnect(Integration $integration): void;
}
