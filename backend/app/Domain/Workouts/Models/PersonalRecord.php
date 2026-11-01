<?php

namespace App\Domain\Workouts\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalRecord extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'personal_records';

    protected function casts(): array
    {
        return [
            'value' => 'float',
            'previous_value' => 'float',
            'achieved_at' => 'datetime',
        ];
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
