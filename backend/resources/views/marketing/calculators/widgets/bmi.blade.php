<div class="flex justify-end">@include('marketing.calculators.widgets._units')</div>
<div class="mt-4 grid grid-cols-2 gap-3">
    @include('marketing.calculators.widgets._num', ['model' => 'weight', 'label' => __('marketing.calculators.common.weight'), 'suffixExpr' => "unit === 'metric' ? 'kg' : 'lb'"])
    @include('marketing.calculators.widgets._num', ['model' => 'height', 'label' => __('marketing.calculators.common.height'), 'suffixExpr' => "unit === 'metric' ? 'cm' : 'in'"])
</div>
<div class="mt-6">@include('marketing.calculators.widgets._result', ['label' => 'BMI', 'expr' => 'value', 'unit' => 'kg/m²', 'big' => true])</div>
<div class="relative mt-6 h-2 rounded-full bg-[linear-gradient(90deg,#5B8DEF_0%,#3DBE8B_28%,#F2B544_60%,#EF5A5A_100%)]">
    <span class="absolute -top-1.5 size-5 -translate-x-1/2 rounded-full border-2 border-surface bg-text-primary shadow transition-all duration-300 rtl:translate-x-1/2" :style="document.dir === 'rtl' ? `right: ${position}%` : `left: ${position}%`"></span>
</div>
<p class="mt-4 text-body-small text-text-secondary">{{ __($k.'.labels.band') }}: <strong class="text-text-primary" x-text="{ under: @js(__($k.'.bands.under')), healthy: @js(__($k.'.bands.healthy')), over: @js(__($k.'.bands.over')), obese: @js(__($k.'.bands.obese')) }[band]"></strong></p>
