<x-layouts::marketing :seo="$seo">
    <section class="relative overflow-hidden border-b border-border">
        <div class="pointer-events-none absolute inset-0 au-grain" aria-hidden="true"></div>
        <div class="au-container relative py-16 lg:py-20">
            <nav aria-label="{{ __('marketing.nav.breadcrumbs') }}" class="mb-8 text-caption text-text-muted">
                <a href="{{ route('marketing.coaches.index') }}" class="hover:text-text-primary">{{ __('marketing.nav.res.coaches') }}</a>
                @if ($coach->city)<span class="mx-1.5">/</span><a href="{{ route('marketing.coaches.city', ['city' => \Illuminate\Support\Str::slug($coach->city)]) }}" class="hover:text-text-primary">{{ $coach->city }}</a>@endif
            </nav>
            <div class="flex flex-col gap-8 sm:flex-row sm:items-center">
                <x-ui.avatar :name="$name" :src="$avatar" size="xl" class="!size-28 !text-3xl" />
                <div>
                    <h1 class="flex flex-wrap items-center gap-3 text-[clamp(2rem,1.5rem+2vw,3.25rem)] leading-[1.05] font-extrabold tracking-[-0.035em] text-text-primary">
                        {{ $name }}
                        @if ($coach->is_verified)<x-ui.badge tone="gold"><x-ui.icon name="shield" :size="13" />{{ __('marketing.coaches.verified') }}</x-ui.badge>@endif
                    </h1>
                    <p class="mt-2 text-[1.125rem] text-text-secondary">{{ $coach->headline ?: __('marketing.coaches.default_headline') }}</p>
                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        @if ($count > 0)<x-ui.badge tone="gold"><x-ui.icon name="star" :size="12" class="fill-current" />{{ $avg }} · {{ trans_choice('marketing.coaches.reviews_count', $count, ['count' => $count]) }}</x-ui.badge>@endif
                        @if ($coach->city)<x-ui.badge><x-ui.icon name="map-pin" :size="12" />{{ $coach->city }}</x-ui.badge>@endif
                        @if ($coach->offers_online)<x-ui.badge>{{ __('marketing.coaches.online') }}</x-ui.badge>@endif
                        @if ($coach->offers_in_person)<x-ui.badge>{{ __('marketing.coaches.in_person') }}</x-ui.badge>@endif
                        @foreach ((array) $coach->languages as $l)<x-ui.badge>{{ \App\View\Locales::name($l) }}</x-ui.badge>@endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="au-container grid items-start gap-12 py-16 lg:grid-cols-[1fr_400px]">
        <div class="space-y-12">
            <div>
                <h2 class="text-h2 text-text-primary">{{ __('marketing.coaches.about', ['name' => \Illuminate\Support\Str::before($name, ' ')]) }}</h2>
                @if ($bio)
                    <div class="au-prose mt-4">{!! nl2br(e($bio)) !!}</div>
                @else
                    <p class="mt-4 text-body text-text-secondary">{{ __('marketing.coaches.profile_description', ['name' => $name]) }}</p>
                @endif
            </div>
            @if (! empty($coach->specialties))
                <div>
                    <h2 class="text-h3 text-text-primary">{{ __('marketing.coaches.specialties') }}</h2>
                    <div class="mt-4 flex flex-wrap gap-2">@foreach ((array) $coach->specialties as $s)<x-ui.chip>{{ is_array($s) ? ($s[app()->getLocale()] ?? reset($s)) : \Illuminate\Support\Str::headline($s) }}</x-ui.chip>@endforeach</div>
                </div>
            @endif
            @if (! empty($coach->certifications))
                <div>
                    <h2 class="text-h3 text-text-primary">{{ __('marketing.coaches.certifications') }}</h2>
                    <ul class="mt-4 space-y-2">@foreach ((array) $coach->certifications as $c)<li class="flex gap-2 text-body-small text-text-secondary"><x-ui.icon name="check-circle" :size="18" class="text-gold" />{{ is_array($c) ? ($c['name'] ?? implode(' · ', array_filter($c, 'is_string'))) : $c }}</li>@endforeach</ul>
                </div>
            @endif
            <div>
                <h2 class="text-h3 text-text-primary">{{ __('marketing.coaches.reviews') }}</h2>
                @if ($reviews->isEmpty())
                    <p class="mt-4 text-body-small text-text-muted">{{ __('marketing.coaches.no_reviews') }}</p>
                @else
                    <ul class="mt-6 space-y-4">
                        @foreach ($reviews as $review)
                            <li class="rounded-[22px] border border-border bg-surface p-6">
                                <div class="flex items-center justify-between">
                                    <span class="flex gap-0.5 text-gold" aria-label="{{ __('marketing.coaches.rating', ['rating' => $review->rating]) }}">@for ($s = 0; $s < 5; $s++)<x-ui.icon name="star" :size="14" class="{{ $s < $review->rating ? 'fill-current' : 'opacity-30' }}" />@endfor</span>
                                    <span class="text-caption text-text-muted">{{ $review->created_at?->translatedFormat('M Y') }}</span>
                                </div>
                                @if ($review->body)<p class="mt-3 text-body-small leading-relaxed text-text-primary">{{ $review->body }}</p>@endif
                                <p class="mt-3 text-caption text-text-muted">{{ \Illuminate\Support\Str::of($review->client?->name)->before(' ') }} · {{ __('marketing.coaches.verified_client') }}</p>
                                @if ($review->coach_reply)<p class="mt-4 border-s-2 border-gold/50 ps-4 text-body-small text-text-secondary">{{ $review->coach_reply }}</p>@endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <aside id="contact" class="rounded-[28px] border border-border bg-surface p-7 lg:sticky lg:top-24" x-data="coachLead(@js(url('/api/v1/public/coaches/'.$coach->slug.'/leads')))" data-error="{{ __('marketing.coaches.lead_error') }}">
            @if ($coach->price_from_minor)<p class="text-caption text-text-muted">{{ __('marketing.coaches.from', ['price' => \App\Domain\Shared\Support\Money::format($coach->price_from_minor, 'USD', app()->getLocale())]) }}</p>@endif
            <h2 class="mt-1 text-h3 text-text-primary">{{ __('marketing.coaches.contact_title', ['name' => \Illuminate\Support\Str::before($name, ' ')]) }}</h2>
            <div x-show="sent" x-cloak class="mt-6 rounded-2xl bg-success-soft p-5 text-body-small text-success" role="status">{{ __('marketing.coaches.lead_sent') }}</div>
            <form x-show="!sent" @submit.prevent="submit" class="mt-6 space-y-4">
                <x-ui.input id="lead-name" :label="__('marketing.contact.name')" x-model="name" required maxlength="120" autocomplete="name" />
                <x-ui.input id="lead-email" type="email" :label="__('marketing.contact.email')" x-model="email" required autocomplete="email" />
                <x-ui.input id="lead-phone" type="tel" :label="__('marketing.coaches.phone')" x-model="phone" :optional="true" autocomplete="tel" />
                <x-ui.textarea id="lead-message" :label="__('marketing.coaches.goal')" x-model="message" rows="3" required maxlength="2000" :placeholder="__('marketing.coaches.goal_placeholder')" />
                <p x-show="error" x-text="error" class="text-caption text-danger" role="alert"></p>
                <x-ui.button type="submit" :block="true" size="lg" x-bind:disabled="sending">
                    <span x-show="!sending">{{ __('marketing.coaches.send') }}</span><span x-show="sending" x-cloak>{{ __('marketing.coaches.sending') }}</span>
                </x-ui.button>
                <p class="text-caption text-text-muted">{{ __('marketing.coaches.lead_privacy') }}</p>
            </form>
        </aside>
    </section>
</x-layouts::marketing>
