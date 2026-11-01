<?php

namespace App\Domain\Integrations\Services;

use App\Domain\Integrations\Jobs\DeliverWebhookJob;
use App\Domain\Integrations\Models\WebhookDelivery;
use App\Domain\Integrations\Models\WebhookEndpoint;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Support\Str;

/**
 * Developer platform: fan-out of domain events to a tenant's webhook
 * endpoints. Each delivery is logged and sent by a queued job signed with
 * X-Aurum-Signature: t=<unix>,v1=<hex HMAC-SHA256(secret, "<t>.<body>")>.
 */
final class OutgoingWebhooks
{
    public const SIGNATURE_HEADER = 'X-Aurum-Signature';

    /**
     * Queues one delivery per active endpoint subscribed to the event.
     *
     * @param  array<string, mixed>  $payload
     * @return int number of deliveries queued
     */
    public function dispatch(Tenant $tenant, string $event, array $payload): int
    {
        $endpoints = WebhookEndpoint::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)->where('is_active', true)->get()
            ->filter(fn (WebhookEndpoint $e) => $e->subscribesTo($event));

        foreach ($endpoints as $endpoint) {
            $delivery = new WebhookDelivery(['webhook_endpoint_id' => $endpoint->id, 'event' => $event, 'status' => 'pending', 'attempts' => 0]);
            $delivery->id = (string) Str::ulid();
            $delivery->payload = [
                'id' => $delivery->id,
                'event' => $event,
                'created_at' => now()->toIso8601String(),
                'tenant_id' => $tenant->id,
                'data' => $payload,
            ];
            $delivery->save();

            DeliverWebhookJob::dispatch($delivery->id)->onQueue((string) config('integrations.webhooks.queue', 'default'))->afterCommit();
        }

        return $endpoints->count();
    }

    public static function sign(string $secret, string $body, int $timestamp): string
    {
        return 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$body, $secret);
    }

    /** Receiver-side verification helper (also documented for API consumers). */
    public static function verify(string $secret, string $body, string $header, int $toleranceSeconds = 300): bool
    {
        $parts = [];
        foreach (explode(',', $header) as $pair) {
            [$k, $v] = array_pad(explode('=', trim($pair), 2), 2, '');
            $parts[$k] = $v;
        }
        if (! isset($parts['t'], $parts['v1']) || abs(time() - (int) $parts['t']) > $toleranceSeconds) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $parts['t'].'.'.$body, $secret), $parts['v1']);
    }

    /** Delay before the next attempt: 1, 2, 4, 8 … minutes, capped at 6 hours. */
    public static function backoffSeconds(int $attempts): int
    {
        return (int) min(21600, 60 * (2 ** max(0, $attempts - 1)));
    }

    public static function generateSecret(): string
    {
        return 'whsec_'.Str::random(40);
    }
}
