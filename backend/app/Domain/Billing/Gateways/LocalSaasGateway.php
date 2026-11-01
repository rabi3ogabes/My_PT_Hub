<?php

namespace App\Domain\Billing\Gateways;

use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Billing\Models\SaasSubscription;
use App\Domain\Tenancy\Models\Tenant;

/** Development/self-hosted driver: no external processor, changes apply immediately. */
final class LocalSaasGateway implements SaasGateway
{
    public function name(): string
    {
        return 'local';
    }

    public function createCheckout(Tenant $tenant, SaasPlan $plan, string $interval, string $successUrl, string $cancelUrl): ?string
    {
        return null;
    }

    public function cancel(SaasSubscription $subscription, bool $atPeriodEnd): void {}

    public function resume(SaasSubscription $subscription): void {}

    public function refund(SaasSubscription $subscription, int $amountMinor): ?string
    {
        return 'local_refund_'.$subscription->id;
    }

    public function removePaymentMethods(SaasSubscription $subscription): void {}

    public function portalUrl(SaasSubscription $subscription, string $returnUrl): ?string
    {
        return null;
    }

    public function verifyWebhook(string $payload, ?string $signature): array
    {
        throw new \RuntimeException('Local gateway has no webhooks.');
    }
}
