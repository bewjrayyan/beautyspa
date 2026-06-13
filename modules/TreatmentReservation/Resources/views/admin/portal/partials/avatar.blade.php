@php
    $portalUser = $user ?? $beautician->user ?? null;
    $avatarUrl = $beautician->profile_image->exists
        ? $beautician->profile_image->path
        : $portalUser?->avatarUrl();
    $profileColor = $beautician->profile_color ?? $portalUser?->avatarBackgroundColor() ?? '#6366f1';
    $avatarClass = trim('tr-portal-avatar' . ($class ?? '') . ($avatarUrl ? ' tr-portal-avatar--photo' : ''));
@endphp

<span
    class="{{ $avatarClass }}"
    @unless($avatarUrl) style="background-color: {{ $profileColor }};" @endunless
>
    @if ($avatarUrl)
        <img src="{{ $avatarUrl }}" alt="{{ $beautician->name }}">
    @else
        {{ $beautician->initials }}
    @endif
</span>
