@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            @if ($comment->status->value !== 'approved')
                <form method="POST" action="{{ route('admin.comments.approve', $comment) }}">
                    @csrf
                    <x-ui.button type="submit">Tasdiqlash</x-ui.button>
                </form>
            @endif
            @if ($comment->status->value !== 'rejected')
                <form method="POST" action="{{ route('admin.comments.reject', $comment) }}">
                    @csrf
                    <x-ui.button type="submit" variant="danger">Rad etish</x-ui.button>
                </form>
            @endif
            @can('delete', $comment)
                <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" onsubmit="return confirm('O\'chirishni tasdiqlaysizmi?');">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="secondary">O'chirish</x-ui.button>
                </form>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-1">
            <div class="flex justify-center">
                <x-ui.badge :color="match($comment->status->value) { 'approved' => 'success', 'rejected' => 'danger', 'pending' => 'gold', default => 'neutral' }">
                    {{ $comment->status->label() }}
                </x-ui.badge>
            </div>
            <dl class="mt-6 space-y-3 text-sm">
                <div><dt class="text-brown-500">Foydalanuvchi</dt><dd class="text-brown-900">{{ $comment->author->name }} ({{ $comment->author->email }})</dd></div>
                <div><dt class="text-brown-500">Kontent turi</dt><dd class="text-brown-900">{{ $comment->commentableTypeLabel() }}</dd></div>
                <div>
                    <dt class="text-brown-500">Kontent</dt>
                    <dd class="text-brown-900">
                        @if ($comment->commentableUrl())
                            <a href="{{ $comment->commentableUrl() }}" class="text-gold-600 hover:underline" target="_blank" rel="noopener">{{ $comment->commentableTitle() }}</a>
                        @else
                            {{ $comment->commentableTitle() }}
                        @endif
                    </dd>
                </div>
                <div><dt class="text-brown-500">Yaratilgan</dt><dd class="text-brown-900">{{ $comment->created_at->format('d.m.Y H:i') }}</dd></div>
            </dl>
        </x-ui.card>

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Izoh matni">
                <p class="whitespace-pre-line text-sm text-brown-700">{{ $comment->content }}</p>
            </x-ui.card>
        </div>
    </div>
@endsection
