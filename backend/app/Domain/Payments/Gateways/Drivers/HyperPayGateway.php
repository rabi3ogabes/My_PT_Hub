<?php

namespace App\Domain\Payments\Gateways\Drivers;

use App\Domain\Payments\Gateways\AbstractGateway;
use App\Domain\Payments\Gateways\ChargeResult;
use App\Domain\Payments\Gateways\CheckoutResult;
use App\Domain\Payments\Gateways\HandlesReturn;
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

/**
 * HyperPay (OPPWA) COPYandPAY: server creates a checkout, the hosted widget page
 * collects the card (mada, Visa, Mastercard, Apple Pay), the result is read from
 * the checkout's resourcePath. Webhooks are AES-256-GCM encrypted.
 */
final class HyperPayGateway extends AbstractGateway implements HandlesReturn
{
    public function driver(): string
    {
        return 'hyperpay';
    }

    public function base(): string
    {
        return $this->live() ? 'https://eu-prod.oppwa.com' : 'https://eu-test.oppwa.com';
    }

    public function entityId(): string
    {
        return (string) $this->cred('entity_id');
    }

    private function http(): PendingRequest
    {
        return Http::withToken($this->cred('access_token'))->asForm()->acceptJson()->timeout(25);
    }

    public function createCheckout(Order $order, string $successUrl, string $cancelUrl): CheckoutResult
    {
        $order->loadMissing('items.price');
        $recurring = in_array($order->items->first()?->price?->type, ['recurring', 'installments'], true);
        $name = $this->splitName($order->customer_name);
        $response = $this->http()->post($this->base().'/v1/checkouts', array_filter([
            'entityId' => $this->entityId(),
            'amount' => $this->major($order->total_minor, $order->currency),
            'currency' => $order->currency,
            'paymentType' => 'DB',
            'merchantTransactionId' => $order->id,
            'customer.email' => $order->customer_email,
            'customer.givenName' => $name['first'],
            'customer.surname' => $name['last'],
            'createRegistration' => $recurring ? 'true' : null,
            'notificationUrl' => $this->webhookUrl(),
        ]));
        if ($response->failed() || ! $response->json('id')) {
            $this->fail($response, (string) ($response->json('result.description') ?? 'HyperPay checkout failed'));
        }
        $checkoutId = (string) $response->json('id');

        // The widget page hosted by Aurum renders paymentWidgets.js for this checkout id.
        return new CheckoutResult(url('pay/hyperpay/'.$order->id), $checkoutId, $response->json() ?? []);
    }

    /**
     * Reads a payment status from the checkout's resourcePath (after the shopper returns).
     */
    public function resultFor(Order $order, string $resourcePath): WebhookResult
    {
        if (! preg_match('#^/v1/checkouts/[A-Za-z0-9.\-]+/payment$#', $resourcePath)) {
            throw new InvalidWebhookSignature('Invalid resource path.');
        }
        $response = $this->http()->get($this->base().$resourcePath, ['entityId' => $this->entityId()]);

        return $this->normalise($response->json() ?? [], 'result:');
    }

    public function resultFromReturn(Order $order, Request $request): ?WebhookResult
    {
        $path = (string) $request->query('resourcePath', '');
        if ($path === '' || ! str_contains($path, (string) $order->gateway_reference)) {
            return null;
        }

        return $this->resultFor($order, $path);
    }

    public function charge(Order $order, string $customerRef, string $paymentMethodRef): ChargeResult
    {
        $response = $this->http()->post($this->base().'/v1/registrations/'.$paymentMethodRef.'/payments', [
            'entityId' => $this->entityId(),
            'amount' => $this->major($order->total_minor, $order->currency),
            'currency' => $order->currency,
            'paymentType' => 'DB',
            'merchantTransactionId' => $order->id,
            'standingInstruction.mode' => 'REPEATED',
            'standingInstruction.type' => 'UNSCHEDULED',
            'standingInstruction.source' => 'MIT',
        ]);
        $code = (string) $response->json('result.code');

        return new ChargeResult(self::isSuccess($code) ? 'succeeded' : (self::isPending($code) ? 'pending' : 'failed'), $response->json('id'),
            self::isSuccess($code) ? null : (string) ($response->json('result.description') ?? 'declined'), 'card');
    }

    public function refund(Payment $payment, int $amountMinor): RefundResult
    {
        $response = $this->http()->post($this->base().'/v1/payments/'.$payment->gateway_payment_id, [
            'entityId' => $this->entityId(),
            'amount' => $this->major($amountMinor, $payment->currency),
            'currency' => $payment->currency,
            'paymentType' => 'RF',
        ]);
        $code = (string) $response->json('result.code');
        if ($response->failed() && ! $code) {
            $this->fail($response, 'HyperPay refund failed');
        }

        return new RefundResult(self::isSuccess($code) ? 'succeeded' : (self::isPending($code) ? 'pending' : 'failed'), $response->json('id'));
    }

    public function parseWebhook(Request $request, GatewayModel $gateway): WebhookResult
    {
        $body = trim($request->getContent());
        $json = json_decode($body, true);
        $cipherHex = is_array($json) ? (string) ($json['encryptedBody'] ?? '') : $body;
        $decrypted = self::decrypt($cipherHex, (string) $request->header('X-Initialization-Vector'), (string) $request->header('X-Authentication-Tag'), $this->cred('webhook_key'));
        $event = json_decode($decrypted, true);
        if (! is_array($event)) {
            throw new InvalidWebhookSignature('Malformed HyperPay notification.');
        }
        if (strtoupper((string) ($event['type'] ?? '')) !== 'PAYMENT') {
            return WebhookResult::ignored('hp:'.md5($decrypted), $event);
        }

        return $this->normalise($event['payload'] ?? [], '', $event);
    }

    /** Authenticated decryption (AES-256-GCM): a forged or tampered body fails the tag check. */
    public static function decrypt(string $cipherHex, string $ivHex, string $tagHex, string $keyHex): string
    {
        $cipher = @hex2bin($cipherHex);
        $iv = @hex2bin($ivHex);
        $tag = @hex2bin($tagHex);
        $key = @hex2bin($keyHex);
        if (! $cipher || ! $iv || ! $tag || ! $key) {
            throw new InvalidWebhookSignature;
        }
        $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($plain === false) {
            throw new InvalidWebhookSignature;
        }

        return $plain;
    }

    /**
     * @param  array<string, mixed>  $payment
     * @param  array<string, mixed>|null  $event
     */
    private function normalise(array $payment, string $prefix, ?array $event = null): WebhookResult
    {
        $code = (string) ($payment['result']['code'] ?? '');
        $id = (string) ($payment['id'] ?? '');
        $type = strtoupper((string) ($payment['paymentType'] ?? 'DB'));
        $currency = (string) ($payment['currency'] ?? '');
        $amount = isset($payment['amount']) ? Money::toMinor($payment['amount'], $currency) : null;
        $externalId = $prefix.$id.':'.$code;
        $payload = $event ?? $payment;

        if ($type === 'RF') {
            return self::isSuccess($code)
                ? new WebhookResult($externalId, WebhookResult::REFUNDED, paymentId: $payment['referencedId'] ?? null, amountMinor: $amount, currency: $currency, payload: $payload)
                : WebhookResult::ignored($externalId, $payload);
        }

        return match (true) {
            self::isSuccess($code) => new WebhookResult($externalId, WebhookResult::SUCCEEDED, orderId: $payment['merchantTransactionId'] ?? null, reference: $payment['ndc'] ?? $id,
                paymentId: $id, amountMinor: $amount, currency: $currency, customerReference: $payment['registrationId'] ?? null,
                paymentMethodReference: $payment['registrationId'] ?? null, method: strtolower((string) ($payment['paymentBrand'] ?? 'card')), payload: $payload),
            self::isPending($code) => WebhookResult::ignored($externalId, $payload),
            default => new WebhookResult($externalId, WebhookResult::FAILED, orderId: $payment['merchantTransactionId'] ?? null, reference: $id,
                failureReason: (string) ($payment['result']['description'] ?? $code), payload: $payload),
        };
    }

    public static function isSuccess(string $code): bool
    {
        return (bool) preg_match('/^(000\.000\.|000\.100\.1|000\.[36]|000\.400\.1[12]0)/', $code);
    }

    public static function isPending(string $code): bool
    {
        return (bool) preg_match('/^(000\.200|800\.400\.5|100\.400\.500)/', $code);
    }

    public function supportedCurrencies(): array
    {
        return ['SAR', 'AED', 'QAR', 'KWD', 'BHD', 'OMR', 'JOD', 'EGP', 'USD', 'EUR'];
    }

    public function supportedMethods(): array
    {
        return ['card', 'mada', 'apple_pay', 'stc_pay'];
    }

    public function supportsTokenCharges(): bool
    {
        return true;
    }
}
