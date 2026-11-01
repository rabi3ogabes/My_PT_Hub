<?php

namespace App\Domain\Payments\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'payments';

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(ClientSubscription::class, 'client_subscription_id');
    }

    public function refundedMinor(): int
    {
        return (int) $this->refunds()->where('status', 'succeeded')->sum('amount_minor');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }
}
