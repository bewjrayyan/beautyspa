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
