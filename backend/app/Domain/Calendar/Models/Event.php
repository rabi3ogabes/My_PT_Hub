<?php

namespace App\Domain\Calendar\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends BaseModel
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'events';

    /** True for an in-memory copy representing one occurrence of a recurring series (never persisted). */
    public bool $isOccurrence = false;

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'exdates' => 'array',
            'recurrence_id' => 'datetime',
            'capacity' => 'integer',
            'credits_cost' => 'integer',
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'series_id');
    }
}
