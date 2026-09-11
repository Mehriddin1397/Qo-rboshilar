@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <x-ui.button href="{{ route('admin.source-references.create') }}">+ Yangi qo'shish</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6">
        <x-ui.card>
            <form method="GET" class="mb-5 grid gap-3 sm:grid-cols-3">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Muallif yoki sarlavha..."
                       class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">

                <select name="source_type" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha turlar</option>
                    @foreach ($sourceTypes as $type)
                        <option value="{{ $type->value }}" @selected(($filters['source_type'] ?? null) === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <x-ui.button type="submit" variant="secondary">Filtrlash</x-ui.button>
                    @if (array_filter($filters))
                        <x-ui.button href="{{ route('admin.source-references.index') }}" variant="secondary">Tozalash</x-ui.button>
                    @endif
                </div>
            </form>

            @php $tableHeaders = ['#', 'Muallif', 'Sarlavha', 'Yil', 'Turi', 'Adabiyot', 'Yaratilgan', 'Amallar']; @endphp
            <x-admin.table :headers="$tableHeaders">
                @forelse ($items as $source)
                    <tr>
                        <td class="px-4 py-2.5 text-brown-700">{{ $source->id }}</td>
                        <td class="px-4 py-2.5 font-medium text-brown-900">{{ $source->author }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $source->title }}</td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $source->year ?? '—' }}</td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge color="neutral">{{ $source->source_type->label() }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5 text-brown-700">
                            @if ($source->literature)
                                <a href="{{ route('admin.adabiyotlar.show', $source->literature) }}" class="text-gold-600 hover:underline">{{ $source->literature->title }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $source->created_at?->format('d.m.Y') }}</td>
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-3 text-sm">
                                <a href="{{ route('admin.source-references.show', $source) }}" class="text-brown-700 hover:text-gold-600">Ko'rish</a>
                                <a href="{{ route('admin.source-references.edit', $source) }}" class="text-brown-700 hover:text-gold-600">Tahrirlash</a>
                                <form method="POST" action="{{ route('admin.source-references.destroy', $source) }}"
                                      onsubmit="return confirm('&quot;{{ $source->title }}&quot;ni o\'chirishni tasdiqlaysizmi?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger hover:underline">O'chirish</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <x-ui.empty-state message="Hozircha manba qo'shilmagan." class="border-0 bg-transparent">
                                <x-slot:action>
                                    <x-ui.button href="{{ route('admin.source-references.create') }}">+ Birinchi manbani qo'shish</x-ui.button>
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
