@php
    $directLinks = [
        '/qorboshilar' => "Qo'rboshilar",
        '/qozgolonlar' => "Qo'zg'olonlar",
    ];

    $tarixLinks = [
        '/xronologiya' => 'Xronologiya',
        '/xarita' => 'Xarita',
    ];

    $manbalarLinks = [
        '/adabiyotlar' => 'Adabiyotlar',
        '/videolar' => 'Videolar',
        '/bloglar' => 'Bloglar',
    ];

    $loyihaLinks = [
        '/biz-haqimizda' => 'Biz haqimizda',
        '/boglanish' => "Bog'lanish",
    ];

    $isActive = fn (string $href) => request()->is(trim($href, '/') ?: '/');
    $isGroupActive = fn (array $links) => collect($links)->keys()->contains(fn ($href) => $isActive($href));
@endphp

<header x-data="{ mobileOpen: false }" class="border-b border-sand bg-paper">
    <x-ui.container class="flex items-center justify-between gap-4 py-4">
        <a href="/" class="flex items-center gap-2 font-serif text-xl font-semibold tracking-wide text-brown-900">
            <img src="{{ asset('logo/favicon-64x64.png') }}" alt="Qo'rboshilar.uz" class="h-9 w-9 rounded-full">
            <span>QO'RBOSHILAR<span class="text-gold-600">.UZ</span></span>
        </a>

        <nav class="hidden items-center gap-6 lg:flex">
            @foreach ($directLinks as $href => $label)
                <a href="{{ $href }}"
                   class="text-sm font-medium text-brown-700 transition hover:text-gold-600 {{ $isActive($href) ? 'text-gold-600' : '' }}">
                    {{ $label }}
                </a>
            @endforeach

            <x-layout.nav-dropdown label="Tarix" :active="$isGroupActive($tarixLinks)">
                @foreach ($tarixLinks as $href => $label)
                    <a href="{{ $href }}" role="menuitem"
                       class="block px-4 py-2 text-sm text-brown-900 hover:bg-paper-dark {{ $isActive($href) ? 'text-gold-600' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </x-layout.nav-dropdown>

            <x-layout.nav-dropdown label="Manbalar" :active="$isGroupActive($manbalarLinks)">
                @foreach ($manbalarLinks as $href => $label)
                    <a href="{{ $href }}" role="menuitem"
                       class="block px-4 py-2 text-sm text-brown-900 hover:bg-paper-dark {{ $isActive($href) ? 'text-gold-600' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </x-layout.nav-dropdown>

            <x-layout.nav-dropdown label="Loyiha" :active="$isGroupActive($loyihaLinks)">
                @foreach ($loyihaLinks as $href => $label)
                    <a href="{{ $href }}" role="menuitem"
                       class="block px-4 py-2 text-sm text-brown-900 hover:bg-paper-dark {{ $isActive($href) ? 'text-gold-600' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </x-layout.nav-dropdown>
        </nav>

        <div class="hidden items-center gap-4 lg:flex">
            <x-ui.search-bar compact />

            @auth
                @php $currentUser = auth()->user(); @endphp
                @if ($currentUser->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-brown-700 hover:text-gold-600">
                        Admin paneli
                    </a>
                @elseif ($currentUser->isEditor())
                    <a href="{{ route('profile.edit') }}" class="text-sm font-medium text-brown-700 hover:text-gold-600">
                        Profil
                    </a>
                    <a href="{{ route('admin.comments.index') }}" class="text-sm font-medium text-brown-700 hover:text-gold-600">
                        Tahrir paneli
                    </a>
                @else
                    <a href="{{ route('profile.edit') }}" class="text-sm font-medium text-brown-700 hover:text-gold-600">
                        Profil
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-brown-700 hover:text-danger">Chiqish</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-brown-700 hover:text-gold-600">Kirish</a>
                <x-ui.button href="{{ route('register') }}">Ro'yxatdan o'tish</x-ui.button>
            @endauth
        </div>

        <button @click="mobileOpen = !mobileOpen" aria-label="Menyu" class="text-brown-900 lg:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-6 w-6">
                <path x-show="!mobileOpen" d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
                <path x-show="mobileOpen" x-cloak d="M6 6l12 12M18 6 6 18" stroke-linecap="round" />
            </svg>
        </button>
    </x-ui.container>

    <div x-show="mobileOpen" x-cloak x-transition class="border-t border-sand bg-paper lg:hidden">
        <x-ui.container class="flex flex-col gap-1 py-4">
            <x-ui.search-bar class="mb-2" />

            @foreach ($directLinks as $href => $label)
                <a href="{{ $href }}"
                   class="rounded-md px-3 py-2 text-sm font-medium text-brown-700 hover:bg-paper-dark {{ $isActive($href) ? 'text-gold-600' : '' }}">
                    {{ $label }}
                </a>
            @endforeach

            @foreach ([['Tarix', $tarixLinks], ['Manbalar', $manbalarLinks], ['Loyiha', $loyihaLinks]] as [$groupLabel, $groupLinks])
                <div x-data="{ open: {{ $isGroupActive($groupLinks) ? 'true' : 'false' }} }" class="border-t border-sand pt-1 first:border-t-0 first:pt-0">
                    <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                            class="flex w-full items-center justify-between rounded-md px-3 py-2 text-sm font-medium text-brown-900">
                        <span>{{ $groupLabel }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                             class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }">
                            <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                    <div x-show="open" x-cloak x-transition class="flex flex-col gap-1 pl-4">
                        @foreach ($groupLinks as $href => $label)
                            <a href="{{ $href }}"
                               class="rounded-md px-3 py-2 text-sm font-medium text-brown-700 hover:bg-paper-dark {{ $isActive($href) ? 'text-gold-600' : '' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="my-2 border-t border-sand"></div>
            @auth
                @php $currentUser = auth()->user(); @endphp
                @if ($currentUser->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-brown-700 hover:bg-paper-dark">
                        Admin paneli
                    </a>
                @elseif ($currentUser->isEditor())
                    <a href="{{ route('profile.edit') }}" class="rounded-md px-3 py-2 text-sm font-medium text-brown-700 hover:bg-paper-dark">
                        Profil
                    </a>
                    <a href="{{ route('admin.comments.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-brown-700 hover:bg-paper-dark">
                        Tahrir paneli
                    </a>
                @else
                    <a href="{{ route('profile.edit') }}" class="rounded-md px-3 py-2 text-sm font-medium text-brown-700 hover:bg-paper-dark">
                        Profil
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-sm font-medium text-brown-700 hover:bg-paper-dark">
                        Chiqish
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm font-medium text-brown-700 hover:bg-paper-dark">Kirish</a>
                <a href="{{ route('register') }}" class="rounded-md px-3 py-2 text-sm font-medium text-brown-700 hover:bg-paper-dark">
                    Ro'yxatdan o'tish
                </a>
            @endauth
        </x-ui.container>
    </div>
</header>
