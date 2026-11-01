<div class="grid grid-cols-2 gap-3">
    @include('marketing.calculators.widgets._num', ['model' => 'clients', 'label' => __($k.'.labels.clients'), 'step' => 1])
    @include('marketing.calculators.widgets._num', ['model' => 'price', 'label' => __($k.'.labels.price'), 'step' => 5])
    @include('marketing.calculators.widgets._num', ['model' => 'fees', 'label' => __($k.'.labels.fees'), 'suffix' => '%', 'step' => 0.1])
    @include('marketing.calculators.widgets._num', ['model' => 'software', 'label' => __($k.'.labels.software'), 'step' => 1])
</div>
<label class="mt-4 block"><span class="flex justify-between text-label text-text-secondary"><span>{{ __($k.'.labels.churn') }}</span><span class="tabular-nums text-text-primary" x-text="churn + '%'"></span></span><input type="range" min="1" max="20" step="0.5" x-model.number="churn" class="au-range mt-3 w-full"></label>
<div class="mt-6 grid grid-cols-2 gap-3">
    @include('marketing.calculators.widgets._result', ['label' => __($k.'.labels.monthly'), 'expr' => 'monthly.toLocaleString(document.documentElement.lang)', 'big' => true])
    @include('marketing.calculators.widgets._result', ['label' => __($k.'.labels.yearly'), 'expr' => 'yearly.toLocaleString(document.documentElement.lang)', 'big' => true])
</div>
<div class="mt-3">@include('marketing.calculators.widgets._result', ['label' => __($k.'.labels.lifetime'), 'expr' => 'lifetime.toLocaleString(document.documentElement.lang)'])</div>
