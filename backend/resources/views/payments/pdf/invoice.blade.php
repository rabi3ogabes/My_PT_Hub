@php
    $isCredit = $invoice->type === 'credit_note';
    $align = $rtl ? 'right' : 'left';
    $opposite = $rtl ? 'left' : 'right';
    $seller = $invoice->seller ?? [];
    $buyer = $invoice->buyer ?? [];
    $accent = $seller['brand_color'] ?? '#D4AF6A';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8">
<style>
    body { font-family: {{ $rtl ? 'xbriyaz, dejavusans' : 'dejavusans' }}; font-size: 10pt; color: #16161D; }
    h1 { font-size: 20pt; margin: 0; letter-spacing: -0.5px; }
    .muted { color: #6B6B76; }
    .accent { color: {{ $accent }}; }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: {{ $align }}; font-size: 9pt; color: #6B6B76; border-bottom: 1px solid #16161D; padding: 6px 4px; }
    td { padding: 6px 4px; border-bottom: 1px solid #E8E6E1; vertical-align: top; }
    .num { text-align: {{ $opposite }}; white-space: nowrap; }
    .totals td { border: none; padding: 3px 4px; }
    .grand td { font-size: 12pt; font-weight: bold; border-top: 2px solid {{ $accent }}; }
    .box { width: 48%; vertical-align: top; }
</style>
</head>
<body>
<table>
    <tr>
        <td style="border:none; text-align: {{ $align }};">
            @if($logo)<img src="{{ $logo }}" style="height: 48px;"><br>@endif
            <h1>{{ $isCredit ? __('invoices.title.credit_note') : (($seller['vat_number'] ?? null) ? __('invoices.title.tax_invoice') : __('invoices.title.invoice')) }}</h1>
            <span class="accent">{{ $invoice->number }}</span>
        </td>
        <td style="border:none; text-align: {{ $opposite }};" class="muted">
            {{ __('invoices.issued_at') }}: {{ $invoice->issued_at?->toDateString() }}<br>
            @if($invoice->due_on){{ __('invoices.due_on') }}: {{ $invoice->due_on->toDateString() }}<br>@endif
            {{ __('invoices.status.'.$invoice->status) }}
            @if($original)<br>{{ __('invoices.credit_note_for', ['number' => $original->number]) }}@endif
        </td>
    </tr>
</table>
<br>
<table>
    <tr>
        <td class="box" style="border:none; text-align: {{ $align }};">
            <strong class="muted">{{ __('invoices.seller') }}</strong><br>
            <strong>{{ $seller['legal_name'] ?? $seller['name'] ?? '' }}</strong><br>
            @if(!empty($seller['address'])){{ $seller['address'] }}<br>@endif
            @if(!empty($seller['vat_number'])){{ __('invoices.vat_number') }}: {{ $seller['vat_number'] }}<br>@endif
            @if(!empty($seller['registration_number'])){{ __('invoices.registration_number') }}: {{ $seller['registration_number'] }}<br>@endif
            @if(!empty($seller['email'])){{ $seller['email'] }}@endif
        </td>
        <td class="box" style="border:none; text-align: {{ $align }};">
            <strong class="muted">{{ __('invoices.buyer') }}</strong><br>
            <strong>{{ $buyer['name'] ?? '' }}</strong><br>
            {{ $buyer['email'] ?? '' }}
        </td>
    </tr>
</table>
<br>
<table>
    <thead>
        <tr>
            <th>{{ __('invoices.description') }}</th>
            <th class="num">{{ __('invoices.quantity') }}</th>
            <th class="num">{{ __('invoices.unit_price') }}</th>
            <th class="num">{{ __('invoices.amount') }}</th>
        </tr>
    </thead>
    <tbody>
    @foreach($invoice->lines ?? [] as $line)
        <tr>
            <td>{{ $line['description'] }}</td>
            <td class="num">{{ $line['quantity'] }}</td>
            <td class="num">{{ $money((int) $line['unit_amount_minor']) }}</td>
            <td class="num">{{ $money((int) $line['total_minor']) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<br>
<table class="totals">
    <tr><td style="width:60%"></td><td>{{ __('invoices.subtotal') }}</td><td class="num">{{ $money((int) $invoice->subtotal_minor) }}</td></tr>
    <tr><td></td><td>{{ __('invoices.tax', ['rate' => rtrim(rtrim(number_format((float) $invoice->tax_rate, 2, '.', ''), '0'), '.')]) }}</td><td class="num">{{ $money((int) $invoice->tax_minor) }}</td></tr>
    <tr class="grand"><td style="border:none"></td><td>{{ $isCredit ? __('invoices.total_credited') : __('invoices.total') }}</td><td class="num">{{ $money((int) $invoice->total_minor) }}</td></tr>
</table>
@if($qr)
<br>
<div style="text-align: {{ $opposite }};">
    <img src="{{ $qr }}" style="width: 110px; height: 110px;"><br>
    <span class="muted" style="font-size: 8pt;">{{ __('invoices.zatca_qr') }}</span>
</div>
@endif
<br>
<p class="muted" style="font-size: 8pt; text-align: center;">{{ __('invoices.footer', ['business' => $seller['name'] ?? '']) }}</p>
</body>
</html>
