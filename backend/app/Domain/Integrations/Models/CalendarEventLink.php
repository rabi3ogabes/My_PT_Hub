<?php

namespace App\Domain\Integrations\Models;

use App\Domain\Calendar\Models\Event;
use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Maps a platform calendar event to its copy in a coach's external calendar. */
class CalendarEventLink extends BaseModel
{
    protected $table = 'calendar_event_links';

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }
}
