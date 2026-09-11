@props(['title', 'subtitle' => null])

<div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h2 class="font-serif text-2xl font-semibold text-brown-900 sm:text-3xl">{{ $title }}</h2>
        @if ($subtitle)
            <p class="mt-1 text-sm text-brown-700">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($action)
        <div class="text-sm">
            {{ $action }}
        </div>
    @endisset
</div>
