@php $k = 'marketing.features.items.'.$module; @endphp
<x-layouts::marketing :seo="$seo">
    @include('marketing.partials.page-hero', ['crumbs' => $seo->breadcrumbs, 'icon' => $icon, 'eyebrow' => __($k.'.name'), 'title' => __($k.'.title'), 'lead' => __($k.'.lead')])

    <section class="au-container grid items-start gap-14 py-24 lg:grid-cols-[1fr_1.05fr]">
        <div class="lg:sticky lg:top-28">
            <p class="au-eyebrow">{{ __('marketing.features.how_eyebrow') }}</p>
            <h2 class="mt-4 text-h1 text-text-primary">{{ __($k.'.how_title') }}</h2>
            <p class="mt-4 text-body text-text-secondary">{{ __($k.'.how_lead') }}</p>
        </div>
        @include('marketing.partials.demo', ['demo' => $module])
    </section>

    <section class="border-y border-border bg-surface/40 py-24">
        <div class="au-container grid gap-4 md:grid-cols-2">
            @foreach ((array) __($k.'.points') as $point)
                <div class="rounded-[24px] border border-border bg-surface p-7">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-gold/10 text-gold"><x-ui.icon name="check" :size="16" :stroke="2.2" /></span>
                    <h3 class="mt-5 text-title text-text-primary">{{ $point['t'] }}</h3>
                    <p class="mt-2 text-body-small leading-relaxed text-text-secondary">{{ $point['d'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="au-container py-24">
        @include('marketing.partials.section-heading', ['eyebrow' => __('marketing.features.more_eyebrow'), 'title' => __('marketing.features.more_title')])
        <div class="mt-12 flex flex-wrap justify-center gap-2">
            @foreach (\App\View\Marketing\Catalog::FEATURES as $slug => $i)
                @continue($slug === $module)
                <x-ui.chip :href="route('marketing.features.show', ['module' => $slug])" :icon="$i">{{ __('marketing.features.items.'.$slug.'.name') }}</x-ui.chip>
            @endforeach
        </div>
    </section>

    @include('marketing.partials.faq', ['faq' => $faq])
    @include('marketing.partials.final-cta')
</x-layouts::marketing>
