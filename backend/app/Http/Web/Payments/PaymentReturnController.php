<?php

namespace App\Http\Web\Payments;

use App\Domain\Payments\Gateways\Drivers\HyperPayGateway;
use App\Domain\Payments\Gateways\GatewayManager;
use App\Domain\Payments\Gateways\HandlesReturn;
use App\Domain\Payments\Gateways\WebhookResult;
use App\Domain\Payments\Models\Order;
use App\Domain\Payments\Services\WebhookProcessor;
use App\Domain\Shared\Support\Money;
use App\Domain\Tenancy\Models\Tenant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/** Pages the shopper lands on after a hosted checkout (and the HyperPay widget page). */
class PaymentReturnController extends Controller
{
    public function show(Request $request, GatewayManager $gateways, WebhookProcessor $processor, string $order): View
    {
        $model = Order::withoutGlobalScopes()->findOrFail($order);
        $tenant = Tenant::findOrFail($model->tenant_id);
        app()->setLocale($model->meta['locale'] ?? $tenant->locale ?? app()->getLocale());

        if ($model->status === 'pending' && ! $request->boolean('cancelled') && $model->gateway) {
            $driver = $gateways->for($model->gateway);
            if ($driver instanceof HandlesReturn) {
                try {
                    $result = $driver->resultFromReturn($model, $request);
                    if ($result && $result->type !== WebhookResult::IGNORED && ($result->orderId === null || $result->orderId === $model->id)) {
                        $processor->process($result, $model->gateway);
                    }
                } catch (\Throwable $e) {
                    Log::warning('payments.return_check_failed', ['order' => $model->id, 'error' => $e->getMessage()]);
                }
                $model->refresh();
            }
        }

        $state = match (true) {
            $model->isPaid() => 'paid',
            $request->boolean('cancelled') => 'cancelled',
            $model->status === 'failed' => 'failed',
            default => 'pending',
        };

        return view('payments.return', ['state' => $state, 'business' => $tenant->name, 'brand' => $tenant->brand_color ?: '#D4AF6A']);
    }

    public function hyperpay(GatewayManager $gateways, string $order): View
    {
        $model = Order::withoutGlobalScopes()->findOrFail($order);
        abort_unless($model->status === 'pending' && $model->gateway && $model->gateway->driver === 'hyperpay' && $model->gateway_reference, 404);
        $tenant = Tenant::findOrFail($model->tenant_id);
        app()->setLocale($model->meta['locale'] ?? $tenant->locale ?? app()->getLocale());
        /** @var HyperPayGateway $driver */
        $driver = $gateways->for($model->gateway);

        return view('payments.hyperpay', [
            'business' => $tenant->name,
            'amount' => Money::format($model->total_minor, $model->currency),
            'widgetUrl' => $driver->base().'/v1/paymentWidgets.js?checkoutId='.urlencode((string) $model->gateway_reference),
            'resultUrl' => url('pay/return/'.$model->id),
            'brands' => implode(' ', array_filter([
                in_array($model->currency, ['SAR'], true) ? 'MADA' : null, 'VISA', 'MASTER', 'APPLEPAY',
            ])),
        ]);
    }
}
