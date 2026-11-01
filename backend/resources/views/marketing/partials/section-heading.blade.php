@php $center = $center ?? true; @endphp
<div class="{{ $center ? 'mx-auto max-w-3xl text-center' : 'max-w-2xl' }}">
    @if (! empty($eyebrow))<p class="au-eyebrow">{{ $eyebrow }}</p>@endif
    <h2 class="mt-4 text-[clamp(2rem,1.4rem+2.2vw,3.25rem)] leading-[1.05] font-extrabold tracking-[-0.035em] text-text-primary">{{ $title }}</h2>
    @if (! empty($lead))<p class="mt-5 text-[1.0625rem] leading-relaxed text-text-secondary">{{ $lead }}</p>@endif
</div>
