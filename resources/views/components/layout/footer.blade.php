<footer class="border-t border-sand bg-brown-900 text-paper-dark">
    <x-ui.container class="grid gap-8 py-12 lg:grid-cols-4">
        <div>
            <div class="flex items-center gap-2">
                <img src="{{ asset('logo/favicon-64x64.png') }}" alt="Qo'rboshilar.uz" class="h-9 w-9 rounded-full">
                <p class="font-serif text-lg font-semibold text-paper">QO'RBOSHILAR<span class="text-gold-400">.UZ</span></p>
            </div>
            <p class="mt-3 text-sm text-paper-dark/80">
                Turkiston tarixida bosqinchilikka va mustamlakachilikka qarshi kurashgan
                shaxslar, qo'zg'olonlar va tarixiy voqealarni o'rganish, hujjatlashtirish va
                ommaga yetkazishga qaratilgan raqamli tarixiy platforma.
            </p>
        </div>

        <div>
            <p class="font-serif text-sm font-semibold text-paper">Tezkor havolalar</p>
            <ul class="mt-3 space-y-2 text-sm text-paper-dark/80">
                <li><a href="/qorboshilar" class="hover:text-gold-400">Qo'rboshilar</a></li>
                <li><a href="/qozgolonlar" class="hover:text-gold-400">Qo'zg'olonlar</a></li>
                <li><a href="/xronologiya" class="hover:text-gold-400">Xronologiya</a></li>
                <li><a href="/xarita" class="hover:text-gold-400">Xarita</a></li>
                <li><a href="/adabiyotlar" class="hover:text-gold-400">Adabiyotlar</a></li>
                <li><a href="/videolar" class="hover:text-gold-400">Videolar</a></li>
                <li><a href="/bloglar" class="hover:text-gold-400">Bloglar</a></li>
            </ul>
        </div>

        <div>
            <p class="font-serif text-sm font-semibold text-paper">Loyiha</p>
            <ul class="mt-3 space-y-2 text-sm text-paper-dark/80">
                <li><a href="/biz-haqimizda" class="hover:text-gold-400">Biz haqimizda</a></li>
                <li><a href="/boglanish" class="hover:text-gold-400">Bog'lanish</a></li>
            </ul>
        </div>

        <div>
            <p class="font-serif text-sm font-semibold text-paper">Ijtimoiy tarmoqlar</p>
            <ul class="mt-3 space-y-2 text-sm text-paper-dark/80">
                <li><a href="#" class="hover:text-gold-400">Telegram</a></li>
                <li><a href="#" class="hover:text-gold-400">YouTube</a></li>
                <li><a href="#" class="hover:text-gold-400">Instagram</a></li>
            </ul>
        </div>
    </x-ui.container>

    <div class="border-t border-paper-dark/10 px-4 py-4 text-center text-xs text-paper-dark/60 sm:px-6 lg:px-8">
        &copy; {{ date('Y') }} Qo'rboshilar.uz — barcha huquqlar himoyalangan.
    </div>
</footer>
