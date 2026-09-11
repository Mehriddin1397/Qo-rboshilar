@extends('layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])

@section('content')
    <section class="border-b border-sand bg-paper-dark py-12">
        <x-ui.container class="max-w-3xl text-center">
            <h1 class="font-serif text-3xl font-semibold text-brown-900 sm:text-4xl">Qo'rboshilar</h1>
            <p class="mt-3 text-brown-700">
                Turkiston ozodligi yo'lida kurashgan qo'rboshilarning to'liq ro'yxati — har biri o'z hududi,
                qo'zg'olonlari va tarixiy faoliyati bilan.
            </p>
        </x-ui.container>
    </section>

    <section class="py-10">
        <x-ui.container>
            <form method="GET" class="mb-8 grid gap-3 sm:grid-cols-4">
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Ism bo'yicha qidirish..."
                       class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600 sm:col-span-2">

                <select name="region" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha hududlar</option>
                    @foreach ($regions as $region)
                        <option value="{{ $region->id }}" @selected((string) ($filters['region'] ?? '') === (string) $region->id)>{{ $region->name }}</option>
                    @endforeach
                </select>

                <x-ui.button type="submit" variant="secondary">Qidirish</x-ui.button>
            </form>

            @if ($items->isEmpty())
                <x-ui.empty-state message="Hozircha hech qanday qo'rboshi topilmadi." />
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items as $qorboshi)
                        <x-ui.content-card
                            :title="$qorboshi->full_name"
                            :meta="$qorboshi->region?->name"
                            :excerpt="$qorboshi->short_description"
                            :image="$qorboshi->portraitUrl()"
                            :href="route('qorboshilar.show', $qorboshi)"
                        />
                    @endforeach
                </div>

                <x-ui.pagination :paginator="$items" class="mt-6" />
            @endif
        </x-ui.container>
    </section>
@endsection
