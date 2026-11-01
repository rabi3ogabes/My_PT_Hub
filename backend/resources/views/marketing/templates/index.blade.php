<x-layouts::marketing :seo="$seo">
    @include('marketing.partials.page-hero', ['crumbs' => $seo->breadcrumbs, 'icon' => 'document', 'eyebrow' => __('marketing.templates.eyebrow'), 'title' => __('marketing.templates.title'), 'lead' => __('marketing.templates.lead'), 'actions' => false])
    <section class="au-container py-16">
        @if ($templates->isEmpty())
            <x-ui.empty-state icon="document" :title="__('marketing.templates.empty_title')" :description="__('marketing.templates.empty_text')">
                <x-ui.button :href="route('marketing.calculators.index')" variant="secondary">{{ __('marketing.nav.res.calculators') }}</x-ui.button>
                <x-ui.button :href="route('register')">{{ __('marketing.templates.empty_cta') }}</x-ui.button>
            </x-ui.empty-state>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($templates as $program)
                    <a href="{{ route('marketing.templates.show', ['slug' => $program->slug]) }}" class="group flex flex-col overflow-hidden rounded-[24px] border border-border bg-surface transition hover:-translate-y-0.5 hover:border-border-strong hover:shadow-e2">
                        <div class="relative aspect-[16/9] overflow-hidden bg-[#0B0B0F]">
                            @if ($program->cover_url)
                                <img src="{{ $program->cover_url }}" alt="" loading="lazy" decoding="async" width="640" height="360" class="size-full object-cover opacity-90 transition duration-500 group-hover:scale-[1.03]">
                            @else
                                <div class="absolute inset-0 bg-[radial-gradient(30rem_14rem_at_100%_0%,rgba(212,175,106,0.3),transparent_60%)]"></div>
                                <x-ui.icon name="layers" :size="40" class="absolute bottom-5 start-5 text-[#D4AF6A]" />
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <div class="flex flex-wrap gap-2">
                                <x-ui.badge tone="gold">{{ __('marketing.templates.days_per_week', ['n' => $program->days_per_week]) }}</x-ui.badge>
                                <x-ui.badge>{{ __('marketing.exercises.levels.'.$program->level) }}</x-ui.badge>
                            </div>
                            <h2 class="mt-4 text-title text-text-primary">{{ $program->translate('name') }}</h2>
                            <p class="mt-2 line-clamp-2 flex-1 text-body-small text-text-secondary">{{ $program->translate('description') }}</p>
                            <span class="mt-5 inline-flex items-center gap-1.5 text-body-small font-semibold text-gold">{{ __('marketing.templates.get') }} <x-ui.icon name="download" :size="15" /></span>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-10">{{ $templates->links('components.ui.pagination') }}</div>
        @endif
    </section>
    @include('marketing.partials.final-cta')
</x-layouts::marketing>
