@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs" />

    <form method="POST"
          action="{{ $sourceReference->exists ? route('admin.source-references.update', $sourceReference) : route('admin.source-references.store') }}"
          class="mt-6 space-y-6">
        @csrf
        @if ($sourceReference->exists)
            @method('PUT')
        @endif

        @php
            $typeOptions = collect($sourceTypes)->mapWithKeys(fn ($t) => [$t->value => $t->label()])->all();
        @endphp

        <x-ui.card title="Asosiy ma'lumot">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-admin.form-field label="Muallif" name="author" :value="$sourceReference->author" required />
                <x-admin.form-field label="Sarlavha" name="title" :value="$sourceReference->title" required />
                <x-admin.form-field label="Nashriyot" name="publisher" :value="$sourceReference->publisher" />
                <x-admin.form-field label="Yil" name="year" type="number" :value="$sourceReference->year" />
                <x-admin.form-field label="Turi" name="source_type" type="select" :value="$sourceReference->source_type?->value ?? 'book'" :options="$typeOptions" />
                <x-admin.form-field label="Bet (ixtiyoriy)" name="page" :value="$sourceReference->page" />
                <x-admin.form-field label="URL (ixtiyoriy)" name="url" :value="$sourceReference->url" />
                <x-admin.form-field label="Adabiyot (ixtiyoriy — agar bu manba loyihadagi Adabiyot yozuviga tegishli bo'lsa)"
                                     name="literature_id" type="select" :value="$sourceReference->literature_id"
                                     :options="['' => '— Bog\'lanmagan —'] + $literatures->pluck('title', 'id')->all()" />
            </div>
            <div class="mt-4">
                <x-admin.form-field label="Izoh (ixtiyoriy)" name="note" type="textarea" :value="$sourceReference->note" />
            </div>
            <p class="mt-2 text-xs text-brown-500">
                "Adabiyot" bog'lanishi ixtiyoriy — agar bu manba loyihadagi Adabiyotlar bo'limida mavjud kitob/maqolaga
                tegishli bo'lsa tanlang, aks holda bo'sh qoldiring. Bu manba HistoricalRegion/HistoricalMapLayer va
                boshqa yozuvlarning "Manbalar" ro'yxatida shundan keyin tanlash uchun ko'rinadi.
            </p>
        </x-ui.card>

        <div class="flex items-center gap-3">
            <x-ui.button type="submit">{{ $sourceReference->exists ? 'Saqlash' : "Qo'shish" }}</x-ui.button>
            <x-ui.button href="{{ route('admin.source-references.index') }}" variant="secondary">Bekor qilish</x-ui.button>
        </div>
    </form>
@endsection
