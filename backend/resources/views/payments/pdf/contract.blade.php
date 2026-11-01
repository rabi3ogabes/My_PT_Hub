@php $align = $rtl ? 'right' : 'left'; @endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8">
<style>
    body { font-family: {{ $rtl ? 'xbriyaz, dejavusans' : 'dejavusans' }}; font-size: 10.5pt; color: #16161D; line-height: 1.5; }
    h1 { font-size: 18pt; margin: 0 0 4px 0; }
    .muted { color: #6B6B76; }
    .audit td { padding: 3px 6px; font-size: 8.5pt; border-bottom: 1px solid #E8E6E1; }
    .body { text-align: {{ $align }}; }
</style>
</head>
<body>
<h1>{{ $contract->title }}</h1>
<div class="muted">{{ $tenant?->name }} · {{ $contract->client?->name }}</div>
<hr>
<div class="body">{!! nl2br(e($contract->rendered_body)) !!}</div>
<br>
@if($contract->status === 'signed')
    <strong>{{ __('contracts.signature') }}</strong><br>
    @if($contract->signature_type === 'draw')
        <img src="{{ $contract->signature_data }}" style="height: 70px;">
    @else
        <span style="font-size: 20pt; font-style: italic;">{{ $contract->signature_data }}</span>
    @endif
    <br><br>
    <strong>{{ __('contracts.audit_trail') }}</strong>
    <table class="audit" style="width:100%; border-collapse: collapse;">
        <tr><td>{{ __('contracts.signed_by') }}</td><td>{{ $contract->client?->name }} ({{ $contract->client?->email }})</td></tr>
        <tr><td>{{ __('contracts.signed_at') }}</td><td>{{ $contract->signed_at?->toIso8601String() }}</td></tr>
        <tr><td>{{ __('contracts.ip_address') }}</td><td>{{ $contract->signed_ip }}</td></tr>
        <tr><td>{{ __('contracts.user_agent') }}</td><td>{{ $contract->signed_user_agent }}</td></tr>
        <tr><td>{{ __('contracts.document_hash') }}</td><td style="font-family: monospace; font-size: 7.5pt;">{{ $contract->document_hash }}</td></tr>
    </table>
@else
    <p class="muted">{{ __('contracts.awaiting_signature') }}</p>
@endif
</body>
</html>
