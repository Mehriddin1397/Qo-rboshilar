@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <x-ui.button href="{{ route('admin.qorboshilar.create') }}">+ Yangi qo'shish</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6">
        <x-ui.card>
            <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-4">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Ism bo'yicha qidirish..."
                       class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">

                <select name="status" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha holatlar</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
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
                        <x-ui.button href="{{ route('admin.qorboshilar.index') }}" variant="secondary">Tozalash</x-ui.button>
                    @endif
                </div>
            </form>

            <x-admin.table :headers="['', 'Ism', 'Hudud', 'Holat', 'Featured', 'Yaratilgan', 'Amallar']">
                @forelse ($items as $qorboshi)
                    <tr>
                        <td class="px-4 py-2.5">
                            <img src="{{ $qorboshi->portraitUrl() }}"
                                 alt="{{ $qorboshi->full_name }}" loading="lazy" class="h-10 w-10 rounded-full object-cover">
                        </td>
                        <td class="px-4 py-2.5 font-medium text-brown-900">{{ $qorboshi->full_name }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $qorboshi->region?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge :color="$qorboshi->status->value === 'published' ? 'success' : 'neutral'">
                                {{ $qorboshi->status->label() }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5">
                            @if ($qorboshi->featured)
                                <x-ui.badge color="gold">Featured</x-ui.badge>
                            @else
                                <span class="text-brown-500/50">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $qorboshi->created_at->format('d.m.Y') }}</td>
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-3 text-sm">
                                <a href="{{ route('admin.qorboshilar.show', $qorboshi) }}" class="text-brown-700 hover:text-gold-600">Ko'rish</a>
                                <a href="{{ route('admin.qorboshilar.edit', $qorboshi) }}" class="text-brown-700 hover:text-gold-600">Tahrirlash</a>
                                <form method="POST" action="{{ route('admin.qorboshilar.destroy', $qorboshi) }}"
                                      onsubmit="return confirm('&quot;{{ $qorboshi->full_name }}&quot;ni o\'chirishni tasdiqlaysizmi? Bu amalni bekor qilib bo\'lmaydi.');">
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
                            <x-ui.empty-state message="Hozircha qo'rboshi qo'shilmagan." class="border-0 bg-transparent">
                                <x-slot:action>
                                    <x-ui.button href="{{ route('admin.qorboshilar.create') }}">+ Birinchi qo'rboshini qo'shish</x-ui.button>
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
