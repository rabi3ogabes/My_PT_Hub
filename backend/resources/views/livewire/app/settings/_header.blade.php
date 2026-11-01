{{-- Settings page header + section tabs. $title, $lead, $active (tab key) --}}
<header class="space-y-6">
    <div>
        <p class="text-body-small text-text-muted">{{ __('web.settings.title') }}</p>
        <h1 class="mt-1 text-h1 text-text-primary">{{ $title }}</h1>
        @if (! empty($lead))<p class="mt-2 max-w-2xl text-body text-text-secondary">{{ $lead }}</p>@endif
    </div>
    <x-ui.tabs :tabs="collect(['profile', 'business', 'team', 'billing', 'notifications'])->map(fn ($k) => ['label' => __('web.settings.'.$k), 'href' => route('app.settings.'.$k), 'active' => $k === $active])->all()" />
</header>
