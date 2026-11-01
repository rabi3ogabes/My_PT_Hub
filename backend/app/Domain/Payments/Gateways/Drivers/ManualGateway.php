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
use Illuminate\Http\Request;

/** Cash, bank transfer and other offline payments recorded by the coach. */
final class ManualGateway extends AbstractGateway
{
    public function driver(): string
    {
        return 'manual';
    }

    public function createCheckout(Order $order, string $successUrl, string $cancelUrl): CheckoutResult
    {
        // No hosted page: the order stays pending until the coach records the payment.
        return new CheckoutResult(null, 'manual_'.$order->id, ['instructions' => $this->credentials['instructions'] ?? null]);
    }

    public function refund(Payment $payment, int $amountMinor): RefundResult
    {
        return new RefundResult('succeeded', null);
    }

    public function parseWebhook(Request $request, GatewayModel $gateway): WebhookResult
    {
        throw new InvalidWebhookSignature('Manual gateways do not receive webhooks.');
    }

    public function supportedMethods(): array
    {
        return ['cash', 'bank_transfer', 'other'];
    }
}
