@props(['variant' => 'primary', 'href' => null, 'type' => 'button'])

@php
    $variants = [
        'primary' => 'bg-brown-900 text-paper hover:bg-gold-600',
        'secondary' => 'border border-sand bg-white text-brown-900 hover:bg-paper-dark',
        'danger' => 'bg-danger text-paper hover:bg-danger/90',
    ];

    $classes = 'inline-flex items-center justify-center gap-1.5 rounded-md px-4 py-2 text-sm font-medium transition '.$variants[$variant];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
