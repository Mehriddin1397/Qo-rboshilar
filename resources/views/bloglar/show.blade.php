@extends('layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])

@section('content')
    @php
        $breadcrumbItems = [
            ['label' => 'Bosh sahifa', 'url' => route('home')],
            ['label' => 'Bloglar', 'url' => route('bloglar.index')],
            ['label' => $blog->title],
        ];
    @endphp

    <section class="border-b border-sand bg-paper-dark py-10">
        <x-ui.container class="max-w-3xl">
            <x-ui.breadcrumb :items="$breadcrumbItems" class="mb-6" />
            <x-seo.breadcrumb-jsonld :items="$breadcrumbItems" />
            <x-seo.article-jsonld
                :title="$blog->title"
                :description="$blog->excerpt"
                :author-name="$blog->author->name"
                :published-at="$blog->published_at"
                :modified-at="$blog->updated_at"
                :image="$blog->coverUrl()"
                :url="route('bloglar.show', $blog)"
            />

            @if ($blog->coverUrl())
                <img src="{{ $blog->coverUrl() }}" alt="{{ $blog->title }}" class="h-64 w-full rounded-lg object-cover">
            @endif

            <h1 class="mt-6 font-serif text-3xl font-semibold text-brown-900 sm:text-4xl">{{ $blog->title }}</h1>
            <div class="mt-3 flex items-center gap-3 text-sm text-brown-700">
                <img src="{{ $blog->author->avatarUrl() }}" alt="{{ $blog->author->name }}" class="h-8 w-8 rounded-full object-cover">
                <span>{{ $blog->author->name }}</span>
                <span>&middot;</span>
                <span>{{ $blog->published_at?->format('d.m.Y') }}</span>
                <span>&middot;</span>
                <span>{{ $blog->views }} ko'rish</span>
            </div>
        </x-ui.container>
    </section>

    <div class="py-12">
        <x-ui.container class="grid gap-10 lg:grid-cols-3">
            <div class="space-y-10 lg:col-span-2">
                <section>
                    <p class="whitespace-pre-line text-brown-700">{{ $blog->content }}</p>
                </section>

                @if ($blog->qorboshilar->isNotEmpty() || $blog->uzgolonlar->isNotEmpty() || $blog->literatures->isNotEmpty() || $blog->videos->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Bog'liq tarixiy materiallar" />
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($blog->qorboshilar as $qorboshi)
                                <x-ui.content-card :title="$qorboshi->full_name" meta="Qo'rboshi" :image="$qorboshi->portraitUrl()" :href="route('qorboshilar.show', $qorboshi)" />
                            @endforeach
                            @foreach ($blog->uzgolonlar as $uzgolon)
                                <x-ui.content-card :title="$uzgolon->name" meta="Qo'zg'olon" :image="$uzgolon->coverImageUrl()" :href="route('qozgolonlar.show', $uzgolon)" />
                            @endforeach
                            @foreach ($blog->literatures as $literature)
                                <x-ui.content-card :title="$literature->title" meta="Adabiyot" :image="$literature->coverUrl()" :href="route('adabiyotlar.show', $literature)" />
                            @endforeach
                            @foreach ($blog->videos as $video)
                                <x-ui.content-card :title="$video->title" meta="Video" :image="$video->thumbnailUrl()" :href="route('videolar.show', $video)" />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($blog->sourceReferences->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Manbalar" />
                        <ul class="mt-4 space-y-2 text-sm text-brown-700">
                            @foreach ($blog->sourceReferences as $source)
                                <li class="rounded-md border border-sand bg-white px-4 py-3">
                                    <span class="font-medium text-brown-900">{{ $source->author }}</span> — {{ $source->title }}
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section>
                    <x-comments :comments="$comments" :commentable="$blog" />
                </section>
            </div>

            <aside class="space-y-6">
                @if ($related->isNotEmpty())
                    <div>
                        <h2 class="font-serif text-lg font-semibold text-brown-900">Boshqa bloglar</h2>
                        <div class="mt-4 space-y-4">
                            @foreach ($related as $item)
                                <x-ui.content-card :title="$item->title" :meta="$item->author->name" :image="$item->coverUrl()" :href="route('bloglar.show', $item)" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </x-ui.container>
    </div>
@endsection
