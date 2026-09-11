@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs" />

    <form method="POST"
          action="{{ $video->exists ? route('admin.videolar.update', $video) : route('admin.videolar.store') }}"
          enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @if ($video->exists)
            @method('PUT')
        @endif

        @php
            $categoryOptions = collect($categories)->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
            $statusOptions = collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
        @endphp

        <x-ui.card title="Asosiy ma'lumot">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Sarlavha" name="title" :value="$video->title" required />
                <x-admin.form-field label="Slug (bo'sh qoldirsangiz avtomatik yaratiladi)" name="slug" :value="$video->slug" />
                <x-admin.form-field label="Kategoriya" name="category" type="select" :value="$video->category?->value ?? 'qorboshi'" :options="$categoryOptions" />
                <x-admin.form-field label="Davomiyligi (soniyada, ixtiyoriy)" name="duration_seconds" type="number" :value="$video->duration_seconds" />
            </div>
            <div class="mt-4">
                <x-admin.form-field label="Tavsif" name="description" type="textarea" :value="$video->description" />
            </div>
        </x-ui.card>

        <x-ui.card title="YouTube">
            <x-admin.form-field label="YouTube havolasi" name="youtube_url" type="url" :value="$video->youtube_url" required
                                 placeholder="https://www.youtube.com/watch?v=..." />
            <p class="mt-2 text-xs text-brown-500">
                Qabul qilinadigan formatlar: youtube.com/watch?v=..., youtu.be/..., youtube.com/shorts/...
                Video ID havoladan avtomatik ajratib olinadi va xavfsiz embed URL quriladi.
            </p>
            @if ($video->youtube_id)
                <div class="mt-4 aspect-video max-w-md overflow-hidden rounded-md border border-sand">
                    <iframe src="{{ $video->embedUrl() }}" class="h-full w-full" allowfullscreen loading="lazy"></iframe>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card title="Bog'lanishlar">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-admin.form-field label="Qo'rboshi" name="qorboshi_id" type="select" :value="$video->qorboshi_id"
                                     :options="['' => '— Tanlanmagan —'] + $qorboshilar->pluck('full_name', 'id')->all()" />
                <x-admin.form-field label="Qo'zg'olon" name="uzgolon_id" type="select" :value="$video->uzgolon_id"
                                     :options="['' => '— Tanlanmagan —'] + $uzgolonlar->pluck('name', 'id')->all()" />
                <x-admin.form-field label="Adabiyot" name="literature_id" type="select" :value="$video->literature_id"
                                     :options="['' => '— Tanlanmagan —'] + $literatures->pluck('title', 'id')->all()" />
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-brown-900">Manbalar</label>
                <select name="source_reference_ids[]" multiple size="4"
                        class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                    @foreach ($sourceReferences as $source)
                        <option value="{{ $source->id }}" @selected($video->exists && $video->sourceReferences->contains('id', $source->id))>
                            {{ $source->author }} — {{ $source->title }}
                        </option>
                    @endforeach
                </select>
            </div>
        </x-ui.card>

        <x-ui.card title="Rasm va holat">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-brown-900">Thumbnail (ixtiyoriy — bo'lmasa YouTube'dan olinadi)</label>
                    <img src="{{ $video->thumbnailUrl() }}" alt="" class="mt-2 h-20 w-32 rounded object-cover">
                    <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp" class="mt-2 block text-sm text-brown-700">
                </div>
                <div>
                    <x-admin.form-field label="Holat" name="status" type="select" :value="$video->status?->value ?? 'draft'" :options="$statusOptions" />
                    <label class="mt-4 flex items-center gap-2 text-sm font-medium text-brown-900">
                        <input type="hidden" name="featured" value="0">
                        <input type="checkbox" name="featured" value="1" @checked(old('featured', $video->featured))
                               class="rounded border-sand text-gold-600 focus:ring-gold-600">
                        Bosh sahifada ko'rsatish (featured)
                    </label>
                </div>
            </div>
        </x-ui.card>

        <div class="flex items-center gap-3">
            <x-ui.button type="submit">{{ $video->exists ? 'Saqlash' : "Qo'shish" }}</x-ui.button>
            <x-ui.button href="{{ route('admin.videolar.index') }}" variant="secondary">Bekor qilish</x-ui.button>
        </div>
    </form>
@endsection
