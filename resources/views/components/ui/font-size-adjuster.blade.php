@props(['variant' => 'dropdown'])

@if ($variant === 'dropdown')
    {{-- Desktop versiya uchun: ochiladigan menyu --}}
    <div x-data="{
        open: false,
        current: localStorage.getItem('qorboshilar_font_size') || '100%',
        sizes: [
            { scale: '90%', label: 'Kichik', badge: 'A-' },
            { scale: '100%', label: 'Standart', badge: 'A' },
            { scale: '115%', label: 'Katta', badge: 'A+' },
            { scale: '130%', label: 'Juda katta', badge: 'A++' }
        ],
        apply(scale) {
            this.current = scale;
            document.documentElement.style.fontSize = scale;
            try {
                localStorage.setItem('qorboshilar_font_size', scale);
            } catch (e) {}
        },
        reset() {
            this.apply('100%');
        }
    }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative inline-block text-left">
        <button
            type="button"
            @click="open = !open"
            :aria-expanded="open.toString()"
            title="Matn o'lchamini sozlash"
            aria-label="Matn o'lchamini sozlash"
            class="inline-flex items-center gap-1.5 rounded-md border border-sand bg-paper-dark/40 px-2.5 py-1.5 text-xs font-semibold text-brown-900 shadow-sm transition hover:bg-paper-dark hover:text-gold-600 focus:outline-none focus:ring-2 focus:ring-gold-600/40"
        >
            <span class="font-serif text-sm font-bold tracking-tight">Aa</span>
            <span class="text-[11px] font-normal text-brown-700/80" x-text="current"></span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 text-brown-700 transition-transform" :class="{ 'rotate-180': open }">
                <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
            </svg>
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute right-0 z-50 mt-2 w-64 origin-top-right rounded-lg border border-sand bg-paper p-3 shadow-xl ring-1 ring-brown-900/5 focus:outline-none"
        >
            <div class="mb-2 flex items-center justify-between border-b border-sand/70 pb-2">
                <span class="font-serif text-xs font-semibold text-brown-900">Matn o'lchami</span>
                <button
                    type="button"
                    @click="reset()"
                    class="text-[11px] font-medium text-brown-700 underline decoration-sand transition hover:text-gold-600"
                >
                    Asl holatga
                </button>
            </div>

            {{-- 4 ta tezkor tugma paneli --}}
            <div class="grid grid-cols-4 gap-1.5 pb-2">
                <template x-for="item in sizes" :key="item.scale">
                    <button
                        type="button"
                        @click="apply(item.scale)"
                        :class="current === item.scale ? 'bg-gold-600 text-white font-bold shadow-sm' : 'bg-paper-dark/60 text-brown-900 hover:bg-paper-dark font-medium'"
                        class="flex flex-col items-center justify-center rounded-md py-1.5 text-center transition"
                        :title="item.label"
                    >
                        <span class="font-serif text-xs" x-text="item.badge"></span>
                        <span class="text-[9px] opacity-80" x-text="item.scale"></span>
                    </button>
                </template>
            </div>

            <p class="mt-1 text-center text-[10px] text-brown-700/70">
                Saytdagi barcha maqolalar va ma'lumotlar o'lchamini moslashtiradi
            </p>
        </div>
    </div>
@else
    {{-- Mobil menyu uchun: ixcham qator --}}
    <div x-data="{
        current: localStorage.getItem('qorboshilar_font_size') || '100%',
        sizes: [
            { scale: '90%', badge: 'A-' },
            { scale: '100%', badge: 'A' },
            { scale: '115%', badge: 'A+' },
            { scale: '130%', badge: 'A++' }
        ],
        apply(scale) {
            this.current = scale;
            document.documentElement.style.fontSize = scale;
            try {
                localStorage.setItem('qorboshilar_font_size', scale);
            } catch (e) {}
        }
    }" class="rounded-md border border-sand bg-paper-dark/30 p-2.5">
        <div class="mb-2 flex items-center justify-between">
            <span class="text-xs font-semibold text-brown-900">Matn o'lchami:</span>
            <span class="text-[11px] font-medium text-gold-600" x-text="current"></span>
        </div>
        <div class="grid grid-cols-4 gap-1.5">
            <template x-for="item in sizes" :key="item.scale">
                <button
                    type="button"
                    @click="apply(item.scale)"
                    :class="current === item.scale ? 'bg-gold-600 text-white font-bold' : 'bg-paper text-brown-900 border border-sand hover:bg-paper-dark'"
                    class="rounded py-1 text-center font-serif text-xs transition"
                >
                    <span x-text="item.badge"></span>
                    <span class="block text-[9px] opacity-80" x-text="item.scale"></span>
                </button>
            </template>
        </div>
    </div>
@endif
