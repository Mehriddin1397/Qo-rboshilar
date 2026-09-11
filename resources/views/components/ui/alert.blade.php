@props(['type' => 'info'])

@php
    $styles = [
        'success' => 'border-success/30 bg-success/10 text-success',
        'error' => 'border-danger/30 bg-danger/10 text-danger',
        'warning' => 'border-gold-600/30 bg-gold-600/10 text-gold-600',
        'info' => 'border-brown-500/30 bg-brown-500/10 text-brown-700',
    ];
@endphp

<div {{ $attributes->class(['rounded-md border px-4 py-3 text-sm', $styles[$type]]) }}>
    {{ $slot }}
</div>
