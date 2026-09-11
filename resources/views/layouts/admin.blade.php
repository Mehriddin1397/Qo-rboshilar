<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — Qo'rboshilar.uz</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('logo/favicon-192x192.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper-dark text-ink font-sans antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-screen">
        <x-admin.sidebar />

        <div class="flex min-h-screen flex-1 flex-col lg:pl-72">
            <x-admin.topbar />

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                @if (session('status'))
                    <x-ui.alert type="success" class="mb-6">{{ session('status') }}</x-ui.alert>
                @endif

                @if (session('error'))
                    <x-ui.alert type="error" class="mb-6">{{ session('error') }}</x-ui.alert>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
