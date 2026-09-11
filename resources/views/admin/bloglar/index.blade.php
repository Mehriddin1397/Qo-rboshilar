@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            @can('create', \App\Models\Blog::class)
                <x-ui.button href="{{ route('admin.bloglar.create') }}">+ Yangi qo'shish</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6">
        <x-ui.card>
            <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-4">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Sarlavha yoki muallif..."
                       class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600 sm:col-span-2">

                <select name="status" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha holatlar</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>

                <x-ui.button type="submit" variant="secondary">Filtrlash</x-ui.button>
            </form>

            @php $tableHeaders = ['Sarlavha', 'Muallif', 'Holat', 'Ko\'rishlar', 'Yaratilgan', 'Amallar']; @endphp
            <x-admin.table :headers="$tableHeaders">
                @forelse ($items as $blog)
                    <tr>
                        <td class="px-4 py-2.5 font-medium text-brown-900">{{ $blog->title }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $blog->author->name }}</td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge :color="match($blog->status->value) { 'approved' => 'success', 'rejected' => 'danger', 'pending' => 'gold', default => 'neutral' }">
                                {{ $blog->status->label() }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $blog->views }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $blog->created_at->format('d.m.Y') }}</td>
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-3 text-sm">
                                <a href="{{ route('admin.bloglar.show', $blog) }}" class="text-brown-700 hover:text-gold-600">Ko'rish</a>
                                @if ($blog->status->value === 'pending')
                                    <form method="POST" action="{{ route('admin.bloglar.approve', $blog) }}">
                                        @csrf
                                        <button type="submit" class="text-success hover:underline">Tasdiqlash</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.bloglar.reject', $blog) }}">
                                        @csrf
                                        <button type="submit" class="text-danger hover:underline">Rad etish</button>
                                    </form>
                                @endif
                                @can('update', $blog)
                                    <a href="{{ route('admin.bloglar.edit', $blog) }}" class="text-brown-700 hover:text-gold-600">Tahrirlash</a>
                                @endcan
                                @can('delete', $blog)
                                    <form method="POST" action="{{ route('admin.bloglar.destroy', $blog) }}" onsubmit="return confirm('O\'chirishni tasdiqlaysizmi?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-danger hover:underline">O'chirish</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-ui.empty-state message="Hozircha blog yo'q." class="border-0 bg-transparent" />
                        </td>
                    </tr>
                @endforelse
            </x-admin.table>

            <x-ui.pagination :paginator="$items" />
        </x-ui.card>
    </div>
@endsection
