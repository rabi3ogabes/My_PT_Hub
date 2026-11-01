<?php

namespace App\Domain\Payments\Services;

use App\Domain\Payments\Gateways\GatewayManager;
use App\Domain\Payments\Gateways\WebhookResult;
use App\Domain\Payments\Models\PaymentGateway;
use App\Domain\Payments\Models\WebhookEvent;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

/**
 * Verifies a gateway webhook and applies it exactly once: webhook_events has a
 * unique (driver, external_id), so a replayed or duplicated delivery is a no-op.
 */
final class WebhookProcessor
{
    public function __construct(
        private readonly GatewayManager $gateways,
        private readonly PaymentService $payments,
    ) {}

    /** @return array{status: string, event_id: ?string} */
    public function handle(Request $request, PaymentGateway $gateway): array
    {
        $result = $this->gateways->for($gateway)->parseWebhook($request, $gateway);

        return $this->process($result, $gateway);
    }

    /** @return array{status: string, event_id: ?string} */
    public function process(WebhookResult $result, PaymentGateway $gateway): array
    {
        try {
            $event = WebhookEvent::query()->firstOrCreate(
                ['driver' => $gateway->driver, 'external_id' => mb_substr($result->externalId, 0, 250)],
                ['type' => $result->type, 'tenant_id' => $gateway->tenant_id, 'payload' => $result->payload ?: ['type' => $result->type]],
            );
        } catch (UniqueConstraintViolationException) {
            $event = WebhookEvent::query()->where('driver', $gateway->driver)->where('external_id', mb_substr($result->externalId, 0, 250))->firstOrFail();
        }

        if (in_array($event->status, ['processed', 'ignored'], true)) {
            return ['status' => 'duplicate', 'event_id' => $event->id];
        }
        if ($result->type === WebhookResult::IGNORED) {
            $event->update(['status' => 'ignored', 'processed_at' => now()]);

            return ['status' => 'ignored', 'event_id' => $event->id];
        }

        try {
            $this->payments->apply($result, $gateway);
            $event->update(['status' => 'processed', 'processed_at' => now(), 'error' => null]);
        } catch (\Throwable $e) {
            $event->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 2000)]);
            throw $e;
        }

        return ['status' => 'processed', 'event_id' => $event->id];
    }
}
