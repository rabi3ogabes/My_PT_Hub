<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ __('payments.return.title') }}</title>
@if($state === 'pending')<meta http-equiv="refresh" content="4">@endif
<style>
    :root { --bg: #F7F5F0; --fg: #16161D; --muted: #6B6B76; --accent: {{ $brand }}; }
    @media (prefers-color-scheme: dark) { :root { --bg: #0B0B0F; --fg: #F7F5F0; --muted: #A1A1AA; } }
    body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--bg); color: var(--fg); font-family: Inter, 'IBM Plex Sans Arabic', system-ui, sans-serif; }
    main { max-width: 420px; padding: 32px 16px; text-align: center; }
    h1 { font-size: 28px; letter-spacing: -0.5px; margin: 16px 0 8px; }
    p { color: var(--muted); line-height: 1.5; }
    .dot { width: 56px; height: 56px; border-radius: 50%; margin: 0 auto; background: var(--accent); opacity: .9; }
</style>
</head>
<body>
<main>
    <div class="dot" aria-hidden="true"></div>
    <h1>{{ __('payments.return.'.$state.'.title') }}</h1>
    <p>{{ __('payments.return.'.$state.'.body', ['business' => $business]) }}</p>
</main>
</body>
</html>
