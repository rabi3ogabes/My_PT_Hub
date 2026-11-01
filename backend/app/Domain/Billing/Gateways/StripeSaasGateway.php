<?php

namespace App\Domain\Billing\Gateways;

use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Billing\Models\SaasSubscription;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/** Stripe Billing via the REST API (Checkout, Subscriptions, Refunds, Billing Portal). */
final class StripeSaasGateway implements SaasGateway
{
    private const API = 'https://api.stripe.com/v1';

    public function __construct(private readonly string $secret, private readonly ?string $webhookSecret) {}

    public function name(): string
    {
        return 'stripe';
    }

    private function http(): PendingRequest
    {
        return Http::withToken($this->secret)->asForm()->acceptJson()->timeout(20)->retry(2, 300, throw: false);
    }

    /** @return array<string, mixed> */
    private function call(string $method, string $path, array $data = []): array
    {
        $response = $this->http()->{$method}(self::API.$path, $data);
        if ($response->failed()) {
            throw new ApiException('stripe_error', (string) ($response->json('error.message') ?? 'Stripe request failed'), 502);
        }

        return $response->json() ?? [];
    }

    public function createCheckout(Tenant $tenant, SaasPlan $plan, string $interval, string $successUrl, string $cancelUrl): ?string
    {
        $priceId = $interval === 'year' ? $plan->stripe_annual_price_id : $plan->stripe_monthly_price_id;
        if (! $priceId) {
            throw new ApiException('plan_not_configured', 'This plan is not configured for online payment.', 422);
        }
        $subscription = $tenant->subscription;
        $session = $this->call('post', '/checkout/sessions', array_filter([
            'mode' => 'subscription',
            'customer' => $subscription?->gateway_customer_id,
            'customer_email' => $subscription?->gateway_customer_id ? null : $tenant->users()->wherePivot('role', 'owner')->first()?->email,
            'line_items[0][price]' => $priceId,
            'line_items[0][quantity]' => 1,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => $tenant->id,
            'metadata[tenant_id]' => $tenant->id,
            'metadata[plan]' => $plan->code,
            'metadata[interval]' => $interval,
            'subscription_data[metadata][tenant_id]' => $tenant->id,
            'allow_promotion_codes' => 'true',
            'locale' => in_array($tenant->locale, ['fr', 'es'], true) ? $tenant->locale : 'auto',
        ], fn ($v) => $v !== null));

        return $session['url'] ?? null;
    }

    public function cancel(SaasSubscription $subscription, bool $atPeriodEnd): void
    {
        if (! $subscription->gateway_subscription_id) {
            return;
        }
        $atPeriodEnd
            ? $this->call('post', '/subscriptions/'.$subscription->gateway_subscription_id, ['cancel_at_period_end' => 'true'])
            : $this->call('delete', '/subscriptions/'.$subscription->gateway_subscription_id);
    }

    public function resume(SaasSubscription $subscription): void
    {
        if ($subscription->gateway_subscription_id) {
            $this->call('post', '/subscriptions/'.$subscription->gateway_subscription_id, ['cancel_at_period_end' => 'false']);
        }
    }

    public function refund(SaasSubscription $subscription, int $amountMinor): ?string
    {
        if (! $subscription->gateway_subscription_id || $amountMinor <= 0) {
            return null;
        }
        $sub = $this->call('get', '/subscriptions/'.$subscription->gateway_subscription_id, ['expand[]' => 'latest_invoice']);
        $charge = $sub['latest_invoice']['charge'] ?? null;
        $paymentIntent = $sub['latest_invoice']['payment_intent'] ?? null;
        if (! $charge && ! $paymentIntent) {
            return null;
        }
        $refund = $this->call('post', '/refunds', array_filter(['charge' => $charge, 'payment_intent' => $charge ? null : $paymentIntent, 'amount' => $amountMinor, 'reason' => 'requested_by_customer']));

        return $refund['id'] ?? null;
    }

    public function removePaymentMethods(SaasSubscription $subscription): void
    {
        if (! $subscription->gateway_customer_id) {
            return;
        }
        $methods = $this->call('get', '/payment_methods', ['customer' => $subscription->gateway_customer_id, 'type' => 'card']);
        foreach ($methods['data'] ?? [] as $pm) {
            $this->call('post', '/payment_methods/'.$pm['id'].'/detach');
        }
    }

    public function portalUrl(SaasSubscription $subscription, string $returnUrl): ?string
    {
        if (! $subscription->gateway_customer_id) {
            return null;
        }

        return $this->call('post', '/billing_portal/sessions', ['customer' => $subscription->gateway_customer_id, 'return_url' => $returnUrl])['url'] ?? null;
    }

    public function verifyWebhook(string $payload, ?string $signature): array
    {
        if (! $this->webhookSecret || ! $signature) {
            throw new ApiException('invalid_signature', 'Missing webhook signature.', 400);
        }
        $parts = [];
        foreach (explode(',', $signature) as $kv) {
            [$k, $v] = array_pad(explode('=', $kv, 2), 2, null);
            $parts[$k][] = $v;
        }
        $timestamp = (int) ($parts['t'][0] ?? 0);
        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $this->webhookSecret);
        $valid = collect($parts['v1'] ?? [])->contains(fn ($sig) => hash_equals($expected, (string) $sig));
        if (! $valid || abs(time() - $timestamp) > 300) {
            throw new ApiException('invalid_signature', 'Invalid webhook signature.', 400);
        }

        return json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
    }
}
