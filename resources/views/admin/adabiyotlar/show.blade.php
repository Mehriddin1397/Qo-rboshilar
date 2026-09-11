@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <x-ui.button href="{{ route('admin.adabiyotlar.edit', $literature) }}" variant="secondary">Tahrirlash</x-ui.button>
            @if ($literature->status->value === 'published')
                <x-ui.button href="{{ route('adabiyotlar.show', $literature) }}" variant="secondary">Saytda ko'rish</x-ui.button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-1">
            @if ($literature->coverUrl())
                <img src="{{ $literature->coverUrl() }}" alt="{{ $literature->title }}" class="mx-auto h-48 w-36 rounded object-cover">
            @endif
            <div class="mt-4 flex justify-center gap-2">
                <x-ui.badge :color="$literature->status->value === 'published' ? 'success' : 'neutral'">{{ $literature->status->label() }}</x-ui.badge>
                @if ($literature->featured)
                    <x-ui.badge color="gold">Featured</x-ui.badge>
                @endif
            </div>
            <dl class="mt-6 space-y-3 text-sm">
                <div><dt class="text-brown-500">Muallif</dt><dd class="text-brown-900">{{ $literature->author }}</dd></div>
                <div><dt class="text-brown-500">Turi</dt><dd class="text-brown-900">{{ $literature->type->label() }}</dd></div>
                <div><dt class="text-brown-500">Nashr yili</dt><dd class="text-brown-900">{{ $literature->publication_year ?? '—' }}</dd></div>
            </dl>
        </x-ui.card>

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Tavsif">
                <p class="whitespace-pre-line text-sm text-brown-700">{{ $literature->description }}</p>
            </x-ui.card>

            <x-ui.card title="Bog'liq ma'lumotlar">
                <p class="text-sm text-brown-700">
                    <strong>{{ $literature->qorboshilar->count() }}</strong> qo'rboshi ·
                    <strong>{{ $literature->uzgolonlar->count() }}</strong> qo'zg'olon ·
                    <strong>{{ $literature->videos->count() }}</strong> video ·
                    <strong>{{ $literature->sourceReferences->count() }}</strong> manba (unga tayanuvchi yozuvlar)
                </p>
            </x-ui.card>
        </div>
    </div>
@endsection
