@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs" />

    <form method="POST"
          action="{{ $region->exists ? route('admin.regions.update', $region) : route('admin.regions.store') }}"
          class="mt-6 space-y-6">
        @csrf
        @if ($region->exists)
            @method('PUT')
        @endif

        @php
            $statusOptions = collect($statuses)->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();
        @endphp

        <x-ui.card title="Asosiy ma'lumot">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Nomi" name="name" :value="$region->name" required />
                <x-admin.form-field label="Slug (bo'sh qoldirsangiz avtomatik yaratiladi)" name="slug" :value="$region->slug" />
            </div>
            <div class="mt-4">
                <x-admin.form-field label="Tavsif" name="description" type="textarea" :value="$region->description" />
            </div>
        </x-ui.card>

        <x-ui.card title="Nashr va tartib">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Holat" name="status" type="select" :value="$region->status?->value ?? 'published'" :options="$statusOptions" />
                <x-admin.form-field label="Tartib raqami (sort order)" name="sort_order" type="number" :value="$region->sort_order ?? 0" />
            </div>
        </x-ui.card>

        <div class="flex items-center gap-3">
            <x-ui.button type="submit">{{ $region->exists ? 'Saqlash' : "Qo'shish" }}</x-ui.button>
            <x-ui.button href="{{ route('admin.regions.index') }}" variant="secondary">Bekor qilish</x-ui.button>
        </div>
    </form>
@endsection
