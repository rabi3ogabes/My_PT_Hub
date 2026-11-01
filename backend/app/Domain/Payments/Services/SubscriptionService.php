<?php

namespace App\Domain\Payments\Services;

use App\Domain\Identity\Models\User;
use App\Domain\Payments\Gateways\GatewayManager;
use App\Domain\Payments\Models\ClientSubscription;
use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\Payment;
use App\Domain\Payments\Support\Automations;
use App\Domain\Payments\Support\Notify;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Tenancy\TenantContext;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Client subscription state machine (trialing → active → past_due → paused →
 * cancelled/expired), two-tap cancel at period end, pause/resume, Aurum-driven
 * renewals for gateways without native subscriptions, and dunning.
 */
final class SubscriptionService
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly GatewayManager $gateways,
    ) {}

    public static function addInterval(Carbon $from, string $interval, int $count): Carbon
    {
        $count = max(1, $count);

        return match ($interval) {
            'week' => $from->copy()->addWeeks($count),
            'year' => $from->copy()->addYearsNoOverflow($count),
            default => $from->copy()->addMonthsNoOverflow($count),
        };
    }

    /**
     * Creates the subscription for a just-paid order with a recurring / installment price.
     *
     * @param  array<string, mixed>  $gatewayData
     */
    public function startFromOrder(Order $order, array $gatewayData = []): ?ClientSubscription
    {
        $order->loadMissing('items.price', 'coupon');
        $item = $order->items->first();
        $price = $item?->price;
        if (! $price || ! in_array($price->type, ['recurring', 'installments'], true) || ! $order->client_id) {
            return null;
        }
        $existing = ClientSubscription::query()->where('order_id', $order->id)->first();
        if ($existing) {
            return $existing;
        }

        $trial = $price->type === 'recurring' && $price->trial_days > 0;
        $interval = $price->interval ?: 'month';
        $count = max(1, (int) $price->interval_count);
        $start = now();
        $end = $trial ? $start->copy()->addDays($price->trial_days) : self::addInterval($start, $interval, $count);
        $amount = (int) $price->amount_minor;
        if ($order->coupon && $order->coupon->duration === 'forever') {
            $amount -= app(CheckoutService::class)->discountMinor($order->coupon, $amount);
        }
        $installments = $price->type === 'installments' ? max(1, (int) $price->installments) : null;

        $sub = ClientSubscription::create([
            'tenant_id' => $order->tenant_id,
            'client_id' => $order->client_id,
            'product_id' => $item->product_id,
            'price_id' => $price->id,
            'order_id' => $order->id,
            'status' => $trial ? 'trialing' : 'active',
            'amount_minor' => $amount,
            'currency' => $order->currency,
            'interval' => $interval,
            'interval_count' => $count,
            'trial_ends_at' => $trial ? $end : null,
            'current_period_start' => $start,
            'current_period_end' => $end,
            'payment_gateway_id' => $order->payment_gateway_id,
            'gateway_subscription_id' => $gatewayData['subscription_ref'] ?? null,
            'gateway_customer_id' => $gatewayData['customer_ref'] ?? null,
            'gateway_payment_method' => $gatewayData['payment_method_ref'] ?? null,
            'installments_total' => $installments,
            'installments_paid' => $installments ? 1 : 0,
            'cancel_at_period_end' => $installments === 1,
        ]);
        $order->forceFill(['client_subscription_id' => $sub->id])->save();

        return $sub;
    }

    /** A renewal was paid: move the period forward and clear dunning state. */
    public function advancePeriod(ClientSubscription $sub, Payment $payment): ClientSubscription
    {
        $base = $sub->current_period_end && $sub->current_period_end->isAfter(now()->subDays(7)) ? $sub->current_period_end->copy() : now();
        $paid = $sub->installments_total ? $sub->installments_paid + 1 : $sub->installments_paid;
        $sub->update([
            'status' => 'active',
            'current_period_start' => $base,
            'current_period_end' => self::addInterval($base, $sub->interval, (int) $sub->interval_count),
            'failed_attempts' => 0,
            'next_retry_at' => null,
            'paused_at' => null,
            'installments_paid' => $paid,
            'cancel_at_period_end' => $sub->cancel_at_period_end || ($sub->installments_total && $paid >= $sub->installments_total),
        ]);
        $payment->forceFill(['client_subscription_id' => $sub->id])->save();

        return $sub;
    }

    /** Two-tap cancel: access continues until the end of the paid period. */
    public function cancel(ClientSubscription $sub, bool $atPeriodEnd = true, ?User $by = null): ClientSubscription
    {
        if (in_array($sub->status, ['cancelled', 'expired'], true)) {
            throw ApiException::invalid('subscription_ended', __('payments.errors.subscription_ended'));
        }
        if ($sub->gateway_subscription_id && $sub->gateway) {
            $this->gateways->for($sub->gateway)->cancelSubscription($sub, $atPeriodEnd);
        }
        if ($atPeriodEnd && $sub->status !== 'paused') {
            $sub->update(['cancel_at_period_end' => true, 'cancelled_at' => now(), 'next_retry_at' => null]);
        } else {
            $sub->update(['cancelled_at' => now()]);
            $this->markEnded($sub, 'cancelled');
        }
        ActivityLogger::log('payments.subscription_cancelled', $sub, ['at_period_end' => $atPeriodEnd], causerId: $by?->id, tenantId: $sub->tenant_id);
        $tenant = Tenant::find($sub->tenant_id);
        if ($tenant && $by && $by->id === $sub->client_id) {
            Notify::staff($tenant, 'subscription_cancelled', ['client' => $by->name, 'date' => $sub->current_period_end?->toDateString()]);
        }

        return $sub->refresh();
    }

    public function pause(ClientSubscription $sub, ?User $by = null): ClientSubscription
    {
        if (! in_array($sub->status, ['active', 'trialing', 'past_due'], true)) {
            throw ApiException::invalid('cannot_pause', __('payments.errors.cannot_pause'));
        }
        if ($sub->gateway_subscription_id) {
            $driver = $sub->gateway ? $this->gateways->for($sub->gateway) : null;
            if (! $driver || ! method_exists($driver, 'pauseSubscription')) {
                throw ApiException::invalid('cannot_pause', __('payments.errors.cannot_pause'));
            }
            $driver->pauseSubscription($sub);
        }
        $sub->update(['status' => 'paused', 'paused_at' => now(), 'pause_requested_at' => null, 'next_retry_at' => null]);
        ActivityLogger::log('payments.subscription_paused', $sub, causerId: $by?->id, tenantId: $sub->tenant_id);

        return $sub->refresh();
    }

    /** Client pause: immediate when the coach allows self-service pauses, otherwise a request to the coach. */
    public function requestPause(ClientSubscription $sub, User $client): ClientSubscription
    {
        $tenant = Tenant::findOrFail($sub->tenant_id);
        if ($tenant->setting('payments.client_can_pause', false)) {
            return $this->pause($sub, $client);
        }
        if (! in_array($sub->status, ['active', 'trialing', 'past_due'], true)) {
            throw ApiException::invalid('cannot_pause', __('payments.errors.cannot_pause'));
        }
        $sub->update(['pause_requested_at' => now()]);
        Notify::staff($tenant, 'pause_requested', ['client' => $client->name]);

        return $sub->refresh();
    }

    public function resume(ClientSubscription $sub, ?User $by = null): ClientSubscription
    {
        if ($sub->status === 'paused') {
            if ($sub->gateway_subscription_id && $sub->gateway && method_exists($driver = $this->gateways->for($sub->gateway), 'resumeSubscription')) {
                $driver->resumeSubscription($sub);
            }
            $sub->update([
                'status' => 'active',
                'paused_at' => null,
                'failed_attempts' => 0,
                'ended_reason' => null,
                'revoked_at' => null,
                // An expired period renews on the next renewal run.
                'current_period_end' => $sub->current_period_end && $sub->current_period_end->isPast() ? now() : $sub->current_period_end,
            ]);
        } elseif ($sub->cancel_at_period_end && $sub->current_period_end?->isFuture() && in_array($sub->status, ['active', 'trialing', 'past_due'], true)) {
            if ($sub->gateway_subscription_id && $sub->gateway && method_exists($driver = $this->gateways->for($sub->gateway), 'resumeSubscription')) {
                $driver->resumeSubscription($sub);
            }
            $sub->update(['cancel_at_period_end' => false, 'cancelled_at' => null]);
        } else {
            throw ApiException::invalid('cannot_resume', __('payments.errors.cannot_resume'));
        }
        ActivityLogger::log('payments.subscription_resumed', $sub, causerId: $by?->id, tenantId: $sub->tenant_id);

        return $sub->refresh();
    }

    public function markEnded(ClientSubscription $sub, string $reason): ClientSubscription
    {
        if (in_array($sub->status, ['cancelled', 'expired'], true)) {
            return $sub;
        }
        $sub->update([
            'status' => $reason === 'completed' ? 'expired' : 'cancelled',
            'ended_at' => now(),
            'ended_reason' => $reason,
            'cancel_at_period_end' => false,
            'cancelled_at' => $sub->cancelled_at ?? ($reason === 'cancelled' ? now() : null),
            'next_retry_at' => null,
        ]);

        return $sub;
    }

    /** Dunning: retry on the configured schedule, then auto-pause. */
    public function renewalFailed(ClientSubscription $sub, ?string $reason = null): ClientSubscription
    {
        $retryDays = array_values((array) config('payments.dunning_retry_days', [1, 3, 5]));
        $attempts = $sub->failed_attempts + 1;
        $tenant = Tenant::findOrFail($sub->tenant_id);
        $sub->loadMissing('client', 'product');

        if ($attempts > count($retryDays)) {
            $sub->update(['status' => 'paused', 'paused_at' => now(), 'failed_attempts' => $attempts, 'next_retry_at' => null, 'ended_reason' => 'payment_failed']);
            Notify::user($sub->client, 'subscription_auto_paused', ['product' => (string) $sub->product?->translate('name')]);
            Notify::staff($tenant, 'subscription_auto_paused_staff', ['client' => (string) $sub->client?->name, 'product' => (string) $sub->product?->translate('name')]);
        } else {
            $sub->update(['status' => 'past_due', 'failed_attempts' => $attempts, 'next_retry_at' => now()->addDays((int) $retryDays[$attempts - 1])]);
            Notify::user($sub->client, 'payment_failed', ['product' => (string) $sub->product?->translate('name')], '/purchases');
        }
        Automations::fire('payment_failed', $tenant, $sub->client, [
            'client_subscription_id' => $sub->id,
            'attempt' => $attempts,
            'reason' => $reason,
            'final' => $attempts > count($retryDays),
        ]);

        return $sub->refresh();
    }

    /**
     * Charges one Aurum-driven renewal: saved token when the gateway supports it,
     * otherwise a payment link is sent to the client.
     */
    public function renew(ClientSubscription $sub): ?Order
    {
        $tenant = Tenant::findOrFail($sub->tenant_id);

        return $this->context->run($tenant, function () use ($sub) {
            $sub->loadMissing('gateway', 'client');
            $checkout = app(CheckoutService::class);
            $payments = app(PaymentService::class);
            $order = $checkout->renewalOrder($sub);
            $gateway = $sub->gateway;

            if (! $gateway || ! $gateway->is_active) {
                $payments->markFailed($order, 'gateway_unavailable');

                return $order;
            }
            $driver = $this->gateways->for($gateway);
            if ($driver->supportsTokenCharges() && $sub->gateway_customer_id && $sub->gateway_payment_method) {
                $result = $driver->charge($order, $sub->gateway_customer_id, $sub->gateway_payment_method);
                if ($result->succeeded()) {
                    $payments->markPaid($order, ['driver' => $gateway->driver, 'method' => $result->method, 'gateway_payment_id' => $result->paymentId]);
                } elseif ($result->status === 'failed') {
                    $payments->markFailed($order, $result->failureReason, $gateway->driver);
                }

                return $order->refresh();
            }

            // Payment link renewal (manual, or gateways without saved cards).
            $order = $checkout->open($order);
            if (! $order->isPaid()) {
                Notify::user($sub->client, 'renewal_due', ['amount' => \App\Domain\Shared\Support\Money::format($order->total_minor, $order->currency, $sub->client?->preferredLocale())],
                    $order->checkout_url ?? url('pay/return/'.$order->id));
                $this->renewalFailed($sub, 'awaiting_payment');
            }

            return $order;
        });
    }

    /** Scheduled: renew due Aurum-driven subscriptions and run dunning retries. */
    public function processRenewals(): int
    {
        $count = 0;
        ClientSubscription::withoutGlobalScopes()
            ->whereNull('gateway_subscription_id')
            ->where(fn ($q) => $q
                ->where(fn ($q) => $q->whereIn('status', ['active', 'trialing'])->where('cancel_at_period_end', false)->where('current_period_end', '<=', now()))
                ->orWhere(fn ($q) => $q->where('status', 'past_due')->where('next_retry_at', '<=', now())))
            ->orderBy('current_period_end')
            ->limit(500)
            ->get()
            ->each(function (ClientSubscription $sub) use (&$count) {
                try {
                    $this->renew($sub);
                    $count++;
                } catch (\Throwable $e) {
                    Log::error('payments.renewal_failed', ['subscription' => $sub->id, 'error' => $e->getMessage()]);
                    report($e);
                }
            });

        return $count;
    }

    /** Scheduled: end subscriptions cancelled at period end (and completed installment plans). */
    public function expireEnded(): int
    {
        $count = 0;
        ClientSubscription::withoutGlobalScopes()
            ->where('cancel_at_period_end', true)
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->where('current_period_end', '<=', now())
            ->get()
            ->each(function (ClientSubscription $sub) use (&$count) {
                $completed = $sub->installments_total && $sub->installments_paid >= $sub->installments_total;
                DB::transaction(fn () => $this->markEnded($sub, $completed ? 'completed' : 'cancelled'));
                $count++;
            });

        return $count;
    }
}
