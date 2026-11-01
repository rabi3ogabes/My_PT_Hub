<?php

namespace App\Http\Web\Integrations;

use App\Domain\Integrations\Contracts\CalendarProvider;
use App\Domain\Integrations\Contracts\HealthProvider;
use App\Domain\Integrations\Exceptions\InvalidWebhookSignature;
use App\Domain\Integrations\Jobs\ProcessProviderWebhookJob;
use App\Domain\Integrations\Models\Integration;
use App\Domain\Integrations\Services\CalendarSyncService;
use App\Domain\Integrations\Services\ProviderRegistry;
use App\Domain\Integrations\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Inbound provider webhooks: GET verification handshakes and signed POST
 * notifications. Work is queued so providers get a fast 2xx.
 */
class ProviderWebhookController extends Controller
{
    public function __construct(
        private readonly ProviderRegistry $registry,
        private readonly WhatsAppService $whatsapp,
        private readonly CalendarSyncService $calendars,
    ) {}

    public function verify(Request $request, string $provider): Response
    {
        if ($provider === 'whatsapp') {
            return $this->whatsapp->verifyEndpoint($request);
        }
        if (! $this->registry->exists($provider) || ! $this->registry->hasDriver($provider)) {
            return response()->noContent(404);
        }
        $driver = $this->registry->driver($provider);
        $response = $driver instanceof HealthProvider || $driver instanceof CalendarProvider ? $driver->verifyWebhookEndpoint($request) : null;

        return $response ?? response()->noContent(404);
    }

    public function handle(Request $request, string $provider): Response
    {
        try {
            if ($provider === 'whatsapp') {
                $this->whatsapp->handleInbound($request);

                return response()->json(['received' => true]);
            }
            if (! $this->registry->exists($provider) || ! $this->registry->hasDriver($provider)) {
                return response()->noContent(404);
            }

            $driver = $this->registry->driver($provider);
            if ($driver instanceof CalendarProvider) {
                if ($handshake = $driver->verifyWebhookEndpoint($request)) {
                    return $handshake;
                }
                foreach ($driver->handleWebhook($request) as $integrationId) {
                    if ($integration = Integration::query()->find($integrationId)) {
                        $this->calendars->handleChange($integration);
                    }
                }

                return response()->noContent(202);
            }
            if ($driver instanceof HealthProvider) {
                foreach ($driver->handleWebhook($request) as $notification) {
                    ProcessProviderWebhookJob::dispatch($provider, $notification->toArray());
                }

                return response()->noContent($provider === 'fitbit' ? 204 : 200);
            }
        } catch (InvalidWebhookSignature $e) {
            Log::warning('integrations.webhook_rejected', ['provider' => $provider, 'reason' => $e->getMessage(), 'ip' => $request->ip()]);

            return response()->json(['error' => 'invalid_signature'], 401);
        }

        return response()->noContent(404);
    }
}
