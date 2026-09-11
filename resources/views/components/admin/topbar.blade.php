<header class="flex h-16 items-center justify-between border-b border-sand bg-paper px-4 sm:px-6 lg:px-8">
    <button @click="sidebarOpen = true" aria-label="Menyu" class="text-brown-900 lg:hidden">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-6 w-6">
            <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
        </svg>
    </button>

    <a href="{{ route('home') }}" class="hidden text-sm font-medium text-brown-700 hover:text-gold-600 lg:inline">
        &larr; Saytga qaytish
    </a>

    <div x-data="{ userMenuOpen: false }" class="relative ml-auto">
        <button @click="userMenuOpen = !userMenuOpen" @click.outside="userMenuOpen = false"
                class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm font-medium text-brown-900 hover:bg-paper-dark">
            <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="h-7 w-7 rounded-full object-cover">
            <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-4 w-4">
                <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>

        <div x-show="userMenuOpen" x-cloak x-transition
             class="absolute right-0 z-50 mt-2 w-48 rounded-md border border-sand bg-white py-1 shadow-lg">
            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-brown-900 hover:bg-paper-dark">
                Profilim
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-danger hover:bg-paper-dark">
                    Chiqish
                </button>
            </form>
        </div>
    </div>
</header>
