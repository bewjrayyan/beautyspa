<div class="admin-profile-account">
    @include('user::admin.partials.password-panel', [
        'passwordPanelId' => 'new_password',
        'passwordRequired' => false,
        'passwordTitle' => trans('user::users.profile_page.password_title'),
        'passwordLead' => trans('user::users.profile_page.password_lead'),
        'passwordTip' => trans('user::users.profile_page.password_tip'),
        'passwordLabel' => trans('user::attributes.users.new_password'),
        'passwordConfirmLabel' => trans('user::attributes.users.confirm_new_password'),
        'passwordGeneratedHint' => trans('user::users.profile_page.password_generated_hint'),
    ])

    <div class="admin-profile-card">
        <div class="admin-profile-card__head">
            <h2 class="admin-profile-card__title">
                <i class="fa fa-envelope-o" aria-hidden="true"></i>
                {{ trans('user::users.or_reset_password') }}
            </h2>
        </div>

        <a
            href="{{ route('admin.users.reset_password', $user) }}"
            class="btn btn-default btn-reset-password"
            data-loading
        >
            <i class="fa fa-paper-plane" aria-hidden="true"></i>
            {{ trans('user::users.send_reset_password_email') }}
        </a>
    </div>
</div>
