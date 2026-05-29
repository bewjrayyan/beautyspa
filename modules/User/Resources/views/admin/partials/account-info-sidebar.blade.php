@php
    $accountUser = $accountUser ?? $profileUser;
    $accountUser->loadMissing(['roles']);
    $showAdminFields = $showAdminFields ?? request()->routeIs('admin.users.edit');
    $roleNames = $accountUser->roles->pluck('name')->filter()->values();
    $memberSince = $accountUser->created_at
        ? $accountUser->created_at->timezone(config('app.timezone'))->format('d M Y')
        : '—';
    $lastLogin = $accountUser->last_login
        ? $accountUser->last_login->timezone(config('app.timezone'))->format('d M Y, H:i')
        : trans('user::users.profile_page.never_logged_in');
@endphp

<aside class="admin-profile-account-info-sidebar">
    <div class="admin-profile-account-info-sidebar__head">
        <h3>
            <i class="fa fa-info-circle" aria-hidden="true"></i>
            {{ trans('user::users.profile_page.account_info_title') }}
        </h3>
        <p>
            {{ trans($showAdminFields ? 'user::users.profile_page.account_info_lead_admin' : 'user::users.profile_page.account_info_lead') }}
        </p>
    </div>

    <dl class="admin-profile-readonly admin-profile-readonly--sidebar">
        <div>
            <dt>{{ trans('user::users.profile_page.member_since') }}</dt>
            <dd>{{ $memberSince }}</dd>
        </div>
        <div>
            <dt>{{ trans('user::users.profile_page.last_login') }}</dt>
            <dd>{{ $lastLogin }}</dd>
        </div>
        @if ($roleNames->isNotEmpty())
            <div>
                <dt>{{ trans('user::users.profile_page.roles') }}</dt>
                <dd>{{ $roleNames->implode(', ') }}</dd>
            </div>
        @endif
        @if (app('modules')->isEnabled('Loyalty') && $accountUser->referral_code)
            <div>
                <dt>{{ trans('user::users.profile_page.loyalty_referral_code') }}</dt>
                <dd><code>{{ $accountUser->referral_code }}</code></dd>
            </div>
        @endif
    </dl>
</aside>
