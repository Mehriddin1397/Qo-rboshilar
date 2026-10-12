@extends('layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])

@section('content')
    @php
        $breadcrumbItems = [
            ['label' => 'Bosh sahifa', 'url' => route('home')],
            ['label' => "Qo'rboshilar", 'url' => route('qorboshilar.index')],
            ['label' => $qorboshi->full_name],
        ];
    @endphp

    <section class="border-b border-sand bg-brown-900 py-12 text-center">
        <x-ui.container class="max-w-3xl">
            <x-ui.breadcrumb class="mb-4 justify-center text-paper-dark/70" :items="$breadcrumbItems" />
            <x-seo.breadcrumb-jsonld :items="$breadcrumbItems" />
            <x-seo.person-jsonld
                :name="$qorboshi->full_name"
                :description="Str::limit($qorboshi->meta_description ?? $qorboshi->short_description, 160)"
                :image="$qorboshi->portraitUrl()"
                :birth-date="$qorboshi->birth_year"
                :death-date="$qorboshi->death_year"
                :birth-place="$qorboshi->birth_place"
                :death-place="$qorboshi->death_place"
                :url="route('qorboshilar.show', $qorboshi)"
            />

            <img src="{{ $qorboshi->portraitUrl() }}" alt="{{ $qorboshi->full_name }}" class="mx-auto w-40 sm:w-48 aspect-[3/4] rounded-xl object-cover object-top shadow-xl ring-4 ring-gold-600/40">
            <h1 class="mt-4 font-serif text-3xl font-semibold text-paper sm:text-4xl">{{ $qorboshi->full_name }}</h1>
            <p class="mt-2 text-paper-dark/80">{{ $qorboshi->short_description }}</p>

            <div class="mt-4 flex flex-wrap justify-center gap-2 text-sm text-paper-dark/70">
                @if ($qorboshi->region)
                    <span>{{ $qorboshi->region->name }}</span>
                @endif
                @if ($qorboshi->active_from_year || $qorboshi->active_to_year)
                    <span>&middot;</span>
                    <span>Faoliyat: {{ $qorboshi->active_from_year ?? '?' }}–{{ $qorboshi->active_to_year ?? '?' }}</span>
                @endif
                @if ($qorboshi->birth_year || $qorboshi->death_year)
                    <span>&middot;</span>
                    <span>{{ $qorboshi->birth_year ?? '?' }}–{{ $qorboshi->death_year ?? '?' }}</span>
                @endif
            </div>
        </x-ui.container>
    </section>

    <div class="py-12">
        <x-ui.container class="grid gap-10 lg:grid-cols-3">
            <div class="space-y-10 lg:col-span-2">
                <section>
                    <x-ui.section-header title="Biografiya" />
                    <p class="mt-4 whitespace-pre-line text-brown-700">{{ $qorboshi->biography }}</p>
                </section>

                @if ($qorboshi->historical_context)
                    <section>
                        <x-ui.section-header title="Tarixiy kontekst" />
                        <p class="mt-4 whitespace-pre-line text-brown-700">{{ $qorboshi->historical_context }}</p>
                    </section>
                @endif

                @if ($qorboshi->images->isNotEmpty())
                    <section
                        x-data="{
                            lightboxOpen: false,
                            lightboxImage: '',
                            lightboxCaption: '',
                            currentIndex: 0,
                            images: [
                                @foreach ($qorboshi->images as $img)
                                    {
                                        url: '{{ $img->url() }}',
                                        caption: '{{ addslashes($img->caption ?? $img->alt_text ?? $qorboshi->full_name) }}'
                                    },
                                @endforeach
                            ],
                            openLightbox(index) {
                                this.currentIndex = index;
                                this.lightboxImage = this.images[index].url;
                                this.lightboxCaption = this.images[index].caption;
                                this.lightboxOpen = true;
                            },
                            closeLightbox() {
                                this.lightboxOpen = false;
                            },
                            next() {
                                this.currentIndex = (this.currentIndex + 1) % this.images.length;
                                this.lightboxImage = this.images[this.currentIndex].url;
                                this.lightboxCaption = this.images[this.currentIndex].caption;
                            },
                            prev() {
                                this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
                                this.lightboxImage = this.images[this.currentIndex].url;
                                this.lightboxCaption = this.images[this.currentIndex].caption;
                            }
                        }"
                        @keydown.escape.window="closeLightbox()"
                        @keydown.arrow-right.window="lightboxOpen && next()"
                        @keydown.arrow-left.window="lightboxOpen && prev()"
                    >
                        <x-ui.section-header title="Tarixiy suratlar" subtitle="Kattalashtirib ko'rish uchun surat ustiga bosing" />
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                            @foreach ($qorboshi->images as $index => $image)
                                <figure class="group relative cursor-pointer overflow-hidden rounded-lg border border-sand bg-paper-dark shadow-sm transition hover:border-gold-600 hover:shadow-md"
                                        @click="openLightbox({{ $index }})">
                                    <div class="relative h-36 sm:h-44 w-full overflow-hidden">
                                        <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?? $qorboshi->full_name }}" loading="lazy"
                                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                        <div class="absolute inset-0 flex items-center justify-center bg-brown-950/30 opacity-0 transition group-hover:opacity-100">
                                            <span class="rounded-full bg-paper/90 p-2 text-brown-900 shadow">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                    @if ($image->caption)
                                        <figcaption class="p-2 text-xs text-brown-700 line-clamp-2">{{ $image->caption }}</figcaption>
                                    @endif
                                </figure>
                            @endforeach
                        </div>

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
                            {{-- Yopish tugmasi --}}
                            <button
                                type="button"
                                @click="closeLightbox()"
                                class="absolute top-4 right-4 rounded-full bg-paper/10 p-2 text-paper hover:bg-paper/20 transition focus:outline-none"
                                aria-label="Yopish"
                            >
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            {{-- Oldingi surat tugmasi --}}
                            <template x-if="images.length > 1">
                                <button
                                    type="button"
                                    @click="prev()"
                                    class="absolute left-4 top-1/2 -translate-y-1/2 rounded-full bg-paper/10 p-3 text-paper hover:bg-paper/20 transition focus:outline-none"
                                    aria-label="Oldingi surat"
                                >
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                            </template>

                            {{-- Keyingi surat tugmasi --}}
                            <template x-if="images.length > 1">
                                <button
                                    type="button"
                                    @click="next()"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 rounded-full bg-paper/10 p-3 text-paper hover:bg-paper/20 transition focus:outline-none"
                                    aria-label="Keyingi surat"
                                >
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </template>

                            {{-- Asosiy katta rasm va tavsif --}}
                            <div class="flex max-h-[85vh] max-w-[95vw] flex-col items-center">
                                <img :src="lightboxImage" :alt="lightboxCaption || '{{ $qorboshi->full_name }}'" class="max-h-[75vh] max-w-full rounded-lg object-contain shadow-2xl">
                                <div class="mt-3 flex items-center justify-between w-full max-w-2xl px-2">
                                    <p x-show="lightboxCaption" x-text="lightboxCaption" class="text-center text-sm sm:text-base text-paper-dark flex-1"></p>
                                    <template x-if="images.length > 1">
                                        <span class="text-xs text-paper-dark/60 ml-2" x-text="(currentIndex + 1) + ' / ' + images.length"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif

                @if ($qorboshi->uzgolonlar->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Qatnashgan qo'zg'olonlar" />
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($qorboshi->uzgolonlar as $uzgolon)
                                <x-ui.content-card
                                    :title="$uzgolon->name"
                                    :meta="$uzgolon->start_year"
                                    :excerpt="$uzgolon->short_description"
                                    :image="$uzgolon->coverImageUrl()"
                                    :href="route('qozgolonlar.show', $uzgolon)"
                                />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($qorboshi->literatures->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Foydalanilgan adabiyotlar" />
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($qorboshi->literatures as $literature)
                                <x-ui.content-card :title="$literature->title" :meta="$literature->author" :image="$literature->coverUrl()" :href="route('adabiyotlar.show', $literature)" />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($qorboshi->videos->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Videolar" />
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($qorboshi->videos as $video)
                                <x-ui.content-card :title="$video->title" :meta="$video->category->label()" :image="$video->thumbnailUrl()" :href="route('videolar.show', $video)" />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($qorboshi->blogs->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Bog'liq bloglar" />
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($qorboshi->blogs as $blog)
                                <x-ui.content-card :title="$blog->title" :meta="$blog->author->name" :image="$blog->coverUrl()" :href="route('bloglar.show', $blog)" />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($qorboshi->sourceReferences->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Manbalar" subtitle="Ushbu ma'lumot quyidagi manbalarga asoslangan" />
                        <ul class="mt-4 space-y-2 text-sm text-brown-700">
                            @foreach ($qorboshi->sourceReferences as $source)
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
                    <x-comments :comments="$comments" :commentable="$qorboshi" />
                </section>
            </div>

            <aside class="space-y-6">
                <div>
                    <x-ui.button href="{{ route('qozgolonlar.index') }}" variant="secondary" class="w-full justify-center">
                        Qo'zg'olonlarni ko'rish
                    </x-ui.button>
                </div>

                @if ($related->isNotEmpty())
                    <div>
                        <h2 class="font-serif text-lg font-semibold text-brown-900">Boshqa qo'rboshilar</h2>
                        <div class="mt-4 space-y-4">
                            @foreach ($related as $item)
                                <x-ui.content-card :title="$item->full_name" :meta="$item->region?->name" :image="$item->portraitUrl()" :href="route('qorboshilar.show', $item)" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </x-ui.container>
    </div>
@endsection
