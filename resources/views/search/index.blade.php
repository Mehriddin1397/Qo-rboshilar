@extends('layouts.app')

@section('title', $seo['title'])
@section('canonical', $seo['canonical'])

@section('content')
    <section class="border-b border-sand bg-paper-dark py-12">
        <x-ui.container class="max-w-3xl text-center">
            <h1 class="font-serif text-3xl font-semibold text-brown-900 sm:text-4xl">Qidiruv</h1>
            <x-ui.search-bar placeholder="Qo'rboshi, qo'zg'olon, adabiyot, video, blog..." class="mx-auto mt-6 max-w-xl" />
        </x-ui.container>
    </section>

    <section class="py-10">
        <x-ui.container>
            @if ($query === '')
                <x-ui.empty-state message="Qidiruv so'zini kiriting." />
            @else
                <div class="mb-6 flex flex-wrap items-center gap-2">
                    <a href="{{ route('search', ['q' => $query]) }}"
                       class="rounded-full border px-3 py-1.5 text-sm font-medium transition {{ $type === null ? 'border-gold-600 bg-gold-600/15 text-gold-600' : 'border-sand text-brown-700 hover:bg-paper-dark' }}">
                        Barchasi
                    </a>
                    @foreach ($types as $t)
                        <a href="{{ route('search', ['q' => $query, 'type' => $t->value]) }}"
                           class="rounded-full border px-3 py-1.5 text-sm font-medium transition {{ $type === $t->value ? 'border-gold-600 bg-gold-600/15 text-gold-600' : 'border-sand text-brown-700 hover:bg-paper-dark' }}">
                            {{ $t->pluralLabel() }}
                        </a>
                    @endforeach
                </div>

                <p class="mb-6 text-sm text-brown-700">
                    <span class="font-medium text-brown-900">{{ $results->total() }}</span> ta natija —
                    <span class="font-medium text-brown-900">&laquo;{{ $query }}&raquo;</span> bo'yicha
                </p>

                @if ($results->isEmpty())
                    <x-ui.empty-state message="Hech narsa topilmadi. Boshqa so'z bilan qidirib ko'ring." />
                @else
                    <div class="space-y-4">
                        @foreach ($results as $result)
                            <a href="{{ $result['url'] }}"
                               class="block rounded-lg border border-sand bg-white p-5 shadow-sm transition hover:border-gold-600 hover:shadow-md">
                                <x-ui.badge color="gold">{{ $result['type_label'] }}</x-ui.badge>
                                <h2 class="mt-2 font-serif text-lg font-semibold text-brown-900">{{ $result['title'] }}</h2>
                                @if ($result['excerpt'])
                                    <p class="mt-1 line-clamp-2 text-sm text-brown-700">{{ $result['excerpt'] }}</p>
                                @endif
                            </a>
                        @endforeach
                    </div>

                    <x-ui.pagination :paginator="$results" />
                @endif
            @endif
        </x-ui.container>
    </section>
@endsection
