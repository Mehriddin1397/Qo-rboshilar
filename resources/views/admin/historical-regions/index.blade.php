@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <x-ui.button href="{{ route('admin.historical-regions.create') }}">+ Yangi qo'shish</x-ui.button>
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

                <select name="region_id" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha hududlar</option>
                    @foreach ($regions as $region)
                        <option value="{{ $region->id }}" @selected((string) ($filters['region_id'] ?? '') === (string) $region->id)>{{ $region->name }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <x-ui.button type="submit" variant="secondary">Filtrlash</x-ui.button>
                    @if (array_filter($filters))
                        <x-ui.button href="{{ route('admin.historical-regions.index') }}" variant="secondary">Tozalash</x-ui.button>
                    @endif
                </div>
            </form>

            @php $tableHeaders = ['Nomi', 'Davr', 'Hudud', 'Holat', 'Aniqlik', 'Featured', 'Tartib', 'Yangilangan', 'Amallar']; @endphp
            <x-admin.table :headers="$tableHeaders">
                @forelse ($items as $historicalRegion)
                    <tr>
                        <td class="px-4 py-2.5 font-medium text-brown-900">{{ $historicalRegion->name }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $historicalRegion->period?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $historicalRegion->region?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge :color="$historicalRegion->status->value === 'published' ? 'success' : 'neutral'">
                                {{ $historicalRegion->status->label() }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge :color="match($historicalRegion->accuracy_status->value) { 'verified' => 'success', 'approximate' => 'gold', default => 'danger' }">
                                {{ $historicalRegion->accuracy_status->label() }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5">
                            @if ($historicalRegion->featured)
                                <x-ui.badge color="gold">Featured</x-ui.badge>
                            @else
                                <span class="text-brown-500/50">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $historicalRegion->sort_order }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $historicalRegion->updated_at->format('d.m.Y') }}</td>
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-3 text-sm">
                                <a href="{{ route('admin.historical-regions.show', $historicalRegion) }}" class="text-brown-700 hover:text-gold-600">Ko'rish</a>
                                <a href="{{ route('admin.historical-regions.edit', $historicalRegion) }}" class="text-brown-700 hover:text-gold-600">Tahrirlash</a>
                                <form method="POST" action="{{ route('admin.historical-regions.destroy', $historicalRegion) }}"
                                      onsubmit="return confirm('&quot;{{ $historicalRegion->name }}&quot;ni o\'chirishni tasdiqlaysizmi?');">
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
                            <x-ui.empty-state message="Hozircha tarixiy hudud qo'shilmagan." class="border-0 bg-transparent">
                                <x-slot:action>
                                    <x-ui.button href="{{ route('admin.historical-regions.create') }}">+ Birinchi tarixiy hududni qo'shish</x-ui.button>
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
