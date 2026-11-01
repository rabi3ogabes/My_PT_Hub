<?php

namespace App\Http\Api\V1\Tenancy;

use App\Domain\Billing\Models\SaasInvoice;
use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Billing\Services\SaasBillingService;
use App\Domain\Shared\Support\Money;
use App\Http\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Coach → platform billing. Cancel is ONE API call after the app's confirm (two taps total). */
class BillingController extends ApiController
{
    public function __construct(private readonly SaasBillingService $billing) {}

    public function show(): JsonResponse
    {
        $tenant = $this->tenant();
        $sub = $tenant->subscription()->with('plan')->first();

        return $this->ok([
            'plan' => $tenant->plan,
            'subscription' => $sub ? [
                'id' => $sub->id,
                'plan' => $sub->plan?->code,
                'status' => $sub->status,
                'interval' => $sub->interval,
                'price' => ['amount_minor' => $sub->price_minor, 'currency' => $sub->currency, 'formatted' => Money::format($sub->price_minor, $sub->currency)],
                'trial_ends_at' => $sub->trial_ends_at?->toIso8601String(),
                'current_period_end' => $sub->current_period_end?->toIso8601String(),
                'cancel_at_period_end' => $sub->cancel_at_period_end,
                'card' => $sub->card_last4 ? ['brand' => $sub->card_brand, 'last4' => $sub->card_last4] : null,
                'refund_if_cancelled_now' => $sub->interval === 'year' && $sub->status === 'active' ? $this->billing->proRataRefund($sub) : 0,
            ] : null,
            'plans' => SaasPlan::query()->where('is_public', true)->orderBy('sort')->get()->map(fn (SaasPlan $p) => [
                'code' => $p->code,
                'name' => $p->translate('name'),
                'monthly' => ['amount_minor' => $p->monthly_price_minor, 'currency' => $p->currency, 'formatted' => Money::format($p->monthly_price_minor, $p->currency)],
                'annual' => ['amount_minor' => $p->annual_price_minor, 'currency' => $p->currency, 'formatted' => Money::format($p->annual_price_minor, $p->currency)],
                'limits' => $p->limits,
                'features' => $p->features,
                'trial_days' => $p->trial_days,
            ]),
            'invoices' => SaasInvoice::query()->where('tenant_id', $tenant->id)->latest()->limit(24)->get()->map(fn ($i) => [
                'id' => $i->id, 'number' => $i->number, 'status' => $i->status, 'description' => $i->description,
                'amount' => ['amount_minor' => $i->amount_minor, 'currency' => $i->currency, 'formatted' => Money::format($i->amount_minor, $i->currency)],
                'paid_at' => $i->paid_at?->toIso8601String(), 'created_at' => $i->created_at?->toIso8601String(),
            ]),
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plan' => ['required', Rule::exists('saas_plans', 'code')],
            'interval' => ['required', Rule::in(['month', 'year'])],
            'success_url' => ['nullable', 'url'],
            'cancel_url' => ['nullable', 'url'],
        ]);
        $result = $this->billing->subscribe(
            $this->tenant(), $data['plan'], $data['interval'],
            $data['success_url'] ?? url('/app/settings/billing?status=success'),
            $data['cancel_url'] ?? url('/app/settings/billing?status=cancelled'),
        );

        return $this->ok(['checkout_url' => $result['checkout_url'], 'plan' => $this->tenant()->refresh()->plan]);
    }

    public function cancel(Request $request): JsonResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:300']]);
        $result = $this->billing->cancel($this->tenant(), $data['reason'] ?? null);

        return $this->ok(['ends_at' => $result['ends_at']?->toIso8601String(), 'refund_minor' => $result['refund_minor']]);
    }

    public function resume(): JsonResponse
    {
        $this->billing->resume($this->tenant());

        return $this->show();
    }

    public function removeCard(): JsonResponse
    {
        $this->billing->removeCard($this->tenant());

        return $this->noContent();
    }

    public function portal(): JsonResponse
    {
        return $this->ok(['url' => $this->billing->portalUrl($this->tenant(), url('/app/settings/billing'))]);
    }
}
