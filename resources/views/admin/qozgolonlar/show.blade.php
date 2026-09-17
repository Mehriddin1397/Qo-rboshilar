@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <x-ui.button href="{{ route('admin.qozgolonlar.edit', $uzgolon) }}" variant="secondary">Tahrirlash</x-ui.button>
            @if ($uzgolon->status->value === 'published')
                <x-ui.button href="{{ route('qozgolonlar.show', $uzgolon) }}" variant="secondary">Saytda ko'rish</x-ui.button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-1">
            @if ($uzgolon->coverImageUrl())
                <img src="{{ $uzgolon->coverImageUrl() }}" alt="{{ $uzgolon->name }}" class="h-40 w-full rounded-md object-cover">
            @endif
            @if ($uzgolon->backgroundImageUrl())
                <div class="mt-3">
                    <p class="mb-1 text-xs text-brown-500">Header orqa fon rasmi:</p>
                    <img src="{{ $uzgolon->backgroundImageUrl() }}" alt="{{ $uzgolon->name }} background" class="h-24 w-full rounded-md object-cover border border-sand">
                </div>
            @endif
            <div class="mt-4 flex justify-center gap-2">
                <x-ui.badge :color="$uzgolon->status->value === 'published' ? 'success' : 'neutral'">{{ $uzgolon->status->label() }}</x-ui.badge>
                @if ($uzgolon->featured)
                    <x-ui.badge color="gold">Featured</x-ui.badge>
                @endif
            </div>
            <dl class="mt-6 space-y-3 text-sm">
                <div>
                    <dt class="text-brown-500">Yil</dt>
                    <dd class="text-brown-900">{{ $uzgolon->start_year }}@if($uzgolon->end_year)–{{ $uzgolon->end_year }}@endif</dd>
                </div>
                <div>
                    <dt class="text-brown-500">Hudud</dt>
                    <dd class="text-brown-900">{{ $uzgolon->region?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-brown-500">Davr</dt>
                    <dd class="text-brown-900">{{ $uzgolon->period?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-brown-500">Xarita markeri</dt>
                    <dd class="text-brown-900">{{ $uzgolon->primaryMarker ? 'Bor' : "Yo'q" }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Qisqa tavsif">
                <p class="text-sm text-brown-700">{{ $uzgolon->short_description }}</p>
            </x-ui.card>

            <x-ui.card title="Bog'liq ma'lumotlar">
                <p class="text-sm text-brown-700">
                    <strong>{{ $uzgolon->qorboshilar->count() }}</strong> qo'rboshi ·
                    <strong>{{ $uzgolon->literatures->count() }}</strong> adabiyot ·
                    <strong>{{ $uzgolon->videos->count() }}</strong> video ·
                    <strong>{{ $uzgolon->images->count() }}</strong> rasm ·
                    <strong>{{ $uzgolon->sourceReferences->count() }}</strong> manba
                </p>
            </x-ui.card>
        </div>
    </div>
@endsection
