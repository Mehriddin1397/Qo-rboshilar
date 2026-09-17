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

    @php
        $heroBackground = $uzgolon->backgroundImageUrl() ?? $uzgolon->coverImageUrl();
    @endphp

    <section class="relative border-b border-sand bg-brown-900 py-14 sm:py-20 text-center overflow-hidden">
        @if ($heroBackground)
            <div class="absolute inset-0 z-0">
                <img src="{{ $heroBackground }}" alt="{{ $uzgolon->name }}" class="h-full w-full object-cover object-center filter brightness-40">
                <div class="absolute inset-0 bg-gradient-to-b from-brown-950/85 via-brown-900/80 to-brown-950/95"></div>
            </div>
        @endif

        <x-ui.container class="relative z-10 max-w-3xl">
            <x-ui.breadcrumb class="mb-4 justify-center text-paper-dark/80" :items="$breadcrumbItems" />
            <x-seo.breadcrumb-jsonld :items="$breadcrumbItems" />

            <h1 class="font-serif text-3xl font-semibold text-paper sm:text-4xl lg:text-5xl drop-shadow-sm">{{ $uzgolon->name }}</h1>
            <p class="mt-3 text-base sm:text-lg text-paper-dark/90 leading-relaxed drop-shadow-sm">{{ $uzgolon->short_description }}</p>

            <div class="mt-5 flex flex-wrap justify-center items-center gap-3 text-sm text-paper-dark/85">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-paper/10 px-3 py-1 backdrop-blur-xs">
                    <svg class="h-4 w-4 text-gold-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ $uzgolon->start_year }}@if($uzgolon->end_year)–{{ $uzgolon->end_year }}@endif</span>
                </span>
                @if ($uzgolon->region)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-paper/10 px-3 py-1 backdrop-blur-xs">
                        <svg class="h-4 w-4 text-gold-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        <span>{{ $uzgolon->region->name }}</span>
                    </span>
                @endif
                @if ($uzgolon->period)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-paper/10 px-3 py-1 backdrop-blur-xs">
                        <svg class="h-4 w-4 text-gold-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $uzgolon->period->name }}</span>
                    </span>
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
                    <section
                        x-data="{
                            current: 0,
                            total: {{ $uzgolon->images->count() }},
                            timer: null,
                            isPaused: false,
                            lightboxOpen: false,
                            lightboxImage: '',
                            lightboxCaption: '',
                            next() {
                                if (this.total > 1) {
                                    this.current = (this.current + 1) % this.total;
                                }
                            },
                            prev() {
                                if (this.total > 1) {
                                    this.current = (this.current - 1 + this.total) % this.total;
                                }
                            },
                            goTo(index) {
                                this.current = index;
                            },
                            openLightbox(url, caption) {
                                this.lightboxImage = url;
                                this.lightboxCaption = caption;
                                this.lightboxOpen = true;
                                this.isPaused = true;
                            },
                            closeLightbox() {
                                this.lightboxOpen = false;
                                this.isPaused = false;
                            },
                            startAutoplay() {
                                if (this.total > 1) {
                                    this.timer = setInterval(() => {
                                        if (!this.isPaused && !this.lightboxOpen) {
                                            this.next();
                                        }
                                    }, 4500);
                                }
                            },
                            stopAutoplay() {
                                if (this.timer) {
                                    clearInterval(this.timer);
                                    this.timer = null;
                                }
                            }
                        }"
                        x-init="startAutoplay()"
                        @mouseenter="isPaused = true"
                        @mouseleave="isPaused = false"
                        @keydown.escape.window="closeLightbox()"
                        class="space-y-4"
                    >
                        <div class="flex items-center justify-between">
                            <x-ui.section-header title="Tarixiy suratlar" />
                            @if ($uzgolon->images->count() > 1)
                                <div class="flex items-center gap-2 text-xs font-medium text-brown-600 bg-sand/40 px-3 py-1.5 rounded-full">
                                    <span class="inline-block h-2 w-2 rounded-full bg-gold-600 animate-pulse"></span>
                                    <span x-text="(current + 1) + ' / ' + total"></span>
                                </div>
                            @endif
                        </div>

                        {{-- Asosiy katta slayd konteyneri --}}
                        <div class="relative overflow-hidden rounded-xl border border-sand bg-brown-950 shadow-md">
                            <div class="relative h-72 sm:h-[400px] md:h-[460px] lg:h-[500px] w-full">
                                @foreach ($uzgolon->images as $index => $image)
                                    <div
                                        x-show="current === {{ $index }}"
                                        x-transition:enter="transition ease-out duration-700"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-300"
                                        x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0"
                                        class="absolute inset-0 flex items-center justify-center bg-brown-950"
                                    >
                                        {{-- Orqa fonda blur qilingan effekt --}}
                                        <div class="absolute inset-0 overflow-hidden opacity-30">
                                            <img src="{{ $image->url() }}" alt="" class="h-full w-full object-cover filter blur-xl scale-110">
                                        </div>

                                        {{-- Asosiy katta surat --}}
                                        <img
                                            src="{{ $image->url() }}"
                                            alt="{{ $image->alt_text ?? $image->caption ?? $uzgolon->name }}"
                                            class="relative z-10 max-h-full max-w-full object-contain cursor-zoom-in transition-transform duration-300 hover:scale-[1.01]"
                                            @click="openLightbox('{{ $image->url() }}', '{{ addslashes($image->caption ?? $image->alt_text ?? $uzgolon->name) }}')"
                                        >

                                        {{-- Surat pastidagi ma'lumot (caption, yil, manba) --}}
                                        <div class="absolute bottom-0 inset-x-0 z-20 bg-gradient-to-t from-brown-950/95 via-brown-950/70 to-transparent p-4 sm:p-5 text-paper">
                                            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2">
                                                <div class="max-w-2xl">
                                                    @if ($image->caption)
                                                        <p class="text-sm sm:text-base font-medium text-paper leading-snug">{{ $image->caption }}</p>
                                                    @endif
                                                    <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-paper-dark/80">
                                                        @if ($image->year)
                                                            <span>Yil: <strong class="text-gold-400">{{ $image->year }}</strong></span>
                                                        @endif
                                                        @if ($image->source)
                                                            <span>Manba: {{ $image->source }}</span>
                                                        @endif
                                                        @if ($image->copyright)
                                                            <span>Mualliflik: {{ $image->copyright }}</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <button
                                                    type="button"
                                                    @click="openLightbox('{{ $image->url() }}', '{{ addslashes($image->caption ?? $image->alt_text ?? $uzgolon->name) }}')"
                                                    class="inline-flex items-center gap-1.5 self-start sm:self-auto rounded-md bg-paper/20 hover:bg-paper/30 px-3 py-1.5 text-xs text-paper backdrop-blur-xs transition"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                                    </svg>
                                                    Kattalashtirish
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Oldinga / Orqaga boshqaruv tugmalari --}}
                            @if ($uzgolon->images->count() > 1)
                                <button
                                    type="button"
                                    @click="prev()"
                                    aria-label="Oldingi surat"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 z-30 rounded-full bg-brown-900/70 p-2.5 text-paper hover:bg-brown-900 hover:text-gold-400 backdrop-blur-xs shadow-lg transition"
                                >
                                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    @click="next()"
                                    aria-label="Keyingi surat"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 z-30 rounded-full bg-brown-900/70 p-2.5 text-paper hover:bg-brown-900 hover:text-gold-400 backdrop-blur-xs shadow-lg transition"
                                >
                                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            @endif
                        </div>

                        {{-- Pastki miniaturalar (Thumbnails) va pagination dots --}}
                        @if ($uzgolon->images->count() > 1)
                            <div class="flex items-center gap-2 overflow-x-auto pb-2 pt-1">
                                @foreach ($uzgolon->images as $index => $image)
                                    <button
                                        type="button"
                                        @click="goTo({{ $index }})"
                                        :class="current === {{ $index }} ? 'ring-2 ring-gold-600 scale-105 opacity-100' : 'opacity-60 hover:opacity-100'"
                                        class="relative h-16 w-24 shrink-0 overflow-hidden rounded-md border border-sand bg-brown-900 transition-all duration-200"
                                    >
                                        <img src="{{ $image->url() }}" alt="" class="h-full w-full object-cover">
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        {{-- Lightbox Modal --}}
                        <div
                            x-show="lightboxOpen"
                            x-cloak
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-brown-950/95 p-4 backdrop-blur-md"
                            @click.self="closeLightbox()"
                        >
                            <button
                                type="button"
                                @click="closeLightbox()"
                                class="absolute top-4 right-4 rounded-full bg-paper/10 p-2 text-paper hover:bg-paper/20 transition"
                                aria-label="Yopish"
                            >
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <div class="max-h-[85vh] max-w-[95vw] flex flex-col items-center">
                                <img :src="lightboxImage" alt="" class="max-h-[75vh] max-w-full rounded-lg object-contain shadow-2xl">
                                <p x-show="lightboxCaption" x-text="lightboxCaption" class="mt-3 text-center text-sm sm:text-base text-paper-dark max-w-2xl"></p>
                            </div>
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
