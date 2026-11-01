<?php

namespace App\Http\Api\V1\Resources\Payments;

use App\Domain\Payments\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Coupon */
class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'value' => (int) $this->value,
            'currency' => $this->currency,
            'duration' => $this->duration,
            'duration_months' => $this->duration_months,
            'max_redemptions' => $this->max_redemptions,
            'redemptions' => (int) $this->redemptions,
            'product_ids' => $this->product_ids ?? [],
            'referrer_id' => $this->referrer_id,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
