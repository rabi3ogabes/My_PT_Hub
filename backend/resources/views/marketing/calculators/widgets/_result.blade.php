{{-- $label, $expr (Alpine), optional $unit, $big --}}
<div class="rounded-2xl {{ ! empty($big) ? 'border border-gold/30 bg-gold/[0.06] p-5' : 'bg-surface-sunken p-4' }}">
    <p class="text-caption text-text-muted">{{ $label }}</p>
    <p class="mt-1 font-extrabold tracking-[-0.03em] tabular-nums text-text-primary {{ ! empty($big) ? 'text-[2.5rem] leading-none' : 'text-h3' }}" aria-live="polite"><span x-text="{{ $expr }}"></span>@if (! empty($unit)) <span class="text-body-small font-semibold text-text-muted">{{ $unit }}</span>@endif</p>
</div>
