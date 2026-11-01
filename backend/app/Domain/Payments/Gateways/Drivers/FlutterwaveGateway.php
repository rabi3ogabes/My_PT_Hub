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

/** Flutterwave v3 Standard (Africa: cards, mobile money, bank transfer, USSD). Webhooks carry `verif-hash`; every success is re-verified via the API. */
final class FlutterwaveGateway extends AbstractGateway
{
    private const API = 'https://api.flutterwave.com/v3';

    public function driver(): string
    {
        return 'flutterwave';
    }

    private function http(): PendingRequest
    {
        return Http::withToken($this->cred('secret_key'))->acceptJson()->asJson()->timeout(25);
    }

    public function createCheckout(Order $order, string $successUrl, string $cancelUrl): CheckoutResult
    {
        $response = $this->http()->post(self::API.'/payments', [
            'tx_ref' => $order->id,
            'amount' => $this->major($order->total_minor, $order->currency),
            'currency' => $order->currency,
            'redirect_url' => $successUrl,
            'customer' => array_filter(['email' => $order->customer_email, 'name' => $order->customer_name, 'phonenumber' => $order->meta['customer_phone'] ?? null]),
            'customizations' => ['title' => mb_substr($this->description($order), 0, 60)],
            'meta' => ['order_id' => $order->id, 'tenant_id' => $order->tenant_id],
        ]);
        if ($response->failed() || $response->json('status') !== 'success') {
            $this->fail($response, (string) ($response->json('message') ?? 'Flutterwave payment failed'));
        }

        return new CheckoutResult($response->json('data.link'), $order->id, $response->json() ?? []);
    }

    public function charge(Order $order, string $customerRef, string $paymentMethodRef): ChargeResult
    {
        $response = $this->http()->post(self::API.'/tokenized-charges', [
            'token' => $paymentMethodRef,
            'email' => $customerRef,
            'currency' => $order->currency,
            'amount' => $this->major($order->total_minor, $order->currency),
            'tx_ref' => $order->id,
        ]);
        $status = (string) $response->json('data.status');

        return new ChargeResult($status === 'successful' ? 'succeeded' : ($status === 'pending' ? 'pending' : 'failed'),
            $response->json('data.id') ? (string) $response->json('data.id') : null, $status === 'successful' ? null : (string) ($response->json('message') ?? 'declined'), 'card');
    }

    public function refund(Payment $payment, int $amountMinor): RefundResult
    {
        $response = $this->http()->post(self::API.'/transactions/'.$payment->gateway_payment_id.'/refund', [
            'amount' => $this->major($amountMinor, $payment->currency),
        ]);
        if ($response->failed() || $response->json('status') !== 'success') {
            $this->fail($response, (string) ($response->json('message') ?? 'Flutterwave refund failed'));
        }

        return new RefundResult(in_array($response->json('data.status'), ['completed', 'successful'], true) ? 'succeeded' : 'pending', (string) $response->json('data.id'));
    }

    public function parseWebhook(Request $request, GatewayModel $gateway): WebhookResult
    {
        if (! self::safeEquals($this->cred('secret_hash'), (string) $request->header('verif-hash'))) {
            throw new InvalidWebhookSignature;
        }
        $event = json_decode($request->getContent(), true);
        $data = is_array($event) ? ($event['data'] ?? null) : null;
        if (! is_array($data) || empty($data['id'])) {
            throw new InvalidWebhookSignature('Malformed Flutterwave event.');
        }
        $id = (string) $data['id'];
        $status = strtolower((string) ($data['status'] ?? ''));
        $externalId = $id.':'.$status;
        if (($event['event'] ?? '') !== 'charge.completed') {
            return WebhookResult::ignored($externalId.':'.($event['event'] ?? ''), $event);
        }
        if ($status !== 'successful') {
            return $status === 'pending'
                ? WebhookResult::ignored($externalId, $event)
                : new WebhookResult($externalId, WebhookResult::FAILED, orderId: $data['tx_ref'] ?? null, reference: $id, failureReason: (string) ($data['processor_response'] ?? $status), payload: $event);
        }

        // Never trust the webhook body for money: re-verify the transaction.
        $verify = $this->http()->get(self::API.'/transactions/'.$id.'/verify');
        $tx = $verify->json('data') ?? [];
        if ($verify->failed() || ($tx['status'] ?? '') !== 'successful') {
            throw new InvalidWebhookSignature('Flutterwave transaction could not be verified.');
        }
        $currency = (string) ($tx['currency'] ?? '');

        return new WebhookResult($externalId, WebhookResult::SUCCEEDED, orderId: $tx['tx_ref'] ?? $data['tx_ref'] ?? null, reference: (string) ($tx['flw_ref'] ?? $id), paymentId: $id,
            amountMinor: isset($tx['amount']) ? Money::toMinor($tx['amount'], $currency) : null, currency: $currency,
            customerReference: $tx['customer']['email'] ?? null, paymentMethodReference: $tx['card']['token'] ?? null,
            method: strtolower((string) ($tx['payment_type'] ?? 'card')), payload: $event);
    }

    public function supportedCurrencies(): array
    {
        return ['NGN', 'GHS', 'KES', 'UGX', 'TZS', 'ZAR', 'XOF', 'XAF', 'RWF', 'ZMW', 'EGP', 'MAD', 'USD', 'EUR', 'GBP'];
    }

    public function supportedMethods(): array
    {
        return ['card', 'mobile_money', 'bank_transfer', 'ussd', 'mpesa', 'apple_pay', 'google_pay'];
    }

    public function supportsTokenCharges(): bool
    {
        return true;
    }
}
