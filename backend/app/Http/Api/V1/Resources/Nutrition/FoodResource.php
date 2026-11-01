<?php

namespace App\Http\Api\V1\Resources\Nutrition;

use App\Http\Api\V1\Support\ViewerContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Nutrition\Models\Food */
class FoodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->translate('name'),
            'brand' => $this->brand,
            'barcode' => $this->barcode,
            'source' => $this->source,
            'region' => $this->region,
            'category' => $this->category,
            'per_100g' => [
                'kcal' => (float) $this->kcal,
                'protein_g' => (float) $this->protein_g,
                'carbs_g' => (float) $this->carbs_g,
                'fat_g' => (float) $this->fat_g,
                'fiber_g' => (float) $this->fiber_g,
                'sugar_g' => (float) $this->sugar_g,
                'sodium_mg' => (float) $this->sodium_mg,
            ],
            'servings' => array_map(fn (array $s) => [
                'unit' => $s['unit'],
                'grams' => $s['grams'],
                'label' => self::label($s['label'], $s['unit']),
            ], $this->servingList()),
            'diet_tags' => array_values($this->diet_tags ?? []),
            'allergens' => array_values($this->allergens ?? []),
            'is_verified' => (bool) $this->is_verified,
            'is_custom' => $this->tenant_id !== null,
            'is_favorite' => in_array($this->id, ViewerContext::favoriteFoodIds($request), true),
        ];

        if (ViewerContext::isStaff($request)) {
            $data['name_translations'] = (object) ($this->name ?? []);
            $data['aliases'] = array_values($this->aliases ?? []);
            $data['moderation'] = $this->moderation;
        }

        return $data;
    }

    public static function label(mixed $label, string $unit): string
    {
        if (is_array($label)) {
            $locale = app()->getLocale();
            $value = $label[$locale] ?? $label['en'] ?? (array_values($label)[0] ?? null);
            if (is_string($value) && $value !== '') {
                return $value;
            }
        } elseif (is_string($label) && $label !== '') {
            return $label;
        }
        $key = 'nutrition.units.'.$unit;

        return __($key) !== $key ? __($key) : $unit;
    }
}
