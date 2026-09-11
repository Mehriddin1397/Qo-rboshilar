@extends('layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])

@section('content')
    <section class="border-b border-sand bg-paper-dark py-12">
        <x-ui.container class="max-w-3xl text-center">
            <h1 class="font-serif text-3xl font-semibold text-brown-900 sm:text-4xl">Biz haqimizda</h1>
            <p class="mt-3 text-brown-700">
                Qo'rboshilar.uz — Turkiston tarixi, qo'rboshilar, qo'zg'olonlar va tarixiy manbalarni birlashtirgan
                raqamli arxiv loyihasi.
            </p>
        </x-ui.container>
    </section>

    <section class="py-12">
        <x-ui.container class="max-w-2xl">
            <div class="space-y-6 text-brown-700">
                <p>
                    Loyihaning maqsadi — Turkiston tarixidagi qo'rboshilar, qo'zg'olonlar va ular bilan bog'liq
                    adabiyot, video va boshqa manbalarni bir joyda, tartibli va tekshirilgan holda taqdim etish.
                </p>
                <p>
                    Sayt tarkibidagi barcha ma'lumotlar tarixiy manbalarga asoslanadi va doimiy ravishda
                    to'ldirib boriladi. Xatolik yoki tuzatish takliflarini
                    <a href="{{ route('contact') }}" class="text-gold-600 underline hover:text-brown-900">Bog'lanish</a>
                    sahifasi orqali yuborishingiz mumkin.
                </p>
            </div>
        </x-ui.container>
    </section>
@endsection
