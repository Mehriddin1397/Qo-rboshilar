<!DOCTYPE html>
<html lang="uz">
<head>
    @php
        $seo = array_merge([
            'title' => "Qo'rboshilar.uz — Turkiston tarixi",
            'description' => "Turkiston tarixida bosqinchilikka va mustamlakachilikka qarshi kurashgan qo'rboshilar, qo'zg'olonlar, adabiyotlar va videolarni birlashtirgan raqamli tarixiy platforma.",
            'canonical' => url()->current(),
            'image' => asset('logo/og-image-1200x630.png'),
            'type' => 'website',
        ], $seo ?? []);
    @endphp

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', $seo['title'])</title>
    <meta name="description" content="@yield('meta_description', $seo['description'])">
    <link rel="canonical" href="@yield('canonical', $seo['canonical'])">

    <meta property="og:type" content="{{ $seo['type'] }}">
    <meta property="og:title" content="@yield('title', $seo['title'])">
    <meta property="og:description" content="@yield('meta_description', $seo['description'])">
    <meta property="og:url" content="@yield('canonical', $seo['canonical'])">
    <meta property="og:image" content="{{ $seo['image'] }}">

    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('logo/favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('logo/favicon-64x64.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('logo/favicon-192x192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('logo/apple-touch-icon-180x180.png') }}">

    {{-- Production QA §11: faqat aniq qo'llab-quvvatlanadigan schema (WebSite +
    qidiruv) — soxta historical claim yaratilmaydi. JSON_HEX_* — script-breakout
    xavfsizligi uchun (§ breadcrumb-jsonld komponentidagi izohga qarang). --}}
    <script type="application/ld+json">
        {!! json_encode([
            '@@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => "Qo'rboshilar.uz",
            'url' => url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => route('search').'?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper text-ink font-sans antialiased flex flex-col">
    <x-layout.navbar />

    <main class="flex-1">
        @if (session('status'))
            <x-ui.container class="pt-6">
                <x-ui.alert type="success">{{ session('status') }}</x-ui.alert>
            </x-ui.container>
        @endif

        @if (session('error'))
            <x-ui.container class="pt-6">
                <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
            </x-ui.container>
        @endif

        @yield('content')
    </main>

    <x-layout.footer />
</body>
</html>
