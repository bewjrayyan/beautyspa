@php
    $avatarUrl = $user->avatarUrl();
    $baseClass = $class ?? 'user-avatar';
    $avatarClass = $avatarUrl
        ? "{$baseClass} {$baseClass}--photo user-avatar--photo"
        : $baseClass;
@endphp

<span
    @class([$avatarClass])
    @unless($avatarUrl) style="background-color: {{ $user->avatarBackgroundColor() }};" @endunless
>
    @if ($avatarUrl)
        <img src="{{ $avatarUrl }}" alt="{{ $user->full_name }}" loading="lazy" decoding="async">
    @else
        {{ $user->avatarInitial() }}
    @endif
</span>
