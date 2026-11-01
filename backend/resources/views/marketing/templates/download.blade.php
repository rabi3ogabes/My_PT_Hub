<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ \App\View\Locales::dir() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $name }} · {{ config('brand.name') }}</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=manrope:400,600,800|ibm-plex-sans-arabic:400,600&display=swap">
    <style>
        :root { color-scheme: light; }
        body { font-family: Manrope, 'IBM Plex Sans Arabic', system-ui, sans-serif; color: #121217; background: #FAF8F4; margin: 0; }
        .wrap { max-width: 860px; margin: 0 auto; padding: 48px 28px; }
        header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #E7E2D8; padding-bottom: 20px; }
        h1 { font-size: 2.25rem; letter-spacing: -0.03em; margin: 32px 0 8px; }
        h2 { font-size: 1.25rem; margin: 36px 0 12px; padding-bottom: 6px; border-bottom: 2px solid #D4AF6A; }
        h3 { font-size: 1rem; margin: 20px 0 8px; }
        p, li, td, th { font-size: 0.9375rem; line-height: 1.6; color: #4B4A52; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { text-align: start; padding: 6px 8px; border-bottom: 1px solid #E7E2D8; }
        th { color: #86848C; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; }
        .gold { color: #A9853F; font-weight: 700; }
        .btn { display: inline-block; background: #D4AF6A; color: #0B0B0F; padding: 10px 18px; border-radius: 12px; font-weight: 700; text-decoration: none; border: 0; cursor: pointer; font: inherit; }
        .rest { color: #86848C; font-style: italic; }
        @media print { .no-print { display: none; } body { background: #fff; } .wrap { padding: 0; } }
    </style>
</head>
<body>
    <div class="wrap">
        <header>
            <strong>{{ config('brand.name') }}</strong>
            <span class="no-print"><button type="button" class="btn" onclick="window.print()">{{ __('marketing.templates.print') }}</button></span>
        </header>
        <h1>{{ $name }}</h1>
        <p>{{ $program->translate('description') }}</p>
        <p class="gold">{{ __('marketing.templates.days_per_week', ['n' => $program->days_per_week]) }} · {{ __('marketing.exercises.levels.'.$program->level) }}</p>

        @forelse ($program->weeks as $week)
            <h2>{{ __('marketing.templates.week', ['n' => $week->number]) }}@if ($week->is_deload) · {{ __('marketing.templates.deload') }}@endif</h2>
            @foreach ($week->days as $day)
                <h3>{{ __('marketing.templates.day', ['n' => $day->day_number]) }}@if ($day->title) — {{ $day->title }}@elseif ($day->workout) — {{ $day->workout->name }}@endif</h3>
                @if ($day->is_rest || ! $day->workout)
                    <p class="rest">{{ __('marketing.templates.rest') }}</p>
                @else
                    <table>
                        <thead><tr><th>{{ __('marketing.templates.col_exercise') }}</th><th>{{ __('marketing.templates.col_sets') }}</th><th>{{ __('marketing.templates.col_reps') }}</th><th>{{ __('marketing.templates.col_rest') }}</th></tr></thead>
                        <tbody>
                            @foreach ($day->workout->blocks as $block)
                                @foreach ($block->exercises as $we)
                                    @php
                                        $sets = $we->sets;
                                        $reps = $sets->map(fn ($s) => $s->reps_min && $s->reps_max && $s->reps_min !== $s->reps_max ? $s->reps_min.'–'.$s->reps_max : ($s->reps_min ?? $s->reps_max))->filter()->unique()->implode(' / ');
                                    @endphp
                                    <tr>
                                        <td>{{ $we->exercise?->translate('name') }}</td>
                                        <td>{{ $sets->count() ?: $block->rounds }}</td>
                                        <td>{{ $reps ?: '—' }}</td>
                                        <td>{{ $we->rest_seconds ?? $block->rest_seconds ? ($we->rest_seconds ?? $block->rest_seconds).' s' : '—' }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endforeach
        @empty
            <p>{{ __('marketing.templates.no_weeks') }}</p>
        @endforelse

        <p style="margin-top:48px;border-top:1px solid #E7E2D8;padding-top:20px">{{ __('marketing.templates.footer', ['brand' => config('brand.name')]) }} <a href="{{ route('register') }}" class="gold">{{ __('marketing.cta.start_trial') }}</a></p>
    </div>
</body>
</html>
