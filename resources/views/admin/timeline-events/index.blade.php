@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <x-ui.button href="{{ route('admin.timeline-events.create') }}">+ Yangi qo'shish</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6">
        <x-ui.card>
            <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-5">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Sarlavha yoki slug..."
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

                <input type="number" name="year" value="{{ $filters['year'] ?? '' }}" placeholder="Yil"
                       class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">

                <div class="flex gap-2">
                    <x-ui.button type="submit" variant="secondary">Filtrlash</x-ui.button>
                    @if (array_filter($filters))
                        <x-ui.button href="{{ route('admin.timeline-events.index') }}" variant="secondary">Tozalash</x-ui.button>
                    @endif
                </div>
            </form>

            @php $tableHeaders = ['Yil', 'Sarlavha', 'Davr', 'Holat', 'Aniqlik', 'Featured', 'Amallar']; @endphp
            <x-admin.table :headers="$tableHeaders">
                @forelse ($items as $event)
                    <tr>
                        <td class="px-4 py-2.5 font-medium text-brown-900">{{ $event->yearRangeLabel() }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $event->title }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $event->period?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge :color="$event->status->value === 'published' ? 'success' : 'neutral'">
                                {{ $event->status->label() }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge :color="match($event->accuracy_status->value) { 'verified' => 'success', 'approximate' => 'gold', default => 'danger' }">
                                {{ $event->accuracy_status->label() }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5">
                            @if ($event->featured)
                                <x-ui.badge color="gold">Featured</x-ui.badge>
                            @else
                                <span class="text-brown-500/50">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-3 text-sm">
                                <a href="{{ route('admin.timeline-events.show', $event) }}" class="text-brown-700 hover:text-gold-600">Ko'rish</a>
                                <a href="{{ route('admin.timeline-events.edit', $event) }}" class="text-brown-700 hover:text-gold-600">Tahrirlash</a>
                                <form method="POST" action="{{ route('admin.timeline-events.destroy', $event) }}"
                                      onsubmit="return confirm('&quot;{{ $event->title }}&quot;ni o\'chirishni tasdiqlaysizmi?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger hover:underline">O'chirish</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-ui.empty-state message="Hozircha xronologiya voqeasi qo'shilmagan." class="border-0 bg-transparent">
                                <x-slot:action>
                                    <x-ui.button href="{{ route('admin.timeline-events.create') }}">+ Birinchi voqeani qo'shish</x-ui.button>
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
