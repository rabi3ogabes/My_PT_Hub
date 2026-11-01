<?php

namespace App\Domain\Nutrition\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Nutrition\Models\Food;
use App\Domain\Nutrition\Support\SearchText;
use App\Http\Api\V1\Support\TranslatableInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/** Food library queries: visibility/moderation, multilingual search, barcode lookup, favourites. */
final class FoodService
{
    public const REGIONS = ['gulf', 'levant', 'maghreb', 'french', 'international', 'restaurant'];

    public const CATEGORIES = ['produce', 'meat', 'seafood', 'dairy', 'bakery', 'grains', 'pantry', 'drinks', 'frozen', 'snacks', 'prepared', 'other'];

    public const DIET_TAGS = ['halal', 'vegetarian', 'vegan', 'pescatarian', 'keto', 'dairy_free', 'gluten_free', 'high_protein', 'low_carb', 'organic'];

    public function __construct(private readonly OpenFoodFactsClient $off) {}

    /**
     * Foods visible to the user: approved global + tenant foods, plus their own pending submissions.
     * Staff may filter by moderation state (e.g. the pending queue).
     *
     * @return Builder<Food>
     */
    public function visibleQuery(User $user, ?string $moderation = null): Builder
    {
        $query = Food::query();
        if ($moderation !== null) {
            return $query->whereNotNull('tenant_id')->where('moderation', $moderation);
        }

        return $query->where(fn (Builder $q) => $q->where('moderation', 'approved')
            ->orWhere(fn (Builder $q) => $q->where('moderation', 'pending')->where('submitted_by', $user->id)));
    }

    /** @param Builder<Food> $query */
    public function applySearch(Builder $query, ?string $term): void
    {
        $normalized = SearchText::normalize($term);
        if ($normalized === '') {
            return;
        }
        foreach (array_slice(explode(' ', $normalized), 0, 6) as $word) {
            $query->where('search_text', 'like', SearchText::likeTerm($word));
        }
    }

    public function assertVisible(Food $food, User $user): void
    {
        if ($food->moderation !== 'approved' && $food->submitted_by !== $user->id && ! $user->isStaffIn()) {
            throw (new ModelNotFoundException)->setModel(Food::class, [$food->id]);
        }
    }

    /** Local barcode first (tenant food preferred over global), then Open Food Facts → saved globally as source=off. */
    public function byBarcode(string $barcode, User $user): ?Food
    {
        $local = $this->visibleQuery($user)->where('barcode', $barcode)->orderByRaw('tenant_id is null')->first();
        if ($local) {
            return $local;
        }

        $attributes = $this->off->lookup($barcode);
        if ($attributes === null) {
            return null;
        }

        return DB::transaction(function () use ($barcode, $attributes) {
            $existing = Food::withoutGlobalScopes()->whereNull('tenant_id')->where('barcode', $barcode)->whereNull('deleted_at')->first();

            return $existing ?? Food::create($attributes + ['tenant_id' => null, 'moderation' => 'approved', 'is_verified' => false]);
        });
    }

    /**
     * Maps validated API input to model attributes.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function attributes(array $data, ?Food $food = null): array
    {
        $out = [];
        if (array_key_exists('name', $data)) {
            $out['name'] = TranslatableInput::value($data['name'], $food?->name);
        }
        foreach (['aliases', 'brand', 'barcode', 'region', 'category', 'servings', 'diet_tags', 'allergens'] as $key) {
            if (array_key_exists($key, $data)) {
                $out[$key] = $data[$key];
            }
        }
        if (isset($data['per_100g']) && is_array($data['per_100g'])) {
            foreach (['kcal', 'protein_g', 'carbs_g', 'fat_g', 'fiber_g', 'sugar_g', 'sodium_mg'] as $key) {
                if (array_key_exists($key, $data['per_100g']) && $data['per_100g'][$key] !== null) {
                    $out[$key] = (float) $data['per_100g'][$key];
                }
            }
        }

        return $out;
    }

    /**
     * @param  list<string>  $foodIds
     * @return list<string>
     */
    public function favoriteIds(User $user, array $foodIds): array
    {
        if ($foodIds === []) {
            return [];
        }

        return DB::table('food_favorites')->where('user_id', $user->id)->whereIn('food_id', $foodIds)->pluck('food_id')->all();
    }

    /** Toggles a favourite; returns the new state. */
    public function toggleFavorite(User $user, Food $food): bool
    {
        $exists = DB::table('food_favorites')->where('user_id', $user->id)->where('food_id', $food->id)->exists();
        if ($exists) {
            DB::table('food_favorites')->where('user_id', $user->id)->where('food_id', $food->id)->delete();

            return false;
        }
        DB::table('food_favorites')->insert(['user_id' => $user->id, 'food_id' => $food->id]);

        return true;
    }
}
