@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-ui.container class="max-w-3xl py-12">
        <h1 class="font-serif text-2xl font-semibold text-brown-900">{{ $title }}</h1>

        @if ($blog->exists && $blog->status->value !== 'draft')
            <x-ui.alert type="warning" class="mt-4">
                Bu blog hozir "{{ $blog->status->label() }}" holatida — faqat qoralama holatidagi
                bloglarni tahrirlash mumkin.
            </x-ui.alert>
        @else
            <form method="POST"
                  action="{{ $blog->exists ? route('bloglarim.update', $blog) : route('bloglarim.store') }}"
                  enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf
                @if ($blog->exists)
                    @method('PUT')
                @endif

                <x-admin.form-field label="Sarlavha" name="title" :value="$blog->title" required />
                <x-admin.form-field label="Slug (bo'sh qoldirsangiz avtomatik yaratiladi)" name="slug" :value="$blog->slug" />
                <x-admin.form-field label="Qisqacha (excerpt)" name="excerpt" type="textarea" :value="$blog->excerpt" />
                <x-admin.form-field label="To'liq matn" name="content" type="textarea" :value="$blog->content" required class="min-h-64" />

                <div>
                    <label class="block text-sm font-medium text-brown-900">Muqova rasmi</label>
                    @if ($blog->coverUrl())
                        <img src="{{ $blog->coverUrl() }}" alt="" class="mt-2 h-32 w-48 rounded object-cover">
                    @endif
                    <input type="file" name="cover" accept="image/jpeg,image/png,image/webp" class="mt-2 block text-sm text-brown-700">
                    <p class="mt-1 text-xs text-brown-500">JPG/PNG/WEBP, maksimal 6MB.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-brown-900">Bog'liq qo'rboshilar</label>
                        <select name="qorboshi_ids[]" multiple size="5" class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm">
                            @foreach ($qorboshilar as $qorboshi)
                                <option value="{{ $qorboshi->id }}" @selected($blog->exists && $blog->qorboshilar->contains('id', $qorboshi->id))>{{ $qorboshi->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-brown-900">Bog'liq qo'zg'olonlar</label>
                        <select name="uzgolon_ids[]" multiple size="5" class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm">
                            @foreach ($uzgolonlar as $uzgolon)
                                <option value="{{ $uzgolon->id }}" @selected($blog->exists && $blog->uzgolonlar->contains('id', $uzgolon->id))>{{ $uzgolon->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <p class="text-xs text-brown-500">
                    Matn oddiy matn sifatida saqlanadi (xavfsizlik uchun HTML qabul qilinmaydi).
                    Saqlagach, "Mening bloglarim" sahifasidan moderatsiyaga yuborishingiz mumkin.
                </p>

                <div class="flex items-center gap-3">
                    <x-ui.button type="submit">Saqlash</x-ui.button>
                    <x-ui.button href="{{ route('bloglarim.index') }}" variant="secondary">Bekor qilish</x-ui.button>
                </div>
            </form>
        @endif
    </x-ui.container>
@endsection
