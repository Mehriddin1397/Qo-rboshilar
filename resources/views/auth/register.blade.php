@extends('layouts.app')

@section('title', "Ro'yxatdan o'tish — Qo'rboshilar.uz")

@section('content')
    <div class="mx-auto flex max-w-md flex-col justify-center px-4 py-16 sm:px-6">
        <h1 class="font-serif text-2xl font-semibold text-brown-900">Ro'yxatdan o'tish</h1>
        <p class="mt-2 text-sm text-brown-700">Hisob yarating va tarixiy jamiyatga qo'shiling.</p>

        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-brown-900">Ism-familiya</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                       class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                @error('name')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-brown-900">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                       class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                @error('email')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-brown-900">Parol</label>
                <input id="password" type="password" name="password" required
                       class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                @error('password')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-brown-900">Parolni tasdiqlang</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
            </div>

            <button type="submit"
                    class="w-full rounded-md bg-brown-900 px-4 py-2 text-sm font-medium text-paper transition hover:bg-gold-600">
                Ro'yxatdan o'tish
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-brown-700">
            Hisobingiz bormi?
            <a href="{{ route('login') }}" class="font-medium text-gold-600 hover:underline">Kirish</a>
        </p>
    </div>
@endsection
