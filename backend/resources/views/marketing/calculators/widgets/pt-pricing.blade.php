<div class="grid grid-cols-2 gap-3">
    @include('marketing.calculators.widgets._num', ['model' => 'income', 'label' => __($k.'.labels.income'), 'step' => 1000])
    @include('marketing.calculators.widgets._num', ['model' => 'expenses', 'label' => __($k.'.labels.expenses'), 'step' => 500])
    @include('marketing.calculators.widgets._num', ['model' => 'weeks', 'label' => __($k.'.labels.weeks'), 'min' => 1, 'max' => 52, 'step' => 1])
    @include('marketing.calculators.widgets._num', ['model' => 'sessions', 'label' => __($k.'.labels.sessions'), 'min' => 1, 'max' => 60, 'step' => 1])
</div>
<label class="mt-4 block"><span class="flex justify-between text-label text-text-secondary"><span>{{ __($k.'.labels.tax') }}</span><span class="tabular-nums text-text-primary" x-text="tax + '%'"></span></span><input type="range" min="0" max="50" step="1" x-model.number="tax" class="au-range mt-3 w-full"></label>
<div class="mt-6">@include('marketing.calculators.widgets._result', ['label' => __($k.'.labels.per_session'), 'expr' => 'perSession.toLocaleString(document.documentElement.lang)', 'big' => true])</div>
<div class="mt-3 grid grid-cols-2 gap-3">
    @include('marketing.calculators.widgets._result', ['label' => __($k.'.labels.pack10'), 'expr' => 'pack10.toLocaleString(document.documentElement.lang)'])
    @include('marketing.calculators.widgets._result', ['label' => __($k.'.labels.online'), 'expr' => 'monthlyOnline.toLocaleString(document.documentElement.lang)'])
</div>
<p class="mt-3 text-caption text-text-muted">{{ __($k.'.labels.currency_hint') }}</p>
