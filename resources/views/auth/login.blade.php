@extends('layouts.app')

@section('title', "Kirish — Qo'rboshilar.uz")

@section('content')
    <div class="mx-auto flex max-w-md flex-col justify-center px-4 py-16 sm:px-6">
        <h1 class="font-serif text-2xl font-semibold text-brown-900">Tizimga kirish</h1>
        <p class="mt-2 text-sm text-brown-700">Hisobingiz orqali izoh qoldirish va maqola yozish imkoniyatiga ega bo'lasiz.</p>

        @if (session('status'))
            <div class="mt-4 rounded-md bg-success/10 px-4 py-3 text-sm text-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-brown-900">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
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

            <label class="flex items-center gap-2 text-sm text-brown-700">
                <input type="checkbox" name="remember" class="rounded border-sand text-gold-600 focus:ring-gold-600">
                Meni eslab qol
            </label>

            <button type="submit"
                    class="w-full rounded-md bg-brown-900 px-4 py-2 text-sm font-medium text-paper transition hover:bg-gold-600">
                Kirish
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-brown-700">
            Hisobingiz yo'qmi?
            <a href="{{ route('register') }}" class="font-medium text-gold-600 hover:underline">Ro'yxatdan o'ting</a>
        </p>
    </div>
@endsection
