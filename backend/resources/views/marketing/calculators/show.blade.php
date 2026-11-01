@php $k = 'marketing.calculators.items.'.$calculator; @endphp
<x-layouts::marketing :seo="$seo">
    @include('marketing.partials.page-hero', ['crumbs' => $seo->breadcrumbs, 'icon' => 'calculator', 'eyebrow' => __($k.'.audience'), 'title' => __($k.'.title'), 'lead' => __($k.'.lead'), 'actions' => false])

    <section class="au-container grid items-start gap-12 py-20 lg:grid-cols-[1.05fr_1fr]">
        <div x-data="{{ $component }}" class="rounded-[28px] border border-border bg-surface p-6 shadow-e2 sm:p-8 lg:sticky lg:top-24">
            @include('marketing.calculators.widgets.'.$calculator, ['k' => $k])
            <p class="mt-6 border-t border-border pt-4 text-caption text-text-muted">{{ __('marketing.calculators.disclaimer') }}</p>
        </div>
        <article class="au-prose">
            <h2 class="!mt-0">{{ __($k.'.how_title') }}</h2>
            @foreach ((array) __($k.'.explain') as $p)<p>{{ $p }}</p>@endforeach
            <h3>{{ __('marketing.calculators.formula') }}</h3>
            <p><code class="rounded-md bg-surface-sunken px-2 py-1 font-mono text-[0.85em] text-text-primary">{{ __($k.'.formula') }}</code></p>
            <h3>{{ __('marketing.calculators.steps') }}</h3>
            <ol>@foreach ($steps as $s)<li>{{ $s }}</li>@endforeach</ol>
        </article>
    </section>

    <section class="au-container pb-8">
        <div class="flex flex-col items-start justify-between gap-6 rounded-[28px] border border-gold/25 bg-gold/[0.04] p-8 sm:flex-row sm:items-center">
            <div>
                <p class="text-title text-text-primary">{{ __('marketing.calculators.coach_cta_title') }}</p>
                <p class="mt-1 text-body-small text-text-secondary">{{ __('marketing.calculators.coach_cta_text') }}</p>
            </div>
            <x-ui.button :href="route('register')" icon-end="arrow-right">{{ __('marketing.cta.start_trial') }}</x-ui.button>
        </div>
        <div class="mt-10 flex flex-wrap justify-center gap-2">
            @foreach (\App\View\Marketing\Catalog::CALCULATORS as $slug => $c)
                @continue($slug === $calculator)
                <x-ui.chip :href="route('marketing.calculators.show', ['calculator' => $slug])">{{ __('marketing.calculators.items.'.$slug.'.name') }}</x-ui.chip>
            @endforeach
        </div>
    </section>

    @include('marketing.partials.faq', ['faq' => $faq])
</x-layouts::marketing>
