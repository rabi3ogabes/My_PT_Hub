<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ __('payments.checkout.pay') }}</title>
<style>
    body { margin: 0; background: #F7F5F0; color: #16161D; font-family: Inter, 'IBM Plex Sans Arabic', system-ui, sans-serif; }
    main { max-width: 480px; margin: 0 auto; padding: 32px 16px; }
    h1 { font-size: 22px; margin: 0 0 4px; }
    p { color: #6B6B76; margin: 0 0 24px; }
</style>
<script>var wpwlOptions = { locale: "{{ app()->getLocale() }}", style: "card", paymentTarget: "_top" };</script>
<script src="{{ $widgetUrl }}"></script>
</head>
<body>
<main>
    <h1>{{ $business }}</h1>
    <p>{{ $amount }}</p>
    <form action="{{ $resultUrl }}" class="paymentWidgets" data-brands="{{ $brands }}"></form>
</main>
</body>
</html>
