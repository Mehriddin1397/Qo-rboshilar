@extends('layouts.app')

@section('title', $seo['title'])
@section('meta_description', $seo['description'])
@section('canonical', $seo['canonical'])

@section('content')
    <section class="border-b border-sand bg-paper-dark py-12">
        <x-ui.container class="max-w-3xl text-center">
            <h1 class="font-serif text-3xl font-semibold text-brown-900 sm:text-4xl">Bog'lanish</h1>
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
                    Loyihada xatolik topsangiz, tarixiy manba taklif qilmoqchi bo'lsangiz yoki hamkorlik bo'yicha
                    taklifingiz bo'lsa, quyidagi yo'llar orqali fikringizni bildirishingiz mumkin:
                </p>

                <ul class="list-disc space-y-2 pl-5">
                    <li>
                        Har bir maqola va voqea sahifasidagi <strong>izohlar</strong> bo'limi orqali — bu eng tez
                        javob oladigan yo'l.
                    </li>
                    <li>
                        Tarixiy manba yoki tuzatish taklif qilish uchun — tegishli sahifadagi ma'lumotni aniq
                        ko'rsatib, izoh qoldiring.
                    </li>
                </ul>

                <x-ui.empty-state message="To'g'ridan-to'g'ri aloqa manzillari (elektron pochta, ijtimoiy tarmoqlar) hozircha e'lon qilinmagan — tez orada shu yerga qo'shiladi." />
            </div>
        </x-ui.container>
    </section>
@endsection
