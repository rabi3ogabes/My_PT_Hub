<?php

namespace App\Http\Api\V1\Integrations;

use App\Domain\Identity\Models\User;
use App\Domain\Integrations\Exceptions\ProviderException;
use App\Domain\Integrations\Jobs\SyncIntegrationJob;
use App\Domain\Integrations\Models\Integration;
use App\Domain\Integrations\Services\CalendarSyncService;
use App\Domain\Integrations\Services\IntegrationManager;
use App\Domain\Integrations\Services\ProviderRegistry;
use App\Domain\Integrations\Services\WhatsAppService;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Membership;
use App\Http\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IntegrationController extends ApiController
{
    public function __construct(
        private readonly ProviderRegistry $registry,
        private readonly IntegrationManager $manager,
        private readonly WhatsAppService $whatsapp,
    ) {}

    /** Staff: full catalogue with the staff member's own connection status and client adoption counts. */
    public function index(): JsonResponse
    {
        $clientIds = Membership::query()->where('role', 'client')->where('status', 'active')->pluck('user_id');
        $adoption = Integration::query()->whereIn('user_id', $clientIds)->where('status', 'connected')
            ->selectRaw('provider, COUNT(*) as c')->groupBy('provider')->pluck('c', 'provider')->map(fn ($c) => (int) $c);

        return $this->ok($this->catalogue($this->user(), null), [
            'clients_connected' => (object) $adoption->all(),
            'clients_total' => $clientIds->count(),
        ]);
    }

    /** Client (and any user): sources they can connect, with status. */
    public function mine(): JsonResponse
    {
        return $this->ok($this->catalogue($this->user(), 'client'));
    }

    public function connect(Request $request, string $provider): JsonResponse
    {
        $this->assertProvider($provider);
        $user = $this->user();
        $tenant = $this->tenant();

        return match ($this->registry->method($provider)) {
            'device' => $this->ok($this->item($provider, $this->manager->connectDevice($user, $provider, $tenant))),
            'optin' => $this->optIn($request, $user),
            default => $this->ok($this->manager->beginConnect($user, $provider, $tenant, $request->validate([
                'return_to' => ['nullable', 'string', 'max:500'],
            ])['return_to'] ?? null)),
        };
    }

    /** OAuth redirect target (web route): finishes the connection and returns the user to the app. */
    public function callback(Request $request, string $provider): RedirectResponse
    {
        $returnTo = (string) config('integrations.return_url', '/');
        if (! $this->registry->exists($provider)) {
            return redirect()->to($this->withQuery($returnTo, ['provider' => $provider, 'status' => 'error', 'reason' => 'unknown_provider']));
        }

        if ($request->filled('error') || ! $request->filled('code') || ! $request->filled('state')) {
            return redirect()->to($this->withQuery($returnTo, ['provider' => $provider, 'status' => 'error', 'reason' => $request->filled('error') ? 'denied' : 'invalid_request']));
        }

        try {
            $result = $this->manager->completeConnect($provider, (string) $request->query('code'), (string) $request->query('state'));
        } catch (ApiException $e) {
            return redirect()->to($this->withQuery($returnTo, ['provider' => $provider, 'status' => 'error', 'reason' => $e->errorCode]));
        } catch (ProviderException $e) {
            Log::warning('integrations.callback_failed', ['provider' => $provider, 'error' => $e->getMessage()]);

            return redirect()->to($this->withQuery($returnTo, ['provider' => $provider, 'status' => 'error', 'reason' => 'provider_error']));
        }

        return redirect()->to($this->withQuery($result['return_to'] ?? $returnTo, ['provider' => $provider, 'status' => 'connected']));
    }

    public function disconnect(Request $request, string $provider): JsonResponse
    {
        $this->assertProvider($provider);
        $data = $request->validate(['delete_data' => ['sometimes', 'boolean']]);
        $user = $this->user();

        if ($this->registry->method($provider) === 'optin') {
            $this->whatsapp->optOut($user);

            return $this->ok(['provider' => $provider, 'disconnected' => true, 'deleted_samples' => 0]);
        }

        $deleteData = (bool) ($data['delete_data'] ?? false);
        $count = $deleteData ? $this->manager->sampleCount($user, $provider) : 0;
        $this->manager->disconnect($user, $provider, $deleteData);

        return $this->ok(['provider' => $provider, 'disconnected' => true, 'deleted_samples' => $count]);
    }

    public function syncNow(string $provider): JsonResponse
    {
        $this->assertProvider($provider);
        $integration = Integration::query()->where('user_id', $this->user()->id)->where('provider', $provider)->first()
            ?? throw new ApiException('not_found', __('integrations.errors.not_connected'), 404);

        if ($this->registry->method($provider) !== 'oauth') {
            throw new ApiException('sync_on_device', __('integrations.errors.sync_on_device'), 422);
        }
        if ($integration->status === 'revoked' || ! $integration->access_token) {
            throw new ApiException('reconnect_required', __('integrations.errors.reconnect_required'), 409);
        }
        if (! $this->manager->allowManualSync($integration)) {
            throw new ApiException('too_many_requests', __('integrations.errors.sync_throttled'), 429);
        }

        match ($this->registry->category($provider)) {
            'health' => SyncIntegrationJob::dispatch($integration->id),
            'calendar' => app(CalendarSyncService::class)->handleChange($integration),
            default => null,
        };

        return $this->ok(['provider' => $provider, 'queued' => true], [], 202);
    }

    private function optIn(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:32'],
            'consent' => ['accepted'],
        ]);

        return $this->ok($this->item('whatsapp', $this->whatsapp->optIn($user, $data['phone'], $this->tenant())));
    }

    private function assertProvider(string $provider): void
    {
        if (! $this->registry->exists($provider)) {
            throw new ApiException('not_found', __('integrations.errors.unknown_provider'), 404);
        }
    }

    /** @return list<array<string, mixed>> */
    private function catalogue(User $user, ?string $audience): array
    {
        $connections = Integration::query()->where('user_id', $user->id)->get()->keyBy('provider');
        $items = [];
        foreach ($this->registry->catalogue() as $provider => $meta) {
            if ($audience !== null && ! in_array($audience, (array) ($meta['audience'] ?? []), true)) {
                continue;
            }
            $items[] = $this->item($provider, $connections->get($provider));
        }

        return $items;
    }

    /** @return array<string, mixed> */
    private function item(string $provider, ?Integration $integration): array
    {
        $meta = $this->registry->meta($provider);
        $status = $integration?->status ?? 'disconnected';

        return [
            'provider' => $provider,
            'name' => __("integrations.providers.{$provider}.name"),
            'description' => __("integrations.providers.{$provider}.description"),
            'category' => $meta['category'],
            'method' => $meta['method'],
            'audience' => array_values((array) $meta['audience']),
            'configured' => $this->registry->isConfigured($provider),
            'connected' => $status === 'connected',
            'status' => $status,
            'needs_reconnect' => $status === 'error',
            'account' => $integration?->setting('email') ?? ($provider === 'whatsapp' && $integration?->external_user_id ? '+'.$integration->external_user_id : null),
            'connected_at' => $integration?->setting('connected_at') ?? $integration?->created_at?->toIso8601String(),
            'last_sync_at' => $integration?->last_sync_at?->toIso8601String(),
            'last_error' => $status === 'error' ? __('integrations.errors.connection_broken') : null,
        ];
    }

    /** @param array<string, string> $params */
    private function withQuery(string $url, array $params): string
    {
        return $url.(str_contains($url, '?') ? '&' : '?').http_build_query($params);
    }
}
