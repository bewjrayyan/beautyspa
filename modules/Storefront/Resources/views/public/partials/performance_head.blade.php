@php
    $mediaCdn = rtrim((string) config('performance.cdn.media_url', ''), '/');
    $assetCdn = rtrim((string) config('performance.cdn.asset_url', ''), '/');
@endphp

@if ($mediaCdn)
    <link rel="dns-prefetch" href="{{ $mediaCdn }}">
    <link rel="preconnect" href="{{ $mediaCdn }}" crossorigin>
@endif

@if ($assetCdn && $assetCdn !== $mediaCdn)
    <link rel="dns-prefetch" href="{{ $assetCdn }}">
    <link rel="preconnect" href="{{ $assetCdn }}" crossorigin>
@endif

@foreach ([
    'node_modules/line-awesome/dist/line-awesome/fonts/la-solid-900.woff2',
    'node_modules/line-awesome/dist/line-awesome/fonts/la-regular-400.woff2',
    'node_modules/line-awesome/dist/line-awesome/fonts/la-brands-400.woff2',
] as $lineAwesomeFont)
    @if ($fontUrl = vite_build_asset($lineAwesomeFont))
        <link rel="preload" href="{{ $fontUrl }}" as="font" type="font/woff2" crossorigin>
    @endif
@endforeach
