<?php

namespace App\Domain\Payments\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientSubscription extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'client_subscriptions';

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'cancel_at_period_end' => 'boolean',
            'cancelled_at' => 'datetime',
            'paused_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class);
    }

    /** Monthly-normalised recurring revenue in minor units. */
    public function monthlyAmountMinor(): int
    {
        $count = max(1, (int) $this->interval_count);

        return (int) round(match ($this->interval) {
            'week' => $this->amount_minor * 52 / 12 / $count,
            'year' => $this->amount_minor / 12 / $count,
            default => $this->amount_minor / $count,
        });
    }
}
