@component('setting::admin.settings.partials.payment-gateway', [
    'prefix' => 'flutterwave',
    'errors' => $errors,
    'settings' => $settings,
])
    @slot('credentials')
        {{ Form::text('flutterwave_public_key', trans('setting::attributes.flutterwave_public_key'), $errors, $settings, ['required' => true]) }}
        {{ Form::password('flutterwave_secret_key', trans('setting::attributes.flutterwave_secret_key'), $errors, $settings, ['required' => true]) }}
        {{ Form::password('flutterwave_encryption_key', trans('setting::attributes.flutterwave_encryption_key'), $errors, $settings, ['required' => true]) }}
    @endslot
@endcomponent
