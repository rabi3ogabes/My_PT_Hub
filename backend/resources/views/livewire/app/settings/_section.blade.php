{{-- Two-column settings section: $title, $lead on the start side; slot content on the end side. Use via @component. --}}
<section class="grid gap-6 border-b border-border py-10 last:border-b-0 lg:grid-cols-[280px_1fr]" @isset($id) id="{{ $id }}" @endisset>
    <div>
        <h2 class="text-title text-text-primary">{{ $title }}</h2>
        @if (! empty($lead))<p class="mt-1.5 text-body-small text-text-muted">{{ $lead }}</p>@endif
    </div>
    <div class="min-w-0">{{ $slot }}</div>
</section>
