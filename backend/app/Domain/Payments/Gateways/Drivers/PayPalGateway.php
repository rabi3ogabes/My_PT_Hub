<?php

namespace App\Domain\Payments\Gateways\Drivers;

use App\Domain\Payments\Gateways\AbstractGateway;
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/** PayPal Orders v2 with OAuth client credentials; webhooks verified through PayPal's verify-webhook-signature API. */
final class PayPalGateway extends AbstractGateway implements HandlesReturn
{
    public function driver(): string
    {
        return 'paypal';
    }

    private function base(): string
    {
        return $this->live() ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    private function token(): string
    {
        return Cache::remember('paypal_token:'.$this->model->id.':'.md5($this->cred('client_id')), 300, function () {
            $response = Http::asForm()->acceptJson()->timeout(20)
                ->withBasicAuth($this->cred('client_id'), $this->cred('client_secret'))
                ->post($this->base().'/v1/oauth2/token', ['grant_type' => 'client_credentials']);
            if ($response->failed() || ! $response->json('access_token')) {
                $this->fail($response, (string) ($response->json('error_description') ?? 'PayPal authentication failed'));
            }

            return (string) $response->json('access_token');
        });
    }

    private function http(): PendingRequest
    {
        return Http::withToken($this->token())->acceptJson()->asJson()->timeout(20);
    }

    public function createCheckout(Order $order, string $successUrl, string $cancelUrl): CheckoutResult
    {
        $response = $this->http()->withHeaders(['PayPal-Request-Id' => 'order-'.$order->id])->post($this->base().'/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $order->id,
                'custom_id' => $order->id,
                'invoice_id' => $order->id,
                'description' => $this->description($order),
                'amount' => ['currency_code' => $order->currency, 'value' => $this->major($order->total_minor, $order->currency)],
            ]],
            'payment_source' => ['paypal' => ['experience_context' => [
                'return_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'user_action' => 'PAY_NOW',
                'shipping_preference' => 'NO_SHIPPING',
            ]]],
        ]);
        if ($response->failed()) {
            $this->fail($response, (string) ($response->json('message') ?? 'PayPal order failed'));
        }
        $url = collect($response->json('links') ?? [])->first(fn ($l) => in_array($l['rel'] ?? '', ['payer-action', 'approve'], true))['href'] ?? null;

        return new CheckoutResult($url, (string) $response->json('id'), $response->json() ?? []);
    }

    /** Captures an approved order; returns the capture resource. @return array<string, mixed> */
    public function capture(string $paypalOrderId): array
    {
        $response = $this->http()->withHeaders(['PayPal-Request-Id' => 'capture-'.$paypalOrderId])
            ->withBody('{}', 'application/json')
            ->post($this->base().'/v2/checkout/orders/'.$paypalOrderId.'/capture');
        if ($response->failed() && $response->json('details.0.issue') !== 'ORDER_ALREADY_CAPTURED') {
            $this->fail($response, (string) ($response->json('message') ?? 'PayPal capture failed'));
        }

        return $response->json('purchase_units.0.payments.captures.0') ?? [];
    }

    public function resultFromReturn(Order $order, Request $request): ?WebhookResult
    {
        $token = (string) $request->query('token', '');
        if ($token === '' || $token !== $order->gateway_reference) {
            return null;
        }
        $capture = $this->capture($token);

        return ($capture['status'] ?? '') === 'COMPLETED'
            ? $this->succeeded('return:'.$token, $capture, $order->id, $token, ['capture' => $capture])
            : null;
    }

    public function refund(Payment $payment, int $amountMinor): RefundResult
    {
        $response = $this->http()->withHeaders(['PayPal-Request-Id' => 'refund-'.$payment->id.'-'.$amountMinor.'-'.$payment->refunds()->count()])
            ->post($this->base().'/v2/payments/captures/'.$payment->gateway_payment_id.'/refund', [
                'amount' => ['value' => $this->major($amountMinor, $payment->currency), 'currency_code' => $payment->currency],
            ]);
        if ($response->failed()) {
            $this->fail($response, (string) ($response->json('message') ?? 'PayPal refund failed'));
        }

        return new RefundResult($response->json('status') === 'COMPLETED' ? 'succeeded' : 'pending', $response->json('id'));
    }

    public function parseWebhook(Request $request, GatewayModel $gateway): WebhookResult
    {
        $event = json_decode($request->getContent(), true);
        if (! is_array($event) || empty($event['id'])) {
            throw new InvalidWebhookSignature('Malformed PayPal event.');
        }
        $headers = [
            'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
            'cert_url' => $request->header('PAYPAL-CERT-URL'),
            'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
        ];
        if (in_array(null, $headers, true) || ! str_starts_with((string) $headers['cert_url'], 'https://')) {
            throw new InvalidWebhookSignature;
        }
        $verify = $this->http()->post($this->base().'/v1/notifications/verify-webhook-signature', $headers + [
            'webhook_id' => $this->cred('webhook_id'),
            'webhook_event' => $event,
        ]);
        if ($verify->failed() || $verify->json('verification_status') !== 'SUCCESS') {
            throw new InvalidWebhookSignature;
        }

        $resource = $event['resource'] ?? [];
        $id = (string) $event['id'];

        switch ($event['event_type'] ?? '') {
            case 'CHECKOUT.ORDER.APPROVED':
                $capture = $this->capture((string) $resource['id']);

                return ($capture['status'] ?? '') === 'COMPLETED'
                    ? $this->succeeded($id, $capture, $resource['purchase_units'][0]['custom_id'] ?? null, (string) $resource['id'], $event)
                    : WebhookResult::ignored($id, $event);
            case 'PAYMENT.CAPTURE.COMPLETED':
                return $this->succeeded($id, $resource, $resource['custom_id'] ?? null, $resource['supplementary_data']['related_ids']['order_id'] ?? null, $event);
            case 'PAYMENT.CAPTURE.DENIED':
            case 'PAYMENT.CAPTURE.DECLINED':
                return new WebhookResult($id, WebhookResult::FAILED, orderId: $resource['custom_id'] ?? null,
                    reference: $resource['supplementary_data']['related_ids']['order_id'] ?? null, failureReason: strtolower((string) ($event['event_type'])), payload: $event);
            case 'PAYMENT.CAPTURE.REFUNDED':
                $capture = collect($resource['links'] ?? [])->first(fn ($l) => ($l['rel'] ?? '') === 'up')['href'] ?? '';

                return new WebhookResult($id, WebhookResult::REFUNDED, paymentId: basename((string) $capture) ?: null,
                    amountMinor: isset($resource['amount']['value']) ? Money::toMinor($resource['amount']['value'], $resource['amount']['currency_code']) : null,
                    currency: $resource['amount']['currency_code'] ?? null, payload: $event);
            default:
                return WebhookResult::ignored($id, $event);
        }
    }

    /** @param array<string, mixed> $capture @param array<string, mixed> $event */
    private function succeeded(string $id, array $capture, ?string $orderId, ?string $reference, array $event): WebhookResult
    {
        $amount = $capture['amount'] ?? [];

        return new WebhookResult($id, WebhookResult::SUCCEEDED, orderId: $orderId, reference: $reference, paymentId: $capture['id'] ?? null,
            amountMinor: isset($amount['value']) ? Money::toMinor($amount['value'], $amount['currency_code']) : null,
            currency: $amount['currency_code'] ?? null, method: 'paypal', payload: $event);
    }

    public function supportedCurrencies(): array
    {
        return ['AUD', 'BRL', 'CAD', 'CNY', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'THB', 'USD'];
    }

    public function supportedMethods(): array
    {
        return ['paypal', 'card', 'venmo'];
    }
}
