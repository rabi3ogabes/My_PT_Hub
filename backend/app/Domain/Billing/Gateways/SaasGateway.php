<?php

namespace App\Domain\Billing\Gateways;

use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Billing\Models\SaasSubscription;
use App\Domain\Tenancy\Models\Tenant;

/** Platform billing provider (the coach paying Aurum). */
interface SaasGateway
{
    public function name(): string;

    /** Returns a hosted checkout URL, or null when activation is immediate (local driver). */
    public function createCheckout(Tenant $tenant, SaasPlan $plan, string $interval, string $successUrl, string $cancelUrl): ?string;

    public function cancel(SaasSubscription $subscription, bool $atPeriodEnd): void;

    public function resume(SaasSubscription $subscription): void;

    public function refund(SaasSubscription $subscription, int $amountMinor): ?string;

    public function removePaymentMethods(SaasSubscription $subscription): void;

    public function portalUrl(SaasSubscription $subscription, string $returnUrl): ?string;

    /**
     * Verifies the signature and returns the decoded event.
     *
     * @return array<string, mixed>
     */
    public function verifyWebhook(string $payload, ?string $signature): array;
}
