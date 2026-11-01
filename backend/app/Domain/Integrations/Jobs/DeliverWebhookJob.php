<?php

namespace App\Domain\Integrations\Jobs;

use App\Domain\Integrations\Models\WebhookDelivery;
use App\Domain\Integrations\Services\OutgoingWebhooks;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/** Sends one webhook delivery; failures are rescheduled with exponential backoff. */
class DeliverWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public readonly string $deliveryId) {}

    public function handle(): void
    {
        $delivery = WebhookDelivery::query()->with(['endpoint' => fn ($q) => $q->withoutGlobalScopes()])->find($this->deliveryId);
        if (! $delivery || $delivery->status !== 'pending' || ! $delivery->endpoint) {
            return;
        }
        $endpoint = $delivery->endpoint;
        if (! $endpoint->is_active) {
            $delivery->update(['status' => 'failed', 'error' => 'endpoint_disabled', 'next_retry_at' => null]);

            return;
        }

        $body = (string) json_encode($delivery->payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $timestamp = time();
        $started = microtime(true);
        $status = null;
        $responseBody = null;
        $error = null;

        try {
            $response = Http::withHeaders([
                OutgoingWebhooks::SIGNATURE_HEADER => OutgoingWebhooks::sign((string) $endpoint->secret, $body, $timestamp),
                'X-Aurum-Event' => $delivery->event,
                'X-Aurum-Delivery' => $delivery->id,
                'User-Agent' => config('brand.name', 'Aurum').'-Webhooks/1.0',
            ])->withBody($body, 'application/json')->timeout((int) config('integrations.webhooks.timeout_seconds', 10))
                ->post($endpoint->url);
            $status = $response->status();
            $responseBody = Str::limit($response->body(), 2000, '');
        } catch (Throwable $e) {
            $error = Str::limit($e->getMessage(), 250, '');
        }

        $attempts = $delivery->attempts + 1;
        $duration = (int) round((microtime(true) - $started) * 1000);

        if ($status !== null && $status >= 200 && $status < 300) {
            $delivery->update([
                'status' => 'delivered', 'attempts' => $attempts, 'response_code' => $status, 'response_body' => $responseBody,
                'error' => null, 'duration_ms' => $duration, 'delivered_at' => now(), 'next_retry_at' => null,
            ]);

            return;
        }

        $gone = $status === 410;
        $final = $gone || $attempts >= (int) config('integrations.webhooks.max_attempts', 8);
        $delay = OutgoingWebhooks::backoffSeconds($attempts);
        $delivery->update([
            'status' => $final ? 'failed' : 'pending',
            'attempts' => $attempts,
            'response_code' => $status,
            'response_body' => $responseBody,
            'error' => $error ?? ($status !== null ? 'http_'.$status : 'unknown'),
            'duration_ms' => $duration,
            'next_retry_at' => $final ? null : now()->addSeconds($delay),
        ]);

        if ($gone) {
            $endpoint->forceFill(['is_active' => false, 'disabled_at' => now()])->save();
        }
        if (! $final) {
            static::dispatch($delivery->id)->onQueue($this->queue ?? (string) config('integrations.webhooks.queue', 'default'))->delay(now()->addSeconds($delay));
        }
    }
}
