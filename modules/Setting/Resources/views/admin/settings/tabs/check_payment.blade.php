@include('setting::admin.settings.partials.offline-payment', [
    'prefix' => 'check_payment',
    'errors' => $errors,
    'settings' => $settings,
    'hasInstructions' => true,
    'fieldsId' => 'check-payment-fields',
])
