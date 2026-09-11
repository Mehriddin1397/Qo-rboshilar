@props(['title' => null])

<div {{ $attributes->class(['rounded-lg border border-sand bg-white shadow-sm']) }}>
    @if ($title)
        <div class="border-b border-sand px-5 py-3">
            <h2 class="font-serif text-base font-semibold text-brown-900">{{ $title }}</h2>
        </div>
    @endif

    <div class="p-5">
        {{ $slot }}
    </div>
</div>
