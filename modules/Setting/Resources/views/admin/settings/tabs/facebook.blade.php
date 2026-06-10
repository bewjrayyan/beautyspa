@include('setting::admin.settings.partials.social-login', [
    'provider' => 'facebook',
    'errors' => $errors,
    'settings' => $settings,
])
