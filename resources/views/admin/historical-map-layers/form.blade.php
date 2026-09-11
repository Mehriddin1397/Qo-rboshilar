@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs" />

    <form method="POST"
          action="{{ $layer->exists ? route('admin.historical-map-layers.update', $layer) : route('admin.historical-map-layers.store') }}"
          enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @if ($layer->exists)
            @method('PUT')
        @endif

        @php
            $statusOptions = collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
            $accuracyOptions = collect($accuracyStatuses)->mapWithKeys(fn ($a) => [$a->value => $a->label()])->all();
            $bounds = old('bounds', $layer->bounds ?? []);
        @endphp

        <x-ui.card title="Asosiy ma'lumot">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Nomi" name="title" :value="$layer->title" required />
                <x-admin.form-field label="Slug (bo'sh qoldirsangiz avtomatik yaratiladi)" name="slug" :value="$layer->slug" />
            </div>
            <div class="mt-4">
                <x-admin.form-field label="Tavsif" name="description" type="textarea" :value="$layer->description" />
            </div>
        </x-ui.card>

        <x-ui.card title="Davr va tarixiy hudud">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Davr (Period)" name="period_id" type="select" :value="$layer->period_id"
                                     :options="['' => '— Tanlanmagan —'] + $periods->pluck('name', 'id')->all()" />
                <x-admin.form-field label="Tarixiy hudud (ixtiyoriy — umumiy qatlam bo'lsa bo'sh qoldiring)" name="historical_region_id" type="select" :value="$layer->historical_region_id"
                                     :options="['' => '— Tanlanmagan —'] + $historicalRegions->pluck('name', 'id')->all()" />
            </div>
        </x-ui.card>

        <x-ui.card title="Manbalar">
            <label class="block text-sm font-medium text-brown-900">Manba havolalari (SourceReference)</label>
            <select name="source_reference_ids[]" multiple size="4"
                    class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                @foreach ($sourceReferences as $source)
                    <option value="{{ $source->id }}" @selected($layer->exists && $layer->sourceReferences->contains('id', $source->id))>
                        {{ $source->author }} — {{ $source->title }}
                    </option>
                @endforeach
            </select>
            @error('source_reference_ids')
                <p class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs text-brown-500">
                Mavjud manba arxitekturasi (§20) — alohida source_title/source_url ustuni yo'q. Real geometriyaga
                ega qatlamni nashr etish uchun kamida bitta manba shart (§9, §40).
            </p>
        </x-ui.card>

        <x-ui.card title="Aniqlik holati (Historical Accuracy)">
            <x-admin.form-field label="Aniqlik" name="accuracy_status" type="select" :value="$layer->accuracy_status?->value ?? 'uncertain'" :options="$accuracyOptions" />
            <p class="mt-2 text-xs text-brown-500">
                "Holat" (draft/published)dan farqli — geometriyaning tarixiy ishonchlilik darajasi. Yangi qatlam
                standart bo'yicha "Noaniq".
            </p>
        </x-ui.card>

        <x-ui.card title="GeoJSON (vektor qatlam)">
            <div x-data="{
                    value: @js(old('geojson', $layer->geojson ? json_encode($layer->geojson, JSON_PRETTY_PRINT) : '')),
                    valid: true,
                    check() {
                        if (this.value.trim() === '') { this.valid = true; return; }
                        try { JSON.parse(this.value); this.valid = true; } catch (e) { this.valid = false; }
                    },
                 }" x-init="check()">
                <label for="geojson" class="block text-sm font-medium text-brown-900">GeoJSON</label>
                <textarea id="geojson" name="geojson" rows="8" x-model="value" @input="check()"
                          class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 font-mono text-xs focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600"
                          placeholder='{"type": "FeatureCollection", "features": []}'>{{ old('geojson', $layer->geojson ? json_encode($layer->geojson, JSON_PRETTY_PRINT) : '') }}</textarea>
                <p class="mt-1 text-xs" :class="valid ? 'text-success' : 'text-danger'">
                    <span x-show="valid">✓ Texnik jihatdan valid JSON</span>
                    <span x-show="!valid">✗ Valid JSON emas</span>
                </p>
                @error('geojson')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>
        </x-ui.card>

        <x-ui.card title="Raster overlay (ixtiyoriy)">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-brown-900">Rasm (georeferenced overlay)</label>
                    @if ($layer->imageUrl())
                        <img src="{{ $layer->imageUrl() }}" alt="" class="mt-2 h-20 w-32 rounded object-cover">
                    @endif
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="mt-2 block text-sm text-brown-700">
                </div>
                <x-admin.form-field label="Shaffoflik (opacity, 0-1)" name="opacity" type="number" step="0.05" min="0" max="1" :value="$layer->opacity ?? 0.7" />
            </div>

            <p class="mt-4 text-sm font-medium text-brown-900">Georeference chegaralari (bounds)</p>
            <div class="mt-2 grid gap-4 sm:grid-cols-4">
                <x-admin.form-field label="North (kenglik)" name="bounds[north]" type="number" step="any" :value="$bounds['north'] ?? null" />
                <x-admin.form-field label="South (kenglik)" name="bounds[south]" type="number" step="any" :value="$bounds['south'] ?? null" />
                <x-admin.form-field label="East (uzunlik)" name="bounds[east]" type="number" step="any" :value="$bounds['east'] ?? null" />
                <x-admin.form-field label="West (uzunlik)" name="bounds[west]" type="number" step="any" :value="$bounds['west'] ?? null" />
            </div>

            <label class="mt-4 flex items-center gap-2 text-sm font-medium text-brown-900">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $layer->is_active))
                       class="rounded border-sand text-gold-600 focus:ring-gold-600">
                Xaritada faol (is_active) — shu davr uchun ko'rsatiladigan asosiy raster
            </label>
        </x-ui.card>

        <x-ui.card title="Nashr va tartib">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Holat" name="status" type="select" :value="$layer->status?->value ?? 'draft'" :options="$statusOptions" />
                <x-admin.form-field label="Tartib raqami (sort order)" name="sort_order" type="number" :value="$layer->sort_order ?? 0" />
            </div>
        </x-ui.card>

        <div class="flex items-center gap-3">
            <x-ui.button type="submit">{{ $layer->exists ? 'Saqlash' : "Qo'shish" }}</x-ui.button>
            <x-ui.button href="{{ route('admin.historical-map-layers.index') }}" variant="secondary">Bekor qilish</x-ui.button>
        </div>
    </form>
@endsection
