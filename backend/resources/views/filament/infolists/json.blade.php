@php
    $state = $getState();
    $json = is_string($state) ? $state : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<div style="width: 100%">
    <pre dir="ltr" style="max-height:32rem;overflow:auto;border-radius:.75rem;background:#0B0B0F;color:#F5F3EE;padding:1rem;font-family:'JetBrains Mono',ui-monospace,monospace;font-size:.75rem;line-height:1.6;border:1px solid #2A2A35;white-space:pre;">{{ $json ?? 'null' }}</pre>
</div>
