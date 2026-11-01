<?php

namespace App\Domain\Nutrition\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Models\HasTranslations;
use App\Domain\Shared\Tenancy\SharedWithTenant;
use Illuminate\Database\Eloquent\SoftDeletes;

class Food extends BaseModel
{
    use SharedWithTenant, SoftDeletes, HasTranslations;

    protected $table = 'foods';

    /** @var list<string> */
    protected array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'aliases' => 'array',
            'micros' => 'array',
            'servings' => 'array',
            'diet_tags' => 'array',
            'allergens' => 'array',
            'is_verified' => 'boolean',
            'kcal' => 'float',
            'protein_g' => 'float',
            'carbs_g' => 'float',
            'fat_g' => 'float',
            'fiber_g' => 'float',
            'sugar_g' => 'float',
            'sodium_mg' => 'float',
        ];
    }
}
