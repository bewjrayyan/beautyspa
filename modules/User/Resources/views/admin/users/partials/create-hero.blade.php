@php
    $user = $user ?? new \Modules\User\Entities\User();
    $accent = $user->avatarBackgroundColor();
    $firstName = old('first_name', $user->first_name);
    $lastName = old('last_name', $user->last_name);
    $fullName = trim("{$firstName} {$lastName}");
    $displayName = $fullName !== '' ? $fullName : trans('user::users.create_page.placeholder_name');
    $displayEmail = filled(old('email', $user->email))
        ? old('email', $user->email)
        : trans('user::users.create_page.placeholder_email');
    $initial = $firstName
        ? mb_strtoupper(mb_substr(trim((string) $firstName), 0, 1))
        : $user->avatarInitial();
@endphp

<header class="admin-profile-hero admin-profile-hero--create" style="--profile-accent: {{ $accent }};">
    <div class="admin-profile-hero__main">
        <span
            class="admin-profile-hero__avatar admin-profile-hero__avatar--initial"
            data-admin-profile-hero-avatar
            data-initial="{{ $initial }}"
            data-accent="{{ $accent }}"
            style="background-color: {{ $accent }};"
        >{{ $initial }}</span>

        <div class="admin-profile-hero__text">
            <p class="admin-profile-hero__eyebrow">
                <i class="fa fa-user-plus" aria-hidden="true"></i>
                {{ trans('user::users.create_page.eyebrow') }}
            </p>
            <h1
                class="admin-profile-hero__name"
                data-admin-profile-hero-name
                data-placeholder="{{ trans('user::users.create_page.placeholder_name') }}"
            >{{ $displayName }}</h1>
            <p
                class="admin-profile-hero__email"
                data-admin-profile-hero-email
                data-placeholder="{{ trans('user::users.create_page.placeholder_email') }}"
            >{{ $displayEmail }}</p>
            <p class="admin-profile-hero__lead">{{ trans('user::users.create_page.lead') }}</p>
        </div>
    </div>

    <div class="admin-profile-hero__actions">
        <a href="{{ route('admin.users.index') }}" class="btn btn-default admin-profile-hero__btn">
            <i class="fa fa-arrow-left" aria-hidden="true"></i>
            {{ trans('user::users.navigation.back_to_index') }}
        </a>
        @hasAccess('admin.roles.index')
            <a href="{{ route('admin.roles.index') }}" class="btn btn-default admin-profile-hero__btn">
                <i class="fa fa-shield" aria-hidden="true"></i>
                {{ trans('user::users.index.manage_roles') }}
            </a>
        @endHasAccess
    </div>
</header>
