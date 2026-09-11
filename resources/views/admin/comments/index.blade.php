@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs" />

    <div class="mt-6">
        <x-ui.card>
            <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-5">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Izoh matni yoki foydalanuvchi..."
                       class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600 sm:col-span-2">

                <select name="status" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha holatlar</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>

                <select name="type" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha turlar</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected(($filters['type'] ?? null) === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>

                <x-ui.button type="submit" variant="secondary">Filtrlash</x-ui.button>
            </form>

            @php $tableHeaders = ['Foydalanuvchi', 'Izoh', 'Kontent turi', 'Kontent', 'Holat', 'Yaratilgan', 'Amallar']; @endphp
            <x-admin.table :headers="$tableHeaders">
                @forelse ($items as $comment)
                    <tr>
                        <td class="px-4 py-2.5 text-brown-700">
                            {{ $comment->author->name }}
                            <div class="text-xs text-brown-500">{{ $comment->author->email }}</div>
                        </td>
                        <td class="px-4 py-2.5 max-w-xs text-brown-700">
                            <p class="line-clamp-2">{{ $comment->content }}</p>
                        </td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $comment->commentableTypeLabel() }}</td>
                        <td class="px-4 py-2.5 text-brown-700">
                            @if ($comment->commentableUrl())
                                <a href="{{ $comment->commentableUrl() }}" class="hover:text-gold-600" target="_blank" rel="noopener">
                                    {{ $comment->commentableTitle() }}
                                </a>
                            @else
                                {{ $comment->commentableTitle() }}
                            @endif
                        </td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge :color="match($comment->status->value) { 'approved' => 'success', 'rejected' => 'danger', 'pending' => 'gold', default => 'neutral' }">
                                {{ $comment->status->label() }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $comment->created_at->format('d.m.Y') }}</td>
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-3 text-sm">
                                <a href="{{ route('admin.comments.show', $comment) }}" class="text-brown-700 hover:text-gold-600">Ko'rish</a>
                                @if ($comment->status->value !== 'approved')
                                    <form method="POST" action="{{ route('admin.comments.approve', $comment) }}">
                                        @csrf
                                        <button type="submit" class="text-success hover:underline">Tasdiqlash</button>
                                    </form>
                                @endif
                                @if ($comment->status->value !== 'rejected')
                                    <form method="POST" action="{{ route('admin.comments.reject', $comment) }}">
                                        @csrf
                                        <button type="submit" class="text-danger hover:underline">Rad etish</button>
                                    </form>
                                @endif
                                @can('delete', $comment)
                                    <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" onsubmit="return confirm('O\'chirishni tasdiqlaysizmi?');">
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
                        <td colspan="7">
                            <x-ui.empty-state message="Hozircha izoh yo'q." class="border-0 bg-transparent" />
                        </td>
                    </tr>
                @endforelse
            </x-admin.table>

            <x-ui.pagination :paginator="$items" />
        </x-ui.card>
    </div>
@endsection
