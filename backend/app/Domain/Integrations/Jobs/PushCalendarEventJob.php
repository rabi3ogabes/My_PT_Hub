<?php

namespace App\Domain\Integrations\Jobs;

use App\Domain\Calendar\Models\Event;
use App\Domain\Integrations\Services\CalendarSyncService;
use App\Domain\Integrations\Services\MeetingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/** Ensures a meeting link (when Zoom is connected) and mirrors the event to external calendars. */
class PushCalendarEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [60, 300, 900];

    public function __construct(public readonly string $eventId) {}

    public function handle(CalendarSyncService $calendars, MeetingService $meetings): void
    {
        $event = Event::withoutGlobalScopes()->withTrashed()->find($this->eventId);
        if (! $event) {
            return;
        }
        if ($meetings->wantsMeeting($event)) {
            $meetings->ensureMeeting($event);
        }
        if ($calendars->coachHasCalendar($event->coach_id)) {
            $calendars->pushEvent($event);
        }
    }
}
