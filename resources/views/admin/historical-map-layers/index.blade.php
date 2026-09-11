@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <x-ui.button href="{{ route('admin.historical-map-layers.create') }}">+ Yangi qo'shish</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6">
        <x-ui.card>
            <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-5">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nomi yoki slug..."
                       class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">

                <select name="status" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha holatlar</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>

                <select name="period_id" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha davrlar</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}" @selected((string) ($filters['period_id'] ?? '') === (string) $period->id)>{{ $period->name }}</option>
                    @endforeach
                </select>

                <select name="historical_region_id" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha tarixiy hududlar</option>
                    @foreach ($historicalRegions as $region)
                        <option value="{{ $region->id }}" @selected((string) ($filters['historical_region_id'] ?? '') === (string) $region->id)>{{ $region->name }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <x-ui.button type="submit" variant="secondary">Filtrlash</x-ui.button>
                    @if (array_filter($filters))
                        <x-ui.button href="{{ route('admin.historical-map-layers.index') }}" variant="secondary">Tozalash</x-ui.button>
                    @endif
                </div>
            </form>

            @php $tableHeaders = ['Nomi', 'Davr', 'Tarixiy hudud', 'Holat', 'Aniqlik', 'Shaffoflik', 'Tartib', 'Yangilangan', 'Amallar']; @endphp
            <x-admin.table :headers="$tableHeaders">
                @forelse ($items as $layer)
                    <tr>
                        <td class="px-4 py-2.5 font-medium text-brown-900">{{ $layer->title }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $layer->period?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $layer->historicalRegion?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge :color="$layer->status->value === 'published' ? 'success' : 'neutral'">
                                {{ $layer->status->label() }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge :color="match($layer->accuracy_status->value) { 'verified' => 'success', 'approximate' => 'gold', default => 'danger' }">
                                {{ $layer->accuracy_status->label() }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $layer->opacity }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $layer->sort_order }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $layer->updated_at->format('d.m.Y') }}</td>
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-3 text-sm">
                                <a href="{{ route('admin.historical-map-layers.show', $layer) }}" class="text-brown-700 hover:text-gold-600">Ko'rish</a>
                                <a href="{{ route('admin.historical-map-layers.edit', $layer) }}" class="text-brown-700 hover:text-gold-600">Tahrirlash</a>
                                <form method="POST" action="{{ route('admin.historical-map-layers.destroy', $layer) }}"
                                      onsubmit="return confirm('&quot;{{ $layer->title }}&quot;ni o\'chirishni tasdiqlaysizmi?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger hover:underline">O'chirish</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <x-ui.empty-state message="Hozircha xarita qatlami qo'shilmagan." class="border-0 bg-transparent">
                                <x-slot:action>
                                    <x-ui.button href="{{ route('admin.historical-map-layers.create') }}">+ Birinchi qatlamni qo'shish</x-ui.button>
                                </x-slot:action>
                            </x-ui.empty-state>
                        </td>
                    </tr>
                @endforelse
            </x-admin.table>

            <x-ui.pagination :paginator="$items" />
        </x-ui.card>
    </div>
@endsection
