<?php

namespace App\Http\Api\V1\Resources\Payments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Product as shown to clients and on the public checkout. @mixin \App\Domain\Payments\Models\Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'slug' => $this->slug,
            'name' => $this->translate('name'),
            'description' => $this->translate('description'),
            'image_url' => $this->image_url,
            'prices' => PriceResource::collection($this->relationLoaded('prices') ? $this->prices->where('is_active', true)->values() : collect())->resolve($request),
            'ios_purchase_mode' => $this->sell_in_app_ios,
        ];
    }
}
