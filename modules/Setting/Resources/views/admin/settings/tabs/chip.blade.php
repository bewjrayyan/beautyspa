<div class="st-tab st-tab--chip settings-form">
    <p class="st-tab__lead">{{ trans('setting::settings.tab_leads.chip') }}</p>

    <div class="row st-gateway chip-settings">
        <div class="col-md-12">
            <div class="st-enable-card">
                {{ Form::checkbox('chip_enabled', trans('setting::attributes.chip_enabled'), trans('setting::settings.form.enable_chip'), $errors, $settings) }}
            </div>

            @component('setting::admin.settings.partials.section', [
                'icon' => 'fa-tag',
                'title' => trans('setting::settings.sections.display'),
                'class' => 'st-section--compact',
            ])
                {{ Form::text('translatable[chip_label]', trans('setting::attributes.translatable.chip_label'), $errors, $settings, ['required' => true]) }}
                {{ Form::textarea('translatable[chip_description]', trans('setting::attributes.translatable.chip_description'), $errors, $settings, ['rows' => 3, 'required' => true]) }}
                {{ Form::checkbox('chip_test_mode', trans('setting::attributes.chip_test_mode'), trans('setting::settings.form.use_sandbox_for_test_payments'), $errors, $settings) }}
            @endcomponent

            <div class="{{ old('chip_enabled', array_get($settings, 'chip_enabled')) ? '' : 'hide' }}" id="chip-fields">
                @component('setting::admin.settings.partials.section', [
                    'icon' => 'fa-key',
                    'title' => trans('setting::settings.sections.credentials'),
                ])
                    {{ Form::text('chip_brand_id', trans('setting::attributes.chip_brand_id'), $errors, $settings, ['required' => true]) }}
                    {{ Form::password('chip_api_key', trans('setting::attributes.chip_api_key'), $errors, $settings, ['required' => true]) }}

                    <div class="st-notice chip-settings__notice">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                        <p>
                            {{ trans('setting::settings.form.chip_credentials_help') }}
                            <a href="https://portal.chip-in.asia/collect/developers/api-keys" target="_blank" rel="noopener">CHIP Developer Portal</a>
                        </p>
                    </div>

                    {{ Form::text('chip_webhook_url', trans('setting::attributes.chip_webhook_url'), $errors, $settings, [
                        'placeholder' => 'https://your-domain.com/payment/chip/webhook',
                    ]) }}
                    <p class="help-block text-muted">{{ trans('setting::settings.form.chip_webhook_help') }}</p>

                    {{ Form::textarea('chip_public_key', trans('setting::attributes.chip_public_key'), $errors, $settings, [
                        'rows' => 6,
                        'placeholder' => '-----BEGIN PUBLIC KEY-----',
                    ]) }}
                    <p class="help-block text-muted">{{ trans('setting::settings.form.chip_public_key_help') }}</p>
                @endcomponent

                @component('setting::admin.settings.partials.section', [
                    'icon' => 'fa-credit-card',
                    'title' => trans('setting::settings.form.chip_collect_methods_heading'),
                    'description' => trans('setting::settings.form.chip_collect_methods_help'),
                ])
                    <div class="chip-settings__all-methods">
                        {{ Form::checkbox('chip_all_methods_enabled', trans('setting::attributes.chip_all_methods_enabled'), trans('setting::settings.form.chip_all_methods_enable'), $errors, $settings) }}
                    </div>

                    <div class="chip-settings__methods">
                        @include('setting::admin.settings.partials.chip-method-card', [
                            'methodKey' => 'chip_fpx',
                            'icon' => 'fpx',
                            'faIcon' => 'fa-university',
                            'surchargeType' => 'flat',
                            'settings' => $settings,
                            'errors' => $errors,
                        ])

                        @include('setting::admin.settings.partials.chip-method-card', [
                            'methodKey' => 'chip_card',
                            'icon' => 'card',
                            'faIcon' => 'fa-credit-card',
                            'surchargeType' => 'percent',
                            'settings' => $settings,
                            'errors' => $errors,
                        ])

                        @include('setting::admin.settings.partials.chip-method-card', [
                            'methodKey' => 'chip_atome',
                            'icon' => 'atome',
                            'faIcon' => 'fa-shopping-bag',
                            'surchargeType' => 'percent',
                            'settings' => $settings,
                            'errors' => $errors,
                        ])
                    </div>
                @endcomponent
            </div>
        </div>

        <div class="col-md-4">
            <aside class="chip-brand-panel">
                <div class="chip-brand-panel__logo payment-gateway-logo">
                    {!! file_get_contents(module_path('Payment') . '/Resources/assets/admin/images/chip-logo.svg') !!}
                </div>
                <p class="chip-brand-panel__caption">{{ trans('setting::settings.chip.brand_caption') }}</p>
            </aside>
        </div>
    </div>
</div>
