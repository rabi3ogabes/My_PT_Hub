@props(['size' => 28, 'wordmark' => true, 'href' => null])
@php $tag = $href ? 'a' : 'span'; @endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 text-text-primary']) }} @if ($href) aria-label="{{ config('brand.name') }}" @endif>
    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 32 32" fill="none" aria-hidden="true">
        <defs>
            <linearGradient id="au-logo-g" x1="4" y1="2" x2="28" y2="30" gradientUnits="userSpaceOnUse"><stop stop-color="#F1DDB1"/><stop offset=".5" stop-color="#D4AF6A"/><stop offset="1" stop-color="#9C7A36"/></linearGradient>
        </defs>
        <rect x="1" y="1" width="30" height="30" rx="9" fill="#0B0B0F" stroke="url(#au-logo-g)" stroke-opacity=".55"/>
        <path d="M9 23 16 8l7 15" stroke="url(#au-logo-g)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M11.6 17.6h8.8" stroke="url(#au-logo-g)" stroke-width="2.4" stroke-linecap="round"/>
    </svg>
    @if ($wordmark)
        <span class="text-[17px] font-extrabold tracking-[-0.03em]">{{ config('brand.name') }}</span>
    @endif
</{{ $tag }}>
