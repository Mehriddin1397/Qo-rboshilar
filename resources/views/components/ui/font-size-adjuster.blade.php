@props(['class' => ''])

<div
    x-data="{
        open: false,
        current: localStorage.getItem('qorboshilar_font_size') || '100%',
        sizes: [
            { scale: '90%', label: 'A−' },
            { scale: '100%', label: 'A' },
            { scale: '115%', label: 'A+' },
            { scale: '130%', label: 'A++' }
        ],
        apply(scale) {
            this.current = scale;
            document.documentElement.style.fontSize = scale;
            try {
                localStorage.setItem('qorboshilar_font_size', scale);
            } catch (e) {}
        }
    }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    class="relative inline-block text-left {{ $class }}"
>
    {{-- Yagona tushunarli belgi: [Aa] tugmasi --}}
    <button
        type="button"
        @click="open = !open"
        :aria-expanded="open.toString()"
        title="Aa"
        aria-label="Aa"
        :class="open ? 'border-gold-600 bg-paper-dark text-gold-600 ring-2 ring-gold-600/30' : 'border-sand bg-paper-dark/40 text-brown-900 hover:bg-paper-dark hover:text-gold-600'"
        class="inline-flex h-9 w-9 items-center justify-center rounded-md border font-serif text-sm font-bold shadow-xs transition focus:outline-none"
    >
        Aa
    </button>

    {{-- Tugma ichidagi 4 ta belgi paneli (matnsiz, faqat belgilar) --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 z-50 mt-1.5 flex items-center gap-1 rounded-lg border border-sand bg-paper p-1.5 shadow-lg ring-1 ring-brown-900/5 focus:outline-none"
    >
        <template x-for="item in sizes" :key="item.scale">
            <button
                type="button"
                @click="apply(item.scale)"
                :class="current === item.scale ? 'bg-gold-600 text-white font-bold shadow-xs' : 'text-brown-700 hover:text-brown-900 hover:bg-paper-dark/80 font-medium'"
                class="rounded-md px-2.5 py-1.5 font-serif text-xs transition"
                :title="item.label"
                x-text="item.label"
            ></button>
        </template>
    </div>
</div>
