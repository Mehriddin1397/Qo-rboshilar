@props(['color' => 'neutral'])

@php
    $styles = [
        'gold' => 'bg-gold-600/15 text-gold-600',
        'success' => 'bg-success/15 text-success',
        'danger' => 'bg-danger/15 text-danger',
        'neutral' => 'bg-brown-500/15 text-brown-700',
    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', $styles[$color] ?? $styles['neutral']]) }}>
    {{ $slot }}
</span>
