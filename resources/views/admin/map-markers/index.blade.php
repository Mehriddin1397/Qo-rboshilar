@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <x-ui.button href="{{ route('admin.map-markers.create') }}">+ Yangi qo'shish</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6">
        <x-ui.card>
            <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-4">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nomi..."
                       class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">

                <select name="status" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha holatlar</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>

                <select name="uzgolon_id" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha qo'zg'olonlar</option>
                    @foreach ($uzgolonlar as $uzgolon)
                        <option value="{{ $uzgolon->id }}" @selected((string) ($filters['uzgolon_id'] ?? '') === (string) $uzgolon->id)>{{ $uzgolon->name }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <x-ui.button type="submit" variant="secondary">Filtrlash</x-ui.button>
                    @if (array_filter($filters))
                        <x-ui.button href="{{ route('admin.map-markers.index') }}" variant="secondary">Tozalash</x-ui.button>
                    @endif
                </div>
            </form>

            @php $tableHeaders = ['#', 'Nomi', "Qo'zg'olon", 'Latitude', 'Longitude', 'Primary', 'Status', 'Yaratilgan', 'Amallar']; @endphp
            <x-admin.table :headers="$tableHeaders">
                @forelse ($items as $marker)
                    <tr>
                        <td class="px-4 py-2.5 text-brown-700">{{ $marker->id }}</td>
                        <td class="px-4 py-2.5 font-medium text-brown-900">{{ $marker->title }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $marker->uzgolon?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $marker->latitude }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $marker->longitude }}</td>
                        <td class="px-4 py-2.5">
                            @if ($marker->is_primary)
                                <x-ui.badge color="gold">Primary</x-ui.badge>
                            @else
                                <span class="text-brown-500/50">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge :color="$marker->status->value === 'published' ? 'success' : 'neutral'">
                                {{ $marker->status->label() }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $marker->created_at?->format('d.m.Y') }}</td>
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-3 text-sm">
                                <a href="{{ route('admin.map-markers.show', $marker) }}" class="text-brown-700 hover:text-gold-600">Ko'rish</a>
                                <a href="{{ route('admin.map-markers.edit', $marker) }}" class="text-brown-700 hover:text-gold-600">Tahrirlash</a>
                                <form method="POST" action="{{ route('admin.map-markers.destroy', $marker) }}"
                                      onsubmit="return confirm('&quot;{{ $marker->title }}&quot;ni o\'chirishni tasdiqlaysizmi?');">
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
                            <x-ui.empty-state message="Hozircha xarita markeri qo'shilmagan." class="border-0 bg-transparent">
                                <x-slot:action>
                                    <x-ui.button href="{{ route('admin.map-markers.create') }}">+ Birinchi markerni qo'shish</x-ui.button>
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
