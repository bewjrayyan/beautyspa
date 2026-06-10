<div class="st-fields-grid st-fields-grid--sections">
    <div class="st-fields-grid__col">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-globe',
            'title' => trans('setting::settings.sections.regional'),
        ])
            @component('setting::admin.settings.partials.fields-grid')
                @slot('left')
                    {{ Form::select('supported_countries', trans('setting::attributes.supported_countries'), $errors, $countries, $settings, ['class' => 'selectize prevent-creation', 'required' => true, 'multiple' => true]) }}
                    {{ Form::select('default_country', trans('setting::attributes.default_country'), $errors, $countries, $settings, ['required' => true]) }}
                @endslot
                @slot('right')
                    {{ Form::select('default_timezone', trans('setting::attributes.default_timezone'), $errors, $timeZones, $settings, ['required' => true]) }}
                    {{ Form::select('customer_role', trans('setting::attributes.customer_role'), $errors, $roles, $settings, ['required' => true]) }}
                @endslot
            @endcomponent
        @endcomponent
    </div>

    <div class="st-fields-grid__col">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-star',
            'title' => trans('setting::settings.sections.reviews_privacy'),
            'columns' => 2,
        ])
            {{ Form::checkbox('reviews_enabled', trans('setting::attributes.reviews_enabled'), trans('setting::settings.form.allow_reviews'), $errors, $settings) }}
            {{ Form::checkbox('auto_approve_reviews', trans('setting::attributes.auto_approve_reviews'), trans('setting::settings.form.approve_reviews_automatically'), $errors, $settings) }}
            {{ Form::checkbox('cookie_bar_enabled', trans('setting::attributes.cookie_bar_enabled'), trans('setting::settings.form.show_cookie_bar'), $errors, $settings) }}
        @endcomponent
    </div>
</div>
