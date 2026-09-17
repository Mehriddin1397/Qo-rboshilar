@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs" />

    <form method="POST"
          action="{{ $qorboshi->exists ? route('admin.qorboshilar.update', $qorboshi) : route('admin.qorboshilar.store') }}"
          enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @if ($qorboshi->exists)
            @method('PUT')
        @endif

        <x-ui.card title="Asosiy ma'lumotlar">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Ism-familiya" name="full_name" :value="$qorboshi->full_name" required />
                <x-admin.form-field label="Slug (bo'sh qoldirsangiz avtomatik yaratiladi)" name="slug" :value="$qorboshi->slug" />
            </div>
            <div class="mt-4">
                <x-admin.form-field label="Qisqa tavsif" name="short_description" type="textarea" :value="$qorboshi->short_description" required />
            </div>
        </x-ui.card>

        <x-ui.card title="Biografiya">
            <x-admin.form-field label="To'liq biografiya" name="biography" type="textarea" :value="$qorboshi->biography" required />
            <div class="mt-4">
                <x-admin.form-field label="Tarixiy kontekst" name="historical_context" type="textarea" :value="$qorboshi->historical_context" />
            </div>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-admin.form-field label="Tug'ilgan yil" name="birth_year" type="number" :value="$qorboshi->birth_year" />
                <x-admin.form-field label="Tug'ilgan joy" name="birth_place" :value="$qorboshi->birth_place" />
                <x-admin.form-field label="Vafot yili" name="death_year" type="number" :value="$qorboshi->death_year" />
                <x-admin.form-field label="Vafot joyi" name="death_place" :value="$qorboshi->death_place" />
            </div>
            <p class="mt-2 text-xs text-brown-500">
                Faqat yil kiritiladi — tarixiy shaxslarning aniq tug'ilgan/vafot sanasi ko'pincha noma'lum bo'ladi.
            </p>
        </x-ui.card>

        <x-ui.card title="Faoliyat va hudud">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-admin.form-field label="Faoliyat boshlangan yil" name="active_from_year" type="number" :value="$qorboshi->active_from_year" />
                <x-admin.form-field label="Faoliyat tugagan yil" name="active_to_year" type="number" :value="$qorboshi->active_to_year" />
                <x-admin.form-field label="Hudud" name="region_id" type="select" :value="$qorboshi->region_id"
                                     :options="['' => '— Tanlanmagan —'] + $regions->pluck('name', 'id')->all()" />
            </div>
        </x-ui.card>

        <x-ui.card title="Kontent holati">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Holat" name="status" type="select" :value="$qorboshi->status?->value ?? 'draft'"
                                     :options="collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()" />

                <label class="flex items-center gap-2 pt-7 text-sm font-medium text-brown-900">
                    <input type="hidden" name="featured" value="0">
                    <input type="checkbox" name="featured" value="1" @checked(old('featured', $qorboshi->featured))
                           class="rounded border-sand text-gold-600 focus:ring-gold-600">
                    Bosh sahifada ko'rsatish (featured)
                </label>
            </div>
        </x-ui.card>

        <x-ui.card title="Bog'liq qo'zg'olonlar va adabiyotlar">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-brown-900">Qo'zg'olonlar</label>
                    <select name="uzgolon_ids[]" multiple size="6"
                            class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                        @foreach ($uzgolonlar as $uzgolon)
                            <option value="{{ $uzgolon->id }}" @selected($qorboshi->exists && $qorboshi->uzgolonlar->contains('id', $uzgolon->id))>
                                {{ $uzgolon->name }} ({{ $uzgolon->start_year }})
                            </option>
                        @endforeach
                    </select>
                    @if ($uzgolonlar->isEmpty())
                        <p class="mt-1 text-xs text-brown-500">Hozircha qo'zg'olon kiritilmagan.</p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-brown-900">Adabiyotlar</label>
                    <select name="literature_ids[]" multiple size="6"
                            class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                        @foreach ($literatures as $literature)
                            <option value="{{ $literature->id }}" @selected($qorboshi->exists && $qorboshi->literatures->contains('id', $literature->id))>
                                {{ $literature->title }}
                            </option>
                        @endforeach
                    </select>
                    @if ($literatures->isEmpty())
                        <p class="mt-1 text-xs text-brown-500">Hozircha adabiyot kiritilmagan.</p>
                    @endif
                </div>
            </div>

            @if ($qorboshi->exists && $qorboshi->videos->isNotEmpty())
                <div class="mt-4 border-t border-sand pt-4">
                    <p class="text-sm font-medium text-brown-900">Bog'liq videolar</p>
                    <ul class="mt-2 list-inside list-disc text-sm text-brown-700">
                        @foreach ($qorboshi->videos as $video)
                            <li>{{ $video->title }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card title="Rasmlar">
            <div>
                <label class="block text-sm font-medium text-brown-900">Muqova (portret)</label>
                <img src="{{ $qorboshi->portraitUrl() }}" alt="" class="mt-2 h-24 w-24 rounded-full object-cover">
                <input type="file" name="portrait" accept="image/jpeg,image/png,image/webp" class="mt-2 block text-sm text-brown-700">
                <p class="mt-1 text-xs text-brown-500">JPG, PNG yoki WEBP, maksimal 20MB.</p>
            </div>

            @if ($qorboshi->exists && $qorboshi->images->isNotEmpty())
                <div class="mt-5 border-t border-sand pt-4">
                    <p class="text-sm font-medium text-brown-900">Mavjud galereya</p>
                    <div class="mt-2 grid grid-cols-3 gap-3 sm:grid-cols-6">
                        @foreach ($qorboshi->images as $image)
                            <div class="relative">
                                <img src="{{ $image->url() }}" alt="{{ $image->alt_text }}"
                                     class="h-20 w-full rounded-md object-cover">
                                <form method="POST" action="{{ route('admin.qorboshilar.images.destroy', [$qorboshi, $image]) }}"
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
                <p class="mt-1 text-xs text-brown-500">Bir nechta rasm tanlash mumkin, har biri maksimal 20MB.</p>
            </div>
        </x-ui.card>

        <x-ui.card title="Manbalar (provenance)">
            <label class="block text-sm font-medium text-brown-900">Ushbu ma'lumot asoslangan manbalar</label>
            <select name="source_reference_ids[]" multiple size="5"
                    class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                @foreach ($sourceReferences as $source)
                    <option value="{{ $source->id }}" @selected($qorboshi->exists && $qorboshi->sourceReferences->contains('id', $source->id))>
                        {{ $source->author }} — {{ $source->title }}@if($source->page) (b. {{ $source->page }})@endif
                    </option>
                @endforeach
            </select>
            @if ($sourceReferences->isEmpty())
                <p class="mt-1 text-xs text-brown-500">
                    Hozircha manba kiritilmagan. Manbalar keyingi bosqichda alohida boshqariladigan bo'ladi.
                </p>
            @endif
        </x-ui.card>

        <div class="flex items-center gap-3">
            <x-ui.button type="submit">{{ $qorboshi->exists ? 'Saqlash' : "Qo'shish" }}</x-ui.button>
            <x-ui.button href="{{ route('admin.qorboshilar.index') }}" variant="secondary">Bekor qilish</x-ui.button>
        </div>
    </form>
@endsection
