@component('setting::admin.settings.partials.settings-wrap')
    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-money',
        'title' => trans('setting::settings.tabs.currency'),
    ])
        @component('setting::admin.settings.partials.fields-grid')
            @slot('left')
                {{ Form::select('supported_currencies', trans('setting::attributes.supported_currencies'), $errors, $currencies, $settings, ['class' => 'selectize prevent-creation', 'required' => true, 'multiple' => true]) }}
                {{ Form::select('default_currency', trans('setting::attributes.default_currency'), $errors, $currencies, $settings, ['required' => true]) }}
            @endslot
            @slot('right')
                {{ Form::select('currency_rate_exchange_service', trans('setting::attributes.currency_rate_exchange_service'), $errors, $currencyRateExchangeServices, $settings) }}
                {{ Form::checkbox('auto_refresh_currency_rates', trans('setting::attributes.auto_refresh_currency_rates'), trans('setting::settings.form.enable_auto_refreshing_currency_rates'), $errors, $settings) }}
                <div class="{{ old('auto_refresh_currency_rates', array_get($settings, 'auto_refresh_currency_rates')) ? '' : 'hide' }}" id="auto-refresh-currency-rates-frequency-field">
                    {{ Form::select('auto_refresh_currency_rate_frequency', trans('setting::attributes.auto_refresh_currency_rate_frequency'), $errors, trans('setting::settings.form.auto_refresh_currency_rate_frequencies'), $settings, ['required' => true]) }}
                </div>
            @endslot
            @slot('full')
                @foreach ($currencyRateExchangeServices as $service => $serviceName)
                    @if ($service !== '')
                        <div class="currency-rate-exchange-service hide" id="{{ $service }}-service">
                            @includeIf("setting::admin.settings.partials.currency_rate_exchange_services.{$service}")
                        </div>
                    @endif
                @endforeach
            @endslot
        @endcomponent
    @endcomponent
@endcomponent
