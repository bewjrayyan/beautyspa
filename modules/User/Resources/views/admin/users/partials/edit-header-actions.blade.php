@php
    $user = $user ?? new \Modules\User\Entities\User();
    $passwordTabUrl = route('admin.users.edit', $user) . '?tab=new_password';
@endphp

<div class="admin-users-header-actions">
    <a href="{{ route('admin.users.index') }}" class="btn btn-default admin-users-header-actions__btn">
        <i class="fa fa-arrow-left" aria-hidden="true"></i>
        {{ trans('user::users.navigation.back_to_index') }}
    </a>
    @hasAccess('admin.roles.index')
        <a href="{{ route('admin.roles.index') }}" class="btn btn-default admin-users-header-actions__btn">
            <i class="fa fa-shield" aria-hidden="true"></i>
            {{ trans('user::users.index.manage_roles') }}
        </a>
    @endHasAccess
    <a href="{{ $passwordTabUrl }}" class="btn btn-default admin-users-header-actions__btn">
        <i class="fa fa-lock" aria-hidden="true"></i>
        {{ trans('user::users.edit_page.change_password') }}
    </a>
</div>
