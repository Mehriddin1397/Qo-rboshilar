@extends('layouts.app')

@use('Illuminate\Support\Str')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])

@section('content')
    <x-ui.hero
        title="QO'RBOSHILAR"
        subtitle="Turkiston ozodligi yo'lida kurashganlar tarixi"
    >
        <x-slot:actions>
            <x-ui.button href="/qorboshilar">Qo'rboshilarni o'rganish</x-ui.button>
            <x-ui.button href="/qozgolonlar" variant="secondary">Qo'zg'olonlarni xaritada ko'rish</x-ui.button>
        </x-slot:actions>
    </x-ui.hero>

    {{-- Loyiha haqida --}}
    <section class="py-16">
        <x-ui.container class="max-w-3xl text-center">
            <x-ui.section-header title="Loyiha haqida" />
            <p class="mt-4 text-brown-700">
                Qo'rboshilar.uz — Turkiston tarixida bosqinchilikka va mustamlakachilikka qarshi
                kurashgan shaxslar, qo'zg'olonlar va tarixiy voqealarni o'rganish, hujjatlashtirish
                va ommaga yetkazishga qaratilgan raqamli tarixiy platforma. Sayt raqamli
                ensiklopediya, interaktiv xarita va manbalar arxivini yagona tizimga birlashtiradi.
            </p>
        </x-ui.container>
    </section>

    {{-- Interaktiv xarita preview --}}
    <section class="bg-paper-dark py-16">
        <x-ui.container>
            <x-ui.section-header
                title="Interaktiv Turkiston xaritasi"
                subtitle="Qo'zg'olonlar va tarixiy hududlar xaritada — tez orada"
            />

            @if (empty($mapGeoJson['features']))
                <div class="relative mt-8 flex h-72 items-center justify-center overflow-hidden rounded-lg border border-sand bg-brown-900 sm:h-96">
                    <div class="absolute inset-0 opacity-[0.08]" style="background-image: radial-gradient(circle at 1px 1px, #E4D6B8 1px, transparent 0); background-size: 28px 28px;"></div>

                    <div class="relative flex flex-col items-center gap-3 px-6 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"
                             class="h-14 w-14 text-gold-400/70">
                            <path d="M9 20 3 17V4l6 3m0 13 6-3m-6 3V7m6 10 6 3V7l-6-3m0 13V4m0 3-6-3" stroke-linejoin="round" stroke-linecap="round" />
                        </svg>
                        <p class="font-serif text-lg text-paper">Tarixiy Turkiston xaritasi</p>
                        <p class="max-w-md text-sm text-paper-dark/70">
                            Qo'zg'olonlarga koordinata kiritilgach, shu yerda interaktiv xarita ko'rinadi.
                        </p>
                    </div>
                </div>
            @else
                <div
                    id="home-map-preview"
                    class="mt-8 h-72 w-full rounded-lg border border-sand sm:h-96"
                    x-data
                    x-init="window.initUzgolonMap('home-map-preview', @js($mapGeoJson), { interactive: false })"
                ></div>
            @endif

            <div class="mt-6 text-center">
                <x-ui.button href="{{ route('xarita') }}" variant="secondary">To'liq xaritani ko'rish</x-ui.button>
            </div>
        </x-ui.container>
    </section>

    {{-- Mashhur qo'rboshilar --}}
    <section class="py-16">
        <x-ui.container>
            <x-ui.section-header title="Mashhur qo'rboshilar" subtitle="Turkiston ozodligi uchun kurashgan shaxslar" />

            @if ($qorboshilar->isEmpty())
                <x-ui.empty-state message="Hozircha qo'rboshilar ma'lumotlari kiritilmagan. Ma'lumotlar admin panel orqali qo'shiladi." class="mt-6" />
            @else
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($qorboshilar as $qorboshi)
                        <x-ui.content-card
                            :title="$qorboshi->full_name"
                            :meta="$qorboshi->region?->name"
                            :excerpt="$qorboshi->short_description"
                            :image="$qorboshi->portraitUrl()"
                            :href="route('qorboshilar.show', $qorboshi)"
                        />
                    @endforeach
                </div>
            @endif
        </x-ui.container>
    </section>

    {{-- Muhim qo'zg'olonlar --}}
    <section class="bg-paper-dark py-16">
        <x-ui.container>
            <x-ui.section-header title="Muhim qo'zg'olonlar" subtitle="Turkiston tarixidagi asosiy qo'zg'olonlar" />

            @if ($uzgolonlar->isEmpty())
                <x-ui.empty-state message="Hozircha qo'zg'olonlar ma'lumotlari kiritilmagan. Ma'lumotlar admin panel orqali qo'shiladi." class="mt-6" />
            @else
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($uzgolonlar as $uzgolon)
                        <x-ui.content-card
                            :title="$uzgolon->name"
                            :meta="($uzgolon->region?->name ?? '').' · '.$uzgolon->start_year"
                            :excerpt="$uzgolon->short_description"
                            :image="$uzgolon->coverImageUrl()"
                            :href="route('qozgolonlar.show', $uzgolon)"
                        />
                    @endforeach
                </div>
            @endif
        </x-ui.container>
    </section>

    {{-- Tarixiy xronologiya --}}
    <section class="py-16">
        <x-ui.container>
            <x-ui.section-header title="Tarixiy xronologiya" subtitle="Asosiy voqealar yillar bo'yicha">
                <x-slot:action>
                    <a href="{{ route('xronologiya.index') }}" class="font-medium text-gold-600 hover:underline">Barcha xronologiya &rarr;</a>
                </x-slot:action>
            </x-ui.section-header>

            @if ($timelineEvents->isEmpty())
                <x-ui.empty-state message="Hozircha xronologiya voqealari kiritilmagan." class="mt-6" />
            @else
                <div class="mt-8 flex gap-6 overflow-x-auto pb-4">
                    @foreach ($timelineEvents as $event)
                        <a href="{{ route('xronologiya.show', $event) }}" class="flex w-56 shrink-0 flex-col border-l-2 border-gold-600 pl-4 hover:border-gold-400">
                            <p class="font-serif text-2xl font-semibold text-brown-900">{{ $event->yearRangeLabel() }}</p>
                            <p class="mt-1 text-sm font-medium text-brown-900">{{ $event->title }}</p>
                            <p class="mt-1 text-sm text-brown-700">{{ Str::limit($event->description, 90) }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-ui.container>
    </section>

    {{-- So'nggi videolar --}}
    <section class="bg-paper-dark py-16">
        <x-ui.container>
            <x-ui.section-header title="So'nggi videolar" />

            @if ($videos->isEmpty())
                <x-ui.empty-state message="Hozircha video qo'shilmagan." class="mt-6" />
            @else
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($videos as $video)
                        <x-ui.content-card
                            :title="$video->title"
                            :meta="$video->category->label()"
                            :excerpt="$video->description"
                            :image="$video->thumbnailUrl()"
                            :href="route('videolar.show', $video)"
                        />
                    @endforeach
                </div>
            @endif
        </x-ui.container>
    </section>

    {{-- So'nggi bloglar --}}
    <section class="py-16">
        <x-ui.container>
            <x-ui.section-header title="So'nggi bloglar" />

            @if ($blogs->isEmpty())
                <x-ui.empty-state message="Hozircha tasdiqlangan blog yo'q." class="mt-6" />
            @else
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($blogs as $blog)
                        <x-ui.content-card
                            :title="$blog->title"
                            :meta="$blog->author->name"
                            :excerpt="$blog->excerpt"
                            :image="$blog->coverUrl()"
                            :href="route('bloglar.show', $blog)"
                        />
                    @endforeach
                </div>
            @endif
        </x-ui.container>
    </section>

    {{-- Foydalanilgan asosiy adabiyotlar --}}
    <section class="bg-paper-dark py-16">
        <x-ui.container>
            <x-ui.section-header title="Foydalanilgan asosiy adabiyotlar" />

            @if ($literature->isEmpty())
                <x-ui.empty-state message="Hozircha adabiyotlar ro'yxati kiritilmagan." class="mt-6" />
            @else
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($literature as $item)
                        <x-ui.content-card
                            :title="$item->title"
                            :meta="$item->author"
                            :excerpt="$item->description"
                            :image="$item->coverUrl()"
                            :href="route('adabiyotlar.show', $item)"
                        />
                    @endforeach
                </div>
            @endif
        </x-ui.container>
    </section>
@endsection
