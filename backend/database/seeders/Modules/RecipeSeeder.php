<?php

namespace Database\Seeders\Modules;

use App\Domain\Nutrition\Models\Food;
use App\Domain\Nutrition\Models\Recipe;
use App\Domain\Nutrition\Models\RecipeItem;
use App\Domain\Nutrition\Services\MacroCalculator;
use App\Domain\Shared\Tenancy\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Global recipes (tenant_id = null). Runs after FoodSeeder (alphabetical order);
 * per-serving macros are computed from the seeded foods. Idempotent on slug.
 */
class RecipeSeeder extends Seeder
{
    public function run(MacroCalculator $macros): void
    {
        app(TenantContext::class)->run(null, function () use ($macros) {
            if (! Food::withoutGlobalScopes()->whereNull('tenant_id')->exists()) {
                $this->call(FoodSeeder::class);
            }
            /** @var list<array<string, mixed>> $recipes */
            $recipes = require database_path('seeders/data/recipes.php');
            $foodIds = Food::withoutGlobalScopes()->whereNull('tenant_id')->get(['id', 'name'])
                ->mapWithKeys(fn (Food $f) => [$f->name['en'] ?? '' => $f->id]);

            DB::transaction(function () use ($recipes, $foodIds, $macros) {
                foreach ($recipes as $data) {
                    $recipe = Recipe::withoutGlobalScopes()->withTrashed()->whereNull('tenant_id')->where('slug', $data['slug'])->first()
                        ?? new Recipe(['tenant_id' => null]);
                    $recipe->fill([
                        'slug' => $data['slug'],
                        'name' => $data['name'],
                        'description' => $data['description'],
                        'steps' => $data['steps'],
                        'cuisine' => $data['cuisine'],
                        'prep_minutes' => $data['prep_minutes'],
                        'cook_minutes' => $data['cook_minutes'],
                        'servings' => $data['servings'],
                        'tags' => $data['tags'],
                        'is_published' => true,
                        'deleted_at' => null,
                    ]);
                    $recipe->tenant_id = null;
                    $recipe->save();

                    RecipeItem::query()->where('recipe_id', $recipe->id)->delete();
                    foreach ($data['items'] as $position => $item) {
                        $foodId = $foodIds[$item['food']] ?? throw new RuntimeException("Unknown food [{$item['food']}] in recipe {$data['slug']}");
                        RecipeItem::create([
                            'recipe_id' => $recipe->id,
                            'food_id' => $foodId,
                            'grams' => $item['grams'],
                            'display_qty' => $item['display_qty'],
                            'display_unit' => $item['display_unit'],
                            'position' => $position,
                        ]);
                    }

                    $macros->refreshRecipe($recipe);
                }
            });
        });
    }
}
