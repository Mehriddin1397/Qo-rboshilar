@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs" />

    <form method="POST"
          action="{{ $historicalRegion->exists ? route('admin.historical-regions.update', $historicalRegion) : route('admin.historical-regions.store') }}"
          class="mt-6 space-y-6">
        @csrf
        @if ($historicalRegion->exists)
            @method('PUT')
        @endif

        @php
            $statusOptions = collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
            $regionTypeOptions = collect($regionTypes)->mapWithKeys(fn ($t) => [$t->value => $t->label()])->all();
            $accuracyOptions = collect($accuracyStatuses)->mapWithKeys(fn ($a) => [$a->value => $a->label()])->all();
        @endphp

        <x-ui.card title="Asosiy ma'lumot">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Nomi" name="name" :value="$historicalRegion->name" required />
                <x-admin.form-field label="Slug (bo'sh qoldirsangiz avtomatik yaratiladi)" name="slug" :value="$historicalRegion->slug" />
                <x-admin.form-field label="Tarixiy nomi (ixtiyoriy — o'sha davrda ishlatilgan nom)" name="historical_name" :value="$historicalRegion->historical_name" />
                <x-admin.form-field label="Zamonaviy nomi (ixtiyoriy)" name="modern_name" :value="$historicalRegion->modern_name" />
                <x-admin.form-field label="Hudud turi" name="region_type" type="select" :value="$historicalRegion->region_type?->value ?? 'other'" :options="$regionTypeOptions" />
            </div>
            <div class="mt-4">
                <x-admin.form-field label="Tavsif" name="description" type="textarea" :value="$historicalRegion->description" />
            </div>
        </x-ui.card>

        <x-ui.card title="Davr va hudud">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Davr (Period)" name="period_id" type="select" :value="$historicalRegion->period_id"
                                     :options="['' => '— Tanlanmagan —'] + $periods->pluck('name', 'id')->all()" />
                <x-admin.form-field label="Zamonaviy hudud (Region)" name="region_id" type="select" :value="$historicalRegion->region_id"
                                     :options="['' => '— Tanlanmagan —'] + $regions->pluck('name', 'id')->all()" />
            </div>
        </x-ui.card>

        <x-ui.card title="Manbalar (Source / Provenance)">
            <label class="block text-sm font-medium text-brown-900">Manba havolalari (SourceReference)</label>
            <select name="source_reference_ids[]" multiple size="4"
                    class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                @foreach ($sourceReferences as $source)
                    <option value="{{ $source->id }}" @selected($historicalRegion->exists && $historicalRegion->sourceReferences->contains('id', $source->id))>
                        {{ $source->author }} — {{ $source->title }} ({{ $source->year ?? '?' }})
                    </option>
                @endforeach
            </select>
            @error('source_reference_ids')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs text-brown-500">
                Real geometriyaga ega hududni nashr etish uchun kamida bitta manba shart (§9, §40).
            </p>
        </x-ui.card>

        <x-ui.card title="GeoJSON">
            <div x-data="{
                    value: @js(old('geojson', $historicalRegion->geojson ? json_encode($historicalRegion->geojson, JSON_PRETTY_PRINT) : '')),
                    valid: true,
                    check() {
                        if (this.value.trim() === '') { this.valid = true; return; }
                        try { JSON.parse(this.value); this.valid = true; } catch (e) { this.valid = false; }
                    },
                 }" x-init="check()">
                <label for="geojson" class="block text-sm font-medium text-brown-900">
                    GeoJSON (Feature / FeatureCollection / Polygon / MultiPolygon)
                </label>
                <textarea id="geojson" name="geojson" rows="8" x-model="value" @input="check()"
                          class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 font-mono text-xs focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600"
                          placeholder='{"type": "FeatureCollection", "features": []}'>{{ old('geojson', $historicalRegion->geojson ? json_encode($historicalRegion->geojson, JSON_PRETTY_PRINT) : '') }}</textarea>
                <p class="mt-1 text-xs" :class="valid ? 'text-success' : 'text-danger'">
                    <span x-show="valid">✓ Texnik jihatdan valid JSON (bu tarixiy to'g'rilikni tasdiqlamaydi)</span>
                    <span x-show="!valid">✗ Valid JSON emas</span>
                </p>
                @error('geojson')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-brown-500">
                    Diqqat: bu maydon faqat texnik JSON to'g'riligini tekshiradi. Haqiqiy tarixiy chegara/koordinata
                    faqat ishonchli manba asosida kiritilsin — hozircha bo'sh yoki DEMO/TEST geometriya qoldiring.
                </p>
            </div>
        </x-ui.card>

        <x-ui.card title="Aniqlik holati (Historical Accuracy)">
            <x-admin.form-field label="Aniqlik" name="accuracy_status" type="select" :value="$historicalRegion->accuracy_status?->value ?? 'uncertain'" :options="$accuracyOptions" />
            <p class="mt-2 text-xs text-brown-500">
                MUHIM: bu "Holat" (draft/published)dan farqli tushuncha — geometriya publicga chiqarilgan bo'lsa ham,
                uning tarixiy ishonchlilik darajasini ko'rsatadi. Yangi yozuv standart bo'yicha "Noaniq" bo'ladi —
                admin buni ongli ravishda "Tasdiqlangan"ga o'zgartirishi kerak (§41 — false precision yaratilmasin).
            </p>
        </x-ui.card>

        <x-ui.card title="Nashr va tartib">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-admin.form-field label="Holat" name="status" type="select" :value="$historicalRegion->status?->value ?? 'draft'" :options="$statusOptions" />
                <x-admin.form-field label="Tartib raqami (sort order)" name="sort_order" type="number" :value="$historicalRegion->sort_order ?? 0" />
                <label class="mt-6 flex items-center gap-2 text-sm font-medium text-brown-900">
                    <input type="hidden" name="featured" value="0">
                    <input type="checkbox" name="featured" value="1" @checked(old('featured', $historicalRegion->featured))
                           class="rounded border-sand text-gold-600 focus:ring-gold-600">
                    Featured
                </label>
            </div>
        </x-ui.card>

        <div class="flex items-center gap-3">
            <x-ui.button type="submit">{{ $historicalRegion->exists ? 'Saqlash' : "Qo'shish" }}</x-ui.button>
            <x-ui.button href="{{ route('admin.historical-regions.index') }}" variant="secondary">Bekor qilish</x-ui.button>
        </div>
    </form>
@endsection
