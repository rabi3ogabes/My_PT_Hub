<div class="grid grid-cols-2 gap-3">
    @include('marketing.calculators.widgets._num', ['model' => 'calories', 'label' => __($k.'.labels.calories'), 'suffix' => 'kcal', 'step' => 50])
    @include('marketing.calculators.widgets._num', ['model' => 'weight', 'label' => __('marketing.calculators.common.weight'), 'suffix' => 'kg'])
</div>
<div class="mt-4 grid grid-cols-3 gap-2" role="radiogroup" aria-label="{{ __($k.'.labels.goal') }}">
    @foreach (['cut', 'maintain', 'bulk'] as $g)
        <button type="button" role="radio" @click="goal = '{{ $g }}'" :aria-checked="(goal === '{{ $g }}').toString()" class="h-10 rounded-xl border text-caption font-semibold transition" :class="goal === '{{ $g }}' ? 'border-gold bg-gold/10 text-gold' : 'border-border text-text-secondary'">{{ __($k.'.labels.goals.'.$g) }}</button>
    @endforeach
</div>
<label class="mt-5 block"><span class="flex justify-between text-label text-text-secondary"><span>{{ __($k.'.labels.protein_per_kg') }}</span><span class="tabular-nums text-text-primary" x-text="proteinPerKg + ' g/kg'"></span></span><input type="range" min="1.2" max="2.6" step="0.1" x-model.number="proteinPerKg" class="au-range mt-3 w-full"></label>
<label class="mt-4 block"><span class="flex justify-between text-label text-text-secondary"><span>{{ __($k.'.labels.fat_share') }}</span><span class="tabular-nums text-text-primary" x-text="fatShare + '%'"></span></span><input type="range" min="20" max="40" step="1" x-model.number="fatShare" class="au-range mt-3 w-full"></label>
<div class="mt-6">@include('marketing.calculators.widgets._result', ['label' => __($k.'.labels.target'), 'expr' => 'target', 'unit' => 'kcal', 'big' => true])</div>
<div class="mt-3 grid grid-cols-3 gap-3">
    @foreach (['protein' => [4, 'bg-macro-protein'], 'carbs' => [4, 'bg-macro-carbs'], 'fat' => [9, 'bg-macro-fat']] as $m => [$kc, $bg])
        <div class="rounded-2xl bg-surface-sunken p-4">
            <p class="flex items-center gap-1.5 text-caption text-text-muted"><span class="size-2 rounded-full {{ $bg }}"></span>{{ __('marketing.calculators.common.'.$m) }}</p>
            <p class="mt-1 text-h3 font-extrabold tabular-nums text-text-primary"><span x-text="{{ $m }}"></span><span class="text-caption text-text-muted"> g</span></p>
            <p class="text-caption tabular-nums text-text-muted" x-text="share({{ $m }}, {{ $kc }}) + '%'"></p>
        </div>
    @endforeach
</div>
