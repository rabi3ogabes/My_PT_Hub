<?php

namespace App\Domain\Payments\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Payments\Gateways\GatewayManager;
use App\Domain\Payments\Models\ClientSubscription;
use App\Domain\Payments\Models\Coupon;
use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\OrderItem;
use App\Domain\Payments\Models\PaymentGateway;
use App\Domain\Payments\Models\Price;
use App\Domain\Payments\Models\Product;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Builds orders (coupon, tax by seller country, inclusive/exclusive math in minor
 * units) and opens the gateway checkout. Free orders are settled immediately.
 */
final class CheckoutService
{
    public function __construct(
        private readonly GatewayManager $gateways,
        private readonly TaxCalculator $tax,
        private readonly TenantContext $context,
    ) {}

    /** Amount due at checkout: trials only charge the setup fee. */
    public function firstChargeMinor(Price $price): int
    {
        $recurring = in_array($price->type, ['recurring', 'installments'], true);

        return ($recurring && $price->trial_days > 0 ? 0 : (int) $price->amount_minor) + (int) $price->setup_fee_minor;
    }

    public function findCoupon(Tenant $tenant, string $code, Product $product, string $currency): Coupon
    {
        $coupon = Coupon::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('code', strtoupper(trim($code)))->first();
        $invalid = fn (string $reason) => ApiException::invalid('coupon_invalid', __('payments.coupon.'.$reason), ['coupon' => [__('payments.coupon.'.$reason)]]);

        if (! $coupon || ! $coupon->is_active) {
            throw $invalid('not_found');
        }
        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            throw $invalid('expired');
        }
        if ($coupon->max_redemptions !== null && $coupon->redemptions >= $coupon->max_redemptions) {
            throw $invalid('exhausted');
        }
        if (! empty($coupon->product_ids) && ! in_array($product->id, $coupon->product_ids, true)) {
            throw $invalid('not_applicable');
        }
        if ($coupon->type === 'fixed' && $coupon->currency && strtoupper($coupon->currency) !== strtoupper($currency)) {
            throw $invalid('currency_mismatch');
        }

        return $coupon;
    }

    public function discountMinor(?Coupon $coupon, int $amountMinor): int
    {
        if (! $coupon || $amountMinor <= 0) {
            return 0;
        }

        return $coupon->type === 'percent'
            ? (int) round($amountMinor * min(100, (int) $coupon->value) / 100)
            : min($amountMinor, (int) $coupon->value);
    }

    /**
     * Price breakdown shown on the checkout page.
     *
     * @return array<string, mixed>
     */
    public function quote(Tenant $tenant, Product $product, Price $price, ?string $couponCode = null): array
    {
        $coupon = $couponCode ? $this->findCoupon($tenant, $couponCode, $product, $price->currency) : null;
        $amount = $this->firstChargeMinor($price);

        return TaxCalculator::compute($amount, $this->discountMinor($coupon, $amount), $this->tax->rateFor($tenant), (bool) $price->tax_inclusive)
            + ['currency' => $price->currency, 'coupon' => $coupon?->code, 'tax_country' => $tenant->country];
    }

    /**
     * @param  array{client?: ?User, email?: ?string, name?: ?string, phone?: ?string, locale?: ?string}  $customer
     * @param  array<string, mixed>  $meta
     */
    public function createOrder(Tenant $tenant, Product $product, Price $price, array $customer, ?string $couponCode = null, ?string $idempotencyKey = null, ?PaymentGateway $gateway = null, array $meta = []): Order
    {
        $this->assertSellable($tenant, $product, $price);
        $key = $idempotencyKey ? hash('sha256', $tenant->id.'|'.($customer['client']?->id ?? $customer['email'] ?? '').'|'.$idempotencyKey) : null;
        if ($key && ($existing = Order::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('idempotency_key', $key)->first())) {
            return $existing;
        }

        return $this->context->run($tenant, function () use ($tenant, $product, $price, $customer, $couponCode, $key, $gateway, $meta) {
            $coupon = $couponCode ? $this->findCoupon($tenant, $couponCode, $product, $price->currency) : null;
            $amount = $this->firstChargeMinor($price);
            $calc = TaxCalculator::compute($amount, $this->discountMinor($coupon, $amount), $this->tax->rateFor($tenant), (bool) $price->tax_inclusive);
            $gateway ??= $calc['total_minor'] > 0 ? $this->gateways->resolve($tenant, $price->currency) : null;
            $client = $customer['client'] ?? null;

            try {
                return DB::transaction(function () use ($tenant, $product, $price, $customer, $client, $coupon, $calc, $key, $gateway, $meta, $amount) {
                    $order = Order::create([
                        'tenant_id' => $tenant->id,
                        'client_id' => $client?->id,
                        'customer_email' => $client?->email ?? (isset($customer['email']) ? strtolower(trim($customer['email'])) : null),
                        'customer_name' => $client?->name ?? ($customer['name'] ?? null),
                        'status' => 'pending',
                        'currency' => strtoupper($price->currency),
                        'subtotal_minor' => $calc['subtotal_minor'],
                        'discount_minor' => $calc['discount_minor'],
                        'tax_minor' => $calc['tax_minor'],
                        'total_minor' => $calc['total_minor'],
                        'tax_rate' => $calc['tax_rate'],
                        'tax_country' => $tenant->country,
                        'coupon_id' => $coupon?->id,
                        'payment_gateway_id' => $gateway?->id,
                        'idempotency_key' => $key,
                        'meta' => array_filter($meta + [
                            'tax_inclusive' => $calc['tax_inclusive'],
                            'customer_phone' => $customer['phone'] ?? null,
                            'locale' => $customer['locale'] ?? app()->getLocale(),
                        ], fn ($v) => $v !== null),
                    ]);
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'price_id' => $price->id,
                        'description' => $this->lineDescription($product, $price, $tenant),
                        'quantity' => 1,
                        'unit_amount_minor' => $amount,
                        'total_minor' => $amount,
                    ]);

                    return $order->load('items');
                });
            } catch (UniqueConstraintViolationException) {
                return Order::withoutGlobalScopes()->where('idempotency_key', $key)->firstOrFail();
            }
        });
    }

    /**
     * Creates the order and opens the hosted checkout.
     *
     * @param  array{client?: ?User, email?: ?string, name?: ?string, phone?: ?string, locale?: ?string}  $customer
     * @return array{order: Order, checkout_url: ?string}
     */
    public function checkout(Tenant $tenant, Product $product, Price $price, array $customer, ?string $couponCode = null, ?string $idempotencyKey = null, ?string $successUrl = null, ?string $cancelUrl = null): array
    {
        $order = $this->createOrder($tenant, $product, $price, $customer, $couponCode, $idempotencyKey, meta: ['channel' => $customer['client'] ?? null ? 'app' : 'link']);
        $order = $this->open($order, $successUrl, $cancelUrl);

        return ['order' => $order, 'checkout_url' => $order->checkout_url];
    }

    /** Opens the gateway checkout for a pending order (no-op when already opened or settled). */
    public function open(Order $order, ?string $successUrl = null, ?string $cancelUrl = null): Order
    {
        if ($order->status !== 'pending' || $order->checkout_url || $order->gateway_reference) {
            return $order;
        }
        $tenant = Tenant::findOrFail($order->tenant_id);

        return $this->context->run($tenant, function () use ($order, $successUrl, $cancelUrl) {
            if ($order->total_minor <= 0) {
                app(PaymentService::class)->markPaid($order, ['driver' => 'manual', 'method' => 'free', 'amount_minor' => 0]);

                return $order->refresh();
            }
            $gatewayModel = $order->gateway ?? throw new ApiException('no_gateway', __('payments.errors.no_gateway', ['currency' => $order->currency]), 422);
            $result = $this->gateways->for($gatewayModel)->createCheckout(
                $order,
                $successUrl ?: url('pay/return/'.$order->id),
                $cancelUrl ?: url('pay/return/'.$order->id).'?cancelled=1',
            );
            $order->forceFill(['checkout_url' => $result->url, 'gateway_reference' => $result->reference ?: null])->save();

            return $order;
        });
    }

    /** Next-period order for a subscription renewed by Aurum (gateways without native subscriptions). */
    public function renewalOrder(ClientSubscription $subscription): Order
    {
        $tenant = Tenant::findOrFail($subscription->tenant_id);

        return $this->context->run($tenant, function () use ($subscription, $tenant) {
            $subscription->loadMissing('price', 'product', 'client');
            $price = $subscription->price;
            $calc = TaxCalculator::compute((int) $subscription->amount_minor, 0, $this->tax->rateFor($tenant), (bool) ($price?->tax_inclusive ?? true));
            $period = $subscription->current_period_end?->toDateString() ?? now()->toDateString();

            return DB::transaction(function () use ($subscription, $tenant, $calc, $period) {
                $existing = Order::query()->where('client_subscription_id', $subscription->id)->where('status', 'pending')->where('meta->renewal_period', $period)->first();
                if ($existing) {
                    return $existing;
                }
                $order = Order::create([
                    'client_id' => $subscription->client_id,
                    'customer_email' => $subscription->client?->email,
                    'customer_name' => $subscription->client?->name,
                    'status' => 'pending',
                    'currency' => $subscription->currency,
                    'subtotal_minor' => $calc['subtotal_minor'],
                    'discount_minor' => 0,
                    'tax_minor' => $calc['tax_minor'],
                    'total_minor' => $calc['total_minor'],
                    'tax_rate' => $calc['tax_rate'],
                    'tax_country' => $tenant->country,
                    'payment_gateway_id' => $subscription->payment_gateway_id,
                    'client_subscription_id' => $subscription->id,
                    'meta' => ['renewal' => true, 'renewal_period' => $period, 'tax_inclusive' => $calc['tax_inclusive']],
                ]);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $subscription->product_id,
                    'price_id' => $subscription->price_id,
                    'description' => $this->lineDescription($subscription->product, $subscription->price, $tenant).' — '.__('payments.renewal', [], $tenant->locale),
                    'quantity' => 1,
                    'unit_amount_minor' => (int) $subscription->amount_minor,
                    'total_minor' => (int) $subscription->amount_minor,
                ]);

                return $order->load('items');
            });
        });
    }

    private function assertSellable(Tenant $tenant, Product $product, Price $price): void
    {
        if ($product->tenant_id !== $tenant->id || $price->tenant_id !== $tenant->id || $price->product_id !== $product->id) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }
        if (! $product->is_active || $product->trashed() || ! $price->is_active) {
            throw ApiException::invalid('product_unavailable', __('payments.errors.product_unavailable'));
        }
    }

    private function lineDescription(?Product $product, ?Price $price, Tenant $tenant): string
    {
        $name = (string) ($product?->translate('name', $tenant->locale) ?? $product?->translate('name') ?? '');
        if ($price && $price->type === 'recurring') {
            $name .= ' — '.trans_choice('payments.interval.'.($price->interval ?: 'month'), max(1, (int) $price->interval_count), ['count' => max(1, (int) $price->interval_count)], $tenant->locale);
        } elseif ($price && $price->type === 'installments') {
            $name .= ' — '.__('payments.installments_label', ['count' => (int) $price->installments], $tenant->locale);
        }

        return mb_substr($name, 0, 250);
    }
}
