@props(['message' => "Hozircha ma'lumot kiritilmagan."])

<div {{ $attributes->class(['flex flex-col items-center justify-center gap-3 rounded-lg border border-dashed border-sand bg-paper-dark/40 px-6 py-12 text-center']) }}>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"
         class="h-10 w-10 text-brown-500">
        <path d="M4 19.5V6a2 2 0 0 1 2-2h8.5L20 8.5V19.5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1Z" stroke-linejoin="round" />
        <path d="M14 4v4a1 1 0 0 0 1 1h4" stroke-linejoin="round" />
    </svg>

    <p class="max-w-sm text-sm text-brown-700">{{ $message }}</p>

    @isset($action)
        {{ $action }}
    @endisset
</div>
