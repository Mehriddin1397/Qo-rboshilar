@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <x-ui.button href="{{ route('admin.adabiyotlar.create') }}">+ Yangi qo'shish</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6">
        <x-ui.card>
            <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-5">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nom yoki muallif..."
                       class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600 sm:col-span-2">

                <select name="type" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha turlar</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected(($filters['type'] ?? null) === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>

                <select name="status" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha holatlar</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>

                <x-ui.button type="submit" variant="secondary">Filtrlash</x-ui.button>
            </form>

            @php
                $tableHeaders = ['', 'Sarlavha', 'Muallif', 'Tur', 'Yil', 'Holat', 'Amallar'];
            @endphp
            <x-admin.table :headers="$tableHeaders">
                @forelse ($items as $item)
                    <tr>
                        <td class="px-4 py-2.5">
                            @if ($item->coverUrl())
                                <img src="{{ $item->coverUrl() }}" alt="{{ $item->title }}" loading="lazy" class="h-10 w-8 rounded object-cover">
                            @else
                                <div class="h-10 w-8 rounded bg-paper-dark"></div>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 font-medium text-brown-900">{{ $item->title }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $item->author }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $item->type->label() }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $item->publication_year ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge :color="$item->status->value === 'published' ? 'success' : 'neutral'">{{ $item->status->label() }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-3 text-sm">
                                <a href="{{ route('admin.adabiyotlar.show', $item) }}" class="text-brown-700 hover:text-gold-600">Ko'rish</a>
                                <a href="{{ route('admin.adabiyotlar.edit', $item) }}" class="text-brown-700 hover:text-gold-600">Tahrirlash</a>
                                <form method="POST" action="{{ route('admin.adabiyotlar.destroy', $item) }}"
                                      onsubmit="return confirm('O\'chirishni tasdiqlaysizmi?');">
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
                            <x-ui.empty-state message="Hozircha adabiyot qo'shilmagan." class="border-0 bg-transparent">
                                <x-slot:action>
                                    <x-ui.button href="{{ route('admin.adabiyotlar.create') }}">+ Birinchi adabiyotni qo'shish</x-ui.button>
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
