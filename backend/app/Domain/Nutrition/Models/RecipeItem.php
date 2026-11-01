<?php

namespace App\Domain\Nutrition\Models;

use App\Domain\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeItem extends BaseModel
{
    protected $table = 'recipe_items';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'grams' => 'float',
            'display_qty' => 'float',
        ];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }
}
