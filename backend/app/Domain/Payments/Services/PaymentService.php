<?php

namespace App\Domain\Payments\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Payments\Events\OrderPaid;
use App\Domain\Payments\Events\OrderRefunded;
use App\Domain\Payments\Gateways\GatewayManager;
use App\Domain\Payments\Gateways\WebhookResult;
use App\Domain\Payments\Models\ClientSubscription;
use App\Domain\Payments\Models\Coupon;
use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\Payment;
use App\Domain\Payments\Models\PaymentGateway;
use App\Domain\Payments\Models\Refund;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Settles orders from gateway events, manual records and free checkouts, and
 * issues refunds. Every entry point is idempotent: a paid order is never paid twice.
 */
final class PaymentService
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly GatewayManager $gateways,
        private readonly InvoiceService $invoices,
        private readonly FulfilmentService $fulfilment,
        private readonly SubscriptionService $subscriptions,
    ) {}

    /**
     * @param  array{driver: string, method?: ?string, amount_minor?: ?int, gateway_payment_id?: ?string, reference?: ?string, receipt_path?: ?string, recorded_by?: ?string, paid_at?: mixed, customer_ref?: ?string, payment_method_ref?: ?string, subscription_ref?: ?string}  $data
     */
    public function markPaid(Order $order, array $data): Payment
    {
        $tenant = Tenant::findOrFail($order->tenant_id);

        $result = $this->context->run($tenant, fn () => DB::transaction(function () use ($order, $data) {
            /** @var Order $locked */
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->isPaid() || $locked->status === 'refunded') {
                return [$locked, $locked->payments()->where('status', 'succeeded')->latest()->first(), false];
            }

            $paidAt = isset($data['paid_at']) ? Carbon::parse($data['paid_at']) : now();
            $payment = Payment::create([
                'order_id' => $locked->id,
                'client_subscription_id' => $locked->client_subscription_id,
                'driver' => $data['driver'],
                'method' => $data['method'] ?? null,
                'amount_minor' => $data['amount_minor'] ?? $locked->total_minor,
                'currency' => $locked->currency,
                'status' => 'succeeded',
                'gateway_payment_id' => $data['gateway_payment_id'] ?? null,
                'receipt_path' => $data['receipt_path'] ?? null,
                'recorded_by' => $data['recorded_by'] ?? null,
                'paid_at' => $paidAt,
            ]);
            $locked->forceFill([
                'status' => 'paid',
                'paid_at' => $paidAt,
                'gateway_reference' => $locked->gateway_reference ?? ($data['reference'] ?? null),
            ])->save();

            if ($locked->coupon_id) {
                Coupon::query()->whereKey($locked->coupon_id)->increment('redemptions');
            }

            $this->fulfilment->ensureClient($locked);

            if ($locked->client_subscription_id) {
                $this->subscriptions->advancePeriod(ClientSubscription::query()->findOrFail($locked->client_subscription_id), $payment);
            } else {
                $this->subscriptions->startFromOrder($locked, $data);
            }
            $this->invoices->issueForOrder($locked->refresh());
            ActivityLogger::log('payments.order_paid', $locked, ['driver' => $data['driver'], 'amount_minor' => $payment->amount_minor], tenantId: $locked->tenant_id);

            return [$locked, $payment, true];
        }));

        [$paidOrder, $payment, $fresh] = $result;
        if ($fresh) {
            event(new OrderPaid($paidOrder->refresh(), $payment));
        }

        return $payment;
    }

    public function markFailed(Order $order, ?string $reason = null, ?string $driver = null): void
    {
        $tenant = Tenant::findOrFail($order->tenant_id);
        $this->context->run($tenant, fn () => DB::transaction(function () use ($order, $reason, $driver) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending') {
                return;
            }
            Payment::create([
                'order_id' => $locked->id,
                'client_subscription_id' => $locked->client_subscription_id,
                'driver' => $driver ?? $locked->gateway?->driver ?? 'manual',
                'amount_minor' => $locked->total_minor,
                'currency' => $locked->currency,
                'status' => 'failed',
                'failure_reason' => $reason ? mb_substr($reason, 0, 250) : null,
            ]);
            // Initial checkouts stay pending so the client can retry with another card;
            // renewal orders are handed to dunning.
            if ($locked->client_subscription_id) {
                $locked->forceFill(['status' => 'failed'])->save();
                $this->subscriptions->renewalFailed(ClientSubscription::query()->findOrFail($locked->client_subscription_id), $reason);
            }
        }));
    }

    /** Applies a verified, normalised gateway event. */
    public function apply(WebhookResult $result, PaymentGateway $gateway): void
    {
        $tenant = Tenant::findOrFail($gateway->tenant_id);
        $this->context->run($tenant, function () use ($result, $gateway) {
            match ($result->type) {
                WebhookResult::SUCCEEDED => $this->onSucceeded($result, $gateway),
                WebhookResult::FAILED => ($order = $this->findOrder($result, $gateway)) ? $this->markFailed($order, $result->failureReason, $gateway->driver) : null,
                WebhookResult::RENEWED => $this->onRenewed($result, $gateway),
                WebhookResult::RENEWAL_FAILED => ($sub = $this->findSubscription($result, $gateway)) ? $this->subscriptions->renewalFailed($sub, $result->failureReason) : null,
                WebhookResult::CANCELLED => ($sub = $this->findSubscription($result, $gateway)) ? $this->subscriptions->markEnded($sub, 'cancelled') : null,
                WebhookResult::REFUNDED => $this->onExternalRefund($result, $gateway),
                default => null,
            };
        });
    }

    private function onSucceeded(WebhookResult $result, PaymentGateway $gateway): void
    {
        $order = $this->findOrder($result, $gateway);
        if (! $order) {
            throw new ApiException('order_not_found', 'Order not found for gateway event.', 404);
        }
        if ($result->currency && strtoupper($result->currency) !== $order->currency) {
            $this->markFailed($order, 'currency_mismatch', $gateway->driver);

            return;
        }
        if ($result->amountMinor !== null && $result->amountMinor < $order->total_minor) {
            $this->markFailed($order, 'amount_mismatch', $gateway->driver);

            return;
        }
        $this->markPaid($order, [
            'driver' => $gateway->driver,
            'method' => $result->method,
            'amount_minor' => $result->amountMinor ?? $order->total_minor,
            'gateway_payment_id' => $result->paymentId,
            'reference' => $result->reference,
            'customer_ref' => $result->customerReference,
            'payment_method_ref' => $result->paymentMethodReference,
            'subscription_ref' => $result->subscriptionReference,
        ]);
    }

    private function onRenewed(WebhookResult $result, PaymentGateway $gateway): void
    {
        $sub = $this->findSubscription($result, $gateway);
        if (! $sub) {
            return;
        }
        if ($result->paymentId && Payment::query()->where('gateway_payment_id', $result->paymentId)->where('status', 'succeeded')->exists()) {
            return;
        }
        $order = app(CheckoutService::class)->renewalOrder($sub);
        $this->markPaid($order, [
            'driver' => $gateway->driver,
            'method' => $result->method,
            'amount_minor' => $result->amountMinor ?? $order->total_minor,
            'gateway_payment_id' => $result->paymentId,
        ]);
    }

    private function onExternalRefund(WebhookResult $result, PaymentGateway $gateway): void
    {
        if (! $result->paymentId) {
            return;
        }
        $payment = Payment::query()->where('gateway_payment_id', $result->paymentId)->where('status', 'succeeded')->first();
        if (! $payment || ! $payment->order) {
            return;
        }
        $already = $payment->refundedMinor();
        // Stripe reports the cumulative refunded amount; other drivers report the refund amount itself.
        $delta = $gateway->driver === 'stripe' ? (int) $result->amountMinor - $already : (int) $result->amountMinor;
        $delta = min($delta, $payment->amount_minor - $already);
        if ($delta <= 0) {
            return;
        }
        $this->recordRefund($payment->order, $payment, $delta, null, 'gateway', null, 'succeeded');
    }

    private function findOrder(WebhookResult $result, PaymentGateway $gateway): ?Order
    {
        $query = Order::query()->where('tenant_id', $gateway->tenant_id);
        if ($result->orderId && ($order = (clone $query)->whereKey($result->orderId)->first())) {
            return $order;
        }

        return $result->reference ? (clone $query)->where('gateway_reference', $result->reference)->first() : null;
    }

    private function findSubscription(WebhookResult $result, PaymentGateway $gateway): ?ClientSubscription
    {
        return $result->subscriptionReference
            ? ClientSubscription::query()->where('tenant_id', $gateway->tenant_id)->where('gateway_subscription_id', $result->subscriptionReference)->first()
            : null;
    }

    /**
     * Refunds a paid order through its gateway and issues a credit note.
     */
    public function refund(Order $order, ?int $amountMinor, ?string $reason, ?User $by, bool $revokeAccess = false): Refund
    {
        if (! $order->isPaid()) {
            throw ApiException::invalid('not_refundable', __('payments.errors.not_refundable'));
        }
        $payment = $order->payments()->where('status', 'succeeded')->latest('paid_at')->first()
            ?? throw ApiException::invalid('not_refundable', __('payments.errors.not_refundable'));
        $refundable = $payment->amount_minor - $payment->refundedMinor();
        $amount = $amountMinor ?? $refundable;
        if ($amount <= 0 || $amount > $refundable) {
            throw ApiException::invalid('invalid_amount', __('payments.errors.refund_amount', ['max' => $refundable]), ['amount_minor' => [__('payments.errors.refund_amount', ['max' => $refundable])]]);
        }

        $gatewayModel = $order->gateway ?? $this->gateways->manual(Tenant::findOrFail($order->tenant_id));
        $driver = $payment->driver === 'manual' ? $this->gateways->for($this->gateways->manual(Tenant::findOrFail($order->tenant_id))) : $this->gateways->for($gatewayModel);
        $result = $driver->refund($payment, $amount);
        if ($result->status === 'failed') {
            throw new ApiException('refund_failed', __('payments.errors.refund_failed'), 502);
        }

        $refund = $this->recordRefund($order, $payment, $amount, $reason, 'staff', $result->refundId, $result->status, $by);
        if ($revokeAccess) {
            $this->fulfilment->revoke($order->refresh());
        }

        return $refund;
    }

    private function recordRefund(Order $order, Payment $payment, int $amount, ?string $reason, string $source, ?string $gatewayRefundId, string $status, ?User $by = null): Refund
    {
        $refund = DB::transaction(function () use ($order, $payment, $amount, $reason, $gatewayRefundId, $status) {
            $refund = Refund::create([
                'tenant_id' => $order->tenant_id,
                'payment_id' => $payment->id,
                'amount_minor' => $amount,
                'reason' => $reason,
                'status' => $status === 'pending' ? 'pending' : 'succeeded',
                'gateway_refund_id' => $gatewayRefundId,
            ]);
            $refundedTotal = (int) Refund::query()->whereIn('payment_id', $order->payments()->pluck('id'))->whereIn('status', ['succeeded', 'pending'])->sum('amount_minor');
            $paidTotal = (int) $order->payments()->where('status', 'succeeded')->sum('amount_minor');
            $order->forceFill(['status' => $refundedTotal >= $paidTotal ? 'refunded' : 'partially_refunded'])->save();
            $this->invoices->creditNote($order, $refund);

            return $refund;
        });
        ActivityLogger::log('payments.refunded', $order, ['amount_minor' => $amount, 'source' => $source], causerId: $by?->id, tenantId: $order->tenant_id);
        event(new OrderRefunded($order, $refund));

        return $refund;
    }
}
