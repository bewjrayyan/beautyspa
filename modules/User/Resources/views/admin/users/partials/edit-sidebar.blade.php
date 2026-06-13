@php
    $user = $user ?? null;
    $passwordTabUrl = $user
        ? route('admin.users.edit', $user) . '?tab=new_password'
        : '#';
    $permissionsTabUrl = $user
        ? route('admin.users.edit', $user) . '?tab=permissions'
        : '#';
@endphp

<aside class="admin-profile-edit-sidebar">
    <div class="admin-profile-edit-sidebar__head">
        <h3>
            <i class="fa fa-lightbulb-o" aria-hidden="true"></i>
            {{ trans('user::users.edit_page.sidebar_title') }}
        </h3>
        <p>{{ trans('user::users.edit_page.sidebar_lead') }}</p>
    </div>

    <ul class="admin-profile-edit-sidebar__tips">
        <li>
            <i class="fa fa-shield" aria-hidden="true"></i>
            <span>{{ trans('user::users.edit_page.tip_roles') }}</span>
        </li>
        <li>
            <i class="fa fa-map-marker" aria-hidden="true"></i>
            <span>{{ trans('user::users.edit_page.tip_address') }}</span>
        </li>
        <li>
            <i class="fa fa-lock" aria-hidden="true"></i>
            <span>
                {{ trans('user::users.edit_page.tip_password') }}
                <a href="{{ $passwordTabUrl }}">{{ trans('user::users.edit_page.tip_password_link') }}</a>
            </span>
        </li>
        <li>
            <i class="fa fa-check-circle" aria-hidden="true"></i>
            <span>{{ trans('user::users.edit_page.tip_activate') }}</span>
        </li>
        <li>
            <i class="fa fa-key" aria-hidden="true"></i>
            <span>
                {{ trans('user::users.edit_page.tip_permissions') }}
                <a href="{{ $permissionsTabUrl }}">{{ trans('user::users.edit_page.tip_permissions_link') }}</a>
            </span>
        </li>
    </ul>
</aside>
