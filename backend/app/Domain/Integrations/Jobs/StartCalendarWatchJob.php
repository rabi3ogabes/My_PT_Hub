<?php

namespace App\Domain\Integrations\Jobs;

use App\Domain\Integrations\Models\Integration;
use App\Domain\Integrations\Services\CalendarSyncService;
use App\Domain\Integrations\Services\IntegrationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class StartCalendarWatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public readonly string $integrationId) {}

    public function handle(CalendarSyncService $calendars, IntegrationManager $manager): void
    {
        $integration = Integration::query()->find($this->integrationId);
        if (! $integration || ! $integration->isConnected()) {
            return;
        }
        try {
            $calendars->startWatch($integration);
            $manager->recordSuccess($integration->refresh());
        } catch (Throwable $e) {
            $manager->recordFailure($integration, $e);
        }
    }
}
