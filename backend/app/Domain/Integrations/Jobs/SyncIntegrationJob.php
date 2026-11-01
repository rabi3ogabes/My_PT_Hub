<?php

namespace App\Domain\Integrations\Jobs;

use App\Domain\Integrations\Models\Integration;
use App\Domain\Integrations\Services\IntegrationManager;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/** Pulls (or backfills) one health integration. Failures are tracked with backoff, not queue retries. */
class SyncIntegrationJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 300;

    public int $uniqueFor = 600;

    public function __construct(
        public readonly string $integrationId,
        public readonly bool $backfill = false,
        public readonly ?string $since = null,
    ) {}

    public function uniqueId(): string
    {
        return $this->integrationId.($this->backfill ? ':backfill' : '');
    }

    public function handle(IntegrationManager $manager): void
    {
        $integration = Integration::query()->find($this->integrationId);
        if (! $integration || $integration->status === 'revoked' || ! $integration->access_token) {
            return;
        }

        try {
            $manager->syncHealth($integration, $this->since ? CarbonImmutable::parse($this->since) : null, $this->backfill);
        } catch (Throwable) {
            // Recorded on the integration (status/backoff/reconnect notification) by the manager.
        }
    }
}
