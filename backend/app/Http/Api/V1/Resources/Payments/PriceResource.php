<?php

namespace App\Http\Api\V1\Resources\Payments;

use App\Domain\Payments\Models\Price;
use App\Domain\Payments\Support\MoneyJson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Price */
class PriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => MoneyJson::make((int) $this->amount_minor, $this->currency),
            'interval' => $this->interval,
            'interval_count' => (int) $this->interval_count,
            'installments' => $this->installments,
            'trial_days' => (int) $this->trial_days,
            'setup_fee' => MoneyJson::make((int) $this->setup_fee_minor, $this->currency),
            'tax_inclusive' => (bool) $this->tax_inclusive,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
