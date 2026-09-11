@extends('layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])

@section('content')
    <section class="border-b border-sand bg-paper-dark py-12">
        <x-ui.container class="max-w-3xl text-center">
            <h1 class="font-serif text-3xl font-semibold text-brown-900 sm:text-4xl">Adabiyotlar</h1>
            <p class="mt-3 text-brown-700">Turkiston tarixiga oid kitoblar, maqolalar va arxiv hujjatlari.</p>
        </x-ui.container>
    </section>

    <section class="py-10">
        <x-ui.container>
            <form method="GET" class="mb-8 grid gap-3 sm:grid-cols-4">
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nom yoki muallif bo'yicha qidirish..."
                       class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600 sm:col-span-2">

                <select name="type" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha turlar</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected((string) ($filters['type'] ?? '') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>

                <x-ui.button type="submit" variant="secondary">Qidirish</x-ui.button>
            </form>

            @if ($items->isEmpty())
                <x-ui.empty-state message="Hozircha hech qanday adabiyot topilmadi." />
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items as $item)
                        <x-ui.content-card
                            :title="$item->title"
                            :meta="$item->author.($item->publication_year ? ' · '.$item->publication_year : '')"
                            :excerpt="$item->description"
                            :image="$item->coverUrl()"
                            :href="route('adabiyotlar.show', $item)"
                        />
                    @endforeach
                </div>

                <x-ui.pagination :paginator="$items" class="mt-6" />
            @endif
        </x-ui.container>
    </section>
@endsection
