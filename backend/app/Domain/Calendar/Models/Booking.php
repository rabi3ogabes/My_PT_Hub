<?php

namespace App\Domain\Calendar\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'bookings';

    protected function casts(): array
    {
        return [
            'waitlist_offered_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'reminded_24h_at' => 'datetime',
            'reminded_2h_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
