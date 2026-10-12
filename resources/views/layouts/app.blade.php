<!DOCTYPE html>
<html lang="uz">
<head>
    @php
        $rawCanonical = $seo['canonical'] ?? url()->current();
        if (config('app.url') && str_contains(config('app.url'), 'qorboshilar.uz')) {
            $path = parse_url($rawCanonical, PHP_URL_PATH) ?? '/';
            $canonicalUrl = 'https://qorboshilar.uz' . $path;
        } else {
            $canonicalUrl = $rawCanonical;
        }

        $rawImage = $seo['image'] ?? asset('logo/og-image-1200x630.png');
        $seoImage = $rawImage ? (str_starts_with($rawImage, 'http') ? $rawImage : url($rawImage)) : asset('logo/og-image-1200x630.png');

        $seo = array_merge([
            'title' => "Qo'rboshilar.uz — Turkiston milliy ozodlik harakati tarixi",
            'description' => "Turkiston tarixida mustamlakachilikka qarshi kurashgan qo'rboshilar, qo'zg'olonlar, tarixiy manbalar va videolarni jamlagan raqamli ilmiy ensiklopediya.",
            'canonical' => $canonicalUrl,
            'image' => $seoImage,
            'type' => 'website',
        ], $seo ?? []);
        $seo['image'] = $seoImage;

        $pageTitle = trim($__env->yieldContent('title', $seo['title']));
        $pageDescription = \Illuminate\Support\Str::limit(trim($__env->yieldContent('meta_description', $seo['description'])), 160);
        $pageCanonical = trim($__env->yieldContent('canonical', $seo['canonical']));
    @endphp

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $pageCanonical }}">

    {{-- Open Graph meta teglar (Facebook, Telegram, LinkedIn) --}}
    <meta property="og:site_name" content="Qo'rboshilar.uz">
    <meta property="og:locale" content="uz_UZ">
    <meta property="og:type" content="{{ $seo['type'] ?? 'website' }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $pageCanonical }}">
    <meta property="og:image" content="{{ $seo['image'] }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $pageTitle }}">

    {{-- Twitter Card meta teglar --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $seo['image'] }}">

    {{-- Favicon va ilova belgilari --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('logo/favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('logo/favicon-64x64.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('logo/favicon-192x192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('logo/apple-touch-icon-180x180.png') }}">

    {{-- Schema.org Structured Data (WebSite + Organization) --}}
    <script type="application/ld+json">
        {!! json_encode([
            '@@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => "Qo'rboshilar.uz",
            'alternateName' => 'Turkiston Qorboshilari Tarixi',
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
    <script type="application/ld+json">
        {!! json_encode([
            '@@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => "Qo'rboshilar.uz",
            'url' => url('/'),
            'logo' => asset('logo/favicon-192x192.png'),
            'description' => "Turkiston milliy ozodlik harakati va qo'rboshilar tarixi bo'yicha raqamli ilmiy platforma.",
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>

    <script>
        (function() {
            try {
                var s = localStorage.getItem('qorboshilar_font_size');
                if (s && s !== '100%') {
                    document.documentElement.style.fontSize = s;
                }
            } catch (e) {}
        })();
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
