@php
    $user = $user ?? new \Modules\User\Entities\User();
    $user->loadMissing(['roles', 'files']);

    if (app('modules')->isEnabled('Beautician')) {
        $user->loadMissing('beauticianProfile');
    }

    $accent = $user->avatarBackgroundColor();
    $heroAvatarUrl = $user->avatarUrl();
    $beauticianProfile = $user->beauticianProfile ?? null;
    $firstName = old('first_name', $user->first_name);
    $lastName = old('last_name', $user->last_name);
    $fullName = trim("{$firstName} {$lastName}") ?: $user->full_name;
    $displayEmail = old('email', $user->email);
    $isActivated = (bool) old('activated', $user->isActivated());
    $memberSince = $user->created_at
        ? $user->created_at->timezone(config('app.timezone'))->format('d M Y')
        : '—';
    $lastLogin = $user->last_login
        ? $user->last_login->timezone(config('app.timezone'))->format('d M Y, H:i')
        : trans('user::users.profile_page.never_logged_in');
@endphp

<header class="admin-profile-hero admin-profile-hero--edit" style="--profile-accent: {{ $accent }};">
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
                    alt="{{ $fullName }}"
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
            <p class="admin-profile-hero__eyebrow">
                <i class="fa fa-pencil" aria-hidden="true"></i>
                {{ trans('user::users.edit_page.eyebrow') }}
            </p>
            <div class="admin-profile-hero__name-row">
                <h1
                    class="admin-profile-hero__name"
                    data-admin-profile-hero-name
                >{{ $fullName }}</h1>

                <div class="admin-profile-hero__meta">
                    <span
                        @class([
                            'admin-profile-hero__status',
                            'admin-profile-hero__status--active' => $isActivated,
                            'admin-profile-hero__status--inactive' => ! $isActivated,
                        ])
                        data-admin-profile-hero-status
                        data-label-active="{{ trans('user::users.edit_page.status_active') }}"
                        data-label-inactive="{{ trans('user::users.edit_page.status_inactive') }}"
                    >
                        <i class="fa {{ $isActivated ? 'fa-check-circle' : 'fa-pause-circle' }}" aria-hidden="true" data-admin-profile-hero-status-icon></i>
                        <span data-admin-profile-hero-status-label>{{ trans($isActivated ? 'user::users.edit_page.status_active' : 'user::users.edit_page.status_inactive') }}</span>
                    </span>

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
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <p
                class="admin-profile-hero__email"
                data-admin-profile-hero-email
            >{{ $displayEmail }}</p>

            <p class="admin-profile-hero__lead">{{ trans('user::users.edit_page.lead') }}</p>
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
