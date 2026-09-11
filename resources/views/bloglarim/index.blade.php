@extends('layouts.app')

@section('title', "Mening bloglarim — Qo'rboshilar.uz")

@section('content')
    <x-ui.container class="py-12">
        <div class="flex items-center justify-between">
            <h1 class="font-serif text-2xl font-semibold text-brown-900">Mening bloglarim</h1>
            <x-ui.button href="{{ route('bloglarim.create') }}">+ Yangi blog</x-ui.button>
        </div>

        @if ($items->isEmpty())
            <x-ui.empty-state message="Hozircha blog yozmagansiz." class="mt-8">
                <x-slot:action>
                    <x-ui.button href="{{ route('bloglarim.create') }}">Birinchi blogingizni yozing</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <div class="mt-8 overflow-hidden rounded-lg border border-sand bg-white">
                @php $tableHeaders = ['Sarlavha', 'Holat', "Ko'rishlar", 'Yaratilgan', 'Amallar']; @endphp
                <x-admin.table :headers="$tableHeaders">
                    @foreach ($items as $blog)
                        <tr>
                            <td class="px-4 py-2.5 font-medium text-brown-900">{{ $blog->title }}</td>
                            <td class="px-4 py-2.5">
                                <x-ui.badge :color="match($blog->status->value) { 'approved' => 'success', 'rejected' => 'danger', 'pending' => 'gold', default => 'neutral' }">
                                    {{ $blog->status->label() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-2.5 text-brown-700">{{ $blog->views }}</td>
                            <td class="px-4 py-2.5 text-brown-700">{{ $blog->created_at->format('d.m.Y') }}</td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-3 text-sm">
                                    @if ($blog->status->value === 'approved')
                                        <a href="{{ route('bloglar.show', $blog) }}" class="text-brown-700 hover:text-gold-600">Ko'rish</a>
                                    @endif
                                    @if ($blog->status->value === 'draft')
                                        <a href="{{ route('bloglarim.edit', $blog) }}" class="text-brown-700 hover:text-gold-600">Tahrirlash</a>
                                        <form method="POST" action="{{ route('bloglarim.submit', $blog) }}" onsubmit="return confirm('Moderatsiyaga yuborishni tasdiqlaysizmi? Yuborilgach tahrirlab bo\'lmaydi.');">
                                            @csrf
                                            <button type="submit" class="text-success hover:underline">Yuborish</button>
                                        </form>
                                        <form method="POST" action="{{ route('bloglarim.destroy', $blog) }}" onsubmit="return confirm('O\'chirishni tasdiqlaysizmi?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-danger hover:underline">O'chirish</button>
                                        </form>
                                    @endif
                                    @if ($blog->status->value === 'pending')
                                        <span class="text-brown-500">Moderatsiyada</span>
                                    @endif
                                    @if ($blog->status->value === 'rejected')
                                        <span class="text-danger">Rad etilgan</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-admin.table>
            </div>

            <x-ui.pagination :paginator="$items" class="mt-4" />
        @endif
    </x-ui.container>
@endsection
