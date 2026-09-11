@props(['placeholder' => "Qidiruv...", 'compact' => false])

<form method="GET" action="{{ route('search') }}" {{ $attributes->class(['relative']) }}>
    <label for="q-{{ $compact ? 'compact' : 'full' }}" class="sr-only">Qidiruv</label>

    <input
        id="q-{{ $compact ? 'compact' : 'full' }}"
        type="search"
        name="q"
        value="{{ request('q') }}"
        placeholder="{{ $placeholder }}"
        class="w-full rounded-md border border-sand bg-white py-1.5 pl-9 pr-3 text-sm text-ink placeholder:text-brown-500/60 focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600 {{ $compact ? 'w-40 focus:w-64 transition-[width]' : '' }}"
    >

    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
         class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-brown-500">
        <circle cx="11" cy="11" r="7" />
        <path d="m20 20-3.5-3.5" stroke-linecap="round" />
    </svg>
</form>
