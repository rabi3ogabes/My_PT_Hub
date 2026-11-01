@php
    $compact = $compact ?? false;
    $ios = config('brand.ios_app_id') ? 'https://apps.apple.com/app/id'.config('brand.ios_app_id') : null;
    $android = config('brand.android_package') ? 'https://play.google.com/store/apps/details?id='.config('brand.android_package') : null;
    $cls = 'inline-flex items-center gap-2.5 rounded-xl border border-border bg-surface-raised text-start transition hover:border-border-strong '.($compact ? 'h-10 px-3' : 'h-12 px-4');
@endphp
@if ($ios)
    <a href="{{ $ios }}" class="{{ $cls }}" rel="noopener" target="_blank">
        <x-ui.icon name="apple" :size="$compact ? 18 : 22" class="text-text-primary" />
        <span class="leading-tight"><span class="block text-[10px] text-text-muted">{{ __('marketing.stores.ios_small') }}</span><span class="block text-[13px] font-semibold text-text-primary">App Store</span></span>
    </a>
@endif
@if ($android)
    <a href="{{ $android }}" class="{{ $cls }}" rel="noopener" target="_blank">
        <x-ui.icon name="android" :size="$compact ? 18 : 22" class="text-text-primary" />
        <span class="leading-tight"><span class="block text-[10px] text-text-muted">{{ __('marketing.stores.android_small') }}</span><span class="block text-[13px] font-semibold text-text-primary">Google Play</span></span>
    </a>
@endif
