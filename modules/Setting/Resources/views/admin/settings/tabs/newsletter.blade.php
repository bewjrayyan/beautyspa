@component('setting::admin.settings.partials.settings-wrap')
    <div class="st-enable-card">
        {{ Form::checkbox('newsletter_enabled', trans('setting::attributes.newsletter_enabled'), trans('setting::settings.form.allow_customers_to_subscribe'), $errors, $settings) }}
    </div>

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-envelope',
        'title' => trans('setting::settings.sections.credentials'),
    ])
        @component('setting::admin.settings.partials.fields-grid')
            @slot('left')
                {{ Form::password('mailchimp_api_key', trans('setting::attributes.mailchimp_api_key'), $errors, $settings, ['required' => true]) }}
            @endslot
            @slot('right')
                {{ Form::text('mailchimp_list_id', trans('setting::attributes.mailchimp_list_id'), $errors, $settings, ['required' => true]) }}
            @endslot
        @endcomponent
    @endcomponent
@endcomponent
