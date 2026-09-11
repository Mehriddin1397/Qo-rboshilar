@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <x-ui.button href="{{ route('admin.qorboshilar.edit', $qorboshi) }}" variant="secondary">Tahrirlash</x-ui.button>
            @if ($qorboshi->status->value === 'published')
                <x-ui.button href="{{ route('qorboshilar.show', $qorboshi) }}" variant="secondary">Saytda ko'rish</x-ui.button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-1">
            <img src="{{ $qorboshi->portraitUrl() }}" alt="{{ $qorboshi->full_name }}" class="mx-auto h-40 w-40 rounded-full object-cover">
            <div class="mt-4 flex justify-center gap-2">
                <x-ui.badge :color="$qorboshi->status->value === 'published' ? 'success' : 'neutral'">{{ $qorboshi->status->label() }}</x-ui.badge>
                @if ($qorboshi->featured)
                    <x-ui.badge color="gold">Featured</x-ui.badge>
                @endif
            </div>
            <dl class="mt-6 space-y-3 text-sm">
                <div>
                    <dt class="text-brown-500">Hudud</dt>
                    <dd class="text-brown-900">{{ $qorboshi->region?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-brown-500">Tug'ilgan / vafot yili</dt>
                    <dd class="text-brown-900">{{ $qorboshi->birth_year ?? '?' }} — {{ $qorboshi->death_year ?? '...' }}</dd>
                </div>
                <div>
                    <dt class="text-brown-500">Faoliyat davri</dt>
                    <dd class="text-brown-900">{{ $qorboshi->active_from_year ?? '?' }} — {{ $qorboshi->active_to_year ?? '?' }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Qisqa tavsif">
                <p class="text-sm text-brown-700">{{ $qorboshi->short_description }}</p>
            </x-ui.card>

            <x-ui.card title="Biografiya">
                <p class="whitespace-pre-line text-sm text-brown-700">{{ $qorboshi->biography }}</p>
            </x-ui.card>

            <x-ui.card title="Bog'liq ma'lumotlar">
                <p class="text-sm text-brown-700">
                    <strong>{{ $qorboshi->uzgolonlar->count() }}</strong> qo'zg'olon ·
                    <strong>{{ $qorboshi->literatures->count() }}</strong> adabiyot ·
                    <strong>{{ $qorboshi->videos->count() }}</strong> video ·
                    <strong>{{ $qorboshi->images->count() }}</strong> rasm ·
                    <strong>{{ $qorboshi->sourceReferences->count() }}</strong> manba
                </p>
            </x-ui.card>
        </div>
    </div>
@endsection
