@extends('layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])

@section('content')
    @php
        $breadcrumbItems = [
            ['label' => 'Bosh sahifa', 'url' => route('home')],
            ['label' => 'Adabiyotlar', 'url' => route('adabiyotlar.index')],
            ['label' => $literature->title],
        ];
    @endphp

    <section class="border-b border-sand bg-paper-dark py-10">
        <x-ui.container>
            <x-ui.breadcrumb :items="$breadcrumbItems" class="mb-6" />
            <x-seo.breadcrumb-jsonld :items="$breadcrumbItems" />

            <div class="grid gap-8 sm:grid-cols-[200px_1fr]">
                <div>
                    @if ($literature->coverUrl())
                        <img src="{{ $literature->coverUrl() }}" alt="{{ $literature->title }}" class="h-64 w-full rounded-lg object-cover shadow-sm sm:h-auto">
                    @else
                        <div class="flex h-64 items-center justify-center rounded-lg bg-paper-dark text-brown-500 sm:h-full">Muqova yo'q</div>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gold-600">{{ $literature->type->label() }}</p>
                    <h1 class="mt-1 font-serif text-3xl font-semibold text-brown-900">{{ $literature->title }}</h1>
                    <p class="mt-2 text-brown-700">{{ $literature->author }}</p>

                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                        @if ($literature->publication_year)
                            <div><dt class="text-brown-500">Nashr yili</dt><dd class="text-brown-900">{{ $literature->publication_year }}</dd></div>
                        @endif
                        @if ($literature->publisher)
                            <div><dt class="text-brown-500">Nashriyot</dt><dd class="text-brown-900">{{ $literature->publisher }}</dd></div>
                        @endif
                        @if ($literature->isbn)
                            <div><dt class="text-brown-500">ISBN</dt><dd class="text-brown-900">{{ $literature->isbn }}</dd></div>
                        @endif
                        @if ($literature->language)
                            <div><dt class="text-brown-500">Til</dt><dd class="text-brown-900">{{ $literature->language }}</dd></div>
                        @endif
                    </dl>

                    <div class="mt-6 flex flex-wrap gap-3">
                        @if ($literature->file_path)
                            <x-ui.button href="{{ $literature->fileUrl() }}">Faylni ko'rish</x-ui.button>
                        @elseif ($literature->source_url)
                            <x-ui.button href="{{ $literature->source_url }}">Manba havolasi</x-ui.button>
                        @endif
                    </div>
                </div>
            </div>
        </x-ui.container>
    </section>

    <div class="py-12">
        <x-ui.container class="grid gap-10 lg:grid-cols-3">
            <div class="space-y-10 lg:col-span-2">
                <section>
                    <x-ui.section-header title="Tavsif" />
                    <p class="mt-4 whitespace-pre-line text-brown-700">{{ $literature->description }}</p>
                </section>

                @if ($literature->qorboshilar->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Bog'liq qo'rboshilar" />
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($literature->qorboshilar as $qorboshi)
                                <x-ui.content-card :title="$qorboshi->full_name" :meta="$qorboshi->region?->name" :image="$qorboshi->portraitUrl()" :href="route('qorboshilar.show', $qorboshi)" />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($literature->uzgolonlar->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Bog'liq qo'zg'olonlar" />
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($literature->uzgolonlar as $uzgolon)
                                <x-ui.content-card :title="$uzgolon->name" :meta="$uzgolon->region?->name" :image="$uzgolon->coverImageUrl()" :href="route('qozgolonlar.show', $uzgolon)" />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($literature->videos->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Bog'liq videolar" />
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($literature->videos as $video)
                                <x-ui.content-card :title="$video->title" :meta="$video->category->label()" :image="$video->thumbnailUrl()" />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($literature->sourceReferences->isNotEmpty())
                    <section>
                        <x-ui.section-header title="Ushbu adabiyotga tayanuvchi manbalar" />
                        <ul class="mt-4 space-y-2 text-sm text-brown-700">
                            @foreach ($literature->sourceReferences as $source)
                                <li class="rounded-md border border-sand bg-white px-4 py-3">
                                    <span class="font-medium text-brown-900">{{ $source->author }}</span> — {{ $source->title }}
                                    @if ($source->page)<span class="text-brown-500">(bet: {{ $source->page }})</span>@endif
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section>
                    <x-comments :comments="$comments" :commentable="$literature" />
                </section>
            </div>

            <aside class="space-y-6">
                @if ($related->isNotEmpty())
                    <div>
                        <h2 class="font-serif text-lg font-semibold text-brown-900">O'xshash adabiyotlar</h2>
                        <div class="mt-4 space-y-4">
                            @foreach ($related as $item)
                                <x-ui.content-card :title="$item->title" :meta="$item->author" :image="$item->coverUrl()" :href="route('adabiyotlar.show', $item)" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </x-ui.container>
    </div>
@endsection
