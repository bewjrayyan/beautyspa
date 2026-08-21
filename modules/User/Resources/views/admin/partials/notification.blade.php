@php
    $sweetFlashes = array_filter([
        'success' => session('success'),
        'error' => session('error'),
        'warning' => session('warning'),
        'info' => session('info'),
    ], static fn ($value) => filled($value));
@endphp

@if (! empty($sweetFlashes))
    <script type="application/json" id="sweet-notification-flashes">@json($sweetFlashes)</script>
@endif

@stack('notifications')
