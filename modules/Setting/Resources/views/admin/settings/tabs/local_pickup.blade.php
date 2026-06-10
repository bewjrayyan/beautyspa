@component('setting::admin.settings.partials.settings-wrap')
    <div class="st-enable-card">
        {{ Form::checkbox('local_pickup_enabled', trans('setting::attributes.local_pickup_enabled'), trans('setting::settings.form.enable_local_pickup'), $errors, $settings) }}
    </div>

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-map-marker',
        'title' => trans('setting::settings.sections.display'),
        'class' => 'st-section--compact',
    ])
        @component('setting::admin.settings.partials.fields-grid')
            @slot('left')
                {{ Form::text('translatable[local_pickup_label]', trans('setting::attributes.translatable.local_pickup_label'), $errors, $settings, ['required' => true]) }}
            @endslot
            @slot('right')
                {{ Form::number('local_pickup_cost', trans('setting::attributes.local_pickup_cost'), $errors, $settings, ['min' => 0, 'required' => true]) }}
            @endslot
        @endcomponent
    @endcomponent
@endcomponent
