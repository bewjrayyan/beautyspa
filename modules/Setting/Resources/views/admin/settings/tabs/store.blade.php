@component('setting::admin.settings.partials.settings-wrap')
    <div class="box-content clearfix">
        @component('setting::admin.settings.partials.fields-grid')
            @slot('left')
                {{ Form::text('translatable[store_name]', trans('setting::attributes.translatable.store_name'), $errors, $settings, ['required' => true]) }}
                {{ Form::text('translatable[store_tagline]', trans('setting::attributes.translatable.store_tagline'), $errors, $settings) }}
                {{ Form::text('store_email', trans('setting::attributes.store_email'), $errors, $settings, ['required' => true]) }}
                {{ Form::phone('store_phone', trans('setting::attributes.store_phone'), $errors, $settings, ['required' => true]) }}
            @endslot
            @slot('right')
                {{ Form::text('store_address_1', trans('setting::attributes.store_address_1'), $errors, $settings) }}
                {{ Form::text('store_address_2', trans('setting::attributes.store_address_2'), $errors, $settings) }}
                {{ Form::text('store_city', trans('setting::attributes.store_city'), $errors, $settings) }}
                {{ Form::select('store_country', trans('setting::attributes.store_country'), $errors, $countries, $settings) }}
                <div class="st-fields-grid__state">
                    <div class="store-state input">
                        {{ Form::text('store_state', trans('setting::attributes.store_state'), $errors, $settings) }}
                    </div>
                    <div class="store-state select hide">
                        {{ Form::select('store_state', trans('setting::attributes.store_state'), $errors, [], $settings) }}
                    </div>
                </div>
                {{ Form::text('store_zip', trans('setting::attributes.store_zip'), $errors, $settings) }}
            @endslot
            @slot('full')
                {{ Form::textarea('translatable[store_description]', trans('setting::attributes.translatable.store_description'), $errors, $settings, ['rows' => 5]) }}
            @endslot
        @endcomponent
    </div>

    <div class="box-content clearfix">
        <h4 class="section-title">{{ trans('setting::settings.form.privacy_settings') }}</h4>
        @component('setting::admin.settings.partials.fields-grid')
            @slot('left')
                {{ Form::checkbox('store_phone_hide', trans('setting::attributes.store_phone_hide'), trans('setting::settings.form.hide_store_phone'), $errors, $settings) }}
            @endslot
            @slot('right')
                {{ Form::checkbox('store_email_hide', trans('setting::attributes.store_email_hide'), trans('setting::settings.form.hide_store_email'), $errors, $settings) }}
            @endslot
        @endcomponent
    </div>
@endcomponent
