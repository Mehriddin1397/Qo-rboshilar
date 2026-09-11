@props(['comments', 'commentable'])

@php
    $commentableType = \App\Enums\ContentType::fromModel($commentable)->value;
@endphp

<div {{ $attributes }}>
    <x-ui.section-header title="Izohlar ({{ $comments->total() }})" />

    <div class="mt-4 space-y-4">
        @forelse ($comments as $comment)
            <div class="rounded-lg border border-sand bg-white p-4">
                <div class="flex items-center gap-3">
                    <img src="{{ $comment->author->avatarUrl() }}" alt="{{ $comment->author->name }}" loading="lazy" class="h-9 w-9 rounded-full object-cover">
                    <div>
                        <p class="text-sm font-medium text-brown-900">{{ $comment->author->name }}</p>
                        <p class="text-xs text-brown-500">{{ $comment->created_at->format('d.m.Y') }}</p>
                    </div>
                </div>
                <p class="mt-3 whitespace-pre-line text-sm text-brown-700">{{ $comment->content }}</p>
            </div>
        @empty
            <x-ui.empty-state message="Hozircha izoh yo'q. Birinchi bo'lib fikr bildiring." />
        @endforelse
    </div>

    <x-ui.pagination :paginator="$comments" />

    <div class="mt-6 border-t border-sand pt-6">
        @auth
            <form method="POST" action="{{ route('comments.store') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="commentable_type" value="{{ $commentableType }}">
                <input type="hidden" name="commentable_id" value="{{ $commentable->id }}">

                <label for="comment-content" class="block text-sm font-medium text-brown-900">Yangi izoh yozing</label>
                <textarea id="comment-content" name="content" rows="4" maxlength="5000" required
                          class="w-full rounded-md border border-sand bg-white px-3 py-2 text-sm text-ink focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">{{ old('content') }}</textarea>

                @error('content')
                    <p class="text-sm text-danger">{{ $message }}</p>
                @enderror
                @error('commentable_id')
                    <p class="text-sm text-danger">{{ $message }}</p>
                @enderror

                <x-ui.button type="submit">Izoh qoldirish</x-ui.button>
            </form>
        @else
            <p class="text-sm text-brown-700">
                Izoh qoldirish uchun <a href="{{ route('login') }}" class="font-medium text-gold-600 hover:underline">tizimga kiring</a>.
            </p>
        @endauth
    </div>
</div>
