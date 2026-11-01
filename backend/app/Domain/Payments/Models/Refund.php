<?php

namespace App\Domain\Payments\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'refunds';

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
