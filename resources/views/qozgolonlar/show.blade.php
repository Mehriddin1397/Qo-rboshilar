@extends('layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])

@section('content')
    @php
        $breadcrumbItems = [
            ['label' => 'Bosh sahifa', 'url' => route('home')],
            ['label' => "Qo'zg'olonlar", 'url' => route('qozgolonlar.index')],
            ['label' => $uzgolon->name],
        ];
    @endphp

    <section class="border-b border-sand bg-brown-900 py-12 text-center">
        <x-ui.container class="max-w-3xl">
            <x-ui.breadcrumb class="mb-4 justify-center text-paper-dark/70" :items="$breadcrumbItems" />
            <x-seo.breadcrumb-jsonld :items="$breadcrumbItems" />

            <h1 class="font-serif text-3xl font-semibold text-paper sm:text-4xl">{{ $uzgolon->name }}</h1>
            <p class="mt-2 text-paper-dark/80">{{ $uzgolon->short_description }}</p>

            <div class="mt-4 flex flex-wrap justify-center gap-2 text-sm text-paper-dark/70">
                <span>{{ $uzgolon->start_year }}@if($uzgolon->end_year)–{{ $uzgolon->end_year }}@endif</span>
                @if ($uzgolon->region)
                    <span>&middot;</span>
                    <span>{{ $uzgolon->region->name }}</span>
                @endif
                @if ($uzgolon->period)
                    <span>&middot;</span>
                    <span>{{ $uzgolon->period->name }}</span>
                @endif
            </div>
        </x-ui.container>
    </section>

    <div class="py-12">
        <x-ui.container class="grid gap-10 lg:grid-cols-3">
            <div class="space-y-10 lg:col-span-2">
                @if ($uzgolon->historical_location || $uzgolon->modern_location)
                    <section class="grid gap-4 sm:grid-cols-2">
                        @if ($uzgolon->historical_location)
                            <div class="rounded-md border border-sand bg-white px-4 py-3">
                                <p class="text-xs uppercase tracking-wide text-brown-500">Tarixiy hudud</p>
                                <p class="mt-1 text-sm font-medium text-brown-900">{{ $uzgolon->historical_location }}</p>
                            </div>
                        @endif
                        @if ($uzgolon->modern_location)
                            <div class="rounded-md border border-sand bg-white px-4 py-3">
                                <p class="text-xs uppercase tracking-wide text-brown-500">Hozirgi hudud</p>
                                <p class="mt-1 text-sm font-medium text-brown-900">{{ $uzgolon->modern_location }}</p>
                            </div>
                        @endif
                    </section>
                @endif

                @if ($uzgolon->historical_context)
                    <section>
                        <x-ui.section-header title="Tarixiy kontekst" />
                        <p class="mt-4 whitespace-pre-line text-brown-700">{{ $uzgolon->historical_context }}</p>
                    </section>
                @endif

                @if ($uzgolon->causes)
                    <section>
                        <x-ui.section-header title="Sabablari" />
                        <p class="mt-4 whitespace-pre-line text-brown-700">{{ $uzgolon->causes }}</p>
                    </section>
                @endif

                @if ($uzgolon->main_events)
                    <section>
                        <x-ui.section-header title="Asosiy voqealar" />
                        <p class="mt-4 whitespace-pre-line text-brown-700">{{ $uzgolon->main_events }}</p>
                    </section>
                @endif

                @if ($uzgolon->results)
                    <section>
                        <x-ui.section-header title="Natijalari" />
                        <p class="mt-4 whitespace-pre-line text-brown-700">{{ $uzgolon->results }}</p>
                    </section>
                @endif

                @if ($uzgolon->historical_significance)
                    <section>
                        <x-ui.section-header title="Tarixiy ahamiyati" />
                        <p class="mt-4 whitespace-pre-line text-brown-700">{{ $uzgolon->historical_significance }}</p>
                    </section>
                @endif

                @if ($uzgolon->primaryMarker)
                    <section>
                        <x-ui.section-header title="Xaritada" />
                        <div
                            id="uzgolon-map"
                            class="mt-4 h-72 w-full rounded-lg border border-sand"
                            x-data
                            x-init="window.initUzgolonMap('uzgolon-map', @js([
                                'type' => 'FeatureCollection',
                                'features' => [[
                                    'type' => 'Feature',
                                    'geometry' => ['type' => 'Point', 'coordinates' => [(float) $uzgolon->primaryMarker->longitude, (float) $uzgolon->primaryMarker->latitude]],
                                    'properties' => [
                                        'title' => $uzgolon->name,
                                        'startYear' => $uzgolon->start_year,
                                        'endYear' => $uzgolon->end_year,
                                        'region' => $uzgolon->region?->name,
                                        'shortDescription' => $uzgolon->short_description,
                                        'url' => route('qozgolonlar.show', $uzgolon),
                                    ],
                                ]],
                            ]), { zoom: 7, fitBounds: false })"
                        ></div>
                    </section>
                @endif

                @if ($uzgolon->images->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Tarixiy suratlar" />
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($uzgolon->images as $image)
                                <figure>
                                    <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?? $uzgolon->name }}" loading="lazy" class="h-32 w-full rounded-md object-cover">
                                    @if ($image->caption)
                                        <figcaption class="mt-1 text-xs text-brown-500">{{ $image->caption }}</figcaption>
                                    @endif
                                </figure>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($uzgolon->qorboshilar->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Qatnashgan qo'rboshilar" />
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($uzgolon->qorboshilar as $qorboshi)
                                <x-ui.content-card :title="$qorboshi->full_name" :meta="$qorboshi->region?->name" :image="$qorboshi->portraitUrl()" :href="route('qorboshilar.show', $qorboshi)" />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($uzgolon->literatures->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Bog'liq adabiyotlar" />
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($uzgolon->literatures as $literature)
                                <x-ui.content-card :title="$literature->title" :meta="$literature->author" :image="$literature->coverUrl()" :href="route('adabiyotlar.show', $literature)" />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($uzgolon->videos->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Videolar" />
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($uzgolon->videos as $video)
                                <x-ui.content-card :title="$video->title" :meta="$video->category->label()" :image="$video->thumbnailUrl()" :href="route('videolar.show', $video)" />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($uzgolon->timelineEvents->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Timeline" />
                        <div class="mt-4 flex gap-6 overflow-x-auto pb-4">
                            @foreach ($uzgolon->timelineEvents as $event)
                                <div class="flex w-56 shrink-0 flex-col border-l-2 border-gold-600 pl-4">
                                    <p class="font-serif text-xl font-semibold text-brown-900">{{ $event->year }}</p>
                                    <p class="mt-1 text-sm font-medium text-brown-900">{{ $event->title }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($uzgolon->blogs->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Bog'liq bloglar" />
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($uzgolon->blogs as $blog)
                                <x-ui.content-card :title="$blog->title" :meta="$blog->author->name" :image="$blog->coverUrl()" :href="route('bloglar.show', $blog)" />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($uzgolon->sourceReferences->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Manbalar" subtitle="Ushbu ma'lumot quyidagi manbalarga asoslangan" />
                        <ul class="mt-4 space-y-2 text-sm text-brown-700">
                            @foreach ($uzgolon->sourceReferences as $source)
                                <li class="rounded-md border border-sand bg-white px-4 py-3">
                                    <span class="font-medium text-brown-900">{{ $source->author }}</span> — {{ $source->title }}
                                    @if ($source->page)
                                        <span class="text-brown-500">(bet: {{ $source->page }})</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section>
                    <x-comments :comments="$comments" :commentable="$uzgolon" />
                </section>
            </div>

            <aside class="space-y-6">
                <div>
                    <x-ui.button href="{{ route('qorboshilar.index') }}" variant="secondary" class="w-full justify-center">
                        Qo'rboshilarni ko'rish
                    </x-ui.button>
                </div>

                @if ($related->isNotEmpty())
                    <div>
                        <h2 class="font-serif text-lg font-semibold text-brown-900">Boshqa qo'zg'olonlar</h2>
                        <div class="mt-4 space-y-4">
                            @foreach ($related as $item)
                                <x-ui.content-card :title="$item->name" :meta="$item->region?->name" :image="$item->coverImageUrl()" :href="route('qozgolonlar.show', $item)" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </x-ui.container>
    </div>
@endsection
