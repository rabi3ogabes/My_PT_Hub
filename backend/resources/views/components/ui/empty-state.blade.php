@props(['icon' => 'sparkles', 'title', 'description' => null, 'compact' => false])
<div {{ $attributes->class(['relative flex flex-col items-center overflow-hidden rounded-[20px] border border-dashed border-border-strong text-center', 'px-6 py-14' => ! $compact, 'px-5 py-8' => $compact]) }}>
    <div class="pointer-events-none absolute inset-0 au-grain opacity-60" aria-hidden="true"></div>
    <span class="relative flex size-12 items-center justify-center rounded-2xl border border-gold/25 bg-gold/10 text-gold shadow-[0_0_40px_-8px_rgba(212,175,106,0.5)]"><x-ui.icon :name="$icon" :size="22" /></span>
    <h3 class="relative mt-5 text-title text-text-primary">{{ $title }}</h3>
    @if ($description)<p class="relative mt-2 max-w-sm text-body-small text-text-secondary">{{ $description }}</p>@endif
    @if (trim($slot) !== '')<div class="relative mt-6 flex flex-wrap items-center justify-center gap-3">{{ $slot }}</div>@endif
</div>
