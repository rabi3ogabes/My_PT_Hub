@props(['padding' => 'p-6', 'interactive' => false, 'as' => 'div'])
<{{ $as }} {{ $attributes->class([
    'rounded-[20px] border border-border bg-surface',
    $padding,
    'transition duration-200 ease-out hover:-translate-y-0.5 hover:border-border-strong hover:shadow-e2' => $interactive,
]) }}>
    @isset($header)
        <div class="mb-5 flex items-start justify-between gap-4">{{ $header }}</div>
    @endisset
    {{ $slot }}
</{{ $as }}>
