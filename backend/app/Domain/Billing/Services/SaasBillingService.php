<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\Gateways\SaasGateway;
use App\Domain\Billing\Models\SaasInvoice;
use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Billing\Models\SaasSubscription;
use App\Domain\Payments\Models\WebhookEvent;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Tenant;
use App\Notifications\Billing\SubscriptionCancelled;
use App\Notifications\Billing\PaymentFailed;
use App\Notifications\Billing\RenewalReminder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Coach → platform subscription lifecycle with the "billing trust" rules:
 * trial without card, reminders 7d + 1d before trial end / renewal,
 * two-click cancel (end of period on monthly, pro-rata refund on annual),
 * "remove my card", idempotent webhooks, dunning.
 */
final class SaasBillingService
{
    public function __construct(private readonly SaasGateway $gateway) {}

    public function plan(string $code): SaasPlan
    {
        return SaasPlan::query()->where('code', $code)->firstOrFail();
    }

    public function startTrial(Tenant $tenant, string $planCode = 'pro'): SaasSubscription
    {
        $plan = SaasPlan::query()->where('code', $planCode)->first();
        if (! $plan) {
            // Plans not seeded (e.g. fresh install): stay on free silently.
            return new SaasSubscription;
        }
        $endsAt = now()->addDays($plan->trial_days ?: 14);

        $sub = SaasSubscription::create([
            'tenant_id' => $tenant->id,
            'saas_plan_id' => $plan->id,
            'interval' => 'month',
            'status' => 'trialing',
            'price_minor' => $plan->monthly_price_minor,
            'currency' => $plan->currency,
            'trial_ends_at' => $endsAt,
            'current_period_start' => now(),
            'current_period_end' => $endsAt,
            'gateway' => $this->gateway->name(),
        ]);
        $tenant->forceFill(['plan' => $plan->code, 'trial_ends_at' => $endsAt])->save();
        $this->flushPlanCache($tenant);

        return $sub;
    }

    /**
     * Returns ['checkout_url' => string|null, 'subscription' => SaasSubscription].
     *
     * @return array{checkout_url: ?string, subscription: ?SaasSubscription}
     */
    public function subscribe(Tenant $tenant, string $planCode, string $interval, string $successUrl, string $cancelUrl): array
    {
        $plan = $this->plan($planCode);
        if (! in_array($interval, ['month', 'year'], true)) {
            throw ApiException::invalid('invalid_interval', 'Interval must be month or year.');
        }
        if ($plan->code === 'free') {
            $current = $tenant->subscription;
            if ($current && $current->isActive()) {
                $this->cancel($tenant, 'downgrade_to_free');
            }

            return ['checkout_url' => null, 'subscription' => null];
        }
        if ($plan->code === 'enterprise') {
            throw ApiException::invalid('contact_sales', __('billing.contact_sales'));
        }

        $url = $this->gateway->createCheckout($tenant, $plan, $interval, $successUrl, $cancelUrl);
        if ($url !== null) {
            return ['checkout_url' => $url, 'subscription' => $tenant->subscription];
        }

        // Local driver: activate immediately.
        return ['checkout_url' => null, 'subscription' => $this->activate($tenant, $plan, $interval)];
    }

    public function activate(Tenant $tenant, SaasPlan $plan, string $interval, array $gatewayData = []): SaasSubscription
    {
        return DB::transaction(function () use ($tenant, $plan, $interval, $gatewayData) {
            $tenant->subscriptions()->whereIn('status', ['trialing', 'active', 'past_due'])->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            $periodEnd = $interval === 'year' ? now()->addYear() : now()->addMonth();
            $price = $interval === 'year' ? $plan->annual_price_minor : $plan->monthly_price_minor;
            $sub = SaasSubscription::create(array_merge([
                'tenant_id' => $tenant->id,
                'saas_plan_id' => $plan->id,
                'interval' => $interval,
                'status' => 'active',
                'price_minor' => $price,
                'currency' => $plan->currency,
                'current_period_start' => now(),
                'current_period_end' => $periodEnd,
                'gateway' => $this->gateway->name(),
            ], $gatewayData));
            $tenant->forceFill(['plan' => $plan->code, 'trial_ends_at' => null])->save();
            $this->recordInvoice($tenant, $sub, $price, 'paid', $plan->translate('name').' — '.($interval === 'year' ? 'annual' : 'monthly'));
            $this->flushPlanCache($tenant);
            ActivityLogger::log('billing.subscribed', $sub, ['plan' => $plan->code, 'interval' => $interval], tenantId: $tenant->id);

            return $sub;
        });
    }

    /**
     * Two-click cancel. Monthly → access until period end. Annual → immediate
     * cancel with pro-rata refund of the unused days. Trial → ends now.
     *
     * @return array{ends_at: ?Carbon, refund_minor: int}
     */
    public function cancel(Tenant $tenant, ?string $reason = null): array
    {
        $sub = $tenant->subscription;
        if (! $sub || ! $sub->isActive()) {
            throw ApiException::invalid('no_active_subscription', __('billing.no_active_subscription'));
        }

        $refund = 0;
        if ($sub->status === 'trialing') {
            $sub->update(['status' => 'cancelled', 'cancelled_at' => now(), 'cancel_reason' => $reason, 'current_period_end' => now()]);
            $this->downgrade($tenant);
            $endsAt = now();
        } elseif ($sub->interval === 'year') {
            $refund = $this->proRataRefund($sub);
            $this->gateway->cancel($sub, atPeriodEnd: false);
            if ($refund > 0) {
                $this->gateway->refund($sub, $refund);
                $this->recordInvoice($tenant, $sub, -$refund, 'refunded', __('billing.prorata_refund'));
            }
            $sub->update(['status' => 'cancelled', 'cancelled_at' => now(), 'cancel_reason' => $reason, 'current_period_end' => now()]);
            $this->downgrade($tenant);
            $endsAt = now();
        } else {
            $this->gateway->cancel($sub, atPeriodEnd: true);
            $sub->update(['cancel_at_period_end' => true, 'cancelled_at' => now(), 'cancel_reason' => $reason]);
            $endsAt = $sub->current_period_end;
        }

        ActivityLogger::log('billing.cancelled', $sub, ['reason' => $reason, 'refund_minor' => $refund], tenantId: $tenant->id);
        $this->owners($tenant)->each->notify(new SubscriptionCancelled($tenant, $sub->fresh(), $refund));

        return ['ends_at' => $endsAt, 'refund_minor' => $refund];
    }

    public function resume(Tenant $tenant): SaasSubscription
    {
        $sub = $tenant->subscription;
        if (! $sub || ! $sub->cancel_at_period_end || $sub->current_period_end?->isPast()) {
            throw ApiException::invalid('cannot_resume', __('billing.cannot_resume'));
        }
        $this->gateway->resume($sub);
        $sub->update(['cancel_at_period_end' => false, 'cancelled_at' => null, 'cancel_reason' => null]);

        return $sub;
    }

    public function proRataRefund(SaasSubscription $sub): int
    {
        if (! $sub->current_period_start || ! $sub->current_period_end || $sub->current_period_end->isPast()) {
            return 0;
        }
        $total = max(1, $sub->current_period_start->diffInDays($sub->current_period_end));
        $remaining = max(0, (int) floor(now()->diffInDays($sub->current_period_end)));

        return (int) floor($sub->price_minor * $remaining / $total);
    }

    public function removeCard(Tenant $tenant): void
    {
        $sub = $tenant->subscription;
        if ($sub) {
            $this->gateway->removePaymentMethods($sub);
            $sub->update(['card_brand' => null, 'card_last4' => null]);
            ActivityLogger::log('billing.card_removed', $sub, tenantId: $tenant->id);
        }
    }

    public function portalUrl(Tenant $tenant, string $returnUrl): ?string
    {
        $sub = $tenant->subscription;

        return $sub ? $this->gateway->portalUrl($sub, $returnUrl) : null;
    }

    /** Scheduled: close subscriptions whose paid period ended after a cancel, and expired trials. */
    public function expireEnded(): int
    {
        $count = 0;
        SaasSubscription::query()
            ->where(fn ($q) => $q->where(fn ($q) => $q->where('cancel_at_period_end', true)->whereIn('status', ['active', 'past_due']))
                ->orWhere('status', 'trialing'))
            ->where('current_period_end', '<=', now())
            ->with('tenant')
            ->each(function (SaasSubscription $sub) use (&$count) {
                $sub->update(['status' => $sub->status === 'trialing' ? 'expired' : 'cancelled']);
                if ($sub->tenant && $sub->tenant->subscription?->is($sub)) {
                    $this->downgrade($sub->tenant);
                }
                $count++;
            });

        return $count;
    }

    /** Scheduled daily: reminders 7 days and 1 day before trial end or renewal. */
    public function sendReminders(): int
    {
        $sent = 0;
        foreach ([7 => 'reminder_7d_sent_at', 1 => 'reminder_1d_sent_at'] as $days => $column) {
            SaasSubscription::query()
                ->whereIn('status', ['trialing', 'active'])
                ->where('cancel_at_period_end', false)
                ->whereNull($column)
                ->whereBetween('current_period_end', [now()->addDays($days)->subHours(12), now()->addDays($days)->addHours(12)])
                ->with(['tenant', 'plan'])
                ->each(function (SaasSubscription $sub) use ($days, $column, &$sent) {
                    if (! $sub->tenant) {
                        return;
                    }
                    $this->owners($sub->tenant)->each->notify(new RenewalReminder($sub, $days));
                    $sub->update([$column => now()]);
                    $sent++;
                });
        }

        return $sent;
    }

    /**
     * Idempotent Stripe webhook processing.
     *
     * @param  array<string, mixed>  $event
     */
    public function handleWebhook(array $event): void
    {
        $id = (string) ($event['id'] ?? '');
        $record = WebhookEvent::query()->firstOrCreate(
            ['driver' => 'stripe_saas', 'external_id' => $id],
            ['type' => $event['type'] ?? null, 'payload' => $event],
        );
        if ($record->status === 'processed') {
            return;
        }

        try {
            $obj = $event['data']['object'] ?? [];
            match ($event['type'] ?? '') {
                'checkout.session.completed' => $this->onCheckoutCompleted($obj),
                'invoice.paid' => $this->onInvoicePaid($obj),
                'invoice.payment_failed' => $this->onPaymentFailed($obj),
                'customer.subscription.deleted' => $this->onSubscriptionDeleted($obj),
                'customer.subscription.updated' => $this->onSubscriptionUpdated($obj),
                default => null,
            };
            $record->update(['status' => 'processed', 'processed_at' => now()]);
        } catch (\Throwable $e) {
            $record->update(['status' => 'failed', 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function onCheckoutCompleted(array $obj): void
    {
        $tenant = Tenant::find($obj['metadata']['tenant_id'] ?? $obj['client_reference_id'] ?? null);
        $plan = SaasPlan::query()->where('code', $obj['metadata']['plan'] ?? '')->first();
        if (! $tenant || ! $plan) {
            return;
        }
        $this->activate($tenant, $plan, $obj['metadata']['interval'] ?? 'month', [
            'gateway_customer_id' => $obj['customer'] ?? null,
            'gateway_subscription_id' => $obj['subscription'] ?? null,
        ]);
    }

    private function bySubscriptionId(?string $id): ?SaasSubscription
    {
        return $id ? SaasSubscription::query()->where('gateway_subscription_id', $id)->latest()->first() : null;
    }

    private function onInvoicePaid(array $obj): void
    {
        $sub = $this->bySubscriptionId($obj['subscription'] ?? null);
        if (! $sub || ($obj['billing_reason'] ?? '') === 'subscription_create') {
            return;
        }
        $periodEnd = isset($obj['lines']['data'][0]['period']['end']) ? Carbon::createFromTimestamp($obj['lines']['data'][0]['period']['end']) : ($sub->interval === 'year' ? now()->addYear() : now()->addMonth());
        $sub->update(['status' => 'active', 'failed_attempts' => 0, 'current_period_start' => now(), 'current_period_end' => $periodEnd, 'reminder_7d_sent_at' => null, 'reminder_1d_sent_at' => null]);
        $this->recordInvoice($sub->tenant, $sub, (int) ($obj['amount_paid'] ?? $sub->price_minor), 'paid', 'Renewal', $obj['id'] ?? null);
    }

    private function onPaymentFailed(array $obj): void
    {
        $sub = $this->bySubscriptionId($obj['subscription'] ?? null);
        if (! $sub) {
            return;
        }
        $sub->update(['status' => 'past_due', 'failed_attempts' => $sub->failed_attempts + 1]);
        $this->owners($sub->tenant)->each->notify(new PaymentFailed($sub));
    }

    private function onSubscriptionDeleted(array $obj): void
    {
        $sub = $this->bySubscriptionId($obj['id'] ?? null);
        if ($sub && $sub->status !== 'cancelled') {
            $sub->update(['status' => 'cancelled', 'cancelled_at' => $sub->cancelled_at ?? now()]);
            $this->downgrade($sub->tenant);
        }
    }

    private function onSubscriptionUpdated(array $obj): void
    {
        $sub = $this->bySubscriptionId($obj['id'] ?? null);
        if ($sub) {
            $sub->update(['cancel_at_period_end' => (bool) ($obj['cancel_at_period_end'] ?? false)]);
        }
    }

    private function downgrade(Tenant $tenant): void
    {
        $tenant->forceFill(['plan' => 'free', 'trial_ends_at' => null])->save();
        $this->flushPlanCache($tenant);
    }

    private function recordInvoice(Tenant $tenant, SaasSubscription $sub, int $amount, string $status, string $description, ?string $gatewayId = null): SaasInvoice
    {
        $seq = SaasInvoice::query()->lockForUpdate()->count() + 1;

        return SaasInvoice::create([
            'tenant_id' => $tenant->id,
            'saas_subscription_id' => $sub->id,
            'number' => sprintf('AUR-%s-%06d', now()->format('Y'), $seq),
            'amount_minor' => $amount,
            'currency' => $sub->currency,
            'status' => $status,
            'description' => $description,
            'gateway_invoice_id' => $gatewayId,
            'paid_at' => $status === 'paid' ? now() : null,
        ]);
    }

    /** @return \Illuminate\Support\Collection<int, \App\Domain\Identity\Models\User> */
    private function owners(Tenant $tenant)
    {
        return $tenant->users()->wherePivot('role', 'owner')->get();
    }

    private function flushPlanCache(Tenant $tenant): void
    {
        Cache::forget("plan:{$tenant->plan}");
    }
}
