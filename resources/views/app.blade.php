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

    {{-- Open Graph (WhatsApp, Facebook, Telegram …) --}}
    <meta property="og:site_name" content="{{ config('seo.site_name') }}" />
    <meta property="og:type" content="{{ $seo->ogType }}" />
    <meta property="og:title" content="{{ $seo->fullTitle() }}" />
    @if ($seo->description)
    <meta property="og:description" content="{{ $seo->description }}" />
    @endif
    <meta property="og:url" content="{{ $seo->canonical() }}" />
    <meta property="og:image" content="{{ $seo->ogImage() }}" />
    <meta property="og:locale" content="{{ $seo->ogLocale() }}" />

    {{-- Twitter Cards --}}
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $seo->fullTitle() }}" />
    @if ($seo->description)
    <meta name="twitter:description" content="{{ $seo->description }}" />
    @endif
    <meta name="twitter:image" content="{{ $seo->ogImage() }}" />

    {{-- Strukturierte Daten (Schema.org) --}}
    @foreach ($seo->jsonLd as $ld)
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endforeach
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
