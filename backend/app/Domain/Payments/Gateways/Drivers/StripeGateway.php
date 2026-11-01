<?php

namespace App\Domain\Payments\Gateways\Drivers;

use App\Domain\Payments\Gateways\AbstractGateway;
use App\Domain\Payments\Gateways\ChargeResult;
use App\Domain\Payments\Gateways\CheckoutResult;
use App\Domain\Payments\Gateways\InvalidWebhookSignature;
use App\Domain\Payments\Gateways\RefundResult;
use App\Domain\Payments\Gateways\WebhookResult;
use App\Domain\Payments\Models\ClientSubscription;
use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\Payment;
use App\Domain\Payments\Models\PaymentGateway as GatewayModel;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Stripe Connect (Standard accounts): the coach is merchant of record. Requests
 * are made with the platform key on behalf of the connected account via the
 * `Stripe-Account` header (or with the coach's own secret key when provided).
 */
final class StripeGateway extends AbstractGateway
{
    private const API = 'https://api.stripe.com/v1';

    private const TOLERANCE_SECONDS = 300;

    public function driver(): string
    {
        return 'stripe';
    }

    private function http(): PendingRequest
    {
        $own = $this->cred('secret_key', false);
        $request = Http::withToken($own ?? (string) config('services.stripe.secret'))->asForm()->acceptJson()->timeout(20);
        $account = $this->cred('account_id', false);
        if (! $own && $account) {
            $request = $request->withHeaders(['Stripe-Account' => $account]);
        }

        return $request;
    }

    /** @return array<string, mixed> */
    private function call(string $method, string $path, array $data = [], ?string $idempotencyKey = null): array
    {
        $request = $this->http();
        if ($idempotencyKey) {
            $request = $request->withHeaders(['Idempotency-Key' => $idempotencyKey]);
        }
        $response = $request->{$method}(self::API.$path, $data);
        if ($response->failed()) {
            $this->fail($response, (string) ($response->json('error.message') ?? 'Stripe request failed'));
        }

        return $response->json() ?? [];
    }

    public function createCustomer(array $customer): ?string
    {
        return $this->call('post', '/customers', array_filter([
            'name' => $customer['name'] ?? null,
            'email' => $customer['email'] ?? null,
            'phone' => $customer['phone'] ?? null,
        ]))['id'] ?? null;
    }

    public function createCheckout(Order $order, string $successUrl, string $cancelUrl): CheckoutResult
    {
        $order->loadMissing('items.price');
        $price = $order->items->first()?->price;
        $recurring = $price && in_array($price->type, ['recurring', 'installments'], true);
        $currency = strtolower($order->currency);

        $data = [
            'mode' => $recurring ? 'subscription' : 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => $order->id,
            'customer_email' => $order->customer_email,
            'metadata[order_id]' => $order->id,
            'metadata[tenant_id]' => $order->tenant_id,
            'line_items[0][quantity]' => 1,
            'line_items[0][price_data][currency]' => $currency,
            'line_items[0][price_data][product_data][name]' => $this->description($order),
            'locale' => 'auto',
        ];

        $fee = (int) config('payments.platform_fee_bps', 0);
        if ($recurring) {
            $data['line_items[0][price_data][unit_amount]'] = $price->amount_minor;
            $data['line_items[0][price_data][recurring][interval]'] = $price->interval ?: 'month';
            $data['line_items[0][price_data][recurring][interval_count]'] = max(1, (int) $price->interval_count);
            $data['subscription_data[metadata][order_id]'] = $order->id;
            if ($price->trial_days > 0) {
                $data['subscription_data[trial_period_days]'] = $price->trial_days;
            }
            if ($fee > 0) {
                $data['subscription_data[application_fee_percent]'] = round($fee / 100, 2);
            }
        } else {
            $data['line_items[0][price_data][unit_amount]'] = $order->total_minor;
            $data['payment_intent_data[metadata][order_id]'] = $order->id;
            if ($fee > 0 && $this->cred('account_id', false)) {
                $data['payment_intent_data[application_fee_amount]'] = intdiv($order->total_minor * $fee, 10000);
            }
        }

        $session = $this->call('post', '/checkout/sessions', array_filter($data, fn ($v) => $v !== null), 'checkout_'.$order->id);

        return new CheckoutResult($session['url'] ?? null, (string) ($session['id'] ?? ''), $session);
    }

    public function charge(Order $order, string $customerRef, string $paymentMethodRef): ChargeResult
    {
        $response = $this->http()->withHeaders(['Idempotency-Key' => 'charge_'.$order->id])->post(self::API.'/payment_intents', [
            'amount' => $order->total_minor,
            'currency' => strtolower($order->currency),
            'customer' => $customerRef,
            'payment_method' => $paymentMethodRef,
            'off_session' => 'true',
            'confirm' => 'true',
            'metadata[order_id]' => $order->id,
        ]);
        $intent = $response->json() ?? [];
        if ($response->failed()) {
            return new ChargeResult('failed', $intent['error']['payment_intent']['id'] ?? null, (string) ($intent['error']['message'] ?? 'card_declined'));
        }

        return new ChargeResult(($intent['status'] ?? '') === 'succeeded' ? 'succeeded' : 'pending', $intent['id'] ?? null, method: 'card');
    }

    public function createSubscription(ClientSubscription $subscription, string $customerRef, ?string $paymentMethodRef = null): ?string
    {
        $product = $this->call('post', '/products', ['name' => (string) ($subscription->product?->translate('name') ?? 'Subscription')]);
        $sub = $this->call('post', '/subscriptions', array_filter([
            'customer' => $customerRef,
            'default_payment_method' => $paymentMethodRef,
            'items[0][price_data][currency]' => strtolower($subscription->currency),
            'items[0][price_data][unit_amount]' => $subscription->amount_minor,
            'items[0][price_data][product]' => $product['id'] ?? null,
            'items[0][price_data][recurring][interval]' => $subscription->interval,
            'items[0][price_data][recurring][interval_count]' => max(1, (int) $subscription->interval_count),
            'metadata[client_subscription_id]' => $subscription->id,
        ]), 'sub_'.$subscription->id);

        return $sub['id'] ?? null;
    }

    public function cancelSubscription(ClientSubscription $subscription, bool $atPeriodEnd = true): void
    {
        if (! $subscription->gateway_subscription_id) {
            return;
        }
        $atPeriodEnd
            ? $this->call('post', '/subscriptions/'.$subscription->gateway_subscription_id, ['cancel_at_period_end' => 'true'])
            : $this->call('delete', '/subscriptions/'.$subscription->gateway_subscription_id);
    }

    public function refund(Payment $payment, int $amountMinor): RefundResult
    {
        $refund = $this->call('post', '/refunds', [
            'payment_intent' => $payment->gateway_payment_id,
            'amount' => $amountMinor,
            'metadata[payment_id]' => $payment->id,
        ], 'refund_'.$payment->id.'_'.$amountMinor.'_'.$payment->refunds()->count());

        return new RefundResult(($refund['status'] ?? '') === 'failed' ? 'failed' : (($refund['status'] ?? '') === 'pending' ? 'pending' : 'succeeded'), $refund['id'] ?? null);
    }

    public function parseWebhook(Request $request, GatewayModel $gateway): WebhookResult
    {
        $payload = $request->getContent();
        self::verifySignature($payload, (string) $request->header('Stripe-Signature'), $this->cred('webhook_secret'));
        $event = json_decode($payload, true);
        if (! is_array($event) || empty($event['id'])) {
            throw new InvalidWebhookSignature('Malformed Stripe event.');
        }
        $obj = $event['data']['object'] ?? [];
        $id = (string) $event['id'];
        $currency = isset($obj['currency']) ? strtoupper((string) $obj['currency']) : null;

        return match ($event['type'] ?? '') {
            'checkout.session.completed', 'checkout.session.async_payment_succeeded' => in_array($obj['payment_status'] ?? '', ['paid', 'no_payment_required'], true)
                ? new WebhookResult($id, WebhookResult::SUCCEEDED, orderId: $obj['metadata']['order_id'] ?? $obj['client_reference_id'] ?? null, reference: $obj['id'] ?? null,
                    paymentId: $obj['payment_intent'] ?? $obj['invoice'] ?? $obj['id'] ?? null, amountMinor: isset($obj['amount_total']) ? (int) $obj['amount_total'] : null,
                    currency: $currency, subscriptionReference: $obj['subscription'] ?? null, customerReference: $obj['customer'] ?? null, method: 'card', payload: $event)
                : WebhookResult::ignored($id, $event),
            'checkout.session.async_payment_failed', 'payment_intent.payment_failed' => new WebhookResult($id, WebhookResult::FAILED,
                orderId: $obj['metadata']['order_id'] ?? $obj['client_reference_id'] ?? null, reference: $obj['id'] ?? null,
                failureReason: $obj['last_payment_error']['message'] ?? 'payment_failed', payload: $event),
            'invoice.paid' => ($obj['billing_reason'] ?? '') === 'subscription_create'
                ? WebhookResult::ignored($id, $event)
                : new WebhookResult($id, WebhookResult::RENEWED, paymentId: $obj['payment_intent'] ?? $obj['id'] ?? null,
                    amountMinor: (int) ($obj['amount_paid'] ?? 0), currency: $currency, subscriptionReference: self::invoiceSubscription($obj), method: 'card', payload: $event),
            'invoice.payment_failed' => new WebhookResult($id, WebhookResult::RENEWAL_FAILED, subscriptionReference: self::invoiceSubscription($obj),
                failureReason: 'invoice_payment_failed', payload: $event),
            'customer.subscription.deleted' => new WebhookResult($id, WebhookResult::CANCELLED, subscriptionReference: $obj['id'] ?? null, payload: $event),
            'charge.refunded' => new WebhookResult($id, WebhookResult::REFUNDED, paymentId: $obj['payment_intent'] ?? $obj['id'] ?? null,
                amountMinor: (int) ($obj['amount_refunded'] ?? 0), currency: $currency, payload: $event),
            default => WebhookResult::ignored($id, $event),
        };
    }

    /** @param array<string, mixed> $invoice */
    private static function invoiceSubscription(array $invoice): ?string
    {
        return $invoice['subscription'] ?? $invoice['parent']['subscription_details']['subscription'] ?? null;
    }

    /** Stripe-Signature: "t=<unix>,v1=<hex hmac>[,v1=...]"; signed payload is "<t>.<body>". */
    public static function verifySignature(string $payload, string $header, string $secret, ?int $now = null): void
    {
        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $header) as $part) {
            [$k, $v] = array_pad(explode('=', trim($part), 2), 2, '');
            if ($k === 't') {
                $timestamp = (int) $v;
            } elseif ($k === 'v1') {
                $signatures[] = $v;
            }
        }
        if (! $timestamp || $signatures === []) {
            throw new InvalidWebhookSignature;
        }
        if (abs(($now ?? time()) - $timestamp) > self::TOLERANCE_SECONDS) {
            throw new InvalidWebhookSignature('Stripe webhook timestamp outside tolerance.');
        }
        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                return;
            }
        }
        throw new InvalidWebhookSignature;
    }

    public function supportedMethods(): array
    {
        return ['card', 'apple_pay', 'google_pay', 'link', 'sepa_debit'];
    }

    public function supportsNativeSubscriptions(): bool
    {
        return true;
    }

    public function supportsTokenCharges(): bool
    {
        return true;
    }
}
