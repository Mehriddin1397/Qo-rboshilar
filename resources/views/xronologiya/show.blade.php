@extends('layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])

@section('content')
    @php
        $breadcrumbItems = [
            ['label' => 'Bosh sahifa', 'url' => route('home')],
            ['label' => 'Xronologiya', 'url' => route('xronologiya.index')],
            ['label' => $event->title],
        ];
    @endphp

    <section class="border-b border-sand bg-brown-900 py-12 text-center">
        <x-ui.container class="max-w-3xl">
            <x-ui.breadcrumb class="mb-4 justify-center text-paper-dark/70" :items="$breadcrumbItems" />
            <x-seo.breadcrumb-jsonld :items="$breadcrumbItems" />

            <p class="font-serif text-4xl font-semibold text-gold-400">{{ $event->yearRangeLabel() }}</p>
            <h1 class="mt-2 font-serif text-3xl font-semibold text-paper sm:text-4xl">{{ $event->title }}</h1>

            <div class="mt-4 flex flex-wrap justify-center gap-2 text-sm text-paper-dark/70">
                <x-ui.badge :color="match($event->accuracy_status->value) { 'verified' => 'success', 'approximate' => 'gold', default => 'danger' }">
                    {{ $event->accuracy_status->label() }}
                </x-ui.badge>
                @if ($event->period)
                    <x-ui.badge color="neutral">{{ $event->period->name }}</x-ui.badge>
                @endif
                @if ($event->event_date)
                    <span>&middot; {{ $event->event_date->format('d.m.Y') }}</span>
                @endif
            </div>
        </x-ui.container>
    </section>

    <div class="py-12">
        <x-ui.container class="grid gap-10 lg:grid-cols-3">
            <div class="space-y-10 lg:col-span-2">
                @if ($event->imageUrl())
                    <img src="{{ $event->imageUrl() }}" alt="{{ $event->title }}" class="w-full rounded-lg object-cover">
                @endif

                <section>
                    <x-ui.section-header title="Tavsif" />
                    <p class="mt-4 whitespace-pre-line text-brown-700">{{ $event->description }}</p>
                </section>

                @if ($event->sourceReferences->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Manbalar" subtitle="Ushbu ma'lumot quyidagi manbalarga asoslangan" />
                        <ul class="mt-4 space-y-2 text-sm text-brown-700">
                            @foreach ($event->sourceReferences as $source)
                                <li class="rounded-md border border-sand bg-white px-4 py-3">
                                    <span class="font-medium text-brown-900">{{ $source->author }}</span> — {{ $source->title }}
                                    @if ($source->year)
                                        <span class="text-brown-500">({{ $source->year }})</span>
                                    @endif
                                    @if ($source->page)
                                        <span class="text-brown-500">, bet: {{ $source->page }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @else
                    <section>
                        <x-ui.section-header title="Manbalar" />
                        <x-ui.empty-state message="Manba kiritilmagan." class="mt-4" />
                    </section>
                @endif

                @if ($event->hasCoordinates())
                    <section>
                        <x-ui.section-header title="Joylashuv" />
                        <div
                            id="timeline-event-preview"
                            class="mt-4 h-64 w-full rounded-lg border border-sand"
                            x-data
                            x-init="window.initGeoJsonPreview('timeline-event-preview', @js(['type' => 'Point', 'coordinates' => [(float) $event->longitude, (float) $event->latitude]]))"
                        ></div>
                        <div class="mt-3">
                            <x-ui.button href="{{ route('xarita', ['period' => $event->period_id, 'event' => $event->slug]) }}" variant="secondary">
                                To'liq xaritada ko'rish
                            </x-ui.button>
                        </div>
                    </section>
                @endif

                <section>
                    <x-comments :comments="$comments" :commentable="$event" />
                </section>
            </div>

            <aside class="space-y-6">
                <div class="rounded-lg border border-sand bg-white p-5">
                    <h2 class="font-serif text-lg font-semibold text-brown-900">Bog'lanishlar</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        @if ($event->qorboshi)
                            <div>
                                <dt class="text-brown-500">Qo'rboshi</dt>
                                <dd><a href="{{ route('qorboshilar.show', $event->qorboshi) }}" class="text-gold-600 hover:underline">{{ $event->qorboshi->full_name }}</a></dd>
                            </div>
                        @endif
                        @if ($event->uzgolon)
                            <div>
                                <dt class="text-brown-500">Qo'zg'olon</dt>
                                <dd><a href="{{ route('qozgolonlar.show', $event->uzgolon) }}" class="text-gold-600 hover:underline">{{ $event->uzgolon->name }}</a></dd>
                            </div>
                        @endif
                        @if ($event->historicalRegion)
                            <div>
                                <dt class="text-brown-500">Tarixiy hudud</dt>
                                <dd class="text-brown-900">{{ $event->historicalRegion->name }}</dd>
                            </div>
                        @endif
                        @if (!$event->qorboshi && !$event->uzgolon && !$event->historicalRegion)
                            <p class="text-brown-500">Bog'liq kontent yo'q.</p>
                        @endif
                    </dl>
                </div>

                <div>
                    <x-ui.button href="{{ route('xronologiya.index') }}" variant="secondary" class="w-full justify-center">
                        Barcha xronologiya
                    </x-ui.button>
                </div>
            </aside>
        </x-ui.container>
    </div>
@endsection
