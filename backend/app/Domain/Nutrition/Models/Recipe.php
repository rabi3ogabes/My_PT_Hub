<?php

namespace App\Domain\Nutrition\Models;

use App\Domain\Nutrition\Support\SearchText;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Models\HasTranslations;
use App\Domain\Shared\Tenancy\SharedWithTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends BaseModel
{
    use SharedWithTenant, SoftDeletes, HasTranslations;

    protected $table = 'recipes';

    /** @var list<string> */
    protected array $translatable = ['name', 'description', 'steps'];

    protected static function booted(): void
    {
        static::saving(function (Recipe $recipe): void {
            $recipe->search_text = SearchText::build($recipe->name ?? [], $recipe->tags ?? [], (string) $recipe->cuisine);
        });
    }

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'steps' => 'array',
            'tags' => 'array',
            'is_published' => 'boolean',
            'kcal' => 'float',
            'protein_g' => 'float',
            'carbs_g' => 'float',
            'fat_g' => 'float',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(RecipeItem::class)->orderBy('position');
    }
}
