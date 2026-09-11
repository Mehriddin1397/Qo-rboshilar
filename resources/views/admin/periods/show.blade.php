@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            @can('update', $period)
                <x-ui.button href="{{ route('admin.periods.edit', $period) }}" variant="secondary">Tahrirlash</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-1">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-brown-500">Slug</dt><dd class="text-brown-900">{{ $period->slug }}</dd></div>
                <div><dt class="text-brown-500">Yillar oralig'i</dt>
                    <dd class="text-brown-900">
                        @if ($period->start_year || $period->end_year)
                            {{ $period->start_year ?? '?' }}–{{ $period->end_year ?? '?' }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div><dt class="text-brown-500">Qo'zg'olonlar soni</dt><dd class="text-brown-900">{{ $period->uzgolonlar_count }}</dd></div>
                <div><dt class="text-brown-500">Tarixiy hududlar soni</dt><dd class="text-brown-900">{{ $period->historical_regions_count }}</dd></div>
                <div><dt class="text-brown-500">Xarita qatlamlari soni</dt><dd class="text-brown-900">{{ $period->historical_map_layers_count }}</dd></div>
                <div><dt class="text-brown-500">Xronologiya voqealari soni</dt><dd class="text-brown-900">{{ $period->timeline_events_count }}</dd></div>
                <div><dt class="text-brown-500">Yaratilgan</dt><dd class="text-brown-900">{{ $period->created_at?->format('d.m.Y') }}</dd></div>
            </dl>
        </x-ui.card>

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Tavsif">
                <p class="whitespace-pre-line text-sm text-brown-700">{{ $period->description ?? '—' }}</p>
            </x-ui.card>

            <x-ui.card title="Bog'langan Qo'zg'olonlar">
                @if ($period->uzgolonlar->isNotEmpty())
                    <ul class="space-y-2 text-sm">
                        @foreach ($period->uzgolonlar as $uzgolon)
                            <li class="flex items-center justify-between rounded-md border border-sand px-4 py-2.5">
                                <a href="{{ route('admin.qozgolonlar.show', $uzgolon) }}" class="text-brown-900 hover:text-gold-600">{{ $uzgolon->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                    @if ($period->uzgolonlar_count > $period->uzgolonlar->count())
                        <p class="mt-2 text-xs text-brown-500">... va yana {{ $period->uzgolonlar_count - $period->uzgolonlar->count() }} ta.</p>
                    @endif
                @else
                    <x-ui.empty-state message="Bu davrga hali qo'zg'olon biriktirilmagan." class="border-0 bg-transparent" />
                @endif
            </x-ui.card>

            <x-ui.card title="Bog'langan Tarixiy hududlar">
                @if ($period->historicalRegions->isNotEmpty())
                    <ul class="space-y-2 text-sm">
                        @foreach ($period->historicalRegions as $historicalRegion)
                            <li class="flex items-center justify-between rounded-md border border-sand px-4 py-2.5">
                                <a href="{{ route('admin.historical-regions.show', $historicalRegion) }}" class="text-brown-900 hover:text-gold-600">{{ $historicalRegion->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                    @if ($period->historical_regions_count > $period->historicalRegions->count())
                        <p class="mt-2 text-xs text-brown-500">... va yana {{ $period->historical_regions_count - $period->historicalRegions->count() }} ta.</p>
                    @endif
                @else
                    <x-ui.empty-state message="Bu davrga hali tarixiy hudud biriktirilmagan." class="border-0 bg-transparent" />
                @endif
            </x-ui.card>
        </div>
    </div>
@endsection
