@props(['title', 'meta' => null, 'excerpt' => null, 'image' => null, 'href' => null])

<article {{ $attributes->class(['group flex flex-col overflow-hidden rounded-lg border border-sand bg-white shadow-sm transition hover:border-gold-600 hover:shadow-md']) }}>
    <a href="{{ $href ?? '#' }}" class="flex h-40 items-center justify-center overflow-hidden bg-paper-dark">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $title }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
        @else
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"
                 class="h-10 w-10 text-brown-500/40">
                <rect x="3" y="4" width="18" height="16" rx="1" />
                <path d="m3 16 5-5 4 4 4-5 5 6" stroke-linejoin="round" />
                <circle cx="8.5" cy="8.5" r="1.5" />
            </svg>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-4">
        @if ($meta)
            <p class="text-xs font-medium uppercase tracking-wide text-gold-600">{{ $meta }}</p>
        @endif

        <h3 class="mt-1 font-serif text-lg font-semibold text-brown-900">
            <a href="{{ $href ?? '#' }}" class="hover:text-gold-600">{{ $title }}</a>
        </h3>

        @if ($excerpt)
            <p class="mt-2 line-clamp-3 flex-1 text-sm text-brown-700">{{ $excerpt }}</p>
        @endif

        @isset($footer)
            <div class="mt-3 border-t border-sand pt-3">
                {{ $footer }}
            </div>
        @endisset
    </div>
</article>
