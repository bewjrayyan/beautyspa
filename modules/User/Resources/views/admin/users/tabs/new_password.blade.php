<div class="admin-profile-account">
    <div class="admin-profile-card" id="new_password">
        <div class="admin-profile-card__head">
            <h2 class="admin-profile-card__title">
                <i class="fa fa-lock" aria-hidden="true"></i>
                {{ trans('user::users.profile_page.password_title') }}
            </h2>
            <p class="admin-profile-card__lead">{{ trans('user::users.profile_page.password_lead') }}</p>
            <p class="admin-profile-card__tip">
                <i class="fa fa-info-circle" aria-hidden="true"></i>
                {{ trans('user::users.profile_page.password_tip') }}
            </p>
        </div>

        <div class="admin-profile-card__grid admin-profile-card__grid--password admin-profile-form">
            <div class="admin-profile-card__field">
                {{ Form::password('password', trans('user::attributes.users.new_password'), $errors) }}
            </div>
            <div class="admin-profile-card__field">
                {{ Form::password('password_confirmation', trans('user::attributes.users.confirm_new_password'), $errors) }}
            </div>
        </div>
    </div>

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
