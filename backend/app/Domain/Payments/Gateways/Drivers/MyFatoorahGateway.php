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
use Illuminate\Support\Facades\Http;

/** MyFatoorah v2 (Kuwait, KSA, UAE, Qatar, Bahrain, Oman, Jordan, Egypt): ExecutePayment / GetPaymentStatus; webhooks signed with MyFatoorah-Signature. */
final class MyFatoorahGateway extends AbstractGateway implements HandlesReturn
{
    private const LIVE = [
        'ARE' => 'https://api-ae.myfatoorah.com',
        'SAU' => 'https://api-sa.myfatoorah.com',
        'QAT' => 'https://api-qa.myfatoorah.com',
        'EGY' => 'https://api-eg.myfatoorah.com',
    ];

    public function driver(): string
    {
        return 'myfatoorah';
    }

    private function base(): string
    {
        if (! $this->live()) {
            return 'https://apitest.myfatoorah.com';
        }

        return self::LIVE[strtoupper($this->cred('country', false) ?? 'KWT')] ?? 'https://api.myfatoorah.com';
    }

    private function http(): PendingRequest
    {
        return Http::withToken($this->cred('api_key'))->acceptJson()->asJson()->timeout(25);
    }

    /** @return array<string, mixed> */
    private function call(string $path, array $body): array
    {
        $response = $this->http()->post($this->base().$path, $body);
        if ($response->failed() || $response->json('IsSuccess') === false) {
            $this->fail($response, (string) ($response->json('Message') ?? $response->json('ValidationErrors.0.Error') ?? 'MyFatoorah request failed'));
        }

        return $response->json('Data') ?? [];
    }

    public function createCheckout(Order $order, string $successUrl, string $cancelUrl): CheckoutResult
    {
        $body = array_filter([
            'InvoiceValue' => (float) $this->major($order->total_minor, $order->currency),
            'DisplayCurrencyIso' => $order->currency,
            'CustomerName' => $order->customer_name ?: $order->customer_email,
            'CustomerEmail' => $order->customer_email,
            'CallBackUrl' => $successUrl,
            'ErrorUrl' => $cancelUrl,
            'Language' => app()->getLocale() === 'ar' ? 'AR' : 'EN',
            'CustomerReference' => $order->id,
            'UserDefinedField' => $order->id,
            'InvoiceItems' => [['ItemName' => $this->description($order), 'Quantity' => 1, 'UnitPrice' => (float) $this->major($order->total_minor, $order->currency)]],
        ], fn ($v) => $v !== null && $v !== '');

        $methodId = $this->cred('payment_method_id', false);
        if ($methodId) {
            $data = $this->call('/v2/ExecutePayment', $body + ['PaymentMethodId' => (int) $methodId]);
            $url = $data['PaymentURL'] ?? null;
        } else {
            $data = $this->call('/v2/SendPayment', $body + ['NotificationOption' => 'LNK']);
            $url = $data['InvoiceURL'] ?? null;
        }

        return new CheckoutResult($url, (string) ($data['InvoiceId'] ?? ''), $data);
    }

    public function status(string $key, string $keyType = 'PaymentId'): WebhookResult
    {
        $data = $this->call('/v2/GetPaymentStatus', ['Key' => $key, 'KeyType' => $keyType]);
        $status = strtolower((string) ($data['InvoiceStatus'] ?? ''));
        $transaction = collect($data['InvoiceTransactions'] ?? [])->firstWhere('TransactionStatus', 'Succss')
            ?? collect($data['InvoiceTransactions'] ?? [])->firstWhere('TransactionStatus', 'Success')
            ?? collect($data['InvoiceTransactions'] ?? [])->last() ?? [];
        $currency = (string) ($transaction['PaidCurrency'] ?? $data['InvoiceDisplayValue'] ?? '');
        $orderId = $data['CustomerReference'] ?? $data['UserDefinedField'] ?? null;
        $externalId = 'status:'.($data['InvoiceId'] ?? $key).':'.$status;

        if ($status === 'paid') {
            return new WebhookResult($externalId, WebhookResult::SUCCEEDED, orderId: $orderId, reference: (string) ($data['InvoiceId'] ?? ''),
                paymentId: (string) ($transaction['PaymentId'] ?? $key), amountMinor: isset($data['InvoiceValue']) && strlen($currency) === 3 ? Money::toMinor($data['InvoiceValue'], $currency) : null,
                currency: strlen($currency) === 3 ? $currency : null, method: strtolower((string) ($transaction['PaymentGateway'] ?? 'card')), payload: $data);
        }

        return $status === 'failed' || $status === 'expired'
            ? new WebhookResult($externalId, WebhookResult::FAILED, orderId: $orderId, failureReason: (string) ($transaction['Error'] ?? $status), payload: $data)
            : WebhookResult::ignored($externalId, $data);
    }

    public function resultFromReturn(Order $order, Request $request): ?WebhookResult
    {
        $paymentId = (string) $request->query('paymentId', '');

        return $paymentId !== '' ? $this->status($paymentId) : null;
    }

    public function refund(Payment $payment, int $amountMinor): RefundResult
    {
        $data = $this->call('/v2/MakeRefund', [
            'Key' => $payment->gateway_payment_id,
            'KeyType' => 'PaymentId',
            'RefundChargeOnCustomer' => false,
            'ServiceChargeOnCustomer' => false,
            'Amount' => (float) $this->major($amountMinor, $payment->currency),
            'Comment' => 'Refund '.$payment->id,
        ]);

        return new RefundResult('pending', isset($data['RefundId']) ? (string) $data['RefundId'] : null);
    }

    public function parseWebhook(Request $request, GatewayModel $gateway): WebhookResult
    {
        $event = json_decode($request->getContent(), true);
        if (! is_array($event) || ! is_array($event['Data'] ?? null)) {
            throw new InvalidWebhookSignature('Malformed MyFatoorah event.');
        }
        $expected = self::signature($event['Data'], $this->cred('webhook_secret'));
        if (! self::safeEquals($expected, (string) $request->header('MyFatoorah-Signature'))) {
            throw new InvalidWebhookSignature;
        }
        $data = $event['Data'];
        $eventType = (int) ($event['EventType'] ?? 0);

        if ($eventType === 2) {
            $status = strtoupper((string) ($data['RefundStatus'] ?? ''));
            $externalId = 'refund:'.($data['RefundId'] ?? $data['RefundReference'] ?? md5(json_encode($data) ?: '')).':'.$status;

            return $status === 'REFUNDED'
                ? new WebhookResult($externalId, WebhookResult::REFUNDED, paymentId: isset($data['PaymentId']) ? (string) $data['PaymentId'] : null,
                    amountMinor: isset($data['Amount'], $data['DisplayCurrency']) ? Money::toMinor($data['Amount'], (string) $data['DisplayCurrency']) : null,
                    currency: $data['DisplayCurrency'] ?? null, payload: $event)
                : WebhookResult::ignored($externalId, $event);
        }
        if ($eventType !== 1) {
            return WebhookResult::ignored('evt:'.($event['EventType'] ?? '0').':'.md5($request->getContent()), $event);
        }

        $status = strtoupper((string) ($data['TransactionStatus'] ?? ''));
        $externalId = 'txn:'.($data['InvoiceId'] ?? '').':'.($data['PaymentId'] ?? '').':'.$status;
        $orderId = $data['CustomerReference'] ?? $data['UserDefinedField'] ?? null;
        $currency = (string) ($data['DisplayCurrency'] ?? '');

        return match ($status) {
            'SUCCESS' => new WebhookResult($externalId, WebhookResult::SUCCEEDED, orderId: $orderId, reference: isset($data['InvoiceId']) ? (string) $data['InvoiceId'] : null,
                paymentId: isset($data['PaymentId']) ? (string) $data['PaymentId'] : null,
                amountMinor: isset($data['InvoiceValueInDisplayCurreny']) && $currency !== '' ? Money::toMinor((string) $data['InvoiceValueInDisplayCurreny'], $currency) : null,
                currency: $currency ?: null, method: strtolower((string) ($data['PaymentMethod'] ?? 'card')), payload: $event),
            'FAILED', 'CANCELED', 'CANCELLED' => new WebhookResult($externalId, WebhookResult::FAILED, orderId: $orderId,
                reference: isset($data['InvoiceId']) ? (string) $data['InvoiceId'] : null, failureReason: (string) ($data['ErrorMessage'] ?? strtolower($status)), payload: $event),
            default => WebhookResult::ignored($externalId, $event),
        };
    }

    /**
     * MyFatoorah webhook signature: Data properties sorted by key (case-insensitive),
     * joined as "Key=Value" with ",", HMAC-SHA256 with the webhook secret, base64.
     *
     * @param  array<string, mixed>  $data
     */
    public static function signature(array $data, string $secret): string
    {
        uksort($data, 'strcasecmp');
        $pairs = [];
        foreach ($data as $key => $value) {
            $value = is_bool($value) ? ($value ? 'true' : 'false') : (is_scalar($value) ? (string) $value : '');
            $pairs[] = $key.'='.$value;
        }

        return base64_encode(hash_hmac('sha256', implode(',', $pairs), $secret, true));
    }

    public function supportedCurrencies(): array
    {
        return ['KWD', 'SAR', 'AED', 'QAR', 'BHD', 'OMR', 'JOD', 'EGP', 'USD', 'EUR'];
    }

    public function supportedMethods(): array
    {
        return ['card', 'knet', 'mada', 'apple_pay', 'google_pay', 'benefit', 'stc_pay', 'naps', 'amex'];
    }
}
