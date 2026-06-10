@include('setting::admin.settings.partials.offline-payment', [
    'prefix' => 'bank_transfer',
    'errors' => $errors,
    'settings' => $settings,
    'hasInstructions' => true,
    'fieldsId' => 'bank-transfer-fields',
])
