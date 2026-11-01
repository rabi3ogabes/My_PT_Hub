<?php

namespace App\Domain\Nutrition\Models;

use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MealPlanDay extends BaseModel
{
    protected $table = 'meal_plan_days';

    public $timestamps = false;

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MealPlan::class, 'meal_plan_id');
    }

    public function meals(): HasMany
    {
        return $this->hasMany(Meal::class)->orderBy('position');
    }
}
