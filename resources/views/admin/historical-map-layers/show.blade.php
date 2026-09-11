@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            @can('update', $layer)
                <x-ui.button href="{{ route('admin.historical-map-layers.edit', $layer) }}" variant="secondary">Tahrirlash</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-1">
            @if ($layer->imageUrl())
                <img src="{{ $layer->imageUrl() }}" alt="" class="h-40 w-full rounded object-cover">
            @endif
            <div class="mt-4 flex flex-wrap justify-center gap-2">
                <x-ui.badge :color="$layer->status->value === 'published' ? 'success' : 'neutral'">
                    {{ $layer->status->label() }}
                </x-ui.badge>
                <x-ui.badge :color="match($layer->accuracy_status->value) { 'verified' => 'success', 'approximate' => 'gold', default => 'danger' }">
                    {{ $layer->accuracy_status->label() }}
                </x-ui.badge>
                @if ($layer->is_active)
                    <x-ui.badge color="gold">Faol</x-ui.badge>
                @endif
            </div>
            <dl class="mt-6 space-y-3 text-sm">
                <div><dt class="text-brown-500">Slug</dt><dd class="text-brown-900">{{ $layer->slug }}</dd></div>
                <div><dt class="text-brown-500">Davr</dt><dd class="text-brown-900">{{ $layer->period?->name ?? '—' }}</dd></div>
                <div><dt class="text-brown-500">Tarixiy hudud</dt><dd class="text-brown-900">{{ $layer->historicalRegion?->name ?? '—' }}</dd></div>
                <div><dt class="text-brown-500">Shaffoflik</dt><dd class="text-brown-900">{{ $layer->opacity }}</dd></div>
                <div><dt class="text-brown-500">Tartib raqami</dt><dd class="text-brown-900">{{ $layer->sort_order }}</dd></div>
                <div>
                    <dt class="text-brown-500">Bounds</dt>
                    <dd class="text-brown-900">
                        @if ($layer->bounds)
                            N {{ $layer->bounds['north'] ?? '—' }}, S {{ $layer->bounds['south'] ?? '—' }},
                            E {{ $layer->bounds['east'] ?? '—' }}, W {{ $layer->bounds['west'] ?? '—' }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>
        </x-ui.card>

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Tavsif">
                <p class="whitespace-pre-line text-sm text-brown-700">{{ $layer->description ?? '—' }}</p>
            </x-ui.card>

            @if ($layer->sourceReferences->isNotEmpty())
                <x-ui.card title="Manbalar">
                    <ul class="space-y-2 text-sm text-brown-700">
                        @foreach ($layer->sourceReferences as $source)
                            <li class="rounded-md border border-sand bg-white px-4 py-3">
                                <span class="font-medium text-brown-900">{{ $source->author }}</span> — {{ $source->title }}
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            @endif

            <x-ui.card title="GeoJSON preview">
                @if ($layer->geojson)
                    <div
                        id="historical-layer-preview"
                        class="h-80 w-full rounded-lg border border-sand"
                        x-data
                        x-init="window.initGeoJsonPreview('historical-layer-preview', @js($layer->geojson))"
                    ></div>
                    <p class="mt-2 text-xs text-brown-500">
                        Bu faqat mavjud GeoJSON'ning texnik ko'rinishi — tarixiy jihatdan tekshirilgan chegara emas.
                    </p>
                @else
                    <x-ui.empty-state message="Bu qatlam uchun GeoJSON kiritilmagan." class="border-0 bg-transparent" />
                @endif
            </x-ui.card>
        </div>
    </div>
@endsection
