<?php

namespace App\Domain\Nutrition\Models;

use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meal extends BaseModel
{
    protected $table = 'meals';

    public $timestamps = false;

    public function day(): BelongsTo
    {
        return $this->belongsTo(MealPlanDay::class, 'meal_plan_day_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MealItem::class)->orderBy('position');
    }
}
