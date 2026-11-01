<?php

namespace App\Domain\Integrations\Services;

use App\Domain\Calendar\Models\Event;
use App\Domain\Integrations\Models\CalendarEventLink;
use App\Domain\Integrations\Models\Integration;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Platform → external calendar push (create/update/delete) and watch-channel
 * lifecycle. External → platform direction is handled by ExternalBusyService.
 */
final class CalendarSyncService
{
    public function __construct(
        private readonly ProviderRegistry $registry,
        private readonly IntegrationManager $manager,
        private readonly ExternalBusyService $busy,
    ) {}

    public function coachHasCalendar(?string $coachId): bool
    {
        return $coachId !== null && Integration::query()->where('user_id', $coachId)
            ->whereIn('provider', $this->registry->keysByCategory('calendar'))->where('status', 'connected')->exists();
    }

    /** Creates/updates (or removes, when cancelled) the event in every connected calendar of its coach. */
    public function pushEvent(Event $event): void
    {
        if ($event->trashed() || $event->status === 'cancelled') {
            $this->removeEvent($event);

            return;
        }

        $integrations = Integration::query()->where('user_id', $event->coach_id)
            ->whereIn('provider', $this->registry->keysByCategory('calendar'))->where('status', 'connected')->get();

        foreach ($integrations as $integration) {
            $link = CalendarEventLink::query()->where('event_id', $event->id)->where('integration_id', $integration->id)->first();
            try {
                $fresh = $this->manager->withFreshToken($integration);
                $externalId = $this->registry->calendar($integration->provider)->upsertEvent($fresh, $event, $link?->external_id);
                CalendarEventLink::query()->updateOrCreate(['event_id' => $event->id, 'integration_id' => $integration->id], ['external_id' => $externalId]);
            } catch (Throwable $e) {
                $this->manager->recordFailure($integration, $e);
                Log::warning('integrations.calendar_push_failed', ['event' => $event->id, 'integration' => $integration->id, 'error' => $e->getMessage()]);
            }
        }
    }

    public function removeEvent(Event $event): void
    {
        $links = CalendarEventLink::query()->where('event_id', $event->id)->get();
        $this->removeLinks($links->map(fn (CalendarEventLink $l) => ['integration_id' => $l->integration_id, 'external_id' => $l->external_id])->all());
        CalendarEventLink::query()->where('event_id', $event->id)->delete();
    }

    /** @param list<array{integration_id: string, external_id: string}> $links */
    public function removeLinks(array $links): void
    {
        foreach ($links as $link) {
            $integration = Integration::query()->find($link['integration_id']);
            if (! $integration || ! $integration->isConnected()) {
                continue;
            }
            try {
                $this->registry->calendar($integration->provider)->deleteEvent($this->manager->withFreshToken($integration), $link['external_id']);
            } catch (Throwable $e) {
                Log::warning('integrations.calendar_delete_failed', ['integration' => $integration->id, 'error' => $e->getMessage()]);
            }
        }
    }

    public function startWatch(Integration $integration): void
    {
        $provider = $this->registry->calendar($integration->provider);
        $fresh = $this->manager->withFreshToken($integration);
        $settings = $provider->watch($fresh, route('integrations.webhook', ['provider' => $integration->provider]));
        $fresh->mergeSettings($settings)->save();
    }

    /** Renews push channels/subscriptions that expire within a day. */
    public function renewWatches(): int
    {
        $renewed = 0;
        $threshold = CarbonImmutable::now()->addDay();
        Integration::query()->whereIn('provider', $this->registry->keysByCategory('calendar'))->where('status', 'connected')
            ->each(function (Integration $integration) use ($threshold, &$renewed) {
                $expires = $integration->setting('watch_expires_at');
                if ($expires && CarbonImmutable::parse($expires)->gt($threshold)) {
                    return;
                }
                try {
                    $provider = $this->registry->calendar($integration->provider);
                    $fresh = $this->manager->withFreshToken($integration);
                    try {
                        $provider->stopWatch($fresh);
                    } catch (Throwable) {
                        // The old channel may already be gone; a new one is created below.
                    }
                    $this->startWatch($fresh);
                    $renewed++;
                } catch (Throwable $e) {
                    $this->manager->recordFailure($integration, $e);
                }
            });

        return $renewed;
    }

    /** A push notification said the external calendar changed. */
    public function handleChange(Integration $integration): void
    {
        $this->busy->invalidate($integration);
        $integration->forceFill(['last_sync_at' => now()])->save();
    }
}
