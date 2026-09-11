@extends('layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])

@section('content')
    <section class="border-b border-sand bg-paper-dark py-10">
        <x-ui.container class="max-w-3xl text-center">
            <h1 class="font-serif text-3xl font-semibold text-brown-900 sm:text-4xl">Interaktiv xarita</h1>
            <p class="mt-3 text-brown-700">
                Turkiston tarixidagi qo'zg'olonlarning joylashuvi va tarixiy xarita qatlamlari — hududlar,
                chegaralar va davrlar bo'yicha.
            </p>
        </x-ui.container>
    </section>

    <section class="py-10">
        <x-ui.container>
            <form method="GET" class="mb-6 grid gap-3 sm:grid-cols-4">
                <select name="region" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600" aria-label="Hudud bo'yicha filtrlash">
                    <option value="">Barcha hududlar</option>
                    @foreach ($regions as $region)
                        <option value="{{ $region->id }}" @selected((string) ($filters['region'] ?? '') === (string) $region->id)>{{ $region->name }}</option>
                    @endforeach
                </select>

                <select name="period" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600" aria-label="Davr bo'yicha filtrlash">
                    <option value="">Barcha davrlar</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}" @selected((string) ($filters['period'] ?? '') === (string) $period->id)>{{ $period->name }}</option>
                    @endforeach
                </select>

                <x-ui.button type="submit" variant="secondary">Filtrlash</x-ui.button>
                @if (array_filter($filters))
                    <x-ui.button href="{{ route('xarita') }}" variant="secondary">Tozalash</x-ui.button>
                @endif
            </form>

            @if (empty($geojson['features']) && empty($historicalRegions['features']) && empty($historicalLayers['features']) && empty($rasterLayers) && empty($timelineEvents['features']))
                <x-ui.empty-state message="Bu davr uchun tarixiy xarita ma'lumoti mavjud emas." class="mb-4" />
            @endif

            <div
                x-data="{
                    mapInstance: null,
                    mapError: null,
                    loaded: false,
                    showRegions: true,
                    showBoundaries: false,
                    showMarkers: true,
                    showTimeline: true,
                    showRaster: true,
                    panelOpen: false,
                    hasRaster: false,
                    init() {
                        this.mapInstance = window.initTurkestanMap('turkestan-map', {
                            markers: @js($geojson),
                            historicalRegions: @js($historicalRegions),
                            historicalLayers: @js($historicalLayers),
                            rasterLayers: @js($rasterLayers),
                            timelineEvents: @js($timelineEvents),
                        }, {
                            onError: (message) => { this.mapError = message; },
                            focusEventSlug: @js($focusEventSlug),
                        });
                        this.hasRaster = this.mapInstance?.hasRasterLayers ?? false;
                        this.loaded = true;
                    },
                    toggleRegions() {
                        this.showRegions = !this.showRegions;
                        this.mapInstance?.setLayerVisibility(['historical-regions-fill', 'historical-regions-outline'], this.showRegions);
                    },
                    toggleBoundaries() {
                        this.showBoundaries = !this.showBoundaries;
                        this.mapInstance?.setLayerVisibility('historical-layers-line', this.showBoundaries);
                    },
                    toggleMarkers() {
                        this.showMarkers = !this.showMarkers;
                        this.mapInstance?.setLayerVisibility('uprising-markers-points', this.showMarkers);
                    },
                    toggleTimeline() {
                        this.showTimeline = !this.showTimeline;
                        this.mapInstance?.setLayerVisibility('timeline-events-points', this.showTimeline);
                    },
                    toggleRaster() {
                        this.showRaster = !this.showRaster;
                        this.mapInstance?.setLayerVisibility(this.mapInstance.rasterLayerIds, this.showRaster);
                    },
                }"
            >
                <div class="relative">
                    <div
                        id="turkestan-map"
                        class="h-[420px] w-full rounded-lg border border-sand sm:h-[560px]"
                    ></div>

                    <div x-show="!loaded" class="absolute inset-0 flex items-center justify-center rounded-lg bg-paper-dark/70 text-sm text-brown-700">
                        Xarita yuklanmoqda...
                    </div>

                    <div x-show="mapError" x-cloak class="absolute inset-x-4 top-4 rounded-md border border-danger bg-white px-4 py-2 text-sm text-danger shadow">
                        <span x-text="mapError"></span>
                    </div>
                </div>

                <div class="mt-4 rounded-lg border border-sand bg-white">
                    <button
                        type="button"
                        @click="panelOpen = !panelOpen"
                        class="flex w-full items-center justify-between px-4 py-3 text-sm font-medium text-brown-900 lg:cursor-default lg:hover:bg-transparent"
                        :aria-expanded="panelOpen"
                        aria-controls="map-layers-panel"
                    >
                        <span>Xarita qatlamlari va legenda</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                             class="h-4 w-4 transition-transform lg:hidden" :class="panelOpen ? 'rotate-180' : ''" aria-hidden="true">
                            <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <div id="map-layers-panel" x-show="panelOpen" x-transition class="border-t border-sand px-4 py-4 lg:!block" :class="panelOpen ? '' : 'hidden lg:block'">
                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            <fieldset>
                                <legend class="text-xs font-medium uppercase tracking-wide text-brown-500">Qatlamlar</legend>
                                <div class="mt-2 space-y-2 text-sm text-brown-900">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" x-model="showRegions" @change="toggleRegions()" class="rounded border-sand text-gold-600 focus:ring-gold-600">
                                        Tarixiy hududlar
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" x-model="showBoundaries" @change="toggleBoundaries()" class="rounded border-sand text-gold-600 focus:ring-gold-600">
                                        Tarixiy chegaralar
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" x-model="showTimeline" @change="toggleTimeline()" class="rounded border-sand text-gold-600 focus:ring-gold-600">
                                        Xronologiya voqealari
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" x-model="showMarkers" @change="toggleMarkers()" class="rounded border-sand text-gold-600 focus:ring-gold-600">
                                        Qo'zg'olonlar
                                    </label>
                                    <label class="flex items-center gap-2" x-show="hasRaster">
                                        <input type="checkbox" x-model="showRaster" @change="toggleRaster()" class="rounded border-sand text-gold-600 focus:ring-gold-600">
                                        Tarixiy xarita (raster)
                                    </label>
                                </div>
                            </fieldset>

                            <div class="sm:col-span-1 lg:col-span-3">
                                <p class="text-xs font-medium uppercase tracking-wide text-brown-500">Legenda</p>
                                <ul class="mt-2 space-y-2 text-sm text-brown-700">
                                    <li class="flex items-center gap-2">
                                        <span class="h-3 w-3 rounded-sm" style="background-color: #A6791E; opacity: 0.4;" aria-hidden="true"></span>
                                        Tarixiy hudud
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="h-0.5 w-4 border-t-2 border-dashed" style="border-color: #6B4A32;" aria-hidden="true"></span>
                                        Tarixiy chegara (uzuq — taxminiy)
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 rounded-full border-2" style="background-color: #1A1512; border-color: #A6791E;" aria-hidden="true"></span>
                                        Xronologiya voqeasi
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="h-3 w-3 rounded-full" style="background-color: #A6791E;" aria-hidden="true"></span>
                                        Qo'zg'olon
                                    </li>
                                </ul>
                                <p class="mt-3 text-xs text-brown-500">
                                    Diqqat: uzuq chiziq — geometriya "taxminiy" yoki "noaniq" deb belgilangan. Bu texnik
                                    xarita ko'rinishi tarixiy haqiqat sifatida qabul qilinmasligi kerak — batafsil
                                    ma'lumot uchun har bir hudud/chegaraning "Manba" bo'limiga qarang.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.container>
    </section>
@endsection
