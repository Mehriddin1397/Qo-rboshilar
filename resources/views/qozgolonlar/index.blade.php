@extends('layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])

@section('content')
    <section class="border-b border-sand bg-paper-dark py-12">
        <x-ui.container class="max-w-3xl text-center">
            <h1 class="font-serif text-3xl font-semibold text-brown-900 sm:text-4xl">Qo'zg'olonlar</h1>
            <p class="mt-3 text-brown-700">
                Turkiston tarixidagi muhim qo'zg'olonlar — sabablari, borishi va tarixiy ahamiyati bilan.
            </p>
            <div class="mt-5">
                <x-ui.button href="{{ route('xarita') }}" variant="secondary">Xaritada ko'rish</x-ui.button>
            </div>
        </x-ui.container>
    </section>

    <section class="py-10">
        <x-ui.container>
            <form method="GET" class="mb-8 grid gap-3 sm:grid-cols-5">
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nom bo'yicha qidirish..."
                       class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600 sm:col-span-2">

                <select name="region" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha hududlar</option>
                    @foreach ($regions as $region)
                        <option value="{{ $region->id }}" @selected((string) ($filters['region'] ?? '') === (string) $region->id)>{{ $region->name }}</option>
                    @endforeach
                </select>

                <select name="period" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha davrlar</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}" @selected((string) ($filters['period'] ?? '') === (string) $period->id)>{{ $period->name }}</option>
                    @endforeach
                </select>

                <x-ui.button type="submit" variant="secondary">Qidirish</x-ui.button>
            </form>

            @if ($items->isEmpty())
                <x-ui.empty-state message="Hozircha hech qanday qo'zg'olon topilmadi." />
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items as $uzgolon)
                        <x-ui.content-card
                            :title="$uzgolon->name"
                            :meta="($uzgolon->region?->name ?? '').' · '.$uzgolon->start_year.($uzgolon->end_year ? '–'.$uzgolon->end_year : '')"
                            :excerpt="$uzgolon->short_description"
                            :image="$uzgolon->coverImageUrl()"
                            :href="route('qozgolonlar.show', $uzgolon)"
                        />
                    @endforeach
                </div>

                <x-ui.pagination :paginator="$items" class="mt-6" />
            @endif
        </x-ui.container>
    </section>
@endsection
