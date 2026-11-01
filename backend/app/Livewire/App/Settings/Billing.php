<?php

namespace App\Livewire\App\Settings;

use App\Domain\Billing\Models\SaasInvoice;
use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Billing\Services\SaasBillingService;
use App\Domain\Shared\Support\ApiException;
use App\Livewire\App\Concerns\WithTenant;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Plan & billing with the "billing trust" rules: transparent trial, plan
 * switch, TWO-CLICK cancel (button → confirm → done), resume, remove card,
 * invoices and the gateway portal.
 */
#[Layout('layouts.app')]
class Billing extends Component
{
    use WithTenant;

    public const REASONS = ['price', 'missing_feature', 'switching', 'not_using', 'other'];

    public string $interval = 'month';

    public bool $confirmingCancel = false;

    public string $reason = '';

    public bool $cancelled = false;

    public ?string $endsAt = null;

    public int $refundMinor = 0;

    public function mount(): void
    {
        $this->interval = $this->tenant()->subscription?->interval === 'year' ? 'year' : 'month';
        if (request()->query('checkout') === 'success') {
            $this->toast(__('web.billing.checkout_success'));
        }
    }

    public function subscribe(string $plan, SaasBillingService $billing): mixed
    {
        abort_unless($this->isOwner(), 403);
        validator(['plan' => $plan, 'interval' => $this->interval], [
            'plan' => ['required', Rule::exists('saas_plans', 'code')],
            'interval' => ['required', Rule::in(['month', 'year'])],
        ])->validate();
        try {
            $result = $billing->subscribe(
                $this->tenant(), $plan, $this->interval,
                route('app.settings.billing', ['checkout' => 'success']),
                route('app.settings.billing', ['checkout' => 'cancelled']),
            );
        } catch (ApiException $e) {
            $this->toast($e->getMessage(), 'error');

            return null;
        }
        if ($result['checkout_url']) {
            return $this->redirect($result['checkout_url']);
        }
        $this->cancelled = false;
        $this->toast(__('web.billing.plan_changed'));

        return null;
    }

    /** Click 1 of 2. */
    public function askCancel(): void
    {
        abort_unless($this->isOwner(), 403);
        $this->reason = '';
        $this->confirmingCancel = true;
    }

    /** Click 2 of 2. */
    public function cancel(SaasBillingService $billing): void
    {
        abort_unless($this->isOwner(), 403);
        $this->validate(['reason' => ['nullable', Rule::in(self::REASONS)]]);
        try {
            $result = $billing->cancel($this->tenant(), $this->reason ?: null);
        } catch (ApiException $e) {
            $this->confirmingCancel = false;
            $this->toast($e->getMessage(), 'error');

            return;
        }
        $this->confirmingCancel = false;
        $this->cancelled = true;
        $this->endsAt = $result['ends_at']?->toIso8601String();
        $this->refundMinor = (int) $result['refund_minor'];
    }

    public function resume(SaasBillingService $billing): void
    {
        abort_unless($this->isOwner(), 403);
        try {
            $billing->resume($this->tenant());
        } catch (ApiException $e) {
            $this->toast($e->getMessage(), 'error');

            return;
        }
        $this->cancelled = false;
        $this->toast(__('web.billing.resumed'));
    }

    public function removeCard(SaasBillingService $billing): void
    {
        abort_unless($this->isOwner(), 403);
        $billing->removeCard($this->tenant());
        $this->toast(__('web.billing.card_removed'));
    }

    public function portal(SaasBillingService $billing): mixed
    {
        abort_unless($this->isOwner(), 403);
        $url = $billing->portalUrl($this->tenant(), route('app.settings.billing'));
        if (! $url) {
            $this->toast(__('web.billing.portal_unavailable'), 'info');

            return null;
        }

        return $this->redirect($url);
    }

    public function render()
    {
        $tenant = $this->tenant()->fresh();
        $sub = $tenant?->subscription()->with('plan')->first();
        $plans = SaasPlan::query()->where('is_public', true)->orderBy('sort')->get();
        $trialDays = $sub && $sub->status === 'trialing' && $sub->trial_ends_at?->isFuture()
            ? max(1, (int) ceil(now()->diffInHours($sub->trial_ends_at) / 24)) : null;

        return view('livewire.app.settings.billing', [
            'tenant' => $tenant,
            'sub' => $sub,
            'isActive' => $sub && $sub->isActive(),
            'currentPlan' => $plans->firstWhere('code', $tenant?->plan) ?? $plans->firstWhere('code', 'free'),
            'plans' => $plans,
            'trialDays' => $trialDays,
            'invoices' => SaasInvoice::query()->where('tenant_id', $tenant?->id)->latest()->limit(24)->get(),
            'canManage' => $this->isOwner(),
            'reasons' => self::REASONS,
        ])->title(__('web.settings.billing'));
    }
}
