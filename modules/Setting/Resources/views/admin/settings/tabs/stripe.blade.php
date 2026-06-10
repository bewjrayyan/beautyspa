@component('setting::admin.settings.partials.payment-gateway', [
    'prefix' => 'stripe',
    'hasSandbox' => false,
    'errors' => $errors,
    'settings' => $settings,
])
    @slot('displayLeft')
        {{ Form::select('stripe_integration_type', trans('setting::attributes.stripe_integration_type'), $errors, $stripe_integration_types, $settings, ['required' => true]) }}
    @endslot
    @slot('credentials')
        {{ Form::text('stripe_publishable_key', trans('setting::attributes.stripe_publishable_key'), $errors, $settings, ['required' => true]) }}
        {{ Form::password('stripe_secret_key', trans('setting::attributes.stripe_secret_key'), $errors, $settings, ['required' => true]) }}
    @endslot
@endcomponent
