@props(['items' => []])

@if (count($items))
    <nav {{ $attributes->class(['flex items-center gap-1.5 text-xs text-brown-700']) }} aria-label="Breadcrumb">
        @foreach ($items as $index => $crumb)
            @if ($index > 0)
                <span aria-hidden="true">/</span>
            @endif
            @if (! empty($crumb['url']) && $index < count($items) - 1)
                <a href="{{ $crumb['url'] }}" class="hover:text-gold-600">{{ $crumb['label'] }}</a>
            @else
                <span class="text-brown-900">{{ $crumb['label'] }}</span>
            @endif
        @endforeach
    </nav>
@endif
