@php
    $limitKeys = ['clients', 'coaches', 'ai_requests_month', 'video_mb'];
    $featureKeys = collect($plans)->flatMap(fn ($p) => $p->features ?? [])->unique()->values()->all();
    $config = ['currency' => $currency, 'rates' => \App\View\Marketing\Catalog::CURRENCIES, 'locale' => app()->getLocale()];
@endphp
<x-layouts::marketing :seo="$seo">
    <div x-data="pricing(@js($config))">
        <section class="relative overflow-hidden border-b border-border">
            <div class="pointer-events-none absolute inset-0 au-grain" aria-hidden="true"></div>
            <div class="au-container relative pb-20 pt-20 text-center lg:pt-28">
                <p class="au-eyebrow">{{ __('marketing.pricing.eyebrow') }}</p>
                <h1 class="mx-auto mt-4 max-w-3xl text-[clamp(2.25rem,1.5rem+3vw,4rem)] leading-[1.03] font-extrabold tracking-[-0.04em] text-text-primary">{{ __('marketing.pricing.title') }}</h1>
                <p class="mx-auto mt-6 max-w-2xl text-[1.125rem] text-text-secondary">{{ __('marketing.pricing.lead') }}</p>

                <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                    <div class="inline-flex rounded-full border border-border bg-surface p-1" role="radiogroup" aria-label="{{ __('marketing.pricing.interval') }}">
                        <button type="button" role="radio" :aria-checked="(interval === 'month').toString()" @click="interval = 'month'" class="h-9 rounded-full px-5 text-body-small font-semibold transition" :class="interval === 'month' ? 'bg-gold text-on-gold' : 'text-text-secondary'">{{ __('marketing.pricing.monthly') }}</button>
                        <button type="button" role="radio" :aria-checked="(interval === 'year').toString()" @click="interval = 'year'" class="inline-flex h-9 items-center gap-2 rounded-full px-5 text-body-small font-semibold transition" :class="interval === 'year' ? 'bg-gold text-on-gold' : 'text-text-secondary'">
                            {{ __('marketing.pricing.annual') }} <span class="rounded-full px-1.5 py-px text-[10px] font-bold" :class="interval === 'year' ? 'bg-on-gold/15' : 'bg-success-soft text-success'">{{ __('marketing.pricing.save') }}</span>
                        </button>
                    </div>
                    <label class="inline-flex items-center gap-2 text-body-small text-text-secondary">
                        <span>{{ __('marketing.pricing.currency') }}</span>
                        <select x-model="currency" class="h-10 rounded-full border border-border bg-surface px-4 text-body-small font-semibold text-text-primary focus:border-gold focus:outline-none">
                            @foreach (array_keys(\App\View\Marketing\Catalog::CURRENCIES) as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <p class="mt-3 text-caption text-text-muted" x-show="currency !== 'USD'" x-cloak>{{ __('marketing.pricing.currency_note') }}</p>
            </div>
        </section>

        {{-- Plan cards --}}
        <section class="au-container -mt-px py-20">
            <div class="grid gap-4 {{ [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2'][$plans->count()] ?? 'lg:grid-cols-3' }}">
                @foreach ($plans as $plan)
                    @php $featured = $plan->code === 'pro'; @endphp
                    <div class="relative flex flex-col rounded-[28px] border p-8 {{ $featured ? 'border-gold/50 bg-surface au-glow' : 'border-border bg-surface' }}">
                        @if ($featured)<span class="absolute -top-3 start-8 rounded-full bg-gold px-3 py-0.5 text-[11px] font-bold text-on-gold">{{ __('marketing.pricing.popular') }}</span>@endif
                        <h2 class="text-h3 text-text-primary">{{ $plan->translate('name') }}</h2>
                        <p class="mt-1.5 text-body-small text-text-muted">{{ __('marketing.plans.'.$plan->code.'.tagline') }}</p>
                        <div class="mt-8 flex items-baseline gap-2">
                            <span class="text-[3rem] leading-none font-extrabold tracking-[-0.045em] text-text-primary tabular-nums" x-text="price({{ $plan->monthly_price_minor }}, {{ $plan->annual_price_minor }})">{{ \App\Domain\Shared\Support\Money::format($plan->monthly_price_minor, $plan->currency, app()->getLocale()) }}</span>
                            <span class="text-body-small text-text-muted">{{ __('marketing.pricing.per_month') }}</span>
                        </div>
                        <p class="mt-2 h-5 text-caption text-text-muted">
                            @if ($plan->annual_price_minor > 0)
                                <span x-show="interval === 'year'" x-cloak>{{ __('marketing.pricing.billed_yearly') }} <span x-text="yearly({{ $plan->annual_price_minor }})"></span></span>
                                <span x-show="interval === 'month'">{{ __('marketing.pricing.billed_monthly') }}</span>
                            @else
                                {{ __('marketing.pricing.free_forever') }}
                            @endif
                        </p>
                        <x-ui.button :href="route('register')" :variant="$featured ? 'primary' : 'secondary'" class="mt-8" :block="true">
                            {{ $plan->trial_days > 0 ? __('marketing.pricing.cta_trial', ['days' => $plan->trial_days]) : __('marketing.pricing.cta_free') }}
                        </x-ui.button>
                        <ul class="mt-8 space-y-3 border-t border-border pt-8">
                            @foreach ((array) __('marketing.plans.'.$plan->code.'.features') as $line)
                                <li class="flex gap-3 text-body-small text-text-secondary"><x-ui.icon name="check" :size="16" :stroke="2.2" class="mt-0.5 text-gold" />{{ $line }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex flex-col items-start justify-between gap-4 rounded-[24px] border border-border bg-surface p-7 sm:flex-row sm:items-center">
                <div>
                    <p class="text-title text-text-primary">{{ __('marketing.plans.enterprise.title') }}</p>
                    <p class="mt-1 text-body-small text-text-secondary">{{ __('marketing.plans.enterprise.text') }}</p>
                </div>
                <x-ui.button :href="route('marketing.contact')" variant="secondary" icon-end="arrow-right">{{ __('marketing.cta.talk_to_us') }}</x-ui.button>
            </div>
        </section>

        {{-- Promise --}}
        <section class="au-container pb-20">
            <div class="grid gap-4 md:grid-cols-3">
                @foreach (['cancel' => 'shield', 'reminders' => 'bell', 'refund' => 'refresh'] as $k => $icon)
                    <div class="rounded-[24px] border border-gold/20 bg-gold/[0.04] p-7">
                        <x-ui.icon :name="$icon" :size="22" class="text-gold" />
                        <h3 class="mt-5 text-title text-text-primary">{{ __('marketing.pricing.promise.'.$k.'.title') }}</h3>
                        <p class="mt-2 text-body-small leading-relaxed text-text-secondary">{{ __('marketing.pricing.promise.'.$k.'.text') }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Comparison table --}}
        <section class="au-container pb-8">
            @include('marketing.partials.section-heading', ['eyebrow' => __('marketing.pricing.compare_eyebrow'), 'title' => __('marketing.pricing.compare_title')])
            <div class="mt-12 overflow-x-auto rounded-[24px] border border-border bg-surface">
                <table class="w-full min-w-[640px] text-body-small">
                    <caption class="sr-only">{{ __('marketing.pricing.compare_title') }}</caption>
                    <thead>
                        <tr class="border-b border-border">
                            <th scope="col" class="px-6 py-4 text-start text-caption font-semibold text-text-muted">{{ __('marketing.pricing.feature') }}</th>
                            @foreach ($plans as $plan)
                                <th scope="col" class="px-6 py-4 text-center text-body-small font-bold {{ $plan->code === 'pro' ? 'text-gold' : 'text-text-primary' }}">{{ $plan->translate('name') }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($limitKeys as $key)
                            <tr>
                                <th scope="row" class="px-6 py-3.5 text-start font-medium text-text-secondary">{{ __('marketing.pricing.limits.'.$key) }}</th>
                                @foreach ($plans as $plan)
                                    @php $v = $plan->limit($key); @endphp
                                    <td class="px-6 py-3.5 text-center font-semibold tabular-nums text-text-primary">{{ $v === null ? __('marketing.pricing.unlimited') : ($key === 'video_mb' ? number_format($v / 1000, $v < 1000 ? 1 : 0).' GB' : number_format($v)) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        @foreach ($featureKeys as $key)
                            <tr>
                                <th scope="row" class="px-6 py-3.5 text-start font-medium text-text-secondary">{{ __('marketing.pricing.features_labels.'.$key) }}</th>
                                @foreach ($plans as $plan)
                                    <td class="px-6 py-3.5 text-center">
                                        @if ($plan->hasFeature($key))
                                            <x-ui.icon name="check" :size="18" :stroke="2.2" class="mx-auto text-gold" /><span class="sr-only">{{ __('marketing.compare.values.yes') }}</span>
                                        @else
                                            <x-ui.icon name="minus" :size="16" class="mx-auto text-text-muted" /><span class="sr-only">{{ __('marketing.compare.values.no') }}</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @include('marketing.partials.faq', ['faq' => $faq])
    @include('marketing.partials.final-cta')
</x-layouts::marketing>
