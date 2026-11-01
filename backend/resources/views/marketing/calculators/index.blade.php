<x-layouts::marketing :seo="$seo">
    @include('marketing.partials.page-hero', ['crumbs' => $seo->breadcrumbs, 'icon' => 'calculator', 'eyebrow' => __('marketing.calculators.eyebrow'), 'title' => __('marketing.calculators.title'), 'lead' => __('marketing.calculators.lead'), 'actions' => false])
    <section class="au-container py-20">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (\App\View\Marketing\Catalog::CALCULATORS as $slug => $c)
                <a href="{{ route('marketing.calculators.show', ['calculator' => $slug]) }}" class="group rounded-[24px] border border-border bg-surface p-7 transition hover:-translate-y-0.5 hover:border-border-strong hover:shadow-e2">
                    <p class="au-eyebrow">{{ __('marketing.calculators.items.'.$slug.'.audience') }}</p>
                    <h2 class="mt-3 text-title text-text-primary">{{ __('marketing.calculators.items.'.$slug.'.name') }}</h2>
                    <p class="mt-2 text-body-small leading-relaxed text-text-secondary">{{ __('marketing.calculators.items.'.$slug.'.lead') }}</p>
                    <span class="mt-6 inline-flex items-center gap-1.5 text-body-small font-semibold text-gold">{{ __('marketing.calculators.open') }} <x-ui.icon name="arrow-right" :size="15" class="transition group-hover:translate-x-0.5 rtl:-scale-x-100" /></span>
                </a>
            @endforeach
        </div>
    </section>
    @include('marketing.partials.final-cta')
</x-layouts::marketing>
