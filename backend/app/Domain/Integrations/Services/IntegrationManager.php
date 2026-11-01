<?php

namespace App\Domain\Integrations\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Integrations\Contracts\CalendarProvider;
use App\Domain\Integrations\Contracts\HealthProvider;
use App\Domain\Integrations\Data\WebhookNotification;
use App\Domain\Integrations\Exceptions\ProviderAuthException;
use App\Domain\Integrations\Jobs\StartCalendarWatchJob;
use App\Domain\Integrations\Jobs\SyncIntegrationJob;
use App\Domain\Integrations\Models\HealthSample;
use App\Domain\Integrations\Models\Integration;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Tenant;
use App\Notifications\Integrations\IntegrationNeedsReconnect;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Connection lifecycle for every provider: OAuth connect/callback, token
 * refresh, failure tracking with backoff, reconnect prompts and disconnect.
 */
final class IntegrationManager
{
    public function __construct(
        private readonly ProviderRegistry $registry,
        private readonly OAuthStateService $states,
        private readonly HealthIngestionService $ingestion,
    ) {}

    public function assertCanConnect(User $user, string $provider, ?Tenant $tenant): void
    {
        $audience = (array) ($this->registry->meta($provider)['audience'] ?? ['client']);
        $isStaff = $tenant !== null && $user->isStaffIn($tenant->id);
        if (! in_array($isStaff ? 'staff' : 'client', $audience, true) && ! ($isStaff && in_array('client', $audience, true) && $this->registry->category($provider) === 'health')) {
            throw ApiException::forbidden(__('integrations.errors.not_for_role'));
        }
    }

    /**
     * Starts an OAuth connection and returns the provider authorization URL.
     *
     * @return array{provider: string, authorization_url: string, state: string, expires_at: string}
     */
    public function beginConnect(User $user, string $provider, ?Tenant $tenant, ?string $returnTo = null): array
    {
        $this->assertCanConnect($user, $provider, $tenant);
        $driver = $this->registry->driver($provider);
        if (! $driver->isConfigured()) {
            throw new ApiException('integration_unavailable', __('integrations.errors.not_configured'), 422);
        }

        $issued = $this->states->issue($user, $provider, $tenant, $returnTo, $driver->usesPkce());

        return [
            'provider' => $provider,
            'authorization_url' => $driver->authorizationUrl($user, $issued['state'], array_filter(['code_challenge' => $issued['code_challenge']])),
            'state' => $issued['state'],
            'expires_at' => $issued['expires_at']->toIso8601String(),
        ];
    }

    /**
     * Handles the OAuth callback: verifies state, exchanges the code, stores tokens.
     *
     * @return array{integration: Integration, return_to: string|null}
     */
    public function completeConnect(string $provider, string $code, string $state): array
    {
        $context = $this->states->consume($provider, $state);
        $user = User::query()->findOrFail($context['user_id']);
        $driver = $this->registry->driver($provider);
        $tokens = $driver->connect($code, array_filter(['code_verifier' => $context['code_verifier']]));

        $integration = DB::transaction(function () use ($user, $provider, $tokens, $context) {
            $integration = Integration::query()->firstOrNew(['user_id' => $user->id, 'provider' => $provider]);
            $integration->fill([
                'tenant_id' => $context['tenant_id'],
                'status' => 'connected',
                'access_token' => $tokens->accessToken,
                'refresh_token' => $tokens->refreshToken,
                'token_expires_at' => $tokens->expiresAt,
                'external_user_id' => $tokens->externalUserId,
                'scopes' => $tokens->scopes,
                'failure_count' => 0,
                'next_attempt_at' => null,
                'last_error' => null,
            ]);
            $integration->mergeSettings($tokens->extra + ['timezone' => $user->timezone ?: 'UTC', 'connected_at' => now()->toIso8601String()]);
            $integration->save();

            return $integration;
        });

        try {
            $driver->afterConnect($integration);
        } catch (Throwable $e) {
            Log::warning('integrations.after_connect_failed', ['provider' => $provider, 'integration' => $integration->id, 'error' => $e->getMessage()]);
        }

        ActivityLogger::log('integration.connected', $integration, ['provider' => $provider], tenantId: $context['tenant_id']);

        if ($driver instanceof HealthProvider) {
            SyncIntegrationJob::dispatch($integration->id, backfill: true);
        } elseif ($driver instanceof CalendarProvider) {
            StartCalendarWatchJob::dispatch($integration->id);
        }

        return ['integration' => $integration->refresh(), 'return_to' => $context['return_to']];
    }

    /** Device-side sources (Apple Health / Health Connect) are "connected" once the app has permission. */
    public function connectDevice(User $user, string $provider, ?Tenant $tenant): Integration
    {
        if ($this->registry->method($provider) !== 'device') {
            throw new ApiException('integration_not_device', __('integrations.errors.not_device'), 422);
        }

        $integration = Integration::query()->firstOrNew(['user_id' => $user->id, 'provider' => $provider]);
        $integration->fill(['tenant_id' => $tenant?->id, 'status' => 'connected', 'last_error' => null, 'failure_count' => 0]);
        $integration->mergeSettings(['timezone' => $user->timezone ?: 'UTC', 'connected_at' => $integration->setting('connected_at', now()->toIso8601String())]);
        $integration->save();

        return $integration;
    }

    /**
     * Revokes at the provider (best effort), removes the connection and
     * optionally deletes every sample that came from this source.
     */
    public function disconnect(User $user, string $provider, bool $deleteData = false): void
    {
        $integration = Integration::query()->where('user_id', $user->id)->where('provider', $provider)->first();

        if ($integration && $this->registry->hasDriver($provider) && $integration->access_token) {
            try {
                $this->registry->driver($provider)->revoke($integration);
            } catch (Throwable $e) {
                Log::info('integrations.revoke_failed', ['provider' => $provider, 'error' => $e->getMessage()]);
            }
        }

        DB::transaction(function () use ($integration, $user, $provider, $deleteData) {
            $integration?->delete();
            if ($deleteData && $this->registry->category($provider) === 'health') {
                $this->ingestion->deleteSource($user, $provider);
            }
        });

        ActivityLogger::log('integration.disconnected', $integration ?? $user, ['provider' => $provider, 'deleted_data' => $deleteData], tenantId: $integration?->tenant_id);
    }

    /** Refreshes the access token when it is about to expire (serialized per integration). */
    public function withFreshToken(Integration $integration): Integration
    {
        if (! $integration->tokenExpiresSoon() || ! $this->registry->hasDriver($integration->provider)) {
            return $integration;
        }

        return Cache::lock('integrations:refresh:'.$integration->id, 30)->block(15, function () use ($integration) {
            $integration->refresh();
            if (! $integration->tokenExpiresSoon()) {
                return $integration;
            }
            try {
                $tokens = $this->registry->driver($integration->provider)->refresh($integration);
            } catch (ProviderAuthException $e) {
                $this->markNeedsReconnect($integration, $e->getMessage());

                throw $e;
            }
            $integration->forceFill([
                'access_token' => $tokens->accessToken,
                'refresh_token' => $tokens->refreshToken ?? $integration->refresh_token,
                'token_expires_at' => $tokens->expiresAt,
            ])->save();

            return $integration;
        });
    }

    /**
     * Pulls (or backfills) a health integration and ingests the samples.
     *
     * @return array{created: int, updated: int, duplicates: int, skipped: int}
     */
    public function syncHealth(Integration $integration, ?CarbonInterface $since = null, bool $backfill = false): array
    {
        $driver = $this->registry->health($integration->provider);

        try {
            $integration = $this->withFreshToken($integration);
            $samples = $backfill
                ? $driver->backfill($integration, (int) config('integrations.sync.backfill_days', 30))
                : $driver->pull($integration, $since ?? $this->defaultSince($integration));
            $result = $this->ingestion->ingest($integration->user()->firstOrFail(), $samples);
            $this->recordSuccess($integration);

            return $result;
        } catch (Throwable $e) {
            $this->recordFailure($integration, $e);

            throw $e;
        }
    }

    /**
     * Applies one verified webhook notification for the matching integration.
     *
     * @return array{created: int, updated: int, duplicates: int, skipped: int}|null
     */
    public function processNotification(string $provider, WebhookNotification $notification): ?array
    {
        $integration = Integration::query()->where('provider', $provider)
            ->where('external_user_id', $notification->externalUserId)
            ->where('status', '!=', 'revoked')
            ->latest('updated_at')->first();
        if (! $integration) {
            return null;
        }

        if ($notification->deauthorized) {
            $integration->forceFill(['status' => 'revoked', 'access_token' => null, 'refresh_token' => null, 'last_error' => 'deauthorized'])->save();

            return null;
        }

        $user = $integration->user()->firstOrFail();
        if ($externalId = $notification->data['delete_external_id'] ?? null) {
            $this->ingestion->deleteSample($user, $provider, (string) $externalId);

            return null;
        }

        try {
            $samples = $notification->samples;
            if ($samples === []) {
                $integration = $this->withFreshToken($integration);
                $samples = $this->registry->health($provider)->fetchNotification($integration, $notification);
            }
            $result = $this->ingestion->ingest($user, $samples);
            $this->recordSuccess($integration);

            return $result;
        } catch (Throwable $e) {
            $this->recordFailure($integration, $e);

            throw $e;
        }
    }

    public function recordSuccess(Integration $integration): void
    {
        $integration->forceFill([
            'status' => 'connected',
            'last_sync_at' => now(),
            'last_error' => null,
            'failure_count' => 0,
            'next_attempt_at' => null,
        ])->save();
    }

    /** Exponential backoff; auth failures or too many consecutive failures prompt a reconnect. */
    public function recordFailure(Integration $integration, Throwable $e): void
    {
        if ($e instanceof ProviderAuthException) {
            $this->markNeedsReconnect($integration, $e->getMessage());

            return;
        }

        $failures = $integration->failure_count + 1;
        $base = (int) config('integrations.sync.backoff_base_minutes', 5);
        $max = (int) config('integrations.sync.backoff_max_minutes', 360);
        $integration->forceFill([
            'failure_count' => $failures,
            'next_attempt_at' => now()->addMinutes(min($max, $base * (2 ** ($failures - 1)))),
            'last_error' => Str::limit($e->getMessage(), 250, ''),
        ])->save();

        if ($failures >= (int) config('integrations.sync.max_failures', 5)) {
            $this->markNeedsReconnect($integration, $e->getMessage());
        }
    }

    public function markNeedsReconnect(Integration $integration, string $reason): void
    {
        $alreadyFlagged = $integration->status === 'error';
        $integration->forceFill(['status' => 'error', 'last_error' => Str::limit($reason, 250, '')])->save();

        if (! $alreadyFlagged && ($user = $integration->user()->first())) {
            $user->notify(new IntegrationNeedsReconnect($integration->provider));
        }
    }

    private function defaultSince(Integration $integration): CarbonImmutable
    {
        return $integration->last_sync_at
            ? CarbonImmutable::instance($integration->last_sync_at)->subHours(2)
            : CarbonImmutable::now()->subDays(2)->startOfDay();
    }

    /** True when a manual "sync now" is allowed (one per minute per integration). */
    public function allowManualSync(Integration $integration): bool
    {
        return Cache::add('integrations:manual-sync:'.$integration->id, 1, 60);
    }

    public function sampleCount(User $user, string $source): int
    {
        return HealthSample::query()->where('user_id', $user->id)->where('source', $source)->count();
    }
}
