@include('marketing.calculators.widgets._num', ['model' => 'weight', 'label' => __('marketing.calculators.common.weight'), 'suffix' => 'kg'])
<label class="mt-4 block">
    <span class="text-label text-text-secondary">{{ __($k.'.labels.goal') }}</span>
    <select x-model="goal" class="mt-1.5 block h-12 w-full rounded-xl border border-border bg-surface px-4 text-body text-text-primary focus:border-gold focus:outline-none">
        @foreach (['health', 'fatloss', 'muscle', 'endurance', 'older'] as $g)<option value="{{ $g }}">{{ __($k.'.labels.goals.'.$g) }}</option>@endforeach
    </select>
</label>
<div class="mt-6">@include('marketing.calculators.widgets._result', ['label' => __($k.'.labels.daily'), 'expr' => "range[0] + '–' + range[1]", 'unit' => 'g', 'big' => true])</div>
<div class="mt-3">@include('marketing.calculators.widgets._result', ['label' => __($k.'.labels.per_meal'), 'expr' => 'perMeal', 'unit' => 'g'])</div>
