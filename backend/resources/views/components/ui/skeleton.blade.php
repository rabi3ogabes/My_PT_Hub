@props(['lines' => 3, 'avatar' => false, 'class' => ''])
<div {{ $attributes->merge(['class' => 'animate-pulse-none space-y-3']) }} aria-hidden="true">
    @if ($avatar)
        <div class="flex items-center gap-3"><div class="au-skeleton size-10 rounded-full"></div><div class="flex-1 space-y-2"><div class="au-skeleton h-3 w-1/3"></div><div class="au-skeleton h-3 w-1/5"></div></div></div>
    @endif
    @for ($i = 0; $i < $lines; $i++)
        <div class="au-skeleton h-3" style="width: {{ [100, 86, 64, 92, 72][$i % 5] }}%"></div>
    @endfor
</div>
