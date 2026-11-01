<?php

namespace App\Http\Web\Payments;

use App\Domain\Billing\Gateways\SaasGateway;
use App\Domain\Billing\Services\SaasBillingService;
use App\Domain\Payments\Gateways\GatewayManager;
use App\Domain\Payments\Gateways\InvalidWebhookSignature;
use App\Domain\Payments\Models\PaymentGateway;
use App\Domain\Payments\Services\WebhookProcessor;
use App\Domain\Shared\Support\ApiException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/** Gateway → Aurum webhooks. Signatures are verified by each driver; processing is idempotent. */
class PaymentWebhookController extends Controller
{
    public function gateway(Request $request, WebhookProcessor $processor, string $driver, string $gateway): JsonResponse
    {
        if (! array_key_exists($driver, GatewayManager::DRIVERS) || $driver === 'manual') {
            return response()->json(['ok' => false, 'error' => 'unknown_driver'], 404);
        }
        $model = PaymentGateway::withoutGlobalScopes()->whereKey($gateway)->where('driver', $driver)->first();
        if (! $model) {
            return response()->json(['ok' => false, 'error' => 'unknown_gateway'], 404);
        }

        try {
            $result = $processor->handle($request, $model);
        } catch (InvalidWebhookSignature $e) {
            Log::warning('payments.webhook_rejected', ['driver' => $driver, 'gateway' => $gateway, 'ip' => $request->ip()]);

            return response()->json(['ok' => false, 'error' => 'invalid_signature'], 400);
        } catch (ApiException $e) {
            return response()->json(['ok' => false, 'error' => $e->errorCode], $e->status >= 500 ? 500 : 422);
        }

        return response()->json(['ok' => true] + $result);
    }

    public function stripeSaas(Request $request, SaasBillingService $billing): JsonResponse
    {
        try {
            $event = app(SaasGateway::class)->verifyWebhook($request->getContent(), $request->header('Stripe-Signature'));
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => 'invalid_signature'], 400);
        }
        $billing->handleWebhook($event);

        return response()->json(['ok' => true]);
    }
}
