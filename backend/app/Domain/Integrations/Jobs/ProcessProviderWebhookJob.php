<?php

namespace App\Domain\Integrations\Jobs;

use App\Domain\Integrations\Data\WebhookNotification;
use App\Domain\Integrations\Exceptions\ProviderAuthException;
use App\Domain\Integrations\Services\IntegrationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/** Applies one verified provider notification (inline samples or a fetch) for its user. */
class ProcessProviderWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 120, 600];

    /** @param array<string, mixed> $notification */
    public function __construct(public readonly string $provider, public readonly array $notification) {}

    public function handle(IntegrationManager $manager): void
    {
        try {
            $manager->processNotification($this->provider, WebhookNotification::fromArray($this->notification));
        } catch (ProviderAuthException) {
            // The integration was flagged for reconnect; retrying cannot succeed.
        } catch (Throwable $e) {
            if ($this->attempts() >= $this->tries) {
                return;
            }

            throw $e;
        }
    }
}
