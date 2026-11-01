<x-layouts::marketing :seo="$seo">
    @include('marketing.partials.page-hero', ['crumbs' => $seo->breadcrumbs, 'icon' => 'map-pin', 'eyebrow' => __('marketing.coaches.eyebrow'), 'title' => $city ? __('marketing.coaches.city_title', ['city' => $city]) : __('marketing.coaches.title'), 'lead' => $city ? __('marketing.coaches.city_description', ['city' => $city]) : __('marketing.coaches.lead'), 'actions' => false])
    <section class="au-container py-14">
        @unless ($city)
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <label class="block"><span class="text-label text-text-secondary">{{ __('marketing.coaches.filter_language') }}</span>
                    <select name="language" class="mt-1.5 block h-11 w-48 rounded-xl border border-border bg-surface px-3 text-body-small text-text-primary focus:border-gold focus:outline-none">
                        <option value="">{{ __('marketing.coaches.any') }}</option>
                        @foreach (\App\View\Locales::all() as $l)<option value="{{ $l }}" @selected($filters['language'] === $l)>{{ \App\View\Locales::name($l) }}</option>@endforeach
                    </select>
                </label>
                <label class="block"><span class="text-label text-text-secondary">{{ __('marketing.coaches.filter_mode') }}</span>
                    <select name="mode" class="mt-1.5 block h-11 w-48 rounded-xl border border-border bg-surface px-3 text-body-small text-text-primary focus:border-gold focus:outline-none">
                        <option value="">{{ __('marketing.coaches.any') }}</option>
                        <option value="online" @selected($filters['mode'] === 'online')>{{ __('marketing.coaches.online') }}</option>
                        <option value="in_person" @selected($filters['mode'] === 'in_person')>{{ __('marketing.coaches.in_person') }}</option>
                    </select>
                </label>
                <x-ui.button type="submit" variant="secondary">{{ __('marketing.coaches.apply') }}</x-ui.button>
            </form>
        @endunless
        <div class="mt-6 flex flex-wrap gap-2">
            @foreach (array_unique(array_merge(\App\View\Marketing\Catalog::CITIES, array_keys($cities))) as $slug)
                <x-ui.chip :href="route('marketing.coaches.city', ['city' => $slug])" icon="map-pin" :active="$city && \Illuminate\Support\Str::slug($city) === $slug">{{ $cities[$slug] ?? __('marketing.coaches.city_names.'.$slug) }}</x-ui.chip>
            @endforeach
        </div>

        @if ($coaches->isEmpty())
            <x-ui.empty-state class="mt-12" icon="users" :title="__('marketing.coaches.empty_title')" :description="__('marketing.coaches.empty_text')">
                <x-ui.button :href="route('register')">{{ __('marketing.coaches.empty_cta') }}</x-ui.button>
                @if ($city || array_filter($filters))<x-ui.button :href="route('marketing.coaches.index')" variant="secondary">{{ __('marketing.coaches.all') }}</x-ui.button>@endif
            </x-ui.empty-state>
        @else
            <ul class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($coaches as $coach)
                    <li>
                        <a href="{{ route('marketing.coaches.show', ['slug' => $coach->slug]) }}" class="group flex h-full flex-col rounded-[24px] border border-border bg-surface p-6 transition hover:-translate-y-0.5 hover:border-border-strong hover:shadow-e2">
                            <div class="flex items-center gap-4">
                                <x-ui.avatar :name="$coach->user?->name" :src="$coach->user?->avatar_path ? Storage::disk('public')->url($coach->user->avatar_path) : null" size="lg" />
                                <div class="min-w-0">
                                    <p class="flex items-center gap-1.5 truncate text-title text-text-primary">{{ $coach->user?->name }} @if ($coach->is_verified)<x-ui.icon name="shield" :size="16" class="text-gold" /><span class="sr-only">{{ __('marketing.coaches.verified') }}</span>@endif</p>
                                    <p class="truncate text-caption text-text-muted">{{ $coach->headline ?: __('marketing.coaches.default_headline') }}</p>
                                </div>
                            </div>
                            <div class="mt-5 flex flex-wrap gap-2">
                                @if ($coach->city)<x-ui.badge><x-ui.icon name="map-pin" :size="12" />{{ $coach->city }}</x-ui.badge>@endif
                                @if ($coach->offers_online)<x-ui.badge tone="gold">{{ __('marketing.coaches.online') }}</x-ui.badge>@endif
                                @foreach (array_slice((array) $coach->languages, 0, 3) as $l)<x-ui.badge>{{ strtoupper($l) }}</x-ui.badge>@endforeach
                            </div>
                            <div class="mt-auto flex items-center justify-between pt-6 text-caption">
                                @if ($coach->rating_count > 0)
                                    <span class="inline-flex items-center gap-1 font-semibold text-text-primary"><x-ui.icon name="star" :size="14" class="fill-current text-gold" />{{ number_format($coach->rating_avg, 1) }} <span class="font-normal text-text-muted">({{ $coach->rating_count }})</span></span>
                                @else
                                    <span class="text-text-muted">{{ __('marketing.coaches.new') }}</span>
                                @endif
                                @if ($coach->price_from_minor)<span class="text-text-muted">{{ __('marketing.coaches.from', ['price' => \App\Domain\Shared\Support\Money::format($coach->price_from_minor, 'USD', app()->getLocale())]) }}</span>@endif
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="mt-10">{{ $coaches->links('components.ui.pagination') }}</div>
        @endif
    </section>
    <section class="au-container pb-8">
        <div class="flex flex-col items-start justify-between gap-6 rounded-[28px] border border-gold/25 bg-gold/[0.04] p-8 sm:flex-row sm:items-center">
            <div><p class="text-title text-text-primary">{{ __('marketing.coaches.join_title') }}</p><p class="mt-1 text-body-small text-text-secondary">{{ __('marketing.coaches.join_text') }}</p></div>
            <x-ui.button :href="route('register')" icon-end="arrow-right">{{ __('marketing.cta.start_trial') }}</x-ui.button>
        </div>
    </section>
</x-layouts::marketing>
