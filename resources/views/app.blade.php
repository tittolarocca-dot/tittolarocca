<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="{{ $seo->robots() }}" />
    <title inertia>{{ $seo->fullTitle() }}</title>
    @if ($seo->description)
    <meta name="description" content="{{ $seo->description }}" />
    @endif
    <link rel="canonical" href="{{ $seo->canonical() }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="bg-gray-50 antialiased">
    @inertia
</body>
</html>
