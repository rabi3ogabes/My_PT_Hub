<?php

namespace App\Domain\Payments\Gateways\Drivers;

use App\Domain\Payments\Gateways\AbstractGateway;
use App\Domain\Payments\Gateways\CheckoutResult;
use App\Domain\Payments\Gateways\InvalidWebhookSignature;
use App\Domain\Payments\Gateways\RefundResult;
use App\Domain\Payments\Gateways\WebhookResult;
use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\Payment;
use App\Domain\Payments\Models\PaymentGateway as GatewayModel;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** Paymob Intention API + Unified Checkout (Egypt, KSA, UAE, Oman, Pakistan); transaction callbacks HMAC-SHA512 signed. */
final class PaymobGateway extends AbstractGateway
{
    private const ENDPOINTS = [
        'egypt' => 'https://accept.paymob.com',
        'ksa' => 'https://ksa.paymob.com',
        'uae' => 'https://uae.paymob.com',
        'oman' => 'https://oman.paymob.com',
        'pakistan' => 'https://pakistan.paymob.com',
    ];

    /** Field order Paymob uses to build the transaction HMAC. */
    private const HMAC_FIELDS = [
        'amount_cents', 'created_at', 'currency', 'error_occured', 'has_parent_transaction', 'id', 'integration_id',
        'is_3d_secure', 'is_auth', 'is_capture', 'is_refunded', 'is_standalone_payment', 'is_voided', 'order.id', 'owner',
        'pending', 'source_data.pan', 'source_data.sub_type', 'source_data.type', 'success',
    ];

    public function driver(): string
    {
        return 'paymob';
    }

    private function base(): string
    {
        return self::ENDPOINTS[strtolower($this->cred('region', false) ?? 'egypt')] ?? self::ENDPOINTS['egypt'];
    }

    private function http(): PendingRequest
    {
        return Http::withHeaders(['Authorization' => 'Token '.$this->cred('secret_key')])->acceptJson()->asJson()->timeout(25);
    }

    public function createCheckout(Order $order, string $successUrl, string $cancelUrl): CheckoutResult
    {
        $name = $this->splitName($order->customer_name);
        $integrations = array_values(array_map('intval', (array) ($this->credentials['integration_ids'] ?? [])));
        if ($integrations === []) {
            $this->cred('integration_ids');
        }
        $response = $this->http()->post($this->base().'/v1/intention/', [
            'amount' => $order->total_minor,
            'currency' => $order->currency,
            'payment_methods' => $integrations,
            'items' => [['name' => mb_substr($this->description($order), 0, 50), 'amount' => $order->total_minor, 'description' => $this->description($order), 'quantity' => 1]],
            'billing_data' => [
                'first_name' => $name['first'],
                'last_name' => $name['last'],
                'email' => $order->customer_email ?: 'na@example.invalid',
                'phone_number' => (string) ($order->meta['customer_phone'] ?? 'NA'),
            ],
            'special_reference' => $order->id,
            'extras' => ['order_id' => $order->id, 'tenant_id' => $order->tenant_id],
            'notification_url' => $this->webhookUrl(),
            'redirection_url' => $successUrl,
        ]);
        if ($response->failed() || ! $response->json('client_secret')) {
            $this->fail($response, (string) ($response->json('detail') ?? 'Paymob intention failed'));
        }
        $url = $this->base().'/unifiedcheckout/?'.http_build_query(['publicKey' => $this->cred('public_key'), 'clientSecret' => $response->json('client_secret')]);

        return new CheckoutResult($url, (string) ($response->json('id') ?? $response->json('intention_order_id')), $response->json() ?? []);
    }

    public function refund(Payment $payment, int $amountMinor): RefundResult
    {
        $response = $this->http()->post($this->base().'/api/acceptance/void_refund/refund', [
            'transaction_id' => $payment->gateway_payment_id,
            'amount_cents' => $amountMinor,
        ]);
        if ($response->failed()) {
            $this->fail($response, (string) ($response->json('detail') ?? $response->json('message') ?? 'Paymob refund failed'));
        }

        return new RefundResult($response->json('success') ? 'succeeded' : ($response->json('pending') ? 'pending' : 'failed'), (string) $response->json('id'));
    }

    public function parseWebhook(Request $request, GatewayModel $gateway): WebhookResult
    {
        $event = json_decode($request->getContent(), true);
        $obj = is_array($event) ? ($event['obj'] ?? null) : null;
        if (! is_array($obj)) {
            throw new InvalidWebhookSignature('Malformed Paymob callback.');
        }
        $expected = self::hmac($obj, $this->cred('hmac_secret'));
        if (! self::safeEquals($expected, (string) $request->query('hmac', (string) $request->header('hmac')))) {
            throw new InvalidWebhookSignature;
        }
        if (strtoupper((string) ($event['type'] ?? 'TRANSACTION')) !== 'TRANSACTION') {
            return WebhookResult::ignored('pm:'.md5($request->getContent()), $event);
        }

        $id = (string) $obj['id'];
        $success = self::flag($obj['success'] ?? false);
        $pending = self::flag($obj['pending'] ?? false);
        $externalId = $id.':'.($success ? 'success' : ($pending ? 'pending' : 'failed')).':'.(self::flag($obj['is_refunded'] ?? false) ? 'r' : 'p');
        $orderId = $obj['order']['merchant_order_id'] ?? $obj['payment_key_claims']['extra']['order_id'] ?? $obj['extras']['order_id'] ?? null;
        $currency = (string) ($obj['currency'] ?? '');

        if (self::flag($obj['is_refund'] ?? false) || self::flag($obj['has_parent_transaction'] ?? false)) {
            return $success
                ? new WebhookResult($externalId, WebhookResult::REFUNDED, paymentId: isset($obj['parent_transaction']) ? (string) $obj['parent_transaction'] : null,
                    amountMinor: (int) ($obj['amount_cents'] ?? 0), currency: $currency, payload: $event)
                : WebhookResult::ignored($externalId, $event);
        }

        return match (true) {
            $success => new WebhookResult($externalId, WebhookResult::SUCCEEDED, orderId: $orderId, reference: (string) ($obj['order']['id'] ?? $id), paymentId: $id,
                amountMinor: (int) ($obj['amount_cents'] ?? 0), currency: $currency, method: strtolower((string) ($obj['source_data']['type'] ?? 'card')), payload: $event),
            $pending => WebhookResult::ignored($externalId, $event),
            default => new WebhookResult($externalId, WebhookResult::FAILED, orderId: $orderId, reference: (string) ($obj['order']['id'] ?? $id),
                failureReason: (string) ($obj['data']['message'] ?? 'declined'), payload: $event),
        };
    }

    /** @param array<string, mixed> $obj */
    public static function hmac(array $obj, string $secret): string
    {
        $concatenated = '';
        foreach (self::HMAC_FIELDS as $field) {
            $value = data_get($obj, $field);
            $concatenated .= is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
        }

        return hash_hmac('sha512', $concatenated, $secret);
    }

    private static function flag(mixed $value): bool
    {
        return $value === true || $value === 'true' || $value === 1 || $value === '1';
    }

    public function supportedCurrencies(): array
    {
        return ['EGP', 'SAR', 'AED', 'OMR', 'PKR'];
    }

    public function supportedMethods(): array
    {
        return ['card', 'wallet', 'kiosk', 'valu', 'apple_pay', 'mada', 'installments'];
    }
}
