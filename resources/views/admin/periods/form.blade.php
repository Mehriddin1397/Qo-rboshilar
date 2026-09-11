@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs" />

    <form method="POST"
          action="{{ $period->exists ? route('admin.periods.update', $period) : route('admin.periods.store') }}"
          class="mt-6 space-y-6">
        @csrf
        @if ($period->exists)
            @method('PUT')
        @endif

        <x-ui.card title="Asosiy ma'lumot">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Nomi" name="name" :value="$period->name" required />
                <x-admin.form-field label="Slug (bo'sh qoldirsangiz avtomatik yaratiladi)" name="slug" :value="$period->slug" />
                <x-admin.form-field label="Boshlanish yili" name="start_year" type="number" :value="$period->start_year" />
                <x-admin.form-field label="Tugash yili" name="end_year" type="number" :value="$period->end_year" />
            </div>
            <div class="mt-4">
                <x-admin.form-field label="Tavsif" name="description" type="textarea" :value="$period->description" />
            </div>
        </x-ui.card>

        <div class="flex items-center gap-3">
            <x-ui.button type="submit">{{ $period->exists ? 'Saqlash' : "Qo'shish" }}</x-ui.button>
            <x-ui.button href="{{ route('admin.periods.index') }}" variant="secondary">Bekor qilish</x-ui.button>
        </div>
    </form>
@endsection
