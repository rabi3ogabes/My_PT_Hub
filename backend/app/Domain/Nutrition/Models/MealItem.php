<?php

namespace App\Domain\Nutrition\Models;

use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealItem extends BaseModel
{
    protected $table = 'meal_items';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
        ];
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
