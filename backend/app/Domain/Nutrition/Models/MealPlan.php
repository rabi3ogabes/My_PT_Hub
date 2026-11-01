<?php

namespace App\Domain\Nutrition\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealPlan extends BaseModel
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'meal_plans';

    protected function casts(): array
    {
        return [
            'diet_tags' => 'array',
            'fasting' => 'array',
            'is_template' => 'boolean',
            'start_date' => 'date',
        ];
    }

    public function days(): HasMany
    {
        return $this->hasMany(MealPlanDay::class)->orderBy('day_number');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
