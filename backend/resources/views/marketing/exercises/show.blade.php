@php $muscleName = \App\Http\Web\Marketing\ExerciseController::muscleName($muscles, $exercise->primary_muscle); @endphp
<x-layouts::marketing :seo="$seo">
    @include('marketing.partials.page-hero', ['crumbs' => $seo->breadcrumbs, 'eyebrow' => $muscleName, 'title' => $name, 'lead' => __('marketing.exercises.show_lead', ['name' => $name, 'muscle' => $muscleName]), 'actions' => false])
    <section class="au-container grid items-start gap-12 py-16 lg:grid-cols-[1.1fr_1fr]">
        <div class="space-y-10">
            @if ($exercise->video_url && in_array($exercise->video_provider, ['hls', 'upload'], true))
                <video class="aspect-video w-full rounded-[24px] border border-border bg-black" controls preload="none" playsinline @if ($exercise->poster_url) poster="{{ $exercise->poster_url }}" @endif>
                    <source src="{{ $exercise->video_url }}" type="{{ str_ends_with($exercise->video_url, '.m3u8') ? 'application/x-mpegURL' : 'video/mp4' }}">
                </video>
            @elseif ($exercise->poster_url)
                <img src="{{ $exercise->poster_url }}" alt="{{ $name }}" width="1280" height="720" class="aspect-video w-full rounded-[24px] border border-border object-cover" fetchpriority="high">
            @endif

            <div>
                <h2 class="text-h2 text-text-primary">{{ __('marketing.exercises.how_to') }}</h2>
                @if ($steps)
                    <ol class="mt-6 space-y-4">
                        @foreach ($steps as $i => $step)
                            <li class="flex gap-4"><span class="flex size-8 shrink-0 items-center justify-center rounded-full border border-gold/30 bg-gold/10 text-caption font-bold text-gold tabular-nums">{{ $i + 1 }}</span><p class="pt-1 text-body text-text-secondary">{{ is_array($step) ? implode(' ', $step) : $step }}</p></li>
                        @endforeach
                    </ol>
                @else
                    <p class="mt-4 text-body text-text-secondary">{{ __('marketing.exercises.no_steps') }}</p>
                @endif
            </div>

            @if ($mistakes)
                <div>
                    <h2 class="text-h2 text-text-primary">{{ __('marketing.exercises.mistakes') }}</h2>
                    <ul class="mt-6 space-y-3">
                        @foreach ($mistakes as $m)<li class="flex gap-3 text-body text-text-secondary"><x-ui.icon name="x" :size="18" class="mt-1 text-danger" />{{ is_array($m) ? implode(' ', $m) : $m }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @if ($tips)
                <div>
                    <h2 class="text-h2 text-text-primary">{{ __('marketing.exercises.tips') }}</h2>
                    <ul class="mt-6 space-y-3">
                        @foreach ($tips as $t)<li class="flex gap-3 text-body text-text-secondary"><x-ui.icon name="check" :size="18" class="mt-1 text-gold" />{{ is_array($t) ? implode(' ', $t) : $t }}</li>@endforeach
                    </ul>
                </div>
            @endif
        </div>

        <aside class="space-y-4 lg:sticky lg:top-24">
            <div class="rounded-[24px] border border-border bg-surface p-6">
                <dl class="divide-y divide-border text-body-small">
                    <div class="flex justify-between gap-4 pb-3"><dt class="text-text-muted">{{ __('marketing.exercises.primary') }}</dt><dd class="font-semibold text-text-primary">{{ $muscleName }}</dd></div>
                    @if ($exercise->secondary_muscles)
                        <div class="flex justify-between gap-4 py-3"><dt class="text-text-muted">{{ __('marketing.exercises.secondary') }}</dt><dd class="text-end font-semibold text-text-primary">{{ collect($exercise->secondary_muscles)->map(fn ($m) => \App\Http\Web\Marketing\ExerciseController::muscleName($muscles, $m))->implode(', ') }}</dd></div>
                    @endif
                    <div class="flex justify-between gap-4 py-3"><dt class="text-text-muted">{{ __('marketing.exercises.equipment') }}</dt><dd class="text-end font-semibold text-text-primary">{{ $exercise->is_bodyweight || empty($exercise->equipment) ? __('marketing.exercises.bodyweight') : collect($exercise->equipment)->map(fn ($e) => $equipment->get($e)?->translate('name') ?? \Illuminate\Support\Str::headline($e))->implode(', ') }}</dd></div>
                    <div class="flex justify-between gap-4 py-3"><dt class="text-text-muted">{{ __('marketing.exercises.level') }}</dt><dd class="font-semibold text-text-primary">{{ __('marketing.exercises.levels.'.$exercise->level) }}</dd></div>
                    @if ($exercise->mechanics)<div class="flex justify-between gap-4 pt-3"><dt class="text-text-muted">{{ __('marketing.exercises.mechanics') }}</dt><dd class="font-semibold text-text-primary">{{ __('marketing.exercises.mechanics_values.'.$exercise->mechanics) }}</dd></div>@endif
                </dl>
            </div>
            @if ($exercise->alternatives->isNotEmpty())
                <div class="rounded-[24px] border border-border bg-surface p-6">
                    <h2 class="text-label text-text-primary">{{ __('marketing.exercises.alternatives') }}</h2>
                    <ul class="mt-3 space-y-1">
                        @foreach ($exercise->alternatives as $alt)
                            <li><a href="{{ route('marketing.exercises.show', ['slug' => $alt->slug]) }}" class="flex items-center justify-between rounded-lg px-2 py-2 text-body-small text-text-secondary hover:bg-surface-sunken hover:text-text-primary">{{ $alt->translate('name') }}<x-ui.icon name="chevron-right" :size="14" class="rtl:-scale-x-100" /></a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="rounded-[24px] border border-gold/25 bg-gold/[0.05] p-6">
                <p class="text-body-small font-semibold text-text-primary">{{ __('marketing.exercises.coach_cta') }}</p>
                <p class="mt-1 text-caption text-text-secondary">{{ __('marketing.exercises.coach_cta_text') }}</p>
                <x-ui.button :href="route('register')" size="sm" class="mt-4" icon-end="arrow-right">{{ __('marketing.cta.start_trial') }}</x-ui.button>
            </div>
        </aside>
    </section>
    @if ($related->isNotEmpty())
        <section class="au-container pb-8">
            <h2 class="text-h3 text-text-primary">{{ __('marketing.exercises.related', ['muscle' => $muscleName]) }}</h2>
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ($related as $r)<x-ui.chip :href="route('marketing.exercises.show', ['slug' => $r->slug])">{{ $r->translate('name') }}</x-ui.chip>@endforeach
            </div>
        </section>
    @endif
</x-layouts::marketing>
