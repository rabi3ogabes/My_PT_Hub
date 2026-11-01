<?php

namespace App\Http\Api\V1\Resources\Payments;

use App\Domain\Payments\Models\ClientSubscription;
use App\Domain\Payments\Support\MoneyJson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ClientSubscription */
class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->relationLoaded('product') ? $this->product?->translate('name') : null,
            'client' => $this->relationLoaded('client') && $this->client ? ['id' => $this->client->id, 'name' => $this->client->name] : null,
            'status' => $this->status,
            'amount' => MoneyJson::make((int) $this->amount_minor, $this->currency),
            'monthly_amount' => MoneyJson::make($this->monthlyAmountMinor(), $this->currency),
            'interval' => $this->interval,
            'interval_count' => (int) $this->interval_count,
            'trial_ends_at' => $this->trial_ends_at?->toIso8601String(),
            'current_period_start' => $this->current_period_start?->toIso8601String(),
            'current_period_end' => $this->current_period_end?->toIso8601String(),
            'cancel_at_period_end' => (bool) $this->cancel_at_period_end,
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'paused_at' => $this->paused_at?->toIso8601String(),
            'pause_requested_at' => $this->pause_requested_at?->toIso8601String(),
            'failed_attempts' => (int) $this->failed_attempts,
            'next_retry_at' => $this->next_retry_at?->toIso8601String(),
            'installments_total' => $this->installments_total,
            'installments_paid' => (int) $this->installments_paid,
            'ended_at' => $this->ended_at?->toIso8601String(),
            'gateway' => $this->relationLoaded('gateway') ? $this->gateway?->driver : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
