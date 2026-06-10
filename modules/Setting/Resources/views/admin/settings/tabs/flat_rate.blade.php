@component('setting::admin.settings.partials.settings-wrap')
    <div class="st-enable-card">
        {{ Form::checkbox('flat_rate_enabled', trans('setting::attributes.flat_rate_enabled'), trans('setting::settings.form.enable_flat_rate'), $errors, $settings) }}
    </div>

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-send',
        'title' => trans('setting::settings.sections.display'),
        'class' => 'st-section--compact',
    ])
        @component('setting::admin.settings.partials.fields-grid')
            @slot('left')
                {{ Form::text('translatable[flat_rate_label]', trans('setting::attributes.translatable.flat_rate_label'), $errors, $settings, ['required' => true]) }}
            @endslot
            @slot('right')
                {{ Form::number('flat_rate_cost', trans('setting::attributes.flat_rate_cost'), $errors, $settings, ['min' => 0, 'required' => true]) }}
            @endslot
        @endcomponent
    @endcomponent
@endcomponent
