@php
    $user = $user ?? $currentUser;
    $user->loadMissing(['roles', 'files']);

    if (app('modules')->isEnabled('Beautician')) {
        $user->loadMissing('beauticianProfile');
    }

    $accent = $user->avatarBackgroundColor();
    $heroAvatarUrl = $user->avatarUrl();
    $beauticianProfile = $user->beauticianProfile ?? null;
    $memberSince = $user->created_at
        ? $user->created_at->timezone(config('app.timezone'))->format('d M Y')
        : '—';
    $lastLogin = $user->last_login
        ? $user->last_login->timezone(config('app.timezone'))->format('d M Y, H:i')
        : trans('user::users.profile_page.never_logged_in');
@endphp

<header class="admin-profile-hero" style="--profile-accent: {{ $accent }};">
    <div class="admin-profile-hero__main">
        @if ($heroAvatarUrl)
            <span
                class="admin-profile-hero__avatar admin-profile-hero__avatar--photo"
                data-admin-profile-hero-avatar
                data-initial="{{ $user->avatarInitial() }}"
                data-accent="{{ $accent }}"
            >
                <img
                    src="{{ $heroAvatarUrl }}"
                    alt="{{ $user->full_name }}"
                    data-admin-profile-hero-avatar-img
                >
            </span>
        @else
            <span
                class="admin-profile-hero__avatar admin-profile-hero__avatar--initial"
                data-admin-profile-hero-avatar
                data-initial="{{ $user->avatarInitial() }}"
                data-accent="{{ $accent }}"
                style="background-color: {{ $accent }};"
            >{{ $user->avatarInitial() }}</span>
        @endif
        <div class="admin-profile-hero__text">
            <h1 class="admin-profile-hero__name">{{ $user->full_name }}</h1>
            <p class="admin-profile-hero__email">{{ $user->email }}</p>
            @if ($user->roles->isNotEmpty())
                <div class="admin-profile-hero__roles">
                    @foreach ($user->roles as $role)
                        @php
                            $isBeauticianRole = strcasecmp((string) $role->name, 'Beautician') === 0;
                            $roleAccent = $isBeauticianRole
                                ? ($beauticianProfile?->profile_color ?: '#8b5cf6')
                                : null;
                        @endphp
                        <span
                            @class([
                                'admin-profile-hero__role',
                                'admin-profile-hero__role--beautician' => $isBeauticianRole,
                                'admin-profile-hero__role--default' => ! $isBeauticianRole,
                            ])
                            @if ($roleAccent) style="--role-accent: {{ $roleAccent }};" @endif
                        >
                            <i
                                class="fa {{ $isBeauticianRole ? 'fa-scissors' : 'fa-shield' }}"
                                aria-hidden="true"
                            ></i>
                            <span class="admin-profile-hero__role-name">{{ $role->name }}</span>
                            @if ($isBeauticianRole && filled($beauticianProfile?->job_title))
                                <span class="admin-profile-hero__role-position">
                                    {{ $beauticianProfile->job_title }}
                                </span>
                            @endif
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="admin-profile-hero__insights">
        <div class="admin-profile-hero__insight">
            <span class="admin-profile-hero__insight-value">{{ $memberSince }}</span>
            <span class="admin-profile-hero__insight-label">
                <i class="fa fa-calendar" aria-hidden="true"></i>
                {{ trans('user::users.profile_page.member_since') }}
            </span>
        </div>
        <div class="admin-profile-hero__insight">
            <span class="admin-profile-hero__insight-value">{{ $lastLogin }}</span>
            <span class="admin-profile-hero__insight-label">
                <i class="fa fa-sign-in" aria-hidden="true"></i>
                {{ trans('user::users.profile_page.last_login') }}
            </span>
        </div>
    </div>
</header>
