@props(['label', 'active' => false])

<div x-data="{ open: false }" @keydown.escape.window="open = false" class="relative">
    <button
        type="button"
        @click="open = !open"
        @click.outside="open = false"
        :aria-expanded="open.toString()"
        aria-haspopup="true"
        class="flex items-center gap-1 text-sm font-medium text-brown-700 transition hover:text-gold-600 {{ $active ? 'text-gold-600' : '' }}"
    >
        {{ $label }}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
             class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }">
            <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </button>

    <div x-show="open" x-cloak x-transition
         class="absolute left-0 z-50 mt-2 w-48 rounded-md border border-sand bg-white py-1 shadow-lg"
         role="menu">
        {{ $slot }}
    </div>
</div>
