@props(['class' => ''])

<div
    x-data="{
        current: localStorage.getItem('qorboshilar_font_size') || '100%',
        apply(scale) {
            this.current = scale;
            document.documentElement.style.fontSize = scale;
            try {
                localStorage.setItem('qorboshilar_font_size', scale);
            } catch (e) {}
        }
    }"
    class="inline-flex items-center rounded-md border border-sand bg-paper-dark/50 p-0.5 text-xs shadow-xs {{ $class }}"
    role="group"
    aria-label="Font size"
>
    <button
        type="button"
        @click="apply('90%')"
        :class="current === '90%' ? 'bg-gold-600 text-white font-bold shadow-xs' : 'text-brown-700 hover:text-brown-900 hover:bg-paper'"
        class="rounded px-2 py-1 font-serif text-xs transition"
        title="A−"
        aria-label="A−"
    >
        A−
    </button>

    <button
        type="button"
        @click="apply('100%')"
        :class="current === '100%' ? 'bg-gold-600 text-white font-bold shadow-xs' : 'text-brown-700 hover:text-brown-900 hover:bg-paper'"
        class="rounded px-2.5 py-1 font-serif text-xs transition"
        title="A"
        aria-label="A"
    >
        A
    </button>

    <button
        type="button"
        @click="apply('115%')"
        :class="current === '115%' ? 'bg-gold-600 text-white font-bold shadow-xs' : 'text-brown-700 hover:text-brown-900 hover:bg-paper'"
        class="rounded px-2 py-1 font-serif text-xs transition"
        title="A+"
        aria-label="A+"
    >
        A+
    </button>

    <button
        type="button"
        @click="apply('130%')"
        :class="current === '130%' ? 'bg-gold-600 text-white font-bold shadow-xs' : 'text-brown-700 hover:text-brown-900 hover:bg-paper'"
        class="rounded px-2 py-1 font-serif text-xs transition"
        title="A++"
        aria-label="A++"
    >
        A++
    </button>
</div>
