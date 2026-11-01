<x-layouts::marketing :seo="$seo">
    @include('marketing.partials.page-hero', ['crumbs' => $seo->breadcrumbs, 'icon' => 'bolt', 'eyebrow' => __('marketing.exercises.eyebrow'), 'title' => __('marketing.exercises.title'), 'lead' => __('marketing.exercises.lead'), 'actions' => false])
    <section class="au-container py-16">
        <form method="GET" action="{{ route('marketing.exercises.index') }}" class="flex flex-col gap-3 sm:flex-row" role="search">
            <label class="relative flex-1">
                <span class="sr-only">{{ __('marketing.exercises.search') }}</span>
                <span class="pointer-events-none absolute inset-y-0 start-4 flex items-center text-text-muted"><x-ui.icon name="search" :size="18" /></span>
                <input type="search" name="q" value="{{ $q }}" placeholder="{{ __('marketing.exercises.search') }}" class="h-12 w-full rounded-xl border border-border bg-surface ps-11 pe-4 text-body text-text-primary placeholder:text-text-muted focus:border-gold focus:outline-none focus:ring-4 focus:ring-gold/15">
            </label>
            @if ($muscle)<input type="hidden" name="muscle" value="{{ $muscle }}">@endif
            <x-ui.button type="submit" size="lg">{{ __('marketing.exercises.search_cta') }}</x-ui.button>
        </form>
        @if ($muscles->isNotEmpty())
            <div class="mt-5 flex flex-wrap gap-2">
                <x-ui.chip :href="route('marketing.exercises.index')" :active="$muscle === ''">{{ __('marketing.exercises.all') }}</x-ui.chip>
                @foreach ($muscles as $key => $m)
                    <x-ui.chip :href="route('marketing.exercises.index', ['muscle' => $key])" :active="$muscle === $key">{{ $m->translate('name') }}</x-ui.chip>
                @endforeach
            </div>
        @endif

        @if ($exercises->isEmpty())
            <x-ui.empty-state class="mt-12" icon="bolt" :title="$q !== '' ? __('marketing.exercises.empty_search', ['q' => $q]) : __('marketing.exercises.empty_title')" :description="__('marketing.exercises.empty_text')">
                @if ($q !== '' || $muscle !== '')<x-ui.button :href="route('marketing.exercises.index')" variant="secondary">{{ __('marketing.exercises.clear') }}</x-ui.button>@endif
                <x-ui.button :href="route('marketing.calculators.index')">{{ __('marketing.nav.res.calculators') }}</x-ui.button>
            </x-ui.empty-state>
        @else
            <ul class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($exercises as $exercise)
                    <li>
                        <a href="{{ route('marketing.exercises.show', ['slug' => $exercise->slug]) }}" class="group flex items-center gap-4 rounded-[20px] border border-border bg-surface p-4 transition hover:border-border-strong hover:shadow-e2">
                            @if ($exercise->poster_url)
                                <img src="{{ $exercise->poster_url }}" alt="" width="64" height="64" loading="lazy" decoding="async" class="size-16 shrink-0 rounded-xl object-cover">
                            @else
                                <span class="flex size-16 shrink-0 items-center justify-center rounded-xl bg-surface-sunken text-gold"><x-ui.icon name="dumbbell" :size="22" /></span>
                            @endif
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-body-small font-semibold text-text-primary">{{ $exercise->translate('name') }}</span>
                                <span class="mt-0.5 block truncate text-caption text-text-muted">{{ \App\Http\Web\Marketing\ExerciseController::muscleName($muscles, $exercise->primary_muscle) }} · {{ __('marketing.exercises.levels.'.$exercise->level) }}</span>
                            </span>
                            <x-ui.icon name="chevron-right" :size="16" class="text-text-muted transition group-hover:text-gold rtl:-scale-x-100" />
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="mt-10">{{ $exercises->onEachSide(1)->links('components.ui.pagination') }}</div>
        @endif
    </section>
    @include('marketing.partials.final-cta')
</x-layouts::marketing>
