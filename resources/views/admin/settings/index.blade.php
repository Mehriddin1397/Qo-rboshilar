@extends('layouts.admin')

@section('title', 'Sozlamalar')

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs" />

    <div class="mt-6">
        <x-ui.card>
            <p class="text-sm text-brown-700">
                Sayt sozlamalari (masalan, umumiy meta ma'lumotlar, ijtimoiy tarmoq havolalari)
                keyingi bosqichda shu yerga qo'shiladi.
            </p>
        </x-ui.card>
    </div>
@endsection
