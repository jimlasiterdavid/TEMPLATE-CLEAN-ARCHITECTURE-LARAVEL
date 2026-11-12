<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name') }}</title>
    {{-- @feature ui:vue --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- @endfeature --}}
    {{-- @feature ui:react --}}
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    {{-- @endfeature --}}
    @inertiaHead
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
    @inertia
</body>
</html>
