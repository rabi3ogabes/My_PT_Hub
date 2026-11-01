<?php

namespace App\Domain\Payments\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionRule extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'commission_rules';

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }
}
