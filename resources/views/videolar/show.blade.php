@extends('layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])

@section('content')
    @php
        $breadcrumbItems = [
            ['label' => 'Bosh sahifa', 'url' => route('home')],
            ['label' => 'Videolar', 'url' => route('videolar.index')],
            ['label' => $video->title],
        ];
    @endphp

    <section class="border-b border-sand bg-brown-900 py-10">
        <x-ui.container class="max-w-4xl">
            <x-ui.breadcrumb :items="$breadcrumbItems" class="mb-6 text-paper-dark/70" />
            <x-seo.breadcrumb-jsonld :items="$breadcrumbItems" />
            <h1 class="font-serif text-2xl font-semibold text-paper sm:text-3xl">{{ $video->title }}</h1>

            <div class="mt-6 aspect-video overflow-hidden rounded-lg border border-sand/30">
                <iframe src="{{ $video->embedUrl() }}" class="h-full w-full" allowfullscreen loading="lazy" title="{{ $video->title }}"></iframe>
            </div>
        </x-ui.container>
    </section>

    <div class="py-12">
        <x-ui.container class="grid gap-10 lg:grid-cols-3">
            <div class="space-y-10 lg:col-span-2">
                @if ($video->description)
                    <section>
                        <x-ui.section-header title="Tavsif" />
                        <p class="mt-4 whitespace-pre-line text-brown-700">{{ $video->description }}</p>
                    </section>
                @endif

                @if ($video->qorboshi || $video->uzgolon || $video->literature)
                    <section>
                        <x-ui.section-header title="Bog'liq materiallar" />
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @if ($video->qorboshi)
                                <x-ui.content-card :title="$video->qorboshi->full_name" meta="Qo'rboshi" :image="$video->qorboshi->portraitUrl()" :href="route('qorboshilar.show', $video->qorboshi)" />
                            @endif
                            @if ($video->uzgolon)
                                <x-ui.content-card :title="$video->uzgolon->name" meta="Qo'zg'olon" :image="$video->uzgolon->coverImageUrl()" :href="route('qozgolonlar.show', $video->uzgolon)" />
                            @endif
                            @if ($video->literature)
                                <x-ui.content-card :title="$video->literature->title" meta="Adabiyot" :image="$video->literature->coverUrl()" :href="route('adabiyotlar.show', $video->literature)" />
                            @endif
                        </div>
                    </section>
                @endif

                @if ($video->sourceReferences->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Manbalar" />
                        <ul class="mt-4 space-y-2 text-sm text-brown-700">
                            @foreach ($video->sourceReferences as $source)
                                <li class="rounded-md border border-sand bg-white px-4 py-3">
                                    <span class="font-medium text-brown-900">{{ $source->author }}</span> — {{ $source->title }}
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section>
                    <x-comments :comments="$comments" :commentable="$video" />
                </section>
            </div>

            <aside class="space-y-6">
                @if ($related->isNotEmpty())
                    <div>
                        <h2 class="font-serif text-lg font-semibold text-brown-900">Boshqa videolar</h2>
                        <div class="mt-4 space-y-4">
                            @foreach ($related as $item)
                                <x-ui.content-card :title="$item->title" :meta="$item->category->label()" :image="$item->thumbnailUrl()" :href="route('videolar.show', $item)" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </x-ui.container>
    </div>
@endsection
