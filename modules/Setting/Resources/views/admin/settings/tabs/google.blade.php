@include('setting::admin.settings.partials.social-login', [
    'provider' => 'google',
    'errors' => $errors,
    'settings' => $settings,
])
