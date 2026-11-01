{{-- $model, $label, optional $suffix (static) or $suffixExpr (Alpine expression), $step, $min, $max --}}
<label class="block">
    <span class="text-label text-text-secondary">{{ $label }}</span>
    <span class="relative mt-1.5 block">
        <input type="number" inputmode="decimal" x-model.number="{{ $model }}" step="{{ $step ?? 'any' }}" min="{{ $min ?? 0 }}" @isset($max) max="{{ $max }}" @endisset
            class="block h-12 w-full rounded-xl border border-border bg-surface ps-4 pe-14 text-body font-semibold tabular-nums text-text-primary focus:border-gold focus:outline-none focus:ring-4 focus:ring-gold/15">
        @if (! empty($suffixExpr))
            <span class="pointer-events-none absolute inset-y-0 end-4 flex items-center text-caption font-semibold text-text-muted" x-text="{{ $suffixExpr }}"></span>
        @elseif (! empty($suffix))
            <span class="pointer-events-none absolute inset-y-0 end-4 flex items-center text-caption font-semibold text-text-muted">{{ $suffix }}</span>
        @endif
    </span>
</label>
