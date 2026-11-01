<?php

namespace App\Domain\Nutrition\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Nutrition\Support\SearchText;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Models\HasTranslations;
use App\Domain\Shared\Tenancy\SharedWithTenant;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Food extends BaseModel
{
    use HasTranslations, SharedWithTenant, SoftDeletes;

    protected $table = 'foods';

    /** @var list<string> */
    protected array $translatable = ['name'];

    protected static function booted(): void
    {
        static::saving(function (Food $food): void {
            $food->search_text = SearchText::build($food->name ?? [], $food->aliases ?? [], (string) $food->brand);
        });
    }

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

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'food_favorites', 'food_id', 'user_id');
    }

    /**
     * Servings with a gram weight, normalised to [{unit, grams, label}].
     *
     * @return list<array{unit: string, grams: float, label: mixed}>
     */
    public function servingList(): array
    {
        $out = [];
        foreach ($this->servings ?? [] as $serving) {
            if (! empty($serving['unit']) && isset($serving['grams']) && (float) $serving['grams'] > 0) {
                $out[] = ['unit' => (string) $serving['unit'], 'grams' => (float) $serving['grams'], 'label' => $serving['label'] ?? null];
            }
        }

        return $out;
    }
}
