<div class="grid grid-cols-2 gap-3">
    @include('marketing.calculators.widgets._num', ['model' => 'weight', 'label' => __($k.'.labels.weight'), 'suffix' => 'kg', 'step' => 0.5])
    @include('marketing.calculators.widgets._num', ['model' => 'reps', 'label' => __($k.'.labels.reps'), 'suffix' => __($k.'.labels.reps_unit'), 'min' => 1, 'max' => 20, 'step' => 1])
</div>
<div class="mt-6">@include('marketing.calculators.widgets._result', ['label' => __($k.'.labels.estimate'), 'expr' => 'estimate', 'unit' => 'kg', 'big' => true])</div>
<div class="mt-3 grid grid-cols-2 gap-3">
    @include('marketing.calculators.widgets._result', ['label' => 'Epley', 'expr' => 'epley', 'unit' => 'kg'])
    @include('marketing.calculators.widgets._result', ['label' => 'Brzycki', 'expr' => 'brzycki', 'unit' => 'kg'])
</div>
<table class="mt-5 w-full text-body-small">
    <caption class="sr-only">{{ __($k.'.labels.table') }}</caption>
    <thead><tr class="border-b border-border text-caption text-text-muted"><th scope="col" class="py-2 text-start">%1RM</th><th scope="col" class="py-2 text-end">{{ __($k.'.labels.load') }}</th></tr></thead>
    <tbody class="divide-y divide-border">
        <template x-for="row in table" :key="row.pct"><tr><td class="py-2 tabular-nums text-text-secondary" x-text="row.pct + '%'"></td><td class="py-2 text-end font-semibold tabular-nums text-text-primary" x-text="row.load + ' kg'"></td></tr></template>
    </tbody>
</table>
