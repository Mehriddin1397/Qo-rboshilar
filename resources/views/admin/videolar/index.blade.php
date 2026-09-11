@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <x-ui.button href="{{ route('admin.videolar.create') }}">+ Yangi qo'shish</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6">
        <x-ui.card>
            <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-4">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Sarlavha bo'yicha qidirish..."
                       class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600 sm:col-span-2">

                <select name="category" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha kategoriyalar</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->value }}" @selected(($filters['category'] ?? null) === $category->value)>{{ $category->label() }}</option>
                    @endforeach
                </select>

                <x-ui.button type="submit" variant="secondary">Filtrlash</x-ui.button>
            </form>

            @php $tableHeaders = ['', 'Sarlavha', 'Kategoriya', "Qo'rboshi/Qo'zg'olon", 'Holat', 'Amallar']; @endphp
            <x-admin.table :headers="$tableHeaders">
                @forelse ($items as $video)
                    <tr>
                        <td class="px-4 py-2.5">
                            <img src="{{ $video->thumbnailUrl() }}" alt="{{ $video->title }}" loading="lazy" class="h-10 w-16 rounded object-cover">
                        </td>
                        <td class="px-4 py-2.5 font-medium text-brown-900">{{ $video->title }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $video->category->label() }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $video->qorboshi?->full_name ?? $video->uzgolon?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge :color="$video->status->value === 'published' ? 'success' : 'neutral'">{{ $video->status->label() }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-3 text-sm">
                                <a href="{{ route('admin.videolar.show', $video) }}" class="text-brown-700 hover:text-gold-600">Ko'rish</a>
                                <a href="{{ route('admin.videolar.edit', $video) }}" class="text-brown-700 hover:text-gold-600">Tahrirlash</a>
                                <form method="POST" action="{{ route('admin.videolar.destroy', $video) }}" onsubmit="return confirm('O\'chirishni tasdiqlaysizmi?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger hover:underline">O'chirish</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-ui.empty-state message="Hozircha video qo'shilmagan." class="border-0 bg-transparent">
                                <x-slot:action>
                                    <x-ui.button href="{{ route('admin.videolar.create') }}">+ Birinchi videoni qo'shish</x-ui.button>
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
