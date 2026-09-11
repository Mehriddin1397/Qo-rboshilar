@extends('layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])

@section('content')
    <section class="border-b border-sand bg-paper-dark py-12">
        <x-ui.container class="max-w-3xl text-center">
            <h1 class="font-serif text-3xl font-semibold text-brown-900 sm:text-4xl">Bloglar</h1>
            <p class="mt-3 text-brown-700">Foydalanuvchilar tomonidan yozilgan tarixiy maqolalar.</p>

            @auth
                <div class="mt-5">
                    <x-ui.button href="{{ route('bloglarim.index') }}" variant="secondary">Mening bloglarim</x-ui.button>
                </div>
            @endauth
        </x-ui.container>
    </section>

    <section class="py-10">
        <x-ui.container>
            <form method="GET" class="mb-8 flex gap-3">
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Sarlavha bo'yicha qidirish..."
                       class="flex-1 rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                <x-ui.button type="submit" variant="secondary">Qidirish</x-ui.button>
            </form>

            @if ($items->isEmpty())
                <x-ui.empty-state message="Hozircha tasdiqlangan blog yo'q." />
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items as $blog)
                        <x-ui.content-card
                            :title="$blog->title"
                            :meta="$blog->author->name.' · '.$blog->published_at?->format('d.m.Y')"
                            :excerpt="$blog->excerpt"
                            :image="$blog->coverUrl()"
                            :href="route('bloglar.show', $blog)"
                        />
                    @endforeach
                </div>

                <x-ui.pagination :paginator="$items" class="mt-6" />
            @endif
        </x-ui.container>
    </section>
@endsection
