@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            @can('update', $event)
                <x-ui.button href="{{ route('admin.timeline-events.edit', $event) }}" variant="secondary">Tahrirlash</x-ui.button>
            @endcan
            @if ($event->status->value === 'published')
                <x-ui.button href="{{ route('xronologiya.show', $event) }}" variant="secondary">Saytda ko'rish</x-ui.button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-1">
            @if ($event->imageUrl())
                <img src="{{ $event->imageUrl() }}" alt="" class="h-40 w-full rounded object-cover">
            @endif
            <div class="mt-4 flex flex-wrap justify-center gap-2">
                <x-ui.badge :color="$event->status->value === 'published' ? 'success' : 'neutral'">
                    {{ $event->status->label() }}
                </x-ui.badge>
                <x-ui.badge :color="match($event->accuracy_status->value) { 'verified' => 'success', 'approximate' => 'gold', default => 'danger' }">
                    {{ $event->accuracy_status->label() }}
                </x-ui.badge>
                @if ($event->featured)
                    <x-ui.badge color="gold">Featured</x-ui.badge>
                @endif
            </div>
            <dl class="mt-6 space-y-3 text-sm">
                <div><dt class="text-brown-500">Slug</dt><dd class="text-brown-900">{{ $event->slug }}</dd></div>
                <div><dt class="text-brown-500">Yil</dt><dd class="text-brown-900">{{ $event->yearRangeLabel() }}</dd></div>
                <div><dt class="text-brown-500">Aniq sana</dt><dd class="text-brown-900">{{ $event->event_date?->format('d.m.Y') ?? '—' }}</dd></div>
                <div><dt class="text-brown-500">Davr</dt><dd class="text-brown-900">{{ $event->period?->name ?? '—' }}</dd></div>
                <div><dt class="text-brown-500">Qo'rboshi</dt><dd class="text-brown-900">{{ $event->qorboshi?->full_name ?? '—' }}</dd></div>
                <div><dt class="text-brown-500">Qo'zg'olon</dt><dd class="text-brown-900">{{ $event->uzgolon?->name ?? '—' }}</dd></div>
                <div><dt class="text-brown-500">Zamonaviy hudud</dt><dd class="text-brown-900">{{ $event->region?->name ?? '—' }}</dd></div>
                <div><dt class="text-brown-500">Tarixiy hudud</dt><dd class="text-brown-900">{{ $event->historicalRegion?->name ?? '—' }}</dd></div>
                <div>
                    <dt class="text-brown-500">Koordinata</dt>
                    <dd class="text-brown-900">{{ $event->hasCoordinates() ? "{$event->latitude}, {$event->longitude}" : '—' }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Tavsif">
                <p class="whitespace-pre-line text-sm text-brown-700">{{ $event->description }}</p>
            </x-ui.card>

            <x-ui.card title="Manbalar (Source / Provenance)">
                @if ($event->sourceReferences->isNotEmpty())
                    <ul class="space-y-2 text-sm text-brown-700">
                        @foreach ($event->sourceReferences as $source)
                            <li class="rounded-md border border-sand bg-white px-4 py-3">
                                <span class="font-medium text-brown-900">{{ $source->author }}</span> — {{ $source->title }}
                                @if ($source->year)<span class="text-brown-500">({{ $source->year }})</span>@endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <x-ui.empty-state message="Manba kiritilmagan." class="border-0 bg-transparent" />
                @endif
            </x-ui.card>
        </div>
    </div>
@endsection
