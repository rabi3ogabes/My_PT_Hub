<div class="inline-flex rounded-full border border-border bg-surface-sunken p-1 text-caption font-semibold" role="radiogroup" aria-label="{{ __('marketing.calculators.common.sex') }}">
    @foreach (['male', 'female'] as $s)
        <button type="button" role="radio" :aria-checked="(sex === '{{ $s }}').toString()" @click="sex = '{{ $s }}'" class="rounded-full px-3 py-1 transition" :class="sex === '{{ $s }}' ? 'bg-surface text-text-primary shadow-e1' : 'text-text-muted'">{{ __('marketing.calculators.common.'.$s) }}</button>
    @endforeach
</div>
