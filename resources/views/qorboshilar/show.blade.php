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

            <img src="{{ $qorboshi->portraitUrl() }}" alt="{{ $qorboshi->full_name }}" class="mx-auto h-32 w-32 rounded-full object-cover ring-4 ring-gold-600/40">
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
                    <section>
                        <x-ui.section-header title="Tarixiy suratlar" />
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($qorboshi->images as $image)
                                <figure>
                                    <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?? $qorboshi->full_name }}" loading="lazy" class="h-32 w-full rounded-md object-cover">
                                    @if ($image->caption)
                                        <figcaption class="mt-1 text-xs text-brown-500">{{ $image->caption }}</figcaption>
                                    @endif
                                </figure>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($qorboshi->uzgolonlar->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Qatnashgan qo'zg'olonlar" />
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($qorboshi->uzgolonlar as $uzgolon)
                                <x-ui.content-card :title="$uzgolon->name" :meta="$uzgolon->start_year" :excerpt="$uzgolon->short_description" :href="route('qozgolonlar.show', $uzgolon)" />
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
