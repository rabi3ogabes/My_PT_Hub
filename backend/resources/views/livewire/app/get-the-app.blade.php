<div class="space-y-8">
    <div>
        @if ($tenant?->logo_path)<img src="{{ Storage::disk('public')->url($tenant->logo_path) }}" alt="{{ $tenant->name }}" class="mb-6 size-14 rounded-2xl object-cover ring-1 ring-border">@endif
        <p class="au-eyebrow">{{ $tenant ? $tenant->name : config('brand.name') }}</p>
        <h1 class="mt-3 text-h1 text-text-primary">{{ __('web.get_app.title') }}</h1>
        <p class="mt-3 text-body text-text-secondary">{{ $coach ? __('web.get_app.lead_coach', ['coach' => $coach->name]) : __('web.get_app.lead') }}</p>
    </div>
    <div class="flex flex-wrap gap-3">@include('marketing.partials.store-badges')</div>
    <ul class="space-y-3">
        @foreach (['workouts' => 'dumbbell', 'nutrition' => 'leaf', 'chat' => 'chat', 'offline' => 'bolt'] as $k => $icon)
            <li class="flex items-center gap-3 text-body-small text-text-secondary"><span class="flex size-8 items-center justify-center rounded-lg bg-gold/10 text-gold"><x-ui.icon :name="$icon" :size="16" /></span>{{ __('web.get_app.points.'.$k) }}</li>
        @endforeach
    </ul>
    <div class="rounded-2xl border border-border bg-surface p-4 text-caption text-text-muted">{{ __('web.get_app.signed_in_as', ['email' => auth()->user()?->email]) }}</div>
    <div class="flex flex-wrap items-center gap-3">
        @if ($isStaff)<x-ui.button :href="route('app.dashboard')" variant="secondary">{{ __('web.get_app.to_dashboard') }}</x-ui.button>@endif
        <form method="POST" action="{{ route('logout') }}">@csrf<x-ui.button type="submit" variant="ghost" icon="logout">{{ __('web.shell.sign_out') }}</x-ui.button></form>
    </div>
</div>
