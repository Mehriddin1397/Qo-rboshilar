@extends('layouts.app')

@section('title', "Profil — Qo'rboshilar.uz")

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <h1 class="font-serif text-2xl font-semibold text-brown-900">Mening profilim</h1>

        @if (session('status') === 'profile-updated')
            <div class="mt-4 rounded-md bg-success/10 px-4 py-3 text-sm text-success">
                Profil muvaffaqiyatli yangilandi.
            </div>
        @endif

        <div class="mt-8 flex items-center gap-4">
            <img src="{{ $profileUser->avatarUrl() }}"
                 alt="{{ $profileUser->name }}" class="h-16 w-16 rounded-full object-cover">
            <div>
                <p class="font-medium text-brown-900">{{ $profileUser->name }}</p>
                <p class="text-sm text-brown-700">{{ $profileUser->role?->label() ?? '—' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-8 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="block text-sm font-medium text-brown-900">Ism-familiya</label>
                <input id="name" type="text" name="name" value="{{ old('name', $profileUser->name) }}" required
                       class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                @error('name')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-brown-900">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $profileUser->email) }}" required
                       class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                @error('email')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="bio" class="block text-sm font-medium text-brown-900">Bio</label>
                <textarea id="bio" name="bio" rows="4"
                          class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">{{ old('bio', $profileUser->bio) }}</textarea>
                @error('bio')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="avatar" class="block text-sm font-medium text-brown-900">Avatar</label>
                <input id="avatar" type="file" name="avatar" accept="image/*"
                       class="mt-1 w-full text-sm text-brown-700">
                @error('avatar')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="rounded-md bg-brown-900 px-4 py-2 text-sm font-medium text-paper transition hover:bg-gold-600">
                Saqlash
            </button>
        </form>

        <div class="mt-12">
            <div class="flex items-center justify-between">
                <h2 class="font-serif text-lg font-semibold text-brown-900">Mening bloglarim ({{ $profileUser->blogs->count() }})</h2>
                <x-ui.button href="{{ route('bloglarim.index') }}" variant="secondary">Boshqarish</x-ui.button>
            </div>
            <ul class="mt-3 space-y-2">
                @forelse ($profileUser->blogs as $blog)
                    <li class="rounded-md border border-sand bg-white px-4 py-3 text-sm">
                        <span class="font-medium text-brown-900">{{ $blog->title }}</span>
                        <span class="ml-2 text-xs text-brown-700">({{ $blog->status->label() }})</span>
                    </li>
                @empty
                    <li class="text-sm text-brown-700">Hozircha blog yozilmagan.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
