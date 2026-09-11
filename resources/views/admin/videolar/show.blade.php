@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <x-ui.button href="{{ route('admin.videolar.edit', $video) }}" variant="secondary">Tahrirlash</x-ui.button>
            @if ($video->status->value === 'published')
                <x-ui.button href="{{ route('videolar.show', $video) }}" variant="secondary">Saytda ko'rish</x-ui.button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="aspect-video overflow-hidden rounded-lg border border-sand">
                <iframe src="{{ $video->embedUrl() }}" class="h-full w-full" allowfullscreen loading="lazy"></iframe>
            </div>
            <x-ui.card title="Tavsif" class="mt-6">
                <p class="whitespace-pre-line text-sm text-brown-700">{{ $video->description ?? '—' }}</p>
            </x-ui.card>
        </div>

        <x-ui.card class="lg:col-span-1">
            <div class="flex justify-center gap-2">
                <x-ui.badge :color="$video->status->value === 'published' ? 'success' : 'neutral'">{{ $video->status->label() }}</x-ui.badge>
                @if ($video->featured)
                    <x-ui.badge color="gold">Featured</x-ui.badge>
                @endif
            </div>
            <dl class="mt-6 space-y-3 text-sm">
                <div><dt class="text-brown-500">Kategoriya</dt><dd class="text-brown-900">{{ $video->category->label() }}</dd></div>
                <div><dt class="text-brown-500">Qo'rboshi</dt><dd class="text-brown-900">{{ $video->qorboshi?->full_name ?? '—' }}</dd></div>
                <div><dt class="text-brown-500">Qo'zg'olon</dt><dd class="text-brown-900">{{ $video->uzgolon?->name ?? '—' }}</dd></div>
                <div><dt class="text-brown-500">Adabiyot</dt><dd class="text-brown-900">{{ $video->literature?->title ?? '—' }}</dd></div>
                <div><dt class="text-brown-500">Manbalar</dt><dd class="text-brown-900">{{ $video->sourceReferences->count() }}</dd></div>
            </dl>
        </x-ui.card>
    </div>
@endsection
