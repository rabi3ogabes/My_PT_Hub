<?php

namespace App\Http\Api\V1\Resources\Payments;

use App\Domain\Payments\Gateways\GatewayManager;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Connected gateway — credentials are never returned, only which keys are set. @mixin \App\Domain\Payments\Models\PaymentGateway */
class GatewayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $driver = app(GatewayManager::class)->for($this->resource);

        return [
            'id' => $this->id,
            'driver' => $this->driver,
            'label' => $this->label ?? __('payments.gateways.'.$this->driver),
            'currencies' => $this->currencies ?? [],
            'is_default' => (bool) $this->is_default,
            'is_live' => (bool) $this->is_live,
            'is_active' => (bool) $this->is_active,
            'credentials_set' => array_keys(array_filter((array) ($this->credentials ?? []), fn ($v) => $v !== null && $v !== '' && $v !== [])),
            'supported_currencies' => $driver->supportedCurrencies(),
            'supported_methods' => $driver->supportedMethods(),
            'native_subscriptions' => $driver->supportsNativeSubscriptions(),
            'webhook_url' => $this->driver === 'manual' ? null : url('webhooks/payments/'.$this->driver.'/'.$this->id),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
