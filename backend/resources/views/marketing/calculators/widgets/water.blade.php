<div class="grid grid-cols-2 gap-3">
    @include('marketing.calculators.widgets._num', ['model' => 'weight', 'label' => __('marketing.calculators.common.weight'), 'suffix' => 'kg'])
    @include('marketing.calculators.widgets._num', ['model' => 'training', 'label' => __($k.'.labels.training'), 'suffix' => 'min', 'step' => 5])
</div>
<div class="mt-4"><x-ui.toggle x-model="hot" :label="__($k.'.labels.hot')" :description="__($k.'.labels.hot_hint')" id="water-hot" /></div>
<div class="mt-6 grid grid-cols-2 gap-3">
    @include('marketing.calculators.widgets._result', ['label' => __($k.'.labels.daily'), 'expr' => 'litres', 'unit' => 'L', 'big' => true])
    @include('marketing.calculators.widgets._result', ['label' => __($k.'.labels.glasses'), 'expr' => 'glasses', 'unit' => '× 250 ml', 'big' => true])
</div>
