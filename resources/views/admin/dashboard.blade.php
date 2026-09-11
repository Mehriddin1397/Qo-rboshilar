@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <x-admin.page-header title="Dashboard" />

    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        @php
            $tiles = [
                ["Qo'rboshilar", $stats['qorboshilar'], 'admin.qorboshilar.index'],
                ["Qo'zg'olonlar", $stats['uzgolonlar'], 'admin.qozgolonlar.index'],
                ['Adabiyotlar', $stats['adabiyotlar'], 'admin.adabiyotlar.index'],
                ['Videolar', $stats['videolar'], 'admin.videolar.index'],
                ['Bloglar', $stats['bloglar'], 'admin.bloglar.index'],
                ['Foydalanuvchilar', $stats['users'], 'admin.users.index'],
            ];
        @endphp

        @foreach ($tiles as [$label, $value, $route])
            <a href="{{ route($route) }}">
                <x-ui.card class="transition hover:border-gold-600">
                    <p class="text-xs font-medium uppercase tracking-wide text-brown-700">{{ $label }}</p>
                    <p class="mt-2 font-serif text-3xl font-semibold text-brown-900">{{ $value }}</p>
                </x-ui.card>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <x-ui.card title="Moderatsiya kutilmoqda">
            <div class="flex items-center justify-between py-2">
                <span class="text-sm text-brown-700">Pending bloglar</span>
                <a href="{{ route('admin.bloglar.index') }}">
                    <x-ui.badge :color="$stats['pending_bloglar'] > 0 ? 'gold' : 'neutral'">
                        {{ $stats['pending_bloglar'] }}
                    </x-ui.badge>
                </a>
            </div>
            <div class="flex items-center justify-between border-t border-sand py-2">
                <span class="text-sm text-brown-700">Pending kommentlar</span>
                <a href="{{ route('admin.comments.index') }}">
                    <x-ui.badge :color="$stats['pending_comments'] > 0 ? 'gold' : 'neutral'">
                        {{ $stats['pending_comments'] }}
                    </x-ui.badge>
                </a>
            </div>
        </x-ui.card>

        <x-ui.card title="Qo'llanma">
            <p class="text-sm text-brown-700">
                Bu — admin panelning skeleton (Faza 6) versiyasi. Har bir bo'lim uchun to'liq
                qo'shish/tahrirlash/o'chirish funksiyalari keyingi bosqichlarda qo'shiladi.
            </p>
        </x-ui.card>
    </div>
@endsection
