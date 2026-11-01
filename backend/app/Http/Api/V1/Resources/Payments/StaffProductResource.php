<?php

namespace App\Http\Api\V1\Resources\Payments;

use Illuminate\Http\Request;

/** Product with editable fields for the coach dashboard. @mixin \App\Domain\Payments\Models\Product */
class StaffProductResource extends ProductResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'prices' => PriceResource::collection($this->relationLoaded('prices') ? $this->prices : collect())->resolve($request),
            'name_translations' => $this->name,
            'description_translations' => $this->description,
            'fulfilment' => $this->fulfilment ?? (object) [],
            'grace_days' => (int) $this->grace_days,
            'sell_in_app_ios' => $this->sell_in_app_ios,
            'is_active' => (bool) $this->is_active,
            'checkout_url' => url('checkout/'.($this->tenant?->slug ?? '').'/'.$this->slug),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ]);
    }
}
