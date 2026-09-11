@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs" />

    <form method="POST"
          action="{{ $literature->exists ? route('admin.adabiyotlar.update', $literature) : route('admin.adabiyotlar.store') }}"
          enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @if ($literature->exists)
            @method('PUT')
        @endif

        @php
            $typeOptions = collect($types)->mapWithKeys(fn ($t) => [$t->value => $t->label()])->all();
            $statusOptions = collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
        @endphp

        <x-ui.card title="Asosiy ma'lumot">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Sarlavha" name="title" :value="$literature->title" required />
                <x-admin.form-field label="Slug (bo'sh qoldirsangiz avtomatik yaratiladi)" name="slug" :value="$literature->slug" />
                <x-admin.form-field label="Muallif" name="author" :value="$literature->author" required />
                <x-admin.form-field label="Turi" name="type" type="select" :value="$literature->type?->value ?? 'book'" :options="$typeOptions" />
            </div>
        </x-ui.card>

        <x-ui.card title="Bibliografiya">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-admin.form-field label="Nashr yili" name="publication_year" type="number" :value="$literature->publication_year" />
                <x-admin.form-field label="Nashriyot" name="publisher" :value="$literature->publisher" />
                <x-admin.form-field label="ISBN" name="isbn" :value="$literature->isbn" />
            </div>
            <div class="mt-4">
                <x-admin.form-field label="Til" name="language" :value="$literature->language" />
            </div>
        </x-ui.card>

        <x-ui.card title="To'liq tavsif">
            <x-admin.form-field label="Tavsif" name="description" type="textarea" :value="$literature->description" required />
            <div class="mt-4">
                <x-admin.form-field label="Tashqi havola (agar mavjud bo'lsa)" name="source_url" type="url" :value="$literature->source_url" />
            </div>
        </x-ui.card>

        <x-ui.card title="Fayl">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-brown-900">Muqova</label>
                    @if ($literature->coverUrl())
                        <img src="{{ $literature->coverUrl() }}" alt="" class="mt-2 h-32 w-24 rounded object-cover">
                    @endif
                    <input type="file" name="cover" accept="image/jpeg,image/png,image/webp" class="mt-2 block text-sm text-brown-700">
                    <p class="mt-1 text-xs text-brown-500">JPG/PNG/WEBP, maksimal 4MB.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-brown-900">PDF fayl</label>
                    @if ($literature->file_path)
                        <p class="mt-2 text-sm text-brown-700">
                            <a href="{{ $literature->fileUrl() }}" target="_blank" class="text-gold-600 hover:underline">Joriy faylni ko'rish</a>
                        </p>
                    @endif
                    <input type="file" name="file" accept="application/pdf" class="mt-2 block text-sm text-brown-700">
                    <p class="mt-1 text-xs text-brown-500">
                        Faqat PDF, maksimal 20MB. Mualliflik huquqi bilan himoyalangan materiallarni
                        yuklamang.
                    </p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Bog'lanishlar">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-brown-900">Qo'rboshilar</label>
                    <select name="qorboshi_ids[]" multiple size="6"
                            class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                        @foreach ($qorboshilar as $qorboshi)
                            <option value="{{ $qorboshi->id }}" @selected($literature->exists && $literature->qorboshilar->contains('id', $qorboshi->id))>
                                {{ $qorboshi->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-brown-900">Qo'zg'olonlar</label>
                    <select name="uzgolon_ids[]" multiple size="6"
                            class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                        @foreach ($uzgolonlar as $uzgolon)
                            <option value="{{ $uzgolon->id }}" @selected($literature->exists && $literature->uzgolonlar->contains('id', $uzgolon->id))>
                                {{ $uzgolon->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Kontent holati">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Holat" name="status" type="select" :value="$literature->status?->value ?? 'draft'" :options="$statusOptions" />
                <label class="flex items-center gap-2 pt-7 text-sm font-medium text-brown-900">
                    <input type="hidden" name="featured" value="0">
                    <input type="checkbox" name="featured" value="1" @checked(old('featured', $literature->featured))
                           class="rounded border-sand text-gold-600 focus:ring-gold-600">
                    Bosh sahifada ko'rsatish (featured)
                </label>
            </div>
        </x-ui.card>

        <div class="flex items-center gap-3">
            <x-ui.button type="submit">{{ $literature->exists ? 'Saqlash' : "Qo'shish" }}</x-ui.button>
            <x-ui.button href="{{ route('admin.adabiyotlar.index') }}" variant="secondary">Bekor qilish</x-ui.button>
        </div>
    </form>
@endsection
