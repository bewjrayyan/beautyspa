@component('setting::admin.settings.partials.settings-wrap')
    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-wrench',
        'title' => trans('setting::settings.tabs.maintenance'),
    ])
        {{ Form::checkbox('maintenance_mode', trans('setting::attributes.maintenance_mode'), trans('setting::settings.form.put_the_application_into_maintenance_mode'), $errors, $settings) }}
    @endcomponent
@endcomponent
