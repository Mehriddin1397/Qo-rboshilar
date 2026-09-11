@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            @can('update', $mapMarker)
                <x-ui.button href="{{ route('admin.map-markers.edit', $mapMarker) }}" variant="secondary">Tahrirlash</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-1">
            <div class="flex flex-wrap justify-center gap-2">
                <x-ui.badge :color="$mapMarker->status->value === 'published' ? 'success' : 'neutral'">
                    {{ $mapMarker->status->label() }}
                </x-ui.badge>
                @if ($mapMarker->is_primary)
                    <x-ui.badge color="gold">Primary</x-ui.badge>
                @endif
            </div>
            <dl class="mt-6 space-y-3 text-sm">
                <div><dt class="text-brown-500">Turi</dt><dd class="text-brown-900">{{ $mapMarker->type->label() }}</dd></div>
                <div><dt class="text-brown-500">Qo'zg'olon</dt>
                    <dd class="text-brown-900">
                        @if ($mapMarker->uzgolon)
                            <a href="{{ route('admin.qozgolonlar.show', $mapMarker->uzgolon) }}" class="text-gold-600 hover:underline">{{ $mapMarker->uzgolon->name }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div><dt class="text-brown-500">Latitude</dt><dd class="text-brown-900">{{ $mapMarker->latitude }}</dd></div>
                <div><dt class="text-brown-500">Longitude</dt><dd class="text-brown-900">{{ $mapMarker->longitude }}</dd></div>
                <div><dt class="text-brown-500">Tartib raqami</dt><dd class="text-brown-900">{{ $mapMarker->sort_order }}</dd></div>
                <div><dt class="text-brown-500">Yaratilgan</dt><dd class="text-brown-900">{{ $mapMarker->created_at?->format('d.m.Y') }}</dd></div>
            </dl>
        </x-ui.card>

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Tavsif">
                <p class="whitespace-pre-line text-sm text-brown-700">{{ $mapMarker->description ?? '—' }}</p>
            </x-ui.card>

            <x-ui.card title="Xaritadagi joylashuv">
                <x-admin.map-point-picker :lat-value="$mapMarker->latitude" :lng-value="$mapMarker->longitude" readonly />
            </x-ui.card>
        </div>
    </div>
@endsection
