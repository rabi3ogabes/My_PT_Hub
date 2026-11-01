<?php

namespace App\Domain\Payments\Gateways;

use App\Domain\Payments\Models\ClientSubscription;
use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\Payment;
use App\Domain\Payments\Models\PaymentGateway as GatewayModel;
use Illuminate\Http\Request;

/**
 * A client → coach payment provider. One instance is bound to one connected
 * gateway (a payment_gateways row with its decrypted credentials).
 */
interface PaymentGateway
{
    public function driver(): string;

    /**
     * Creates the customer at the provider (where the provider has customers).
     *
     * @param  array{name?: ?string, email?: ?string, phone?: ?string}  $customer
     */
    public function createCustomer(array $customer): ?string;

    /** Creates a hosted checkout for the order. */
    public function createCheckout(Order $order, string $successUrl, string $cancelUrl): CheckoutResult;

    /** Off-session charge of a saved payment method (renewals for gateways without native subscriptions). */
    public function charge(Order $order, string $customerRef, string $paymentMethodRef): ChargeResult;

    /** Creates a native provider subscription; returns its id, or null when renewals are driven by Aurum. */
    public function createSubscription(ClientSubscription $subscription, string $customerRef, ?string $paymentMethodRef = null): ?string;

    public function cancelSubscription(ClientSubscription $subscription, bool $atPeriodEnd = true): void;

    public function refund(Payment $payment, int $amountMinor): RefundResult;

    /**
     * Verifies the webhook signature and normalises the event.
     *
     * @throws InvalidWebhookSignature
     */
    public function parseWebhook(Request $request, GatewayModel $gateway): WebhookResult;

    /** @return list<string> ISO 4217 codes; empty list = any currency. */
    public function supportedCurrencies(): array;

    /** @return list<string> */
    public function supportedMethods(): array;

    public function supportsNativeSubscriptions(): bool;

    public function supportsTokenCharges(): bool;
}
