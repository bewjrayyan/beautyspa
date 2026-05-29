@php
    $avatarUrl = $user->avatarUrl();
    $avatarClass = trim(($class ?? '') . ($avatarUrl ? ' user-avatar--photo' : ''));
@endphp

<span
    @class([$avatarClass ?: null])
    @unless($avatarUrl) style="background-color: {{ $user->avatarBackgroundColor() }};" @endunless
>
    @if ($avatarUrl)
        <img src="{{ $avatarUrl }}" alt="{{ $user->full_name }}">
    @else
        {{ $user->avatarInitial() }}
    @endif
</span>
