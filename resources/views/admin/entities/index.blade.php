@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs" />

    <div class="mt-6">
        <x-ui.card>
            <x-admin.table :headers="['#', 'Nomi', 'Qo\'shilgan sana']">
                @forelse ($items as $item)
                    <tr>
                        <td class="px-4 py-2.5 text-brown-700">{{ $item->id }}</td>
                        <td class="px-4 py-2.5 font-medium text-brown-900">
                            {{ $item->title ?? $item->full_name ?? $item->name ?? "#{$item->id}" }}
                        </td>
                        <td class="px-4 py-2.5 text-brown-700">{{ $item->created_at?->format('d.m.Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-brown-700">
                            Hozircha ma'lumot yo'q. To'liq qo'shish/tahrirlash imkoniyati keyingi bosqichda qo'shiladi.
                        </td>
                    </tr>
                @endforelse
            </x-admin.table>

            <x-ui.pagination :paginator="$items" />
        </x-ui.card>
    </div>
@endsection
