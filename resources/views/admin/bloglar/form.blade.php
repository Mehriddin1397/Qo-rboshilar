@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs" />

    <form method="POST"
          action="{{ $blog->exists ? route('admin.bloglar.update', $blog) : route('admin.bloglar.store') }}"
          enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @if ($blog->exists)
            @method('PUT')
        @endif

        @php
            $statusOptions = collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
        @endphp

        <x-ui.card title="Asosiy ma'lumot">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Sarlavha" name="title" :value="$blog->title" required />
                <x-admin.form-field label="Slug (bo'sh qoldirsangiz avtomatik yaratiladi)" name="slug" :value="$blog->slug" />
                <x-admin.form-field label="Muallif" name="author_id" type="select" :value="$blog->author_id ?? auth()->id()"
                                     :options="$authors->pluck('name', 'id')->all()" />
                <x-admin.form-field label="Holat" name="status" type="select" :value="$blog->status?->value ?? 'draft'" :options="$statusOptions" />
            </div>
            <div class="mt-4">
                <x-admin.form-field label="Qisqacha (excerpt)" name="excerpt" type="textarea" :value="$blog->excerpt" />
            </div>
        </x-ui.card>

        <x-ui.card title="Matn">
            <x-admin.form-field label="To'liq matn" name="content" type="textarea" :value="$blog->content" required class="min-h-64" />
            <p class="mt-2 text-xs text-brown-500">
                Oddiy matn sifatida saqlanadi (xavfsizlik uchun — foydalanuvchidan xom HTML qabul qilinmaydi).
            </p>
        </x-ui.card>

        <x-ui.card title="Muqova">
            @if ($blog->coverUrl())
                <img src="{{ $blog->coverUrl() }}" alt="" class="h-32 w-48 rounded object-cover">
            @endif
            <input type="file" name="cover" accept="image/jpeg,image/png,image/webp" class="mt-2 block text-sm text-brown-700">
            <p class="mt-1 text-xs text-brown-500">JPG/PNG/WEBP, maksimal 6MB.</p>
        </x-ui.card>

        <x-ui.card title="Tarixiy bog'lanishlar">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-brown-900">Qo'rboshilar</label>
                    <select name="qorboshi_ids[]" multiple size="5" class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm">
                        @foreach ($qorboshilar as $qorboshi)
                            <option value="{{ $qorboshi->id }}" @selected($blog->exists && $blog->qorboshilar->contains('id', $qorboshi->id))>{{ $qorboshi->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-brown-900">Qo'zg'olonlar</label>
                    <select name="uzgolon_ids[]" multiple size="5" class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm">
                        @foreach ($uzgolonlar as $uzgolon)
                            <option value="{{ $uzgolon->id }}" @selected($blog->exists && $blog->uzgolonlar->contains('id', $uzgolon->id))>{{ $uzgolon->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-brown-900">Adabiyotlar</label>
                    <select name="literature_ids[]" multiple size="5" class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm">
                        @foreach ($literatures as $literature)
                            <option value="{{ $literature->id }}" @selected($blog->exists && $blog->literatures->contains('id', $literature->id))>{{ $literature->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-brown-900">Videolar</label>
                    <select name="video_ids[]" multiple size="5" class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm">
                        @foreach ($videos as $video)
                            <option value="{{ $video->id }}" @selected($blog->exists && $blog->videos->contains('id', $video->id))>{{ $video->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-brown-900">Manbalar</label>
                <select name="source_reference_ids[]" multiple size="4" class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm">
                    @foreach ($sourceReferences as $source)
                        <option value="{{ $source->id }}" @selected($blog->exists && $blog->sourceReferences->contains('id', $source->id))>{{ $source->author }} — {{ $source->title }}</option>
                    @endforeach
                </select>
            </div>
        </x-ui.card>

        <div class="flex items-center gap-3">
            <x-ui.button type="submit">{{ $blog->exists ? 'Saqlash' : "Qo'shish" }}</x-ui.button>
            <x-ui.button href="{{ route('admin.bloglar.index') }}" variant="secondary">Bekor qilish</x-ui.button>
        </div>
    </form>
@endsection
