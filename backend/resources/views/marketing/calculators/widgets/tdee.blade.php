<div class="flex flex-wrap items-center justify-between gap-3">@include('marketing.calculators.widgets._sex') @include('marketing.calculators.widgets._units')</div>
<div class="mt-6 grid grid-cols-3 gap-3">
    @include('marketing.calculators.widgets._num', ['model' => 'age', 'label' => __('marketing.calculators.common.age'), 'suffix' => __('marketing.calculators.common.years'), 'min' => 14, 'max' => 99])
    @include('marketing.calculators.widgets._num', ['model' => 'weight', 'label' => __('marketing.calculators.common.weight'), 'suffixExpr' => "unit === 'metric' ? 'kg' : 'lb'"])
    @include('marketing.calculators.widgets._num', ['model' => 'height', 'label' => __('marketing.calculators.common.height'), 'suffixExpr' => "unit === 'metric' ? 'cm' : 'in'"])
</div>
<label class="mt-4 block">
    <span class="text-label text-text-secondary">{{ __('marketing.calculators.common.activity') }}</span>
    <select x-model="activity" class="mt-1.5 block h-12 w-full rounded-xl border border-border bg-surface px-4 text-body text-text-primary focus:border-gold focus:outline-none">
        @foreach (['sedentary', 'light', 'moderate', 'active', 'athlete'] as $a)<option value="{{ $a }}">{{ __('marketing.calculators.common.activity_levels.'.$a) }}</option>@endforeach
    </select>
</label>
<div class="mt-6 grid gap-3 sm:grid-cols-2">
    @include('marketing.calculators.widgets._result', ['label' => __($k.'.labels.tdee'), 'expr' => 'tdee', 'unit' => 'kcal', 'big' => true])
    @include('marketing.calculators.widgets._result', ['label' => __($k.'.labels.bmr'), 'expr' => 'bmr', 'unit' => 'kcal', 'big' => true])
    @include('marketing.calculators.widgets._result', ['label' => __($k.'.labels.cut'), 'expr' => 'cut', 'unit' => 'kcal'])
    @include('marketing.calculators.widgets._result', ['label' => __($k.'.labels.bulk'), 'expr' => 'bulk', 'unit' => 'kcal'])
</div>
