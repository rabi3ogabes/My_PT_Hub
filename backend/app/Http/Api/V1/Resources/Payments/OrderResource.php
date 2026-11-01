<?php

namespace App\Http\Api\V1\Resources\Payments;

use App\Domain\Payments\Support\MoneyJson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Payments\Models\Order */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $m = fn (int $minor) => MoneyJson::make($minor, $this->currency);
        $refunded = $this->relationLoaded('payments')
            ? (int) $this->payments->sum(fn ($p) => $p->relationLoaded('refunds') ? $p->refunds->whereIn('status', ['succeeded', 'pending'])->sum('amount_minor') : 0)
            : 0;

        return [
            'id' => $this->id,
            'status' => $this->status,
            'client' => $this->client ? ['id' => $this->client->id, 'name' => $this->client->name, 'email' => $this->client->email] : null,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'currency' => $this->currency,
            'subtotal' => $m((int) $this->subtotal_minor),
            'discount' => $m((int) $this->discount_minor),
            'tax' => $m((int) $this->tax_minor),
            'total' => $m((int) $this->total_minor),
            'refunded' => $m($refunded),
            'tax_rate' => (float) $this->tax_rate,
            'tax_inclusive' => (bool) ($this->meta['tax_inclusive'] ?? true),
            'items' => $this->relationLoaded('items') ? $this->items->map(fn ($i) => [
                'id' => $i->id,
                'product_id' => $i->product_id,
                'price_id' => $i->price_id,
                'description' => $i->description,
                'quantity' => (int) $i->quantity,
                'unit_amount' => $m((int) $i->unit_amount_minor),
                'total' => $m((int) $i->total_minor),
            ])->values()->all() : [],
            'gateway' => $this->relationLoaded('gateway') && $this->gateway ? ['id' => $this->gateway->id, 'driver' => $this->gateway->driver, 'label' => $this->gateway->label] : null,
            'payments' => $this->relationLoaded('payments') ? $this->payments->map(fn ($p) => [
                'id' => $p->id,
                'driver' => $p->driver,
                'method' => $p->method,
                'status' => $p->status,
                'amount' => $m((int) $p->amount_minor),
                'failure_reason' => $p->failure_reason,
                'has_receipt' => $p->receipt_path !== null,
                'paid_at' => $p->paid_at?->toIso8601String(),
            ])->values()->all() : [],
            'invoices' => $this->relationLoaded('invoices') ? $this->invoices->map(fn ($inv) => ['id' => $inv->id, 'number' => $inv->number, 'type' => $inv->type])->values()->all() : [],
            'subscription_id' => $this->client_subscription_id,
            'checkout_url' => $this->status === 'pending' ? $this->checkout_url : null,
            'renewal' => (bool) ($this->meta['renewal'] ?? false),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'fulfilled_at' => $this->fulfilled_at?->toIso8601String(),
            'access_expires_at' => $this->access_expires_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
