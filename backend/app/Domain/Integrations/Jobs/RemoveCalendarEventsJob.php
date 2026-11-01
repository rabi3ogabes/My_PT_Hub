<?php

namespace App\Domain\Integrations\Jobs;

use App\Domain\Integrations\Services\CalendarSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveCalendarEventsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @param list<array{integration_id: string, external_id: string}> $links */
    public function __construct(public readonly array $links) {}

    public function handle(CalendarSyncService $calendars): void
    {
        $calendars->removeLinks($this->links);
    }
}
