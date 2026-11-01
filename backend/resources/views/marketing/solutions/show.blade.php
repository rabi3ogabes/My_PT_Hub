@php $k = 'marketing.solutions.items.'.$segment; @endphp
<x-layouts::marketing :seo="$seo">
    @include('marketing.partials.page-hero', ['crumbs' => $seo->breadcrumbs, 'icon' => $icon, 'eyebrow' => __('marketing.solutions.eyebrow', ['segment' => __($k.'.name')]), 'title' => __($k.'.title'), 'lead' => __($k.'.lead')])

    <section class="au-container py-24">
        <div class="grid gap-14 lg:grid-cols-2">
            <div>
                <p class="au-eyebrow">{{ __('marketing.solutions.pains_eyebrow') }}</p>
                <h2 class="mt-4 text-h1 text-text-primary">{{ __('marketing.solutions.pains_title') }}</h2>
                <ul class="mt-8 space-y-4">
                    @foreach ((array) __($k.'.pains') as $pain)
                        <li class="flex gap-3 text-body text-text-secondary"><x-ui.icon name="x" :size="18" class="mt-1 text-danger" />{{ $pain }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="space-y-4">
                @foreach ((array) __($k.'.points') as $point)
                    <div class="rounded-[24px] border border-border bg-surface p-6">
                        <h3 class="flex items-center gap-2.5 text-title text-text-primary"><x-ui.icon name="check-circle" :size="20" class="text-gold" />{{ $point['t'] }}</h3>
                        <p class="mt-2 text-body-small leading-relaxed text-text-secondary">{{ $point['d'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-y border-border bg-surface/40 py-24">
        <div class="au-container">
            @include('marketing.partials.section-heading', ['eyebrow' => __('marketing.solutions.stack_eyebrow'), 'title' => __('marketing.solutions.stack_title')])
            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ((array) __($k.'.modules') as $slug)
                    <a href="{{ route('marketing.features.show', ['module' => $slug]) }}" class="group rounded-[22px] border border-border bg-surface p-6 transition hover:border-border-strong">
                        <x-ui.icon :name="\App\View\Marketing\Catalog::FEATURES[$slug] ?? 'sparkles'" :size="20" class="text-gold" />
                        <p class="mt-4 text-body-small font-semibold text-text-primary">{{ __('marketing.features.items.'.$slug.'.name') }}</p>
                        <p class="mt-1 text-caption text-text-muted">{{ __('marketing.features.items.'.$slug.'.short') }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @include('marketing.partials.final-cta', ['title' => __($k.'.cta')])
</x-layouts::marketing>
