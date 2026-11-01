<?php

namespace App\Domain\Payments\Gateways;

use App\Domain\Payments\Models\ClientSubscription;
use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Models\PaymentGateway as GatewayModel;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Support\Money;
use Illuminate\Http\Client\Response;

/** Shared plumbing for the HTTP drivers: credentials, amounts, URLs and error mapping. */
abstract class AbstractGateway implements PaymentGateway
{
    /** @var array<string, mixed> */
    protected array $credentials;

    public function __construct(protected readonly GatewayModel $model)
    {
        $this->credentials = is_array($model->credentials) ? $model->credentials : [];
    }

    public function model(): GatewayModel
    {
        return $this->model;
    }

    protected function cred(string $key, bool $required = true): ?string
    {
        $value = $this->credentials[$key] ?? null;
        if (($value === null || $value === '') && $required) {
            throw new ApiException('gateway_misconfigured', __('payments.errors.gateway_misconfigured', ['key' => $key]), 422);
        }

        return $value === null ? null : (string) $value;
    }

    protected function live(): bool
    {
        return (bool) $this->model->is_live;
    }

    /** Major-unit decimal string, e.g. 39900 QAR → "399.00", 12500 KWD → "12.500". */
    protected function major(int $minor, string $currency): string
    {
        return number_format(Money::toMajor($minor, $currency), Money::decimals($currency), '.', '');
    }

    protected function webhookUrl(): string
    {
        return url('webhooks/payments/'.$this->driver().'/'.$this->model->id);
    }

    protected function description(Order $order): string
    {
        $order->loadMissing('items');

        return mb_substr($order->items->pluck('description')->implode(', ') ?: __('payments.order_description', ['id' => $order->id]), 0, 120);
    }

    /** @return array{first: string, last: string} */
    protected function splitName(?string $name): array
    {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $first = array_shift($parts) ?: 'Client';

        return ['first' => $first, 'last' => implode(' ', $parts) ?: $first];
    }

    protected function fail(Response $response, string $fallback): never
    {
        throw new ApiException('gateway_error', __('payments.errors.gateway_error', ['driver' => $this->driver(), 'message' => mb_substr($fallback, 0, 200)]), 502);
    }

    protected function unsupported(string $operation): never
    {
        throw new ApiException('gateway_unsupported', __('payments.errors.unsupported', ['driver' => $this->driver(), 'operation' => $operation]), 422);
    }

    public function createCustomer(array $customer): ?string
    {
        return null;
    }

    public function charge(Order $order, string $customerRef, string $paymentMethodRef): ChargeResult
    {
        $this->unsupported('charge');
    }

    public function createSubscription(ClientSubscription $subscription, string $customerRef, ?string $paymentMethodRef = null): ?string
    {
        return null;
    }

    public function cancelSubscription(ClientSubscription $subscription, bool $atPeriodEnd = true): void
    {
        // Aurum drives renewals for this provider: nothing to cancel remotely.
    }

    public function supportedCurrencies(): array
    {
        return [];
    }

    public function supportsNativeSubscriptions(): bool
    {
        return false;
    }

    public function supportsTokenCharges(): bool
    {
        return false;
    }

    protected static function safeEquals(string $known, string $given): bool
    {
        return $given !== '' && hash_equals($known, $given);
    }
}
