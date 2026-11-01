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

/** Tap Payments (Qatar, KSA, UAE, Kuwait, Bahrain, Oman, Egypt): cards, mada, KNET, Benefit, Apple Pay. */
final class TapGateway extends AbstractGateway
{
    private const API = 'https://api.tap.company/v2';

    public function driver(): string
    {
        return 'tap';
    }

    private function http(): PendingRequest
    {
        return Http::withToken($this->cred('secret_key'))->acceptJson()->asJson()->timeout(20);
    }

    public function createCustomer(array $customer): ?string
    {
        $name = $this->splitName($customer['name'] ?? null);
        $response = $this->http()->post(self::API.'/customers', array_filter([
            'first_name' => $name['first'],
            'last_name' => $name['last'],
            'email' => $customer['email'] ?? null,
        ]));
        if ($response->failed()) {
            $this->fail($response, (string) ($response->json('errors.0.description') ?? 'Tap customer failed'));
        }

        return $response->json('id');
    }

    public function createCheckout(Order $order, string $successUrl, string $cancelUrl): CheckoutResult
    {
        $order->loadMissing('items.price');
        $recurring = in_array($order->items->first()?->price?->type, ['recurring', 'installments'], true);
        $name = $this->splitName($order->customer_name);
        $response = $this->http()->post(self::API.'/charges', [
            'amount' => (float) $this->major($order->total_minor, $order->currency),
            'currency' => $order->currency,
            'threeDSecure' => true,
            'save_card' => $recurring,
            'description' => $this->description($order),
            'customer' => array_filter(['first_name' => $name['first'], 'last_name' => $name['last'], 'email' => $order->customer_email]),
            'source' => ['id' => 'src_all'],
            'reference' => ['transaction' => 'txn_'.$order->id, 'order' => $order->id],
            'metadata' => ['order_id' => $order->id, 'tenant_id' => $order->tenant_id],
            'post' => ['url' => $this->webhookUrl()],
            'redirect' => ['url' => $successUrl],
        ]);
        if ($response->failed()) {
            $this->fail($response, (string) ($response->json('errors.0.description') ?? 'Tap charge failed'));
        }

        return new CheckoutResult($response->json('transaction.url'), (string) $response->json('id'), $response->json() ?? []);
    }

    public function charge(Order $order, string $customerRef, string $paymentMethodRef): ChargeResult
    {
        $token = $this->http()->post(self::API.'/tokens', ['saved_card' => ['card_id' => $paymentMethodRef, 'customer_id' => $customerRef]]);
        if ($token->failed() || ! $token->json('id')) {
            return new ChargeResult('failed', failureReason: (string) ($token->json('errors.0.description') ?? 'token_failed'));
        }
        $response = $this->http()->post(self::API.'/charges', [
            'amount' => (float) $this->major($order->total_minor, $order->currency),
            'currency' => $order->currency,
            'customer_initiated' => false,
            'threeDSecure' => false,
            'save_card' => false,
            'customer' => ['id' => $customerRef],
            'source' => ['id' => $token->json('id')],
            'reference' => ['transaction' => 'txn_'.$order->id, 'order' => $order->id],
            'metadata' => ['order_id' => $order->id, 'tenant_id' => $order->tenant_id],
            'post' => ['url' => $this->webhookUrl()],
            'redirect' => ['url' => url('pay/return/'.$order->id)],
        ]);
        $status = (string) $response->json('status');
        if ($response->failed()) {
            return new ChargeResult('failed', $response->json('id'), (string) ($response->json('errors.0.description') ?? 'charge_failed'));
        }

        return new ChargeResult($status === 'CAPTURED' ? 'succeeded' : ($status === 'INITIATED' ? 'pending' : 'failed'), $response->json('id'),
            $status === 'CAPTURED' ? null : strtolower($status), strtolower((string) $response->json('source.payment_method')) ?: 'card');
    }

    public function refund(Payment $payment, int $amountMinor): RefundResult
    {
        $response = $this->http()->post(self::API.'/refunds', [
            'charge_id' => $payment->gateway_payment_id,
            'amount' => (float) $this->major($amountMinor, $payment->currency),
            'currency' => $payment->currency,
            'reason' => 'requested_by_customer',
            'reference' => ['merchant' => 'rf_'.$payment->id.'_'.$payment->refunds()->count()],
            'post' => ['url' => $this->webhookUrl()],
        ]);
        if ($response->failed()) {
            $this->fail($response, (string) ($response->json('errors.0.description') ?? 'Tap refund failed'));
        }
        $status = (string) $response->json('status');

        return new RefundResult(in_array($status, ['REFUNDED', 'SUCCESS'], true) ? 'succeeded' : ($status === 'PENDING' || $status === 'IN_PROGRESS' ? 'pending' : 'failed'), $response->json('id'));
    }

    public function parseWebhook(Request $request, GatewayModel $gateway): WebhookResult
    {
        $data = json_decode($request->getContent(), true);
        if (! is_array($data) || empty($data['id'])) {
            throw new InvalidWebhookSignature('Malformed Tap payload.');
        }
        $expected = self::hashString($data, $this->cred('secret_key'));
        if (! self::safeEquals($expected, (string) $request->header('hashstring'))) {
            throw new InvalidWebhookSignature;
        }

        $status = strtoupper((string) ($data['status'] ?? ''));
        $id = (string) $data['id'];
        $externalId = $id.':'.$status;
        $orderId = $data['reference']['order'] ?? $data['metadata']['order_id'] ?? null;
        $currency = (string) ($data['currency'] ?? '');
        $amount = isset($data['amount']) ? Money::toMinor($data['amount'], $currency) : null;

        if (str_starts_with($id, 're_')) {
            return in_array($status, ['REFUNDED', 'SUCCESS'], true)
                ? new WebhookResult($externalId, WebhookResult::REFUNDED, paymentId: $data['charge_id'] ?? null, amountMinor: $amount, currency: $currency, payload: $data)
                : WebhookResult::ignored($externalId, $data);
        }

        return match (true) {
            $status === 'CAPTURED' => new WebhookResult($externalId, WebhookResult::SUCCEEDED, orderId: $orderId, reference: $id, paymentId: $id,
                amountMinor: $amount, currency: $currency, customerReference: $data['customer']['id'] ?? null,
                paymentMethodReference: $data['card']['id'] ?? null, method: strtolower((string) ($data['source']['payment_method'] ?? 'card')) ?: 'card', payload: $data),
            in_array($status, ['INITIATED', 'IN_PROGRESS', 'AUTHORIZED'], true) => WebhookResult::ignored($externalId, $data),
            default => new WebhookResult($externalId, WebhookResult::FAILED, orderId: $orderId, reference: $id,
                failureReason: strtolower($status) ?: 'failed', payload: $data),
        };
    }

    /**
     * Tap "hashstring": HMAC-SHA256 (secret API key) over
     * x_id{id}x_amount{amount}x_currency{currency}x_gateway_reference{ref}x_payment_reference{ref}x_status{status}x_created{created}
     * with the amount formatted to the currency's decimals.
     *
     * @param  array<string, mixed>  $data
     */
    public static function hashString(array $data, string $secret): string
    {
        $currency = (string) ($data['currency'] ?? '');
        $amount = number_format((float) ($data['amount'] ?? 0), Money::decimals($currency), '.', '');
        $string = 'x_id'.($data['id'] ?? '')
            .'x_amount'.$amount
            .'x_currency'.$currency
            .'x_gateway_reference'.($data['reference']['gateway'] ?? '')
            .'x_payment_reference'.($data['reference']['payment'] ?? '')
            .'x_status'.($data['status'] ?? '')
            .'x_created'.($data['transaction']['created'] ?? '');

        return hash_hmac('sha256', $string, $secret);
    }

    public function supportedCurrencies(): array
    {
        return ['QAR', 'SAR', 'AED', 'KWD', 'BHD', 'OMR', 'EGP', 'JOD', 'USD', 'EUR', 'GBP'];
    }

    public function supportedMethods(): array
    {
        return ['card', 'mada', 'knet', 'benefit', 'apple_pay', 'google_pay', 'stc_pay', 'naps', 'fawry'];
    }

    public function supportsTokenCharges(): bool
    {
        return true;
    }
}
