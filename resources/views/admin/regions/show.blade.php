@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            @can('update', $region)
                <x-ui.button href="{{ route('admin.regions.edit', $region) }}" variant="secondary">Tahrirlash</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-1">
            <div class="flex flex-wrap justify-center gap-2">
                <x-ui.badge :color="$region->status->value === 'published' ? 'success' : 'neutral'">
                    {{ $region->status->label() }}
                </x-ui.badge>
            </div>
            <dl class="mt-6 space-y-3 text-sm">
                <div><dt class="text-brown-500">Slug</dt><dd class="text-brown-900">{{ $region->slug }}</dd></div>
                <div><dt class="text-brown-500">Tartib raqami</dt><dd class="text-brown-900">{{ $region->sort_order }}</dd></div>
                <div><dt class="text-brown-500">Qo'rboshilar soni</dt><dd class="text-brown-900">{{ $region->qorboshilar_count }}</dd></div>
                <div><dt class="text-brown-500">Qo'zg'olonlar soni</dt><dd class="text-brown-900">{{ $region->uzgolonlar_count }}</dd></div>
                <div><dt class="text-brown-500">Yaratilgan</dt><dd class="text-brown-900">{{ $region->created_at?->format('d.m.Y') }}</dd></div>
            </dl>
        </x-ui.card>

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Tavsif">
                <p class="whitespace-pre-line text-sm text-brown-700">{{ $region->description ?? '—' }}</p>
            </x-ui.card>

            <x-ui.card title="Bog'langan Qo'rboshilar">
                @if ($region->qorboshilar->isNotEmpty())
                    <ul class="space-y-2 text-sm">
                        @foreach ($region->qorboshilar as $qorboshi)
                            <li class="flex items-center justify-between rounded-md border border-sand px-4 py-2.5">
                                <a href="{{ route('admin.qorboshilar.show', $qorboshi) }}" class="text-brown-900 hover:text-gold-600">{{ $qorboshi->full_name }}</a>
                            </li>
                        @endforeach
                    </ul>
                    @if ($region->qorboshilar_count > $region->qorboshilar->count())
                        <p class="mt-2 text-xs text-brown-500">... va yana {{ $region->qorboshilar_count - $region->qorboshilar->count() }} ta.</p>
                    @endif
                @else
                    <x-ui.empty-state message="Bu hududga hali qo'rboshi biriktirilmagan." class="border-0 bg-transparent" />
                @endif
            </x-ui.card>

            <x-ui.card title="Bog'langan Qo'zg'olonlar">
                @if ($region->uzgolonlar->isNotEmpty())
                    <ul class="space-y-2 text-sm">
                        @foreach ($region->uzgolonlar as $uzgolon)
                            <li class="flex items-center justify-between rounded-md border border-sand px-4 py-2.5">
                                <a href="{{ route('admin.qozgolonlar.show', $uzgolon) }}" class="text-brown-900 hover:text-gold-600">{{ $uzgolon->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                    @if ($region->uzgolonlar_count > $region->uzgolonlar->count())
                        <p class="mt-2 text-xs text-brown-500">... va yana {{ $region->uzgolonlar_count - $region->uzgolonlar->count() }} ta.</p>
                    @endif
                @else
                    <x-ui.empty-state message="Bu hududga hali qo'zg'olon biriktirilmagan." class="border-0 bg-transparent" />
                @endif
            </x-ui.card>
        </div>
    </div>
@endsection
