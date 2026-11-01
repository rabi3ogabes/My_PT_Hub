{{-- Inner page hero: $eyebrow, $title, $lead, optional $icon, $actions (bool) --}}
<section class="relative overflow-hidden border-b border-border">
    <div class="pointer-events-none absolute inset-0 au-grain" aria-hidden="true"></div>
    <div class="pointer-events-none absolute inset-0 au-grid-lines opacity-60" aria-hidden="true"></div>
    <div class="au-container relative py-20 lg:py-28">
        @if (! empty($crumbs))
            <nav aria-label="{{ __('marketing.nav.breadcrumbs') }}" class="mb-8 text-caption text-text-muted">
                <ol class="flex flex-wrap items-center gap-1.5">
                    @foreach ($crumbs as $i => [$label, $url])
                        <li class="flex items-center gap-1.5">
                            @if ($i > 0)<x-ui.icon name="chevron-right" :size="12" class="rtl:-scale-x-100" />@endif
                            @if ($loop->last)<span aria-current="page" class="text-text-secondary">{{ $label }}</span>@else<a href="{{ $url }}" class="hover:text-text-primary">{{ $label }}</a>@endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif
        <div class="max-w-3xl animate-fade-up">
            @if (! empty($icon))
                <span class="mb-6 flex size-12 items-center justify-center rounded-2xl border border-gold/30 bg-gold/10 text-gold shadow-[0_0_40px_-10px_rgba(212,175,106,0.6)]"><x-ui.icon :name="$icon" :size="22" /></span>
            @endif
            @if (! empty($eyebrow))<p class="au-eyebrow">{{ $eyebrow }}</p>@endif
            <h1 class="mt-4 text-[clamp(2.25rem,1.5rem+3vw,4rem)] leading-[1.03] font-extrabold tracking-[-0.04em] text-text-primary">{{ $title }}</h1>
            @if (! empty($lead))<p class="mt-6 max-w-2xl text-[1.125rem] leading-relaxed text-text-secondary">{{ $lead }}</p>@endif
            @if ($actions ?? true)
                <div class="mt-9 flex flex-wrap items-center gap-3">
                    <x-ui.button :href="route('register')" size="lg" icon-end="arrow-right">{{ __('marketing.cta.start_trial') }}</x-ui.button>
                    <x-ui.button :href="route('marketing.pricing')" size="lg" variant="secondary">{{ __('marketing.cta.see_pricing') }}</x-ui.button>
                </div>
                <p class="mt-4 text-caption text-text-muted">{{ __('marketing.cta.trust') }}</p>
            @endif
        </div>
    </div>
</section>
