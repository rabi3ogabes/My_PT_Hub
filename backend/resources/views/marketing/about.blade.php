<x-layouts::marketing :seo="$seo">
    @include('marketing.partials.page-hero', ['crumbs' => $seo->breadcrumbs, 'eyebrow' => __('marketing.about.eyebrow'), 'title' => __('marketing.about.title'), 'lead' => __('marketing.about.lead'), 'actions' => false])
    <section class="au-container grid gap-16 py-24 lg:grid-cols-[1fr_1.2fr]">
        <div>
            <p class="au-eyebrow">{{ __('marketing.about.story_eyebrow') }}</p>
            <h2 class="mt-4 text-h1 text-text-primary">{{ __('marketing.about.story_title') }}</h2>
        </div>
        <div class="au-prose">
            @foreach ((array) __('marketing.about.story') as $p)<p>{{ $p }}</p>@endforeach
        </div>
    </section>
    <section class="border-y border-border bg-surface/40 py-24">
        <div class="au-container">
            @include('marketing.partials.section-heading', ['eyebrow' => __('marketing.about.values_eyebrow'), 'title' => __('marketing.about.values_title')])
            <div class="mt-12 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @foreach ((array) __('marketing.about.values') as $value)
                    <div class="rounded-[24px] border border-border bg-surface p-7">
                        <h3 class="text-title text-text-primary">{{ $value['t'] }}</h3>
                        <p class="mt-2 text-body-small leading-relaxed text-text-secondary">{{ $value['d'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <section class="au-container py-24">
        @include('marketing.partials.section-heading', ['eyebrow' => __('marketing.about.markets_eyebrow'), 'title' => __('marketing.about.markets_title'), 'lead' => __('marketing.about.markets_lead')])
        <div class="mt-10 flex flex-wrap justify-center gap-2">
            @foreach (['doha', 'riyadh', 'dubai', 'paris', 'casablanca'] as $city)
                <x-ui.chip :href="route('marketing.coaches.city', ['city' => $city])" icon="map-pin">{{ __('marketing.coaches.city_names.'.$city) }}</x-ui.chip>
            @endforeach
        </div>
    </section>
    @include('marketing.partials.final-cta')
</x-layouts::marketing>
