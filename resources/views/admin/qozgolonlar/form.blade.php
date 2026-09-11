@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs" />

    <form method="POST"
          action="{{ $uzgolon->exists ? route('admin.qozgolonlar.update', $uzgolon) : route('admin.qozgolonlar.store') }}"
          enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @if ($uzgolon->exists)
            @method('PUT')
        @endif

        <x-ui.card title="Asosiy ma'lumot">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Nomi" name="name" :value="$uzgolon->name" required />
                <x-admin.form-field label="Slug (bo'sh qoldirsangiz avtomatik yaratiladi)" name="slug" :value="$uzgolon->slug" />
            </div>
            <div class="mt-4">
                <x-admin.form-field label="Qisqa tavsif" name="short_description" type="textarea" :value="$uzgolon->short_description" required />
            </div>
        </x-ui.card>

        <x-ui.card title="Tarixiy bayon">
            <div class="space-y-4">
                <x-admin.form-field label="Tarixiy kontekst" name="historical_context" type="textarea" :value="$uzgolon->historical_context" />
                <x-admin.form-field label="Sabablari" name="causes" type="textarea" :value="$uzgolon->causes" />
                <x-admin.form-field label="Asosiy voqealar" name="main_events" type="textarea" :value="$uzgolon->main_events" />
                <x-admin.form-field label="Natijalari" name="results" type="textarea" :value="$uzgolon->results" />
                <x-admin.form-field label="Tarixiy ahamiyati" name="historical_significance" type="textarea" :value="$uzgolon->historical_significance" />
            </div>
        </x-ui.card>

        <x-ui.card title="Sana va davr">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-admin.form-field label="Boshlanish yili" name="start_year" type="number" :value="$uzgolon->start_year" required />
                <x-admin.form-field label="Yakunlanish yili" name="end_year" type="number" :value="$uzgolon->end_year" />
                <x-admin.form-field label="Tarixiy davr" name="period_id" type="select" :value="$uzgolon->period_id"
                                     :options="['' => '— Tanlanmagan —'] + $periods->pluck('name', 'id')->all()" />
            </div>
            <p class="mt-2 text-xs text-brown-500">Faqat yil kiritiladi — aniq kun/oy ko'pincha ishonchli ma'lum bo'lmaydi.</p>
        </x-ui.card>

        <x-ui.card title="Hudud">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-admin.form-field label="Hudud (zamonaviy)" name="region_id" type="select" :value="$uzgolon->region_id"
                                     :options="['' => '— Tanlanmagan —'] + $regions->pluck('name', 'id')->all()" />
                <x-admin.form-field label="Tarixiy joy nomi" name="historical_location" :value="$uzgolon->historical_location" />
                <x-admin.form-field label="Zamonaviy joy nomi" name="modern_location" :value="$uzgolon->modern_location" />
            </div>
        </x-ui.card>

        <x-ui.card title="Xarita">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Kenglik (latitude)" name="latitude" type="number" :value="optional($uzgolon->primaryMarker)->latitude" step="any" />
                <x-admin.form-field label="Uzunlik (longitude)" name="longitude" type="number" :value="optional($uzgolon->primaryMarker)->longitude" step="any" />
            </div>
            <p class="mt-2 text-xs text-brown-500">
                Ixtiyoriy — kiritilsa, bu qo'zg'olon interaktiv xaritada marker sifatida ko'rinadi.
                Agar tarixiy joyning aniq koordinatasi ishonchli bo'lmasa, bo'sh qoldiring.
            </p>
        </x-ui.card>

        <x-ui.card title="Kontent holati">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Holat" name="status" type="select" :value="$uzgolon->status?->value ?? 'draft'"
                                     :options="collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()" />

                <label class="flex items-center gap-2 pt-7 text-sm font-medium text-brown-900">
                    <input type="hidden" name="featured" value="0">
                    <input type="checkbox" name="featured" value="1" @checked(old('featured', $uzgolon->featured))
                           class="rounded border-sand text-gold-600 focus:ring-gold-600">
                    Bosh sahifada ko'rsatish (featured)
                </label>
            </div>
        </x-ui.card>

        <x-ui.card title="Bog'lanishlar">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-brown-900">Qo'rboshilar</label>
                    <select name="qorboshi_ids[]" multiple size="6"
                            class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                        @foreach ($qorboshilar as $qorboshi)
                            <option value="{{ $qorboshi->id }}" @selected($uzgolon->exists && $uzgolon->qorboshilar->contains('id', $qorboshi->id))>
                                {{ $qorboshi->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @if ($qorboshilar->isEmpty())
                        <p class="mt-1 text-xs text-brown-500">Hozircha qo'rboshi kiritilmagan.</p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-brown-900">Adabiyotlar</label>
                    <select name="literature_ids[]" multiple size="6"
                            class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                        @foreach ($literatures as $literature)
                            <option value="{{ $literature->id }}" @selected($uzgolon->exists && $uzgolon->literatures->contains('id', $literature->id))>
                                {{ $literature->title }}
                            </option>
                        @endforeach
                    </select>
                    @if ($literatures->isEmpty())
                        <p class="mt-1 text-xs text-brown-500">Hozircha adabiyot kiritilmagan.</p>
                    @endif
                </div>
            </div>

            @if ($uzgolon->exists && $uzgolon->videos->isNotEmpty())
                <div class="mt-4 border-t border-sand pt-4">
                    <p class="text-sm font-medium text-brown-900">Bog'liq videolar</p>
                    <ul class="mt-2 list-inside list-disc text-sm text-brown-700">
                        @foreach ($uzgolon->videos as $video)
                            <li>{{ $video->title }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card title="Rasmlar">
            <div>
                <label class="block text-sm font-medium text-brown-900">Muqova rasmi</label>
                @if ($uzgolon->coverImageUrl())
                    <img src="{{ $uzgolon->coverImageUrl() }}" alt="" class="mt-2 h-24 w-36 rounded object-cover">
                @endif
                <input type="file" name="cover" accept="image/jpeg,image/png,image/webp" class="mt-2 block text-sm text-brown-700">
                <p class="mt-1 text-xs text-brown-500">JPG, PNG yoki WEBP, maksimal 4MB.</p>
            </div>

            @if ($uzgolon->exists && $uzgolon->images->isNotEmpty())
                <div class="mt-5 border-t border-sand pt-4">
                    <p class="text-sm font-medium text-brown-900">Mavjud galereya</p>
                    <div class="mt-2 grid grid-cols-3 gap-3 sm:grid-cols-6">
                        @foreach ($uzgolon->images as $image)
                            <div class="relative">
                                <img src="{{ $image->url() }}" alt="{{ $image->alt_text }}" class="h-20 w-full rounded-md object-cover">
                                <form method="POST" action="{{ route('admin.qozgolonlar.images.destroy', [$uzgolon, $image]) }}"
                                      onsubmit="return confirm('Rasmni o\'chirishni tasdiqlaysizmi?');" class="absolute right-1 top-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-full bg-danger/90 px-1.5 py-0.5 text-xs text-white">&times;</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-5 border-t border-sand pt-4">
                <label class="block text-sm font-medium text-brown-900">Yangi galereya rasmlarini qo'shish</label>
                <input type="file" name="gallery[]" multiple accept="image/jpeg,image/png,image/webp" class="mt-2 block text-sm text-brown-700">
                <p class="mt-1 text-xs text-brown-500">Bir nechta rasm tanlash mumkin, har biri maksimal 6MB.</p>
            </div>
        </x-ui.card>

        <x-ui.card title="Manbalar (provenance)">
            <label class="block text-sm font-medium text-brown-900">Ushbu ma'lumot asoslangan manbalar</label>
            <select name="source_reference_ids[]" multiple size="5"
                    class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                @foreach ($sourceReferences as $source)
                    <option value="{{ $source->id }}" @selected($uzgolon->exists && $uzgolon->sourceReferences->contains('id', $source->id))>
                        {{ $source->author }} — {{ $source->title }}@if($source->page) (b. {{ $source->page }})@endif
                    </option>
                @endforeach
            </select>
            @if ($sourceReferences->isEmpty())
                <p class="mt-1 text-xs text-brown-500">Hozircha manba kiritilmagan.</p>
            @endif
        </x-ui.card>

        <div class="flex items-center gap-3">
            <x-ui.button type="submit">{{ $uzgolon->exists ? 'Saqlash' : "Qo'shish" }}</x-ui.button>
            <x-ui.button href="{{ route('admin.qozgolonlar.index') }}" variant="secondary">Bekor qilish</x-ui.button>
        </div>
    </form>
@endsection
