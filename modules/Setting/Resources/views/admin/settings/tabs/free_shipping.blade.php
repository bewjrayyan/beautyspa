<div class="row st-gateway">
    <div class="col-md-12">
        <div class="st-enable-card">
            {{ Form::checkbox('free_shipping_enabled', ' ', trans('setting::settings.form.enable_free_shipping'), $errors, $settings, ['labelCol' => 0]) }}
        </div>

        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-truck',
            'title' => trans('setting::settings.sections.display'),
            'class' => 'st-section--compact',
        ])
            @component('setting::admin.settings.partials.fields-grid')
                @slot('left')
                    {{ Form::text('translatable[free_shipping_label]', trans('setting::attributes.translatable.free_shipping_label'), $errors, $settings, ['required' => true]) }}
                @endslot
                @slot('right')
                    {{ Form::number('free_shipping_min_amount', trans('setting::attributes.free_shipping_min_amount'), $errors, $settings) }}
                @endslot
            @endcomponent
        @endcomponent
    </div>
</div>
