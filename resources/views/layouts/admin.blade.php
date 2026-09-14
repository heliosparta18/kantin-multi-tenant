<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin Kantin' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 text-gray-100 font-sans antialiased min-h-screen flex">
    <aside class="w-64 bg-gray-800 border-r border-gray-700 min-h-screen p-4">
        <h2 class="font-bold text-lg mb-4 text-white">Superadmin</h2>
        <!-- Navigasi Sidebar Admin -->
    </aside>
    <main class="flex-1 p-6">
        {{ $slot }}
    </main>
</body>
</html>