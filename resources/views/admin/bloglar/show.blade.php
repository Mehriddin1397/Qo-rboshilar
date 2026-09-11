@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            @if ($blog->status->value === 'pending')
                <form method="POST" action="{{ route('admin.bloglar.approve', $blog) }}">
                    @csrf
                    <x-ui.button type="submit">Tasdiqlash</x-ui.button>
                </form>
                <form method="POST" action="{{ route('admin.bloglar.reject', $blog) }}">
                    @csrf
                    <x-ui.button type="submit" variant="danger">Rad etish</x-ui.button>
                </form>
            @endif
            @can('update', $blog)
                <x-ui.button href="{{ route('admin.bloglar.edit', $blog) }}" variant="secondary">Tahrirlash</x-ui.button>
            @endcan
            @if ($blog->status->value === 'approved')
                <x-ui.button href="{{ route('bloglar.show', $blog) }}" variant="secondary">Saytda ko'rish</x-ui.button>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-1">
            @if ($blog->coverUrl())
                <img src="{{ $blog->coverUrl() }}" alt="" class="h-40 w-full rounded object-cover">
            @endif
            <div class="mt-4 flex justify-center">
                <x-ui.badge :color="match($blog->status->value) { 'approved' => 'success', 'rejected' => 'danger', 'pending' => 'gold', default => 'neutral' }">
                    {{ $blog->status->label() }}
                </x-ui.badge>
            </div>
            <dl class="mt-6 space-y-3 text-sm">
                <div><dt class="text-brown-500">Muallif</dt><dd class="text-brown-900">{{ $blog->author->name }}</dd></div>
                <div><dt class="text-brown-500">Ko'rishlar</dt><dd class="text-brown-900">{{ $blog->views }}</dd></div>
                <div><dt class="text-brown-500">Nashr sanasi</dt><dd class="text-brown-900">{{ $blog->published_at?->format('d.m.Y') ?? '—' }}</dd></div>
            </dl>
        </x-ui.card>

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Qisqacha">
                <p class="text-sm text-brown-700">{{ $blog->excerpt ?? '—' }}</p>
            </x-ui.card>
            <x-ui.card title="Matn">
                <p class="whitespace-pre-line text-sm text-brown-700">{{ $blog->content }}</p>
            </x-ui.card>
        </div>
    </div>
@endsection
