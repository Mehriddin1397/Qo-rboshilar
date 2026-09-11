@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs" />

    <form method="POST"
          action="{{ $event->exists ? route('admin.timeline-events.update', $event) : route('admin.timeline-events.store') }}"
          enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @if ($event->exists)
            @method('PUT')
        @endif

        @php
            $statusOptions = collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
            $accuracyOptions = collect($accuracyStatuses)->mapWithKeys(fn ($a) => [$a->value => $a->label()])->all();
        @endphp

        <x-ui.card title="Asosiy ma'lumot">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Sarlavha" name="title" :value="$event->title" required />
                <x-admin.form-field label="Slug (bo'sh qoldirsangiz avtomatik yaratiladi)" name="slug" :value="$event->slug" />
            </div>
            <div class="mt-4">
                <x-admin.form-field label="Tavsif" name="description" type="textarea" :value="$event->description" required />
            </div>
        </x-ui.card>

        <x-ui.card title="Sana">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-admin.form-field label="Boshlanish yili" name="start_year" type="number" :value="$event->start_year" required />
                <x-admin.form-field label="Tugash yili (ixtiyoriy)" name="end_year" type="number" :value="$event->end_year" />
                <x-admin.form-field label="Aniq sana (faqat haqiqatan ma'lum bo'lsa)" name="event_date" type="date" :value="$event->event_date?->format('Y-m-d')" />
            </div>
            <p class="mt-2 text-xs text-brown-500">
                Diqqat: agar faqat yil ma'lum bo'lsa, "Aniq sana"ni bo'sh qoldiring — soxta kun/oy kiritmang.
            </p>
        </x-ui.card>

        <x-ui.card title="Bog'lanishlar">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Davr (Period)" name="period_id" type="select" :value="$event->period_id"
                                     :options="['' => '— Tanlanmagan —'] + $periods->pluck('name', 'id')->all()" />
                <x-admin.form-field label="Qo'rboshi" name="qorboshi_id" type="select" :value="$event->qorboshi_id"
                                     :options="['' => '— Tanlanmagan —'] + $qorboshilar->pluck('full_name', 'id')->all()" />
                <x-admin.form-field label="Qo'zg'olon" name="uzgolon_id" type="select" :value="$event->uzgolon_id"
                                     :options="['' => '— Tanlanmagan —'] + $uzgolonlar->pluck('name', 'id')->all()" />
                <x-admin.form-field label="Zamonaviy hudud" name="region_id" type="select" :value="$event->region_id"
                                     :options="['' => '— Tanlanmagan —'] + $regions->pluck('name', 'id')->all()" />
                <x-admin.form-field label="Tarixiy hudud" name="historical_region_id" type="select" :value="$event->historical_region_id"
                                     :options="['' => '— Tanlanmagan —'] + $historicalRegions->pluck('name', 'id')->all()" />
            </div>
        </x-ui.card>

        <x-ui.card title="Xarita (koordinata)">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Latitude (-90..90)" name="latitude" type="number" step="any" :value="$event->latitude" />
                <x-admin.form-field label="Longitude (-180..180)" name="longitude" type="number" step="any" :value="$event->longitude" />
            </div>
            <p class="mt-2 text-xs text-brown-500">
                Agar koordinata mavjud bo'lsa, voqea xaritada nuqta sifatida ko'rsatiladi va timeline'dan
                xaritaga fokus qilish imkoniyati ochiladi (Faza 14 §12).
            </p>
        </x-ui.card>

        <x-ui.card title="Manbalar (Source / Provenance)">
            <label class="block text-sm font-medium text-brown-900">Manba havolalari (SourceReference)</label>
            <select name="source_reference_ids[]" multiple size="4"
                    class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                @foreach ($sourceReferences as $source)
                    <option value="{{ $source->id }}" @selected($event->exists && $event->sourceReferences->contains('id', $source->id))>
                        {{ $source->author }} — {{ $source->title }} ({{ $source->year ?? '?' }})
                    </option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-brown-500">Mavjud manba arxitekturasi (sourceables) — parallel tizim yo'q.</p>
        </x-ui.card>

        <x-ui.card title="Rasm">
            @if ($event->imageUrl())
                <img src="{{ $event->imageUrl() }}" alt="" class="h-20 w-32 rounded object-cover">
            @endif
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="mt-2 block text-sm text-brown-700">
        </x-ui.card>

        <x-ui.card title="Aniqlik holati (Historical Accuracy)">
            <x-admin.form-field label="Aniqlik" name="accuracy_status" type="select" :value="$event->accuracy_status?->value ?? 'uncertain'" :options="$accuracyOptions" />
            <p class="mt-2 text-xs text-brown-500">
                "Holat" (draft/published)dan mustaqil — geometriya/sananing tarixiy ishonchlilik darajasi.
            </p>
        </x-ui.card>

        <x-ui.card title="Nashr va tartib">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-admin.form-field label="Holat" name="status" type="select" :value="$event->status?->value ?? 'draft'" :options="$statusOptions" />
                <x-admin.form-field label="Tartib raqami (sort order)" name="sort_order" type="number" :value="$event->sort_order ?? 0" />
                <label class="mt-6 flex items-center gap-2 text-sm font-medium text-brown-900">
                    <input type="hidden" name="featured" value="0">
                    <input type="checkbox" name="featured" value="1" @checked(old('featured', $event->featured))
                           class="rounded border-sand text-gold-600 focus:ring-gold-600">
                    Featured
                </label>
            </div>
        </x-ui.card>

        <div class="flex items-center gap-3">
            <x-ui.button type="submit">{{ $event->exists ? 'Saqlash' : "Qo'shish" }}</x-ui.button>
            <x-ui.button href="{{ route('admin.timeline-events.index') }}" variant="secondary">Bekor qilish</x-ui.button>
        </div>
    </form>
@endsection
