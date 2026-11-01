<?php

namespace App\Domain\Calendar\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditLedgerEntry extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'credit_ledger';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'expires_on' => 'date',
            'amount' => 'integer',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public static function booted(): void
    {
        static::creating(fn (self $e) => $e->created_at ??= now());
        static::updating(fn () => throw new \LogicException('Credit ledger is append-only.'));
        static::deleting(fn () => throw new \LogicException('Credit ledger is append-only.'));
    }

    public static function balanceFor(string $clientId): int
    {
        return (int) static::query()->where('client_id', $clientId)->sum('amount');
    }
}
