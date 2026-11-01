<x-layouts::marketing :seo="$seo">
    @include('marketing.partials.page-hero', ['crumbs' => $seo->breadcrumbs, 'eyebrow' => __('marketing.features.eyebrow'), 'title' => __('marketing.features.title'), 'lead' => __('marketing.features.lead')])
    <section class="au-container py-24">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach (\App\View\Marketing\Catalog::FEATURES as $slug => $icon)
                <a href="{{ route('marketing.features.show', ['module' => $slug]) }}" class="group flex flex-col rounded-[24px] border border-border bg-surface p-7 transition duration-200 hover:-translate-y-0.5 hover:border-border-strong hover:shadow-e2">
                    <span class="flex size-11 items-center justify-center rounded-xl border border-gold/25 bg-gold/10 text-gold"><x-ui.icon :name="$icon" :size="20" /></span>
                    <h2 class="mt-6 text-title text-text-primary">{{ __('marketing.features.items.'.$slug.'.name') }}</h2>
                    <p class="mt-2 flex-1 text-body-small leading-relaxed text-text-secondary">{{ __('marketing.features.items.'.$slug.'.lead') }}</p>
                    <span class="mt-6 inline-flex items-center gap-1.5 text-body-small font-semibold text-gold">{{ __('marketing.cta.learn_more') }} <x-ui.icon name="arrow-right" :size="15" class="transition group-hover:translate-x-0.5 rtl:-scale-x-100" /></span>
                </a>
            @endforeach
        </div>
    </section>
    @include('marketing.partials.final-cta')
</x-layouts::marketing>
