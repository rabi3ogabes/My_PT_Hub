<x-layouts::marketing :seo="$seo">
    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 au-grain" aria-hidden="true"></div>
        <div class="pointer-events-none absolute inset-x-0 top-0 h-[38rem] au-grid-lines opacity-70" aria-hidden="true"></div>
        <div class="au-container relative grid items-center gap-16 pb-24 pt-14 lg:grid-cols-[1.02fr_1fr] lg:pb-32 lg:pt-24">
            <div class="animate-fade-up">
                <a href="{{ route('marketing.features.show', ['module' => 'ai-copilot']) }}" class="group inline-flex items-center gap-2 rounded-full border border-gold/30 bg-gold/[0.07] py-1 pe-3 ps-1 text-caption font-semibold text-text-primary transition hover:border-gold/60">
                    <span class="rounded-full bg-gold px-2 py-0.5 text-[11px] font-bold text-on-gold">{{ __('marketing.home.badge_new') }}</span>
                    {{ __('marketing.home.badge') }}
                    <x-ui.icon name="arrow-right" :size="13" class="text-gold transition group-hover:translate-x-0.5 rtl:-scale-x-100" />
                </a>
                <h1 class="mt-7 text-hero text-text-primary">
                    {{ __('marketing.home.title_a') }} <span class="au-gold-text">{{ __('marketing.home.title_b') }}</span>
                </h1>
                <p class="mt-7 max-w-xl text-[1.1875rem] leading-relaxed text-text-secondary">{{ __('marketing.home.lead') }}</p>
                <div class="mt-10 flex flex-wrap items-center gap-3">
                    <x-ui.button :href="route('register')" size="lg" icon-end="arrow-right">{{ __('marketing.cta.start_trial') }}</x-ui.button>
                    <x-ui.button :href="route('marketing.pricing')" size="lg" variant="secondary">{{ __('marketing.cta.see_pricing') }}</x-ui.button>
                </div>
                <ul class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-caption text-text-muted">
                    @foreach (['no_card', 'cancel', 'languages'] as $t)
                        <li class="flex items-center gap-1.5"><x-ui.icon name="check" :size="14" class="text-gold" />{{ __('marketing.home.trust.'.$t) }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="lg:ps-6">
                @include('marketing.partials.product-mock')
            </div>
        </div>
    </section>

    {{-- Proof strip (real numbers only) --}}
    <section class="border-y border-border bg-surface/60">
        <div class="au-container grid grid-cols-2 gap-8 py-10 md:grid-cols-4">
            <div><p class="text-[2rem] font-extrabold tracking-[-0.03em] text-text-primary">4</p><p class="mt-1 text-caption text-text-muted">{{ __('marketing.home.proof.languages') }}</p></div>
            <div><p class="text-[2rem] font-extrabold tracking-[-0.03em] text-text-primary">{{ $stats['exercises'] > 0 ? number_format($stats['exercises']) : '9' }}</p><p class="mt-1 text-caption text-text-muted">{{ $stats['exercises'] > 0 ? __('marketing.home.proof.exercises') : __('marketing.home.proof.modules') }}</p></div>
            <div><p class="text-[2rem] font-extrabold tracking-[-0.03em] text-text-primary">2</p><p class="mt-1 text-caption text-text-muted">{{ __('marketing.home.proof.clicks') }}</p></div>
            <div><p class="text-[2rem] font-extrabold tracking-[-0.03em] text-text-primary">0</p><p class="mt-1 text-caption text-text-muted">{{ __('marketing.home.proof.addons') }}</p></div>
        </div>
        <div class="au-container flex flex-wrap items-center justify-center gap-x-10 gap-y-3 border-t border-border py-6 text-body-small font-semibold tracking-[0.02em] text-text-muted">
            <span class="text-caption font-medium">{{ __('marketing.home.proof.markets') }}</span>
            @foreach (['doha', 'riyadh', 'dubai', 'paris', 'casablanca'] as $city)
                <a href="{{ route('marketing.coaches.city', ['city' => $city]) }}" class="transition hover:text-text-primary">{{ __('marketing.coaches.city_names.'.$city) }}</a>
            @endforeach
        </div>
    </section>

    {{-- Why switch --}}
    <section class="au-container py-28">
        @include('marketing.partials.section-heading', ['eyebrow' => __('marketing.home.why.eyebrow'), 'title' => __('marketing.home.why.title'), 'lead' => __('marketing.home.why.lead')])
        <div class="mt-16 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @foreach (['speed' => 'bolt', 'ai' => 'sparkles', 'languages' => 'language', 'billing' => 'shield'] as $k => $icon)
                <x-ui.card :interactive="true" class="group relative overflow-hidden">
                    <span class="flex size-11 items-center justify-center rounded-xl border border-gold/25 bg-gold/10 text-gold transition group-hover:shadow-[0_0_30px_-6px_rgba(212,175,106,0.6)]"><x-ui.icon :name="$icon" :size="20" /></span>
                    <h3 class="mt-6 text-title text-text-primary">{{ __('marketing.home.why.items.'.$k.'.title') }}</h3>
                    <p class="mt-2.5 text-body-small leading-relaxed text-text-secondary">{{ __('marketing.home.why.items.'.$k.'.text') }}</p>
                </x-ui.card>
            @endforeach
        </div>
    </section>

    {{-- Interactive feature demos --}}
    <section class="border-y border-border bg-surface/40 py-28" x-data="{ tab: 'workouts' }">
        <div class="au-container">
            @include('marketing.partials.section-heading', ['eyebrow' => __('marketing.home.demos.eyebrow'), 'title' => __('marketing.home.demos.title'), 'lead' => __('marketing.home.demos.lead')])
            <div class="mt-12 flex flex-wrap justify-center gap-2" role="tablist" aria-label="{{ __('marketing.home.demos.eyebrow') }}">
                @foreach (['workouts', 'nutrition', 'ai-copilot', 'check-ins'] as $m)
                    <button type="button" role="tab" @click="tab = '{{ $m }}'" :aria-selected="(tab === '{{ $m }}').toString()" class="inline-flex h-10 items-center gap-2 rounded-full border px-4 text-body-small font-semibold transition"
                        :class="tab === '{{ $m }}' ? 'border-gold/60 bg-gold/10 text-gold' : 'border-border text-text-secondary hover:text-text-primary'">
                        <x-ui.icon :name="\App\View\Marketing\Catalog::FEATURES[$m]" :size="16" />{{ __('marketing.features.items.'.$m.'.name') }}
                    </button>
                @endforeach
            </div>
            @foreach (['workouts', 'nutrition', 'ai-copilot', 'check-ins'] as $m)
                <div x-show="tab === '{{ $m }}'" @if (! $loop->first) x-cloak @endif role="tabpanel" class="mt-12 grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <h3 class="text-h2 text-text-primary">{{ __('marketing.features.items.'.$m.'.title') }}</h3>
                        <p class="mt-4 text-body text-text-secondary">{{ __('marketing.features.items.'.$m.'.lead') }}</p>
                        <ul class="mt-7 space-y-3">
                            @foreach (array_slice((array) __('marketing.features.items.'.$m.'.points'), 0, 3) as $point)
                                <li class="flex gap-3 text-body-small text-text-secondary"><span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-gold/15 text-gold"><x-ui.icon name="check" :size="12" :stroke="2.5" /></span><span><strong class="font-semibold text-text-primary">{{ $point['t'] }}.</strong> {{ $point['d'] }}</span></li>
                            @endforeach
                        </ul>
                        <a href="{{ route('marketing.features.show', ['module' => $m]) }}" class="mt-8 inline-flex items-center gap-1.5 text-body-small font-semibold text-gold hover:underline hover:underline-offset-4">{{ __('marketing.cta.learn_more') }} <x-ui.icon name="arrow-right" :size="15" class="rtl:-scale-x-100" /></a>
                    </div>
                    @include('marketing.partials.demo', ['demo' => $m])
                </div>
            @endforeach
        </div>
    </section>

    {{-- All modules --}}
    <section class="au-container py-28">
        @include('marketing.partials.section-heading', ['eyebrow' => __('marketing.home.modules.eyebrow'), 'title' => __('marketing.home.modules.title')])
        <div class="mt-14 grid gap-px overflow-hidden rounded-[28px] border border-border bg-border sm:grid-cols-2 lg:grid-cols-3">
            @foreach (\App\View\Marketing\Catalog::FEATURES as $slug => $icon)
                <a href="{{ route('marketing.features.show', ['module' => $slug]) }}" class="group bg-surface p-7 transition hover:bg-surface-raised">
                    <div class="flex items-center justify-between">
                        <x-ui.icon :name="$icon" :size="22" class="text-gold" />
                        <x-ui.icon name="arrow-up-right" :size="16" class="text-text-muted opacity-0 transition group-hover:opacity-100 rtl:-scale-x-100" />
                    </div>
                    <h3 class="mt-6 text-title text-text-primary">{{ __('marketing.features.items.'.$slug.'.name') }}</h3>
                    <p class="mt-2 text-body-small text-text-secondary">{{ __('marketing.features.items.'.$slug.'.short') }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Social proof: real approved client reviews of coaches on the platform, else our promises --}}
    <section class="border-y border-border bg-surface/40 py-28">
        <div class="au-container">
            @if ($reviews->isNotEmpty())
                @include('marketing.partials.section-heading', ['eyebrow' => __('marketing.home.reviews.eyebrow'), 'title' => __('marketing.home.reviews.title')])
                <div class="mt-14 grid gap-4 lg:grid-cols-3">
                    @foreach ($reviews as $review)
                        <figure class="flex flex-col rounded-[24px] border border-border bg-surface p-7">
                            <div class="flex gap-0.5 text-gold" aria-label="{{ __('marketing.coaches.rating', ['rating' => $review->rating]) }}">
                                @for ($s = 0; $s < $review->rating; $s++)<x-ui.icon name="star" :size="16" class="fill-current" />@endfor
                            </div>
                            <blockquote class="mt-5 flex-1 text-body leading-relaxed text-text-primary">“{{ \Illuminate\Support\Str::limit($review->body, 260) }}”</blockquote>
                            <figcaption class="mt-6 flex items-center gap-3 border-t border-border pt-5 text-caption text-text-muted">
                                <x-ui.avatar :name="$review->coachProfile?->user?->name" size="sm" />
                                <span>{{ \Illuminate\Support\Str::of($review->client?->name)->before(' ') }} · {{ __('marketing.home.reviews.coached_by') }}
                                    <a class="font-semibold text-text-secondary hover:text-text-primary" href="{{ route('marketing.coaches.show', ['slug' => $review->coachProfile?->slug]) }}">{{ $review->coachProfile?->user?->name }}</a></span>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            @else
                @include('marketing.partials.section-heading', ['eyebrow' => __('marketing.home.promises.eyebrow'), 'title' => __('marketing.home.promises.title')])
                <div class="mt-14 grid gap-4 lg:grid-cols-3">
                    @foreach (['cancel', 'data', 'ai'] as $k)
                        <figure class="flex flex-col rounded-[24px] border border-border bg-surface p-7">
                            <x-ui.icon name="shield" :size="22" class="text-gold" />
                            <blockquote class="mt-5 flex-1 text-[1.0625rem] leading-relaxed text-text-primary">“{{ __('marketing.home.promises.items.'.$k) }}”</blockquote>
                            <figcaption class="mt-6 border-t border-border pt-5 text-caption text-text-muted">{{ __('marketing.home.promises.by', ['brand' => config('brand.name')]) }}</figcaption>
                        </figure>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Pricing teaser --}}
    <section class="au-container py-28">
        @include('marketing.partials.section-heading', ['eyebrow' => __('marketing.home.pricing.eyebrow'), 'title' => __('marketing.home.pricing.title'), 'lead' => __('marketing.home.pricing.lead')])
        <div class="mx-auto mt-14 grid max-w-5xl gap-4 md:grid-cols-3">
            @foreach ($plans->take(3) as $plan)
                <div class="relative rounded-[24px] border p-7 {{ $plan->code === 'pro' ? 'border-gold/50 bg-surface au-glow' : 'border-border bg-surface' }}">
                    @if ($plan->code === 'pro')<span class="absolute -top-3 start-7 rounded-full bg-gold px-2.5 py-0.5 text-[11px] font-bold text-on-gold">{{ __('marketing.pricing.popular') }}</span>@endif
                    <p class="text-title text-text-primary">{{ $plan->translate('name') }}</p>
                    <p class="mt-1 text-caption text-text-muted">{{ __('marketing.plans.'.$plan->code.'.tagline') }}</p>
                    <p class="mt-6"><span class="text-[2.5rem] font-extrabold tracking-[-0.04em] text-text-primary">{{ \App\Domain\Shared\Support\Money::format($plan->monthly_price_minor, $plan->currency, app()->getLocale()) }}</span> <span class="text-caption text-text-muted">{{ __('marketing.pricing.per_month') }}</span></p>
                </div>
            @endforeach
        </div>
        <div class="mt-10 text-center">
            <x-ui.button :href="route('marketing.pricing')" variant="secondary" icon-end="arrow-right">{{ __('marketing.cta.compare_plans') }}</x-ui.button>
        </div>
    </section>

    @include('marketing.partials.final-cta')
</x-layouts::marketing>
