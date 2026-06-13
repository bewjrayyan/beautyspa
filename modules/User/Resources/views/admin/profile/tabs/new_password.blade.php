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
