@php
    $sections = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
        ['label' => "Qo'rboshilar", 'route' => 'admin.qorboshilar.index'],
        ['label' => "Qo'zg'olonlar", 'route' => 'admin.qozgolonlar.index'],
        ['label' => 'Adabiyotlar', 'route' => 'admin.adabiyotlar.index'],
        ['label' => 'Videolar', 'route' => 'admin.videolar.index'],
        ['label' => 'Bloglar', 'route' => 'admin.bloglar.index'],
        ['label' => 'Kommentlar', 'route' => 'admin.comments.index'],
        ['label' => 'Foydalanuvchilar', 'route' => 'admin.users.index'],
        ['label' => 'Hududlar', 'route' => 'admin.regions.index'],
        ['label' => 'Xarita markerlari', 'route' => 'admin.map-markers.index'],
        ['label' => 'Davrlar', 'route' => 'admin.periods.index'],
        ['label' => 'Manbalar', 'route' => 'admin.source-references.index'],
        ['label' => 'Tarixiy hududlar', 'route' => 'admin.historical-regions.index'],
        ['label' => 'Xarita qatlamlari', 'route' => 'admin.historical-map-layers.index'],
        ['label' => 'Xronologiya', 'route' => 'admin.timeline-events.index'],
        ['label' => 'Sozlamalar', 'route' => 'admin.settings.index'],
    ];
@endphp

{{-- Mobile backdrop --}}
<div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"
     class="fixed inset-0 z-30 bg-ink/50 lg:hidden"></div>

<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-40 flex w-72 transform flex-col border-r border-sand bg-brown-900 transition-transform duration-200 lg:translate-x-0"
>
    <div class="flex items-center justify-between px-6 py-5">
        <a href="{{ route('home') }}" class="flex items-center gap-2 font-serif text-lg font-semibold text-paper">
            <img src="{{ asset('logo/favicon-64x64.png') }}" alt="" class="h-8 w-8 rounded-full">
            <span>QO'RBOSHILAR<span class="text-gold-400">.UZ</span></span>
        </a>
        <button @click="sidebarOpen = false" class="text-paper-dark hover:text-paper lg:hidden" aria-label="Yopish">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-5 w-5">
                <path d="M6 6l12 12M18 6 6 18" stroke-linecap="round" />
            </svg>
        </button>
    </div>

    <p class="px-6 text-xs font-medium uppercase tracking-wider text-paper-dark/50">Admin panel</p>

    <nav class="mt-3 flex-1 space-y-1 overflow-y-auto px-3 pb-6">
        @foreach ($sections as $item)
            <a href="{{ route($item['route']) }}"
               class="block rounded-md px-3 py-2 text-sm font-medium transition
                      {{ request()->routeIs($item['route']) ? 'bg-gold-600 text-brown-900' : 'text-paper-dark hover:bg-brown-700 hover:text-paper' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</aside>
