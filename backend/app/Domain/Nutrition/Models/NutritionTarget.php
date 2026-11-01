<?php

namespace App\Domain\Nutrition\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionTarget extends BaseModel
{
    use BelongsToTenant;

    protected $table = 'nutrition_targets';

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'calculation' => 'array',
            'override_safety' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
