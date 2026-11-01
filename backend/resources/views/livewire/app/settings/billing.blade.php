@php
    $fmt = fn (int $minor, string $currency) => \App\Domain\Shared\Support\Money::format($minor, $currency, app()->getLocale());
    $tz = auth()->user()->timezone ?: 'UTC';
@endphp
<div>
    @include('livewire.app.settings._header', ['title' => __('web.settings.billing'), 'lead' => __('web.billing.lead'), 'active' => 'billing'])

    {{-- Two-click cancel: done state --}}
    @if ($cancelled)
        <div class="mt-8 rounded-[24px] border border-border bg-surface p-6 sm:p-8" role="status">
            <div class="flex items-start gap-4">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-success-soft text-success"><x-ui.icon name="check" :size="22" :stroke="2.2" /></span>
                <div>
                    <h2 class="text-h3 text-text-primary">{{ __('web.billing.cancelled_title') }}</h2>
                    <p class="mt-2 text-body-small text-text-secondary">
                        @if ($endsAt && \Illuminate\Support\Carbon::parse($endsAt)->isFuture())
                            {{ __('web.billing.cancelled_access_until', ['date' => \Illuminate\Support\Carbon::parse($endsAt)->setTimezone($tz)->translatedFormat('j F Y')]) }}
                        @else
                            {{ __('web.billing.cancelled_now') }}
                        @endif
                        @if ($refundMinor > 0) {{ __('web.billing.refund', ['amount' => $fmt($refundMinor, $sub?->currency ?? 'USD')]) }}@endif
                    </p>
                    <p class="mt-2 text-caption text-text-muted">{{ __('web.billing.cancelled_email') }}</p>
                    @if ($sub?->cancel_at_period_end && $sub->current_period_end?->isFuture())
                        <x-ui.button class="mt-5" variant="secondary" wire:click="resume" loading="resume" icon="refresh">{{ __('web.billing.resume') }}</x-ui.button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Current plan --}}
    <section class="mt-8 grid gap-4 lg:grid-cols-[1.4fr_1fr]">
        <div class="relative overflow-hidden rounded-[24px] border border-border bg-surface p-6 sm:p-7">
            <div class="pointer-events-none absolute inset-0 au-grain opacity-70" aria-hidden="true"></div>
            <div class="relative">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="text-caption font-semibold text-text-muted">{{ __('web.billing.current_plan') }}</p>
                    @if ($sub)
                        <x-ui.badge :tone="match ($sub->status) { 'active' => 'success', 'trialing' => 'gold', 'past_due' => 'danger', default => 'neutral' }" :dot="true">{{ __('web.billing.status.'.$sub->status) }}</x-ui.badge>
                        @if ($sub->cancel_at_period_end)<x-ui.badge tone="warning">{{ __('web.billing.ending') }}</x-ui.badge>@endif
                    @endif
                </div>
                <h2 class="mt-3 text-h1 text-text-primary">{{ $currentPlan?->translate('name') ?? __('web.billing.free') }}</h2>
                <p class="mt-2 text-body-small text-text-secondary">
                    @if ($trialDays)
                        {{ trans_choice('web.billing.trial_left', $trialDays, ['days' => $trialDays, 'date' => $sub->trial_ends_at->setTimezone($tz)->translatedFormat('j F')]) }}
                    @elseif ($isActive && $sub->current_period_end)
                        {{ $sub->cancel_at_period_end ? __('web.billing.access_until', ['date' => $sub->current_period_end->setTimezone($tz)->translatedFormat('j F Y')]) : __('web.billing.renews', ['date' => $sub->current_period_end->setTimezone($tz)->translatedFormat('j F Y'), 'amount' => $fmt($sub->price_minor, $sub->currency), 'interval' => __('web.billing.interval.'.$sub->interval)]) }}
                    @else
                        {{ __('web.billing.free_text') }}
                    @endif
                </p>
                @if ($sub?->status === 'past_due')
                    <p class="mt-4 flex items-center gap-2 rounded-xl bg-danger-soft p-3 text-body-small text-danger"><x-ui.icon name="exclamation" :size="16" />{{ __('web.billing.past_due') }}</p>
                @endif
                @if ($canManage)
                    <div class="mt-6 flex flex-wrap gap-2">
                        @if ($isActive && ! $sub->cancel_at_period_end)
                            <x-ui.button variant="secondary" wire:click="askCancel" icon="x">{{ $sub->status === 'trialing' ? __('web.billing.cancel_trial') : __('web.billing.cancel') }}</x-ui.button>
                        @elseif ($sub?->cancel_at_period_end && $sub->current_period_end?->isFuture())
                            <x-ui.button wire:click="resume" loading="resume" icon="refresh">{{ __('web.billing.resume') }}</x-ui.button>
                        @endif
                        <x-ui.button variant="ghost" wire:click="portal" loading="portal" icon="external">{{ __('web.billing.portal') }}</x-ui.button>
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-[24px] border border-border bg-surface p-6 sm:p-7">
            <p class="text-caption font-semibold text-text-muted">{{ __('web.billing.payment_method') }}</p>
            @if ($sub?->card_last4)
                <div class="mt-4 flex items-center gap-3">
                    <span class="flex h-10 w-14 items-center justify-center rounded-lg border border-border bg-surface-sunken text-caption font-bold uppercase text-text-primary">{{ $sub->card_brand }}</span>
                    <p class="text-body-small font-semibold tabular-nums text-text-primary">•••• {{ $sub->card_last4 }}</p>
                </div>
                @if ($canManage)
                    <x-ui.button class="mt-5" size="sm" variant="ghost" icon="trash" wire:click="removeCard" wire:confirm="{{ __('web.billing.remove_card_confirm') }}">{{ __('web.billing.remove_card') }}</x-ui.button>
                @endif
            @else
                <p class="mt-4 text-body-small text-text-secondary">{{ __('web.billing.no_card') }}</p>
            @endif
            <ul class="mt-6 space-y-2 border-t border-border pt-5 text-caption text-text-muted">
                <li class="flex gap-2"><x-ui.icon name="bell" :size="14" class="mt-0.5 text-gold" />{{ __('web.billing.promise_reminders') }}</li>
                <li class="flex gap-2"><x-ui.icon name="shield" :size="14" class="mt-0.5 text-gold" />{{ __('web.billing.promise_cancel') }}</li>
                <li class="flex gap-2"><x-ui.icon name="refresh" :size="14" class="mt-0.5 text-gold" />{{ __('web.billing.promise_refund') }}</li>
            </ul>
        </div>
    </section>

    {{-- Plans --}}
    <section class="mt-12">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div><h2 class="text-h3 text-text-primary">{{ __('web.billing.plans') }}</h2><p class="mt-1 text-body-small text-text-muted">{{ __('web.billing.plans_lead') }}</p></div>
            <div class="inline-flex rounded-full border border-border bg-surface p-1" role="radiogroup" aria-label="{{ __('web.billing.interval_label') }}">
                @foreach (['month', 'year'] as $i)
                    <button type="button" role="radio" aria-checked="{{ $interval === $i ? 'true' : 'false' }}" wire:click="$set('interval', '{{ $i }}')" class="h-9 rounded-full px-4 text-body-small font-semibold transition {{ $interval === $i ? 'bg-gold text-on-gold' : 'text-text-secondary' }}">
                        {{ __('web.billing.interval_toggle.'.$i) }}@if ($i === 'year') <span class="ms-1 text-[11px] {{ $interval === 'year' ? '' : 'text-success' }}">{{ __('web.billing.save_annual') }}</span>@endif
                    </button>
                @endforeach
            </div>
        </div>
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            @foreach ($plans as $plan)
                @php
                    $isCurrent = $plan->code === ($tenant?->plan ?? 'free') && (! $sub || $sub->interval === $interval || $plan->code === 'free');
                    $price = $interval === 'year' ? (int) round($plan->annual_price_minor / 12) : $plan->monthly_price_minor;
                @endphp
                <div wire:key="plan-{{ $plan->code }}" class="flex flex-col rounded-[22px] border p-6 transition {{ $isCurrent ? 'border-gold/60 bg-gold/[0.04]' : 'border-border bg-surface' }}">
                    <div class="flex items-center justify-between">
                        <p class="text-title text-text-primary">{{ $plan->translate('name') }}</p>
                        @if ($isCurrent)<x-ui.badge tone="gold">{{ __('web.billing.current') }}</x-ui.badge>@endif
                    </div>
                    <p class="mt-4"><span class="text-[2rem] font-extrabold tracking-[-0.04em] tabular-nums text-text-primary">{{ $fmt($price, $plan->currency) }}</span> <span class="text-caption text-text-muted">{{ __('marketing.pricing.per_month') }}</span></p>
                    <p class="mt-1 h-4 text-caption text-text-muted">@if ($interval === 'year' && $plan->annual_price_minor > 0){{ __('web.billing.billed_yearly', ['amount' => $fmt($plan->annual_price_minor, $plan->currency)]) }}@endif</p>
                    <ul class="mt-5 flex-1 space-y-2">
                        @foreach (array_slice((array) __('marketing.plans.'.$plan->code.'.features'), 0, 5) as $line)
                            <li class="flex gap-2 text-caption text-text-secondary"><x-ui.icon name="check" :size="14" class="mt-0.5 text-gold" />{{ $line }}</li>
                        @endforeach
                    </ul>
                    @if ($canManage && ! $isCurrent)
                        <x-ui.button class="mt-6" :variant="$plan->code === 'pro' ? 'primary' : 'secondary'" :block="true" wire:click="subscribe('{{ $plan->code }}')" loading="subscribe('{{ $plan->code }}')">
                            {{ $plan->monthly_price_minor === 0 ? __('web.billing.downgrade') : __('web.billing.choose', ['plan' => $plan->translate('name')]) }}
                        </x-ui.button>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    {{-- Invoices --}}
    <section class="mt-12">
        <h2 class="text-h3 text-text-primary">{{ __('web.billing.invoices') }}</h2>
        @if ($invoices->isEmpty())
            <x-ui.empty-state class="mt-4" :compact="true" icon="receipt" :title="__('web.billing.invoices_empty')" :description="__('web.billing.invoices_empty_text')" />
        @else
            <x-ui.table class="mt-4" :headers="[__('web.billing.col_number'), __('web.billing.col_date'), __('web.billing.col_description'), __('web.billing.col_amount'), __('web.billing.col_status')]">
                @foreach ($invoices as $invoice)
                    <tr wire:key="inv-{{ $invoice->id }}">
                        <td class="font-mono text-caption !text-text-primary">{{ $invoice->number }}</td>
                        <td class="tabular-nums">{{ ($invoice->paid_at ?? $invoice->created_at)?->setTimezone($tz)->translatedFormat('j M Y') }}</td>
                        <td>{{ $invoice->description }}</td>
                        <td class="font-semibold tabular-nums !text-text-primary">{{ $fmt((int) $invoice->amount_minor, $invoice->currency) }}</td>
                        <td><x-ui.badge :tone="match ($invoice->status) { 'paid' => 'success', 'refunded' => 'info', 'open' => 'warning', default => 'neutral' }">{{ __('web.billing.invoice_status.'.$invoice->status) }}</x-ui.badge></td>
                    </tr>
                @endforeach
            </x-ui.table>
        @endif
    </section>

    {{-- Click 2: confirm --}}
    <x-ui.modal wire:model="confirmingCancel" :title="$sub?->status === 'trialing' ? __('web.billing.confirm_trial_title') : __('web.billing.confirm_title')" icon="exclamation" tone="danger">
        <p class="text-body-small text-text-secondary">
            @if ($sub?->status === 'trialing')
                {{ __('web.billing.confirm_trial_text') }}
            @elseif ($sub?->interval === 'year')
                {{ __('web.billing.confirm_annual_text', ['amount' => $fmt(app(\App\Domain\Billing\Services\SaasBillingService::class)->proRataRefund($sub), $sub->currency)]) }}
            @elseif ($sub?->current_period_end)
                {{ __('web.billing.confirm_monthly_text', ['date' => $sub->current_period_end->setTimezone($tz)->translatedFormat('j F Y')]) }}
            @endif
        </p>
        <x-ui.select wire:model="reason" class="mt-5" :label="__('web.billing.reason')" :optional="true" :placeholder="__('web.billing.reason_placeholder')" :options="collect($reasons)->mapWithKeys(fn ($r) => [$r => __('web.billing.reasons.'.$r)])->all()" />
        <x-slot:footer>
            <x-ui.button variant="secondary" @click="open = false">{{ __('web.billing.keep') }}</x-ui.button>
            <x-ui.button variant="danger" wire:click="cancel" loading="cancel">{{ __('web.billing.confirm_cancel') }}</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
