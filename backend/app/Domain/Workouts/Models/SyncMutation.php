<?php

namespace App\Domain\Workouts\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;

/** An offline mutation already processed by /sync/push (idempotency ledger). */
class SyncMutation extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'sync_mutations';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'client_time' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}
