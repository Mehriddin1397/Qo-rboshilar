<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} — Qo'rboshilar.uz</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo/favicon-32x32.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen flex-col items-center justify-center bg-paper px-4 text-center font-sans text-ink antialiased">
    <img src="{{ asset('logo/favicon-64x64.png') }}" alt="Qo'rboshilar.uz" class="mb-6 h-14 w-14 rounded-full">

    <p class="font-serif text-6xl font-semibold text-gold-600">{{ $code }}</p>
    <h1 class="mt-3 font-serif text-2xl font-semibold text-brown-900">{{ $title }}</h1>
    <p class="mx-auto mt-3 max-w-md text-brown-700">{{ $message }}</p>

    <a href="{{ url('/') }}" class="mt-8 inline-flex items-center justify-center rounded-md bg-brown-900 px-4 py-2 text-sm font-medium text-paper transition hover:bg-gold-600">
        Bosh sahifaga qaytish
    </a>
</body>
</html>
