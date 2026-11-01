<div class="flex flex-wrap items-center justify-between gap-3">@include('marketing.calculators.widgets._sex') @include('marketing.calculators.widgets._units')</div>
<div class="mt-6 grid grid-cols-2 gap-3">
    @include('marketing.calculators.widgets._num', ['model' => 'height', 'label' => __('marketing.calculators.common.height'), 'suffixExpr' => "unit === 'metric' ? 'cm' : 'in'"])
    @include('marketing.calculators.widgets._num', ['model' => 'neck', 'label' => __($k.'.labels.neck'), 'suffixExpr' => "unit === 'metric' ? 'cm' : 'in'"])
    @include('marketing.calculators.widgets._num', ['model' => 'waist', 'label' => __($k.'.labels.waist'), 'suffixExpr' => "unit === 'metric' ? 'cm' : 'in'"])
    <div x-show="sex === 'female'" x-cloak>@include('marketing.calculators.widgets._num', ['model' => 'hip', 'label' => __($k.'.labels.hip'), 'suffixExpr' => "unit === 'metric' ? 'cm' : 'in'"])</div>
</div>
<div class="mt-6">@include('marketing.calculators.widgets._result', ['label' => __($k.'.labels.result'), 'expr' => "result + '%'", 'big' => true])</div>
<p class="mt-3 text-body-small text-text-secondary">{{ __($k.'.labels.band') }}: <strong class="text-text-primary" x-text="{ essential: @js(__($k.'.bands.essential')), athletic: @js(__($k.'.bands.athletic')), fitness: @js(__($k.'.bands.fitness')), average: @js(__($k.'.bands.average')), high: @js(__($k.'.bands.high')) }[band]"></strong></p>
