@extends('layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])

@section('content')
    <section class="border-b border-sand bg-paper-dark py-12">
        <x-ui.container class="max-w-3xl text-center">
            <h1 class="font-serif text-3xl font-semibold text-brown-900 sm:text-4xl">Tarixiy xronologiya</h1>
            <p class="mt-3 text-brown-700">
                Turkiston tarixidagi muhim voqealar — yillar bo'yicha, tegishli qo'rboshi, qo'zg'olon va
                hududlar bilan bog'langan holda.
            </p>
        </x-ui.container>
    </section>

    <section class="py-10">
        <x-ui.container class="max-w-3xl">
            <form method="GET" class="mb-10 grid gap-3 sm:grid-cols-5">
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Voqea bo'yicha qidirish..."
                       class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600 sm:col-span-2">

                <select name="period" class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    <option value="">Barcha davrlar</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}" @selected((string) ($filters['period'] ?? '') === (string) $period->id)>{{ $period->name }}</option>
                    @endforeach
                </select>

                <input type="number" name="from_year" value="{{ $filters['from_year'] ?? '' }}" placeholder="Yildan"
                       class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                <input type="number" name="to_year" value="{{ $filters['to_year'] ?? '' }}" placeholder="Yilgacha"
                       class="rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">

                <div class="flex gap-2 sm:col-span-5">
                    <x-ui.button type="submit" variant="secondary">Filtrlash</x-ui.button>
                    @if (array_filter($filters))
                        <x-ui.button href="{{ route('xronologiya.index') }}" variant="secondary">Tozalash</x-ui.button>
                    @endif
                    <x-ui.button href="{{ route('xarita') }}" variant="secondary">Xaritada ko'rish</x-ui.button>
                </div>
            </form>

            @if ($items->isEmpty())
                <x-ui.empty-state message="Bu filtr bo'yicha xronologiya voqeasi topilmadi." />
            @else
                <ol class="relative space-y-8 border-l-2 border-sand pl-6">
                    @foreach ($items as $event)
                        <li class="relative">
                            <span class="absolute -left-[1.95rem] top-1 h-3 w-3 rounded-full border-2 border-paper bg-gold-600"></span>

                            <a href="{{ route('xronologiya.show', $event) }}" class="group block rounded-lg border border-sand bg-white p-5 shadow-sm transition hover:border-gold-600 hover:shadow-md">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-serif text-xl font-semibold text-brown-900">{{ $event->yearRangeLabel() }}</span>
                                    <x-ui.badge :color="match($event->accuracy_status->value) { 'verified' => 'success', 'approximate' => 'gold', default => 'danger' }">
                                        {{ $event->accuracy_status->label() }}
                                    </x-ui.badge>
                                    @if ($event->period)
                                        <x-ui.badge color="neutral">{{ $event->period->name }}</x-ui.badge>
                                    @endif
                                </div>
                                <h2 class="mt-2 font-serif text-lg font-semibold text-brown-900 group-hover:text-gold-600">{{ $event->title }}</h2>
                                <p class="mt-1 line-clamp-2 text-sm text-brown-700">{{ $event->description }}</p>

                                @if ($event->qorboshi || $event->uzgolon)
                                    <p class="mt-2 text-xs text-brown-500">
                                        @if ($event->qorboshi)
                                            <span>Qo'rboshi: {{ $event->qorboshi->full_name }}</span>
                                        @endif
                                        @if ($event->qorboshi && $event->uzgolon)
                                            <span>&middot;</span>
                                        @endif
                                        @if ($event->uzgolon)
                                            <span>Qo'zg'olon: {{ $event->uzgolon->name }}</span>
                                        @endif
                                    </p>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ol>

                <x-ui.pagination :paginator="$items" class="mt-8" />
            @endif
        </x-ui.container>
    </section>
@endsection
