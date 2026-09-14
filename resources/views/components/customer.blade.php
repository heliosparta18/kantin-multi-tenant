<x-layouts-customer>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Kantin' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased min-h-screen">
    <main class="max-w-md mx-auto min-h-screen bg-white shadow-sm flex flex-col">
        {{ $slot }}
    </main>
</body>
</html>
</x-layouts-customer>