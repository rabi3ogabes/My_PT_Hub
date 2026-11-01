<?php

use App\Domain\Integrations\Jobs\DeliverWebhookJob;
use App\Domain\Integrations\Jobs\SyncIntegrationJob;
use App\Domain\Integrations\Models\Integration;
use App\Domain\Integrations\Models\WebhookDelivery;
use App\Domain\Integrations\Services\CalendarSyncService;
use App\Domain\Integrations\Services\IntegrationManager;
use App\Domain\Integrations\Services\ProviderRegistry;
use App\Domain\Marketing\Jobs\SendReviewRequestsJob;
use Illuminate\Support\Facades\Schedule;

// Scheduled pulls for health providers (complements webhooks). Integrations in
// backoff (next_attempt_at in the future) or flagged for reconnect are skipped.
Schedule::call(function () {
    $interval = (int) config('integrations.sync.pull_interval_minutes', 60);
    Integration::query()
        ->whereIn('provider', app(ProviderRegistry::class)->keysByCategory('health'))
        ->where('status', 'connected')->whereNotNull('access_token')
        ->where(fn ($q) => $q->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now()))
        ->where(fn ($q) => $q->whereNull('last_sync_at')->orWhere('last_sync_at', '<=', now()->subMinutes($interval)))
        ->select('id')->chunkById(500, fn ($rows) => $rows->each(fn (Integration $i) => SyncIntegrationJob::dispatch($i->id)));
})->name('integrations:pull')->everyFifteenMinutes()->withoutOverlapping()->onOneServer();

// Proactive token refresh so webhook processing never waits on an expired token.
Schedule::call(function () {
    $manager = app(IntegrationManager::class);
    Integration::query()->where('status', 'connected')->whereNotNull('refresh_token')
        ->where('token_expires_at', '<=', now()->addMinutes(30))
        ->each(function (Integration $integration) use ($manager) {
            try {
                $manager->withFreshToken($integration);
            } catch (Throwable $e) {
                $manager->recordFailure($integration, $e);
            }
        });
})->name('integrations:refresh-tokens')->everyThirtyMinutes()->withoutOverlapping()->onOneServer();

// Calendar push channels / Graph subscriptions expire; renew a day ahead.
Schedule::call(fn () => app(CalendarSyncService::class)->renewWatches())
    ->name('integrations:renew-calendar-watches')->everySixHours()->withoutOverlapping()->onOneServer();

// Safety net for webhook retries whose queued job was lost.
Schedule::call(function () {
    WebhookDelivery::query()->where('status', 'pending')
        ->where(fn ($q) => $q->where('next_retry_at', '<=', now()->subMinutes(5))
            ->orWhere(fn ($q) => $q->whereNull('next_retry_at')->where('created_at', '<=', now()->subMinutes(15))))
        ->select('id')->chunkById(500, fn ($rows) => $rows->each(fn (WebhookDelivery $d) => DeliverWebhookJob::dispatch($d->id)));
})->name('integrations:webhook-retries')->everyFiveMinutes()->withoutOverlapping()->onOneServer();

Schedule::call(fn () => WebhookDelivery::query()->where('created_at', '<', now()->subDays((int) config('integrations.webhooks.retention_days', 30)))->delete())
    ->name('integrations:prune-webhook-deliveries')->daily()->onOneServer();

// Marketplace: ask clients for a review 30 days after they joined.
Schedule::job(new SendReviewRequestsJob)->name('marketplace:review-requests')->dailyAt('10:17')->withoutOverlapping()->onOneServer();
