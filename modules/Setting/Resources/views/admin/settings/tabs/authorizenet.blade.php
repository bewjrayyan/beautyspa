@component('setting::admin.settings.partials.payment-gateway', [
    'prefix' => 'authorizenet',
    'errors' => $errors,
    'settings' => $settings,
])
    @slot('credentials')
        {{ Form::text('authorizenet_merchant_login_id', trans('setting::attributes.authorizenet_merchant_login_id'), $errors, $settings, ['required' => true]) }}
        {{ Form::password('authorizenet_merchant_transaction_key', trans('setting::attributes.authorizenet_merchant_transaction_key'), $errors, $settings, ['required' => true]) }}
    @endslot
@endcomponent
