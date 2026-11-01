<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8">
<style>
    body { font-family: dejavusans, sans-serif; color: #16161D; font-size: 10pt; }
    h1 { font-size: 20pt; margin: 0 0 4pt; color: #0B0B0F; }
    h2 { font-size: 13pt; margin: 14pt 0 4pt; color: #0B0B0F; border-bottom: 1pt solid #D4AF6A; padding-bottom: 2pt; }
    h3 { font-size: 11pt; margin: 8pt 0 2pt; }
    .muted { color: #6b6b75; font-size: 9pt; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 6pt; }
    th { background: #F4EFE4; text-align: {{ $rtl ? 'right' : 'left' }}; padding: 3pt 4pt; font-size: 9pt; }
    td { padding: 3pt 4pt; border-bottom: 0.5pt solid #e5e1d8; font-size: 9pt; }
    td.num, th.num { text-align: {{ $rtl ? 'left' : 'right' }}; }
    .totals td { font-weight: bold; background: #FAF7F0; }
</style>
</head>
<body>
    <h1>{{ $plan->name }}</h1>
    <div class="muted">
        {{ __('nutrition.plan_types.'.$plan->type) }}
        @if ($plan->client) · {{ $plan->client->name }} @endif
        @if ($plan->start_date) · {{ __('nutrition.pdf.starts', ['date' => $plan->start_date->toDateString()]) }} @endif
    </div>

    @foreach ($days as $entry)
        <h2>{{ $entry['day']->label ?: __('nutrition.pdf.day', ['number' => $entry['day']->day_number]) }}</h2>
        @foreach ($entry['meals'] as $meal)
            <h3>{{ $meal['name'] }} @if ($meal['time'])<span class="muted">· {{ $meal['time'] }}</span>@endif</h3>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('nutrition.pdf.food') }}</th>
                        <th class="num">{{ __('nutrition.pdf.quantity') }}</th>
                        <th class="num">{{ __('nutrition.macros.kcal') }}</th>
                        <th class="num">{{ __('nutrition.macros.protein') }}</th>
                        <th class="num">{{ __('nutrition.macros.carbs') }}</th>
                        <th class="num">{{ __('nutrition.macros.fat') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($meal['items'] as $item)
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td class="num">{{ rtrim(rtrim(number_format($item['quantity'], 2, '.', ''), '0'), '.') }} {{ __('nutrition.units.'.$item['unit']) !== 'nutrition.units.'.$item['unit'] ? __('nutrition.units.'.$item['unit']) : $item['unit'] }}</td>
                        <td class="num">{{ round($item['kcal']) }}</td>
                        <td class="num">{{ $item['protein_g'] }}</td>
                        <td class="num">{{ $item['carbs_g'] }}</td>
                        <td class="num">{{ $item['fat_g'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endforeach
        <table>
            <tr class="totals">
                <td>{{ __('nutrition.pdf.day_total') }}</td>
                <td class="num">{{ round($entry['totals']['kcal']) }} {{ __('nutrition.macros.kcal') }}</td>
                <td class="num">{{ __('nutrition.macros.protein') }} {{ $entry['totals']['protein_g'] }} g</td>
                <td class="num">{{ __('nutrition.macros.carbs') }} {{ $entry['totals']['carbs_g'] }} g</td>
                <td class="num">{{ __('nutrition.macros.fat') }} {{ $entry['totals']['fat_g'] }} g</td>
            </tr>
        </table>
    @endforeach

    @if (count($grocery))
        <pagebreak />
        <h2>{{ __('nutrition.pdf.grocery_list') }}</h2>
        @foreach ($grocery as $aisle)
            <h3>{{ $aisle['label'] }}</h3>
            <table>
                @foreach ($aisle['items'] as $item)
                    <tr><td>{{ $item['name'] }}</td><td class="num">{{ $item['grams'] }} g</td></tr>
                @endforeach
            </table>
        @endforeach
    @endif
</body>
</html>
