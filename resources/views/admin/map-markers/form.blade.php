@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs" />

    <form method="POST"
          action="{{ $mapMarker->exists ? route('admin.map-markers.update', $mapMarker) : route('admin.map-markers.store') }}"
          class="mt-6 space-y-6">
        @csrf
        @if ($mapMarker->exists)
            @method('PUT')
        @endif

        @php
            $statusOptions = collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
            $typeOptions = collect($types)->mapWithKeys(fn ($t) => [$t->value => $t->label()])->all();
        @endphp

        <x-ui.card title="Asosiy ma'lumot">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Nomi" name="title" :value="$mapMarker->title" required />
                <x-admin.form-field label="Qo'zg'olon (ixtiyoriy)" name="uzgolon_id" type="select" :value="$mapMarker->uzgolon_id"
                                     :options="['' => '— Bog\'lanmagan —'] + $uzgolonlar->pluck('name', 'id')->all()" />
                <x-admin.form-field label="Turi" name="type" type="select" :value="$mapMarker->type?->value ?? 'other'" :options="$typeOptions" />
            </div>
            <div class="mt-4">
                <x-admin.form-field label="Tavsif" name="description" type="textarea" :value="$mapMarker->description" />
            </div>
        </x-ui.card>

        <x-ui.card title="Joylashuv">
            <x-admin.map-point-picker :lat-value="$mapMarker->latitude" :lng-value="$mapMarker->longitude" />
        </x-ui.card>

        <x-ui.card title="Nashr va tartib">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-admin.form-field label="Holat" name="status" type="select" :value="$mapMarker->status?->value ?? 'published'" :options="$statusOptions" />
                <x-admin.form-field label="Tartib raqami (sort order)" name="sort_order" type="number" :value="$mapMarker->sort_order ?? 0" />
                <label class="mt-6 flex items-center gap-2 text-sm font-medium text-brown-900">
                    <input type="hidden" name="is_primary" value="0">
                    <input type="checkbox" name="is_primary" value="1" @checked(old('is_primary', $mapMarker->is_primary))
                           class="rounded border-sand text-gold-600 focus:ring-gold-600">
                    Primary marker
                </label>
            </div>
            <p class="mt-2 text-xs text-brown-500">
                Bitta qo'zg'olon uchun bir vaqtning o'zida faqat bitta primary marker bo'lishi mumkin — bu markerni
                primary qilib belgilasangiz, o'sha qo'zg'olonning eski primary markeri avtomatik ravishda oddiy
                markerga aylanadi. Qo'zg'olon tanlanmagan bo'lsa, bu belgi e'tiborga olinmaydi.
            </p>
        </x-ui.card>

        <div class="flex items-center gap-3">
            <x-ui.button type="submit">{{ $mapMarker->exists ? 'Saqlash' : "Qo'shish" }}</x-ui.button>
            <x-ui.button href="{{ route('admin.map-markers.index') }}" variant="secondary">Bekor qilish</x-ui.button>
        </div>
    </form>
@endsection
