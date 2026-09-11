@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            @can('update', $historicalRegion)
                <x-ui.button href="{{ route('admin.historical-regions.edit', $historicalRegion) }}" variant="secondary">Tahrirlash</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-1">
            <div class="flex flex-wrap justify-center gap-2">
                <x-ui.badge :color="$historicalRegion->status->value === 'published' ? 'success' : 'neutral'">
                    {{ $historicalRegion->status->label() }}
                </x-ui.badge>
                <x-ui.badge :color="match($historicalRegion->accuracy_status->value) { 'verified' => 'success', 'approximate' => 'gold', default => 'danger' }">
                    {{ $historicalRegion->accuracy_status->label() }}
                </x-ui.badge>
                @if ($historicalRegion->featured)
                    <x-ui.badge color="gold">Featured</x-ui.badge>
                @endif
            </div>
            <dl class="mt-6 space-y-3 text-sm">
                <div><dt class="text-brown-500">Slug</dt><dd class="text-brown-900">{{ $historicalRegion->slug }}</dd></div>
                <div><dt class="text-brown-500">Hudud turi</dt><dd class="text-brown-900">{{ $historicalRegion->region_type->label() }}</dd></div>
                <div><dt class="text-brown-500">Tarixiy nomi</dt><dd class="text-brown-900">{{ $historicalRegion->historical_name ?? '—' }}</dd></div>
                <div><dt class="text-brown-500">Zamonaviy nomi</dt><dd class="text-brown-900">{{ $historicalRegion->modern_name ?? '—' }}</dd></div>
                <div><dt class="text-brown-500">Davr</dt><dd class="text-brown-900">{{ $historicalRegion->period?->name ?? '—' }}</dd></div>
                <div><dt class="text-brown-500">Zamonaviy hudud</dt><dd class="text-brown-900">{{ $historicalRegion->region?->name ?? '—' }}</dd></div>
                <div><dt class="text-brown-500">Tartib raqami</dt><dd class="text-brown-900">{{ $historicalRegion->sort_order }}</dd></div>
                <div><dt class="text-brown-500">Xarita qatlamlari</dt><dd class="text-brown-900">{{ $historicalRegion->historicalMapLayers->count() }}</dd></div>
            </dl>
        </x-ui.card>

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Tavsif">
                <p class="whitespace-pre-line text-sm text-brown-700">{{ $historicalRegion->description ?? '—' }}</p>
            </x-ui.card>

            <x-ui.card title="Manbalar (Source / Provenance)">
                @if ($historicalRegion->sourceReferences->isNotEmpty())
                    <ul class="space-y-2 text-sm text-brown-700">
                        @foreach ($historicalRegion->sourceReferences as $source)
                            <li class="rounded-md border border-sand bg-white px-4 py-3">
                                <span class="font-medium text-brown-900">{{ $source->author }}</span> — {{ $source->title }}
                                @if ($source->year)<span class="text-brown-500">({{ $source->year }})</span>@endif
                                @if ($source->page)<span class="text-brown-500">, bet: {{ $source->page }}</span>@endif
                                @if ($source->url)
                                    <br>
                                    <a href="{{ $source->url }}" target="_blank" rel="noopener noreferrer" class="text-gold-600 hover:underline">Manbani ko'rish</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <x-ui.empty-state message="Manba kiritilmagan." class="border-0 bg-transparent" />
                @endif
            </x-ui.card>

            <x-ui.card title="GeoJSON preview">
                @if ($historicalRegion->geojson)
                    <div
                        id="historical-region-preview"
                        class="h-80 w-full rounded-lg border border-sand"
                        x-data
                        x-init="window.initGeoJsonPreview('historical-region-preview', @js($historicalRegion->geojson))"
                    ></div>
                    <p class="mt-2 text-xs text-brown-500">
                        Bu faqat mavjud GeoJSON'ning texnik ko'rinishi — tarixiy jihatdan tekshirilgan chegara emas.
                    </p>
                @else
                    <x-ui.empty-state message="Bu tarixiy hudud uchun GeoJSON kiritilmagan." class="border-0 bg-transparent" />
                @endif
            </x-ui.card>

            @if ($historicalRegion->historicalMapLayers->isNotEmpty())
                <x-ui.card title="Bog'liq xarita qatlamlari">
                    <ul class="space-y-2 text-sm">
                        @foreach ($historicalRegion->historicalMapLayers as $layer)
                            <li class="flex items-center justify-between rounded-md border border-sand px-4 py-2.5">
                                <a href="{{ route('admin.historical-map-layers.show', $layer) }}" class="text-brown-900 hover:text-gold-600">{{ $layer->title }}</a>
                                <x-ui.badge :color="$layer->status->value === 'published' ? 'success' : 'neutral'">{{ $layer->status->label() }}</x-ui.badge>
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            @endif
        </div>
    </div>
@endsection
