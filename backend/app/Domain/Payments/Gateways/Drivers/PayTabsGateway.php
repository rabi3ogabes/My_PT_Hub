<?php

namespace App\Domain\Payments\Gateways\Drivers;

use App\Domain\Payments\Gateways\AbstractGateway;
use App\Domain\Payments\Gateways\ChargeResult;
use App\Domain\Payments\Gateways\CheckoutResult;
use App\Domain\Payments\Gateways\InvalidWebhookSignature;
use App\Domain\Payments\Gateways\RefundResult;
use App\Domain\Payments\Gateways\WebhookResult;
use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\Payment;
use App\Domain\Payments\Models\PaymentGateway as GatewayModel;
use App\Domain\Shared\Support\Money;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** PayTabs PT2 hosted payment page (payment/request); IPN/callback signed with HMAC-SHA256 of the raw body using the server key. */
final class PayTabsGateway extends AbstractGateway
{
    private const ENDPOINTS = [
        'ARE' => 'https://secure.paytabs.com',
        'SAU' => 'https://secure.paytabs.sa',
        'OMN' => 'https://secure-oman.paytabs.com',
        'JOR' => 'https://secure-jordan.paytabs.com',
        'EGY' => 'https://secure-egypt.paytabs.com',
        'IRQ' => 'https://secure-iraq.paytabs.com',
        'GLOBAL' => 'https://secure-global.paytabs.com',
    ];

    public function driver(): string
    {
        return 'paytabs';
    }

    private function base(): string
    {
        return self::ENDPOINTS[strtoupper($this->cred('region', false) ?? 'ARE')] ?? self::ENDPOINTS['ARE'];
    }

    private function http(): PendingRequest
    {
        return Http::withHeaders(['authorization' => $this->cred('server_key')])->acceptJson()->asJson()->timeout(25);
    }

    /** @return array<string, mixed> */
    private function request(array $body): array
    {
        $response = $this->http()->post($this->base().'/payment/request', ['profile_id' => (int) $this->cred('profile_id')] + $body);
        if ($response->failed()) {
            $this->fail($response, (string) ($response->json('message') ?? 'PayTabs request failed'));
        }

        return $response->json() ?? [];
    }

    public function createCheckout(Order $order, string $successUrl, string $cancelUrl): CheckoutResult
    {
        $order->loadMissing('items.price');
        $recurring = in_array($order->items->first()?->price?->type, ['recurring', 'installments'], true);
        $result = $this->request(array_filter([
            'tran_type' => 'sale',
            'tran_class' => 'ecom',
            'cart_id' => $order->id,
            'cart_currency' => $order->currency,
            'cart_amount' => (float) $this->major($order->total_minor, $order->currency),
            'cart_description' => $this->description($order),
            'paypage_lang' => app()->getLocale() === 'ar' ? 'ar' : 'en',
            'customer_details' => array_filter(['name' => $order->customer_name, 'email' => $order->customer_email]),
            'hide_shipping' => true,
            'callback' => $this->webhookUrl(),
            'return' => $successUrl,
            'tokenise' => $recurring ? 2 : null,
            'show_save_card' => $recurring ?: null,
        ], fn ($v) => $v !== null));

        return new CheckoutResult($result['redirect_url'] ?? null, (string) ($result['tran_ref'] ?? ''), $result);
    }

    public function charge(Order $order, string $customerRef, string $paymentMethodRef): ChargeResult
    {
        $result = $this->request([
            'tran_type' => 'sale',
            'tran_class' => 'recurring',
            'cart_id' => $order->id,
            'cart_currency' => $order->currency,
            'cart_amount' => (float) $this->major($order->total_minor, $order->currency),
            'cart_description' => $this->description($order),
            'token' => $paymentMethodRef,
            'tran_ref' => $customerRef,
        ]);
        $status = $result['payment_result']['response_status'] ?? null;

        return new ChargeResult($status === 'A' ? 'succeeded' : ($status === 'H' || $status === 'P' ? 'pending' : 'failed'), $result['tran_ref'] ?? null,
            $status === 'A' ? null : (string) ($result['payment_result']['response_message'] ?? 'declined'), 'card');
    }

    public function refund(Payment $payment, int $amountMinor): RefundResult
    {
        $result = $this->request([
            'tran_type' => 'refund',
            'tran_class' => 'ecom',
            'cart_id' => (string) $payment->order_id,
            'cart_currency' => $payment->currency,
            'cart_amount' => (float) $this->major($amountMinor, $payment->currency),
            'cart_description' => 'Refund '.$payment->id,
            'tran_ref' => $payment->gateway_payment_id,
        ]);

        return new RefundResult(($result['payment_result']['response_status'] ?? '') === 'A' ? 'succeeded' : 'failed', $result['tran_ref'] ?? null);
    }

    public function parseWebhook(Request $request, GatewayModel $gateway): WebhookResult
    {
        $raw = $request->getContent();
        $expected = hash_hmac('sha256', $raw, $this->cred('server_key'));
        if (! self::safeEquals($expected, (string) $request->header('signature'))) {
            throw new InvalidWebhookSignature;
        }
        $data = json_decode($raw, true);
        if (! is_array($data) || empty($data['tran_ref'])) {
            throw new InvalidWebhookSignature('Malformed PayTabs payload.');
        }
        $status = $data['payment_result']['response_status'] ?? '';
        $type = strtolower((string) ($data['tran_type'] ?? 'sale'));
        $externalId = $data['tran_ref'].':'.$status;
        $currency = (string) ($data['cart_currency'] ?? $data['tran_currency'] ?? '');
        $amount = isset($data['cart_amount']) ? Money::toMinor($data['cart_amount'], $currency) : null;

        if ($type === 'refund') {
            return $status === 'A'
                ? new WebhookResult($externalId, WebhookResult::REFUNDED, paymentId: $data['previous_tran_ref'] ?? null, amountMinor: $amount, currency: $currency, payload: $data)
                : WebhookResult::ignored($externalId, $data);
        }

        return match ($status) {
            'A' => new WebhookResult($externalId, WebhookResult::SUCCEEDED, orderId: $data['cart_id'] ?? null, reference: $data['tran_ref'], paymentId: $data['tran_ref'],
                amountMinor: $amount, currency: $currency, customerReference: $data['tran_ref'], paymentMethodReference: $data['token'] ?? null,
                method: strtolower((string) ($data['payment_info']['payment_method'] ?? 'card')) ?: 'card', payload: $data),
            'H', 'P' => WebhookResult::ignored($externalId, $data),
            default => new WebhookResult($externalId, WebhookResult::FAILED, orderId: $data['cart_id'] ?? null, reference: $data['tran_ref'],
                failureReason: (string) ($data['payment_result']['response_message'] ?? 'declined'), payload: $data),
        };
    }

    public function supportedCurrencies(): array
    {
        return ['AED', 'SAR', 'OMR', 'JOD', 'EGP', 'KWD', 'BHD', 'QAR', 'IQD', 'USD', 'EUR', 'GBP'];
    }

    public function supportedMethods(): array
    {
        return ['card', 'mada', 'apple_pay', 'stcpay', 'knet', 'valu', 'meeza', 'amex'];
    }

    public function supportsTokenCharges(): bool
    {
        return true;
    }
}
