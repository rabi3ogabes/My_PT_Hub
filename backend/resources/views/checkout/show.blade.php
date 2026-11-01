<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ \App\View\Locales::dir() }}" class="dark">
<head>
    @include('layouts.partials.head', ['csrf' => false])
    <title>{{ $name }} · {{ $tenant->name }}</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($description), 150) }}">
    <style>:root { --au-brand: {{ $color }}; }</style>
</head>
<body class="min-h-dvh bg-background font-sans text-text-primary antialiased">
<div x-data="checkoutPage(@js($config), @js([
        'generic' => __('web.checkout.error_generic'),
        'interval' => ['day' => __('web.checkout.per.day'), 'week' => __('web.checkout.per.week'), 'month' => __('web.checkout.per.month'), 'year' => __('web.checkout.per.year')],
        'once' => __('web.checkout.once'),
    ]))" class="relative min-h-dvh">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[28rem]" style="background: radial-gradient(50rem 22rem at 50% -10%, {{ $color }}40, transparent 70%)" aria-hidden="true"></div>

    <header class="au-container relative flex items-center justify-between py-6">
        <div class="flex items-center gap-3">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="" class="size-10 rounded-xl object-cover ring-1 ring-border">
            @else
                <span class="flex size-10 items-center justify-center rounded-xl text-title font-extrabold text-[#0B0B0F]" style="background: {{ $color }}">{{ mb_strtoupper(mb_substr($tenant->name, 0, 1)) }}</span>
            @endif
            <span class="text-title font-bold tracking-[-0.02em]">{{ $tenant->name }}</span>
        </div>
        <div class="flex items-center gap-1">
            @include('layouts.partials.locale-menu', ['links' => collect(\App\View\Locales::all())->mapWithKeys(fn ($l) => [$l => request()->fullUrlWithQuery(['lang' => $l])])->all(), 'compact' => true])
            @include('layouts.partials.theme-toggle')
        </div>
    </header>

    <main class="au-container relative grid gap-8 pb-20 pt-6 lg:grid-cols-[1.1fr_1fr] lg:gap-14 lg:pt-12">
        <section>
            <p class="au-eyebrow" style="color: {{ $color }}">{{ __('web.checkout.eyebrow') }}</p>
            <h1 class="mt-3 text-[clamp(2rem,1.5rem+2vw,3rem)] leading-[1.05] font-extrabold tracking-[-0.035em]">{{ $name }}</h1>
            @if ($description)<div class="au-prose mt-5">{!! nl2br(e($description)) !!}</div>@endif
            @if ($product->image_url)<img src="{{ $product->image_url }}" alt="" class="mt-8 aspect-video w-full rounded-[24px] border border-border object-cover" loading="lazy">@endif
            <ul class="mt-8 space-y-3 text-body-small text-text-secondary">
                <li class="flex items-center gap-3"><x-ui.icon name="lock" :size="16" style="color: {{ $color }}" />{{ __('web.checkout.secure') }}</li>
                <li class="flex items-center gap-3"><x-ui.icon name="phone" :size="16" style="color: {{ $color }}" />{{ __('web.checkout.app_access') }}</li>
                <li class="flex items-center gap-3"><x-ui.icon name="receipt" :size="16" style="color: {{ $color }}" />{{ __('web.checkout.receipt') }}</li>
            </ul>
        </section>

        <section class="rounded-[28px] border border-border bg-surface p-6 shadow-e3 sm:p-8" aria-live="polite">
            {{-- Loading --}}
            <div x-show="state === 'loading'" class="space-y-4" aria-busy="true">
                <div class="au-skeleton h-6 w-1/2"></div><div class="au-skeleton h-16"></div><div class="au-skeleton h-12"></div><div class="au-skeleton h-12"></div>
            </div>

            {{-- Form --}}
            <form x-show="state === 'form'" x-cloak @submit.prevent="pay" class="space-y-5">
                <fieldset x-show="quotes.length > 1">
                    <legend class="text-label text-text-secondary">{{ __('web.checkout.choose_option') }}</legend>
                    <div class="mt-2 grid gap-2">
                        <template x-for="q in quotes" :key="q.price_id">
                            <label class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border p-4 transition" :class="priceId === q.price_id ? 'border-[var(--au-brand)] bg-[color-mix(in_oklab,var(--au-brand)_8%,transparent)]' : 'border-border'">
                                <span class="flex items-center gap-3"><input type="radio" name="price" :value="q.price_id" x-model="priceId" class="size-4 accent-[var(--au-brand)]"><span class="text-body-small font-semibold" x-text="label(q)"></span></span>
                                <span class="text-body-small font-bold tabular-nums" x-text="money(q.total_minor, q.currency)"></span>
                            </label>
                        </template>
                    </div>
                </fieldset>

                <div class="rounded-2xl bg-surface-sunken p-4" x-show="quote">
                    <dl class="space-y-1.5 text-body-small">
                        <div class="flex justify-between"><dt class="text-text-secondary" x-text="label(quote || {})"></dt><dd class="tabular-nums" x-text="quote && money(quote.subtotal_minor, quote.currency)"></dd></div>
                        <div class="flex justify-between text-success" x-show="quote && quote.discount_minor > 0"><dt>{{ __('web.checkout.discount') }} <span x-text="quote && quote.coupon ? '(' + quote.coupon + ')' : ''"></span></dt><dd class="tabular-nums" x-text="quote && '−' + money(quote.discount_minor, quote.currency)"></dd></div>
                        <div class="flex justify-between" x-show="quote && quote.tax_minor > 0"><dt class="text-text-secondary"><span x-text="quote && quote.tax_inclusive ? @js(__('web.checkout.tax_included')) : @js(__('web.checkout.tax'))"></span> (<span x-text="quote && quote.tax_rate"></span>%)</dt><dd class="tabular-nums" x-text="quote && money(quote.tax_minor, quote.currency)"></dd></div>
                        <div class="flex justify-between border-t border-border pt-2 text-title font-bold"><dt>{{ __('web.checkout.total') }}</dt><dd class="tabular-nums" x-text="quote && money(quote.total_minor, quote.currency)"></dd></div>
                    </dl>
                </div>

                <x-ui.input id="co-name" :label="__('web.common.name')" x-model="name" required maxlength="120" autocomplete="name" />
                <x-ui.input id="co-email" type="email" :label="__('web.common.email')" x-model="email" required maxlength="190" autocomplete="email" :hint="__('web.checkout.email_hint')" />
                <x-ui.input id="co-phone" type="tel" :label="__('web.checkout.phone')" x-model="phone" maxlength="32" autocomplete="tel" :optional="true" />

                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="text-body-small font-semibold text-text-muted hover:text-text-primary" x-show="!open">{{ __('web.checkout.have_coupon') }}</button>
                    <div x-show="open" x-cloak class="flex gap-2">
                        <input x-model="coupon" maxlength="32" class="h-12 flex-1 rounded-xl border border-border bg-surface px-4 font-mono uppercase text-text-primary focus:border-[var(--au-brand)] focus:outline-none" placeholder="{{ __('web.checkout.coupon') }}" aria-label="{{ __('web.checkout.coupon') }}">
                        <x-ui.button variant="secondary" class="h-12" @click="load()">{{ __('web.checkout.apply') }}</x-ui.button>
                    </div>
                </div>

                <p x-show="error" x-text="error" class="rounded-xl bg-danger-soft p-3 text-caption text-danger" role="alert"></p>

                <button type="submit" :disabled="busy || !quote" class="flex h-13 w-full items-center justify-center gap-2 rounded-[14px] text-[15px] font-bold text-[#0B0B0F] transition hover:brightness-110 disabled:opacity-60" style="background: {{ $color }}; box-shadow: 0 12px 34px -10px {{ $color }}">
                    <span x-show="!busy">{{ __('web.checkout.pay') }} <span x-text="quote ? money(quote.total_minor, quote.currency) : ''"></span></span>
                    <span x-show="busy" x-cloak>{{ __('web.checkout.redirecting') }}</span>
                </button>
                <p class="text-center text-caption text-text-muted">{{ __('web.checkout.terms', ['business' => $tenant->name]) }}</p>
            </form>

            {{-- Waiting for confirmation --}}
            <div x-show="state === 'polling'" x-cloak class="flex flex-col items-center py-10 text-center">
                <svg class="size-10 animate-spin" style="color: {{ $color }}" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".2" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                <h2 class="mt-6 text-h3">{{ __('web.checkout.confirming') }}</h2>
                <p class="mt-2 max-w-xs text-body-small text-text-secondary">{{ __('web.checkout.confirming_text') }}</p>
            </div>

            {{-- Paid --}}
            <div x-show="state === 'paid'" x-cloak class="flex flex-col items-center py-8 text-center">
                <span class="flex size-14 items-center justify-center rounded-2xl bg-success-soft text-success"><x-ui.icon name="check" :size="26" :stroke="2.4" /></span>
                <h2 class="mt-6 text-h2">{{ __('web.checkout.paid_title') }}</h2>
                <p class="mt-2 max-w-sm text-body-small text-text-secondary">{{ __('web.checkout.paid_text', ['business' => $tenant->name]) }}</p>
                <div class="mt-6 flex flex-wrap justify-center gap-2">@include('marketing.partials.store-badges', ['compact' => true])</div>
            </div>

            {{-- Failed / cancelled / slow --}}
            <div x-show="state === 'failed' || state === 'pending'" x-cloak class="flex flex-col items-center py-8 text-center">
                <span class="flex size-14 items-center justify-center rounded-2xl" :class="state === 'failed' ? 'bg-danger-soft text-danger' : 'bg-warning-soft text-warning'"><x-ui.icon name="exclamation" :size="26" /></span>
                <h2 class="mt-6 text-h3" x-text="state === 'failed' ? @js(__('web.checkout.failed_title')) : @js(__('web.checkout.pending_title'))"></h2>
                <p class="mt-2 max-w-sm text-body-small text-text-secondary" x-text="state === 'failed' ? @js(__('web.checkout.failed_text')) : @js(__('web.checkout.pending_text'))"></p>
                <x-ui.button class="mt-6" variant="secondary" @click="reset()">{{ __('web.checkout.try_again') }}</x-ui.button>
            </div>

            {{-- Unavailable --}}
            <div x-show="state === 'error'" x-cloak class="py-8 text-center">
                <h2 class="text-h3">{{ __('web.checkout.unavailable') }}</h2>
                <p class="mt-2 text-body-small text-text-secondary" x-text="error"></p>
            </div>
        </section>
    </main>
    <footer class="au-container pb-8 text-center text-caption text-text-muted">{{ __('web.checkout.powered', ['brand' => config('brand.name')]) }}</footer>
</div>
</body>
</html>
