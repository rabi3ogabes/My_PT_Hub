@php
    $link = fn (array $names) => ($n = collect($names)->first(fn ($r) => Route::has($r))) ? route($n) : null;
@endphp
<div class="space-y-8">
    <section class="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-6" aria-label="{{ __('web.dashboard.kpis') }}">
        <x-ui.stat-tile :label="__('web.dashboard.kpi.active_clients')" :value="number_format($kpis['active_clients'])" icon="users" tone="gold" :href="$link(['app.clients.index', 'app.clients'])" />
        <x-ui.stat-tile :label="__('web.dashboard.kpi.at_risk')" :value="number_format($kpis['at_risk'])" icon="exclamation" :tone="$kpis['at_risk'] > 0 ? 'danger' : 'neutral'" />
        <x-ui.stat-tile :label="__('web.dashboard.kpi.checkins')" :value="number_format($kpis['checkins'])" icon="clipboard-check" :tone="$kpis['checkins'] > 0 ? 'warning' : 'neutral'" :href="$link(['app.checkins.index', 'app.checkins'])" />
        <x-ui.stat-tile :label="__('web.dashboard.kpi.unread')" :value="number_format($kpis['unread'])" icon="chat" :tone="$kpis['unread'] > 0 ? 'info' : 'neutral'" :href="$link(['app.messages.index', 'app.messages'])" />
        <x-ui.stat-tile :label="__('web.dashboard.kpi.sessions_today')" :value="number_format($kpis['sessions_today'])" icon="calendar" :href="$link(['app.calendar.index', 'app.calendar'])" />
        <x-ui.stat-tile :label="__('web.dashboard.kpi.mrr')" :value="\App\Domain\Shared\Support\Money::format($kpis['mrr_minor'], $currency, app()->getLocale())" icon="trending-up" tone="success" :href="$link(['app.analytics.index', 'app.analytics'])" />
    </section>

    <div class="grid gap-4 lg:grid-cols-2">
        <x-ui.card>
            <x-slot:header>
                <div><h2 class="text-title text-text-primary">{{ __('web.dashboard.at_risk_title') }}</h2><p class="mt-1 text-caption text-text-muted">{{ __('web.dashboard.at_risk_lead') }}</p></div>
                <x-ui.icon name="heart-pulse" class="text-text-muted" />
            </x-slot:header>
            @if ($atRisk->isEmpty())
                <x-ui.empty-state :compact="true" icon="check-circle" :title="$kpis['active_clients'] > 0 ? __('web.dashboard.at_risk_empty') : __('web.dashboard.no_clients_title')" :description="$kpis['active_clients'] > 0 ? __('web.dashboard.at_risk_empty_text') : __('web.dashboard.no_clients_text')">
                    @if ($kpis['active_clients'] === 0)<x-ui.button size="sm" icon="plus" x-on:click="$wire.$parent.openInvite()">{{ __('web.dashboard.invite_client') }}</x-ui.button>@endif
                </x-ui.empty-state>
            @else
                <ul class="divide-y divide-border">
                    @foreach ($atRisk as $profile)
                        <li class="flex items-center gap-3 py-3">
                            <x-ui.avatar :name="$profile->user?->name" :src="$profile->user?->avatar_path ? Storage::disk('public')->url($profile->user->avatar_path) : null" size="sm" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-body-small font-semibold text-text-primary">{{ $profile->user?->name }}</p>
                                <p class="text-caption text-text-muted">{{ $profile->last_active_at ? __('web.dashboard.last_active', ['when' => $profile->last_active_at->diffForHumans()]) : __('web.dashboard.never_active') }}</p>
                            </div>
                            @if ($profile->compliance_score !== null)<span class="text-caption font-semibold tabular-nums text-text-secondary">{{ $profile->compliance_score }}%</span>@endif
                            <x-ui.badge :tone="$profile->risk_status === 'red' ? 'danger' : 'warning'" :dot="true">{{ __('web.dashboard.risk.'.$profile->risk_status) }}</x-ui.badge>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <x-ui.card>
            <x-slot:header>
                <div><h2 class="text-title text-text-primary">{{ __('web.dashboard.today_title') }}</h2><p class="mt-1 text-caption text-text-muted">{{ __('web.dashboard.today_lead') }}</p></div>
                <x-ui.icon name="clock" class="text-text-muted" />
            </x-slot:header>
            @if ($sessions->isEmpty())
                <x-ui.empty-state :compact="true" icon="calendar" :title="__('web.dashboard.today_empty')" :description="__('web.dashboard.today_empty_text')">
                    @if ($href = $link(['app.calendar.index', 'app.calendar']))<x-ui.button size="sm" variant="secondary" :href="$href">{{ __('web.dashboard.open_calendar') }}</x-ui.button>@endif
                </x-ui.empty-state>
            @else
                <ul class="space-y-2">
                    @foreach ($sessions as $s)
                        <li class="flex items-center gap-4 rounded-2xl border border-border p-3">
                            <div class="w-16 text-center"><p class="text-body-small font-bold tabular-nums text-text-primary">{{ $s->starts->format('H:i') }}</p><p class="text-caption tabular-nums text-text-muted">{{ $s->ends->format('H:i') }}</p></div>
                            <div class="min-w-0 flex-1 border-s border-border ps-4">
                                <p class="truncate text-body-small font-semibold text-text-primary">{{ $s->title }}</p>
                                <p class="text-caption text-text-muted">{{ trans_choice('web.dashboard.booked', (int) $s->booked, ['n' => (int) $s->booked, 'capacity' => $s->capacity]) }}</p>
                            </div>
                            @if ($s->meeting_url)<x-ui.button size="sm" variant="secondary" :href="$s->meeting_url" target="_blank" rel="noopener" icon="play">{{ __('web.dashboard.join') }}</x-ui.button>@endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>
</div>
