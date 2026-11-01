<?php

namespace App\Domain\Integrations\Services;

use App\Domain\Calendar\Models\Event;
use App\Domain\Integrations\Models\Integration;

/** Auto-creates video meetings (Zoom) for online sessions and live classes. */
final class MeetingService
{
    public const EVENT_TYPES = ['session', 'class', 'live'];

    public function __construct(
        private readonly ProviderRegistry $registry,
        private readonly IntegrationManager $manager,
    ) {}

    public function zoomIntegration(?string $coachId): ?Integration
    {
        if ($coachId === null) {
            return null;
        }

        return Integration::query()->where('user_id', $coachId)->where('provider', 'zoom')->where('status', 'connected')->first();
    }

    public function wantsMeeting(Event $event): bool
    {
        if ($event->meeting_url || $event->location_id || ! in_array($event->type, self::EVENT_TYPES, true) || $event->status === 'cancelled') {
            return false;
        }
        $zoom = $this->zoomIntegration($event->coach_id);

        return $zoom !== null && $zoom->setting('auto_meetings', true) !== false;
    }

    /** Creates the meeting when needed and stores the join URL on the event. Returns the URL. */
    public function ensureMeeting(Event $event): ?string
    {
        if ($event->meeting_url) {
            return $event->meeting_url;
        }
        $zoom = $this->zoomIntegration($event->coach_id);
        if (! $zoom) {
            return null;
        }

        try {
            $meeting = $this->registry->video('zoom')->createMeeting($this->manager->withFreshToken($zoom), $event);
        } catch (\Throwable $e) {
            $this->manager->recordFailure($zoom, $e);

            return null;
        }

        $event->forceFill(['meeting_url' => $meeting['join_url']])->saveQuietly();

        return $meeting['join_url'];
    }
}
