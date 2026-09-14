<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard Tenant' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased min-h-screen flex">
    <aside class="w-64 bg-white border-r min-h-screen p-4 hidden md:block">
        <h2 class="font-bold text-lg mb-4">Tenant Panel</h2>
        <!-- Navigasi Sidebar Tenant -->
    </aside>
    <div class="flex-1 flex flex-col min-h-screen">
        <main class="p-6 flex-1">
            {{ $slot }}
        </main>
    </div>
</body>
</html>