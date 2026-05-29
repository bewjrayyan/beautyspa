@component('setting::admin.settings.partials.payment-gateway', [
    'prefix' => 'paypal',
    'errors' => $errors,
    'settings' => $settings,
])
    @slot('credentials')
        {{ Form::text('paypal_client_id', trans('setting::attributes.paypal_client_id'), $errors, $settings, ['required' => true]) }}
        {{ Form::password('paypal_secret', trans('setting::attributes.paypal_secret'), $errors, $settings, ['required' => true]) }}
    @endslot
@endcomponent
