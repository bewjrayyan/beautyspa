@component('setting::admin.settings.partials.settings-wrap')
    <div class="st-enable-card">
        {{ Form::checkbox('pwa_enabled', trans('setting::attributes.pwa_enabled'), trans('setting::settings.form.enable_pwa'), $errors, $settings) }}
    </div>

    @component('setting::admin.settings.partials.fields-grid', ['class' => 'st-fields-grid--sections'])
        @slot('left')
            @component('setting::admin.settings.partials.section', [
                'icon' => 'fa-mobile',
                'title' => trans('setting::settings.form.pwa_icon'),
                'class' => 'st-section--media',
            ])
                @include('media::admin.image_picker.single', [
                    'title' => trans('setting::settings.form.pwa_icon'),
                    'inputName' => 'pwa_icon',
                    'file' => $icon,
                ])
            @endcomponent
        @endslot
        @slot('right')
            @component('setting::admin.settings.partials.section', [
                'icon' => 'fa-paint-brush',
                'title' => trans('setting::settings.sections.pwa_appearance'),
            ])
                {{ Form::color('pwa_theme_color', trans('setting::attributes.pwa_theme_color'), $errors, $settings, ['default' => config('pwa.manifest.theme_color', '#0068e1')]) }}
                {{ Form::color('pwa_background_color', trans('setting::attributes.pwa_background_color'), $errors, $settings, ['default' => config('pwa.manifest.background_color', '#ffffff')]) }}
                {{ Form::color('pwa_status_bar', trans('setting::attributes.pwa_status_bar'), $errors, $settings, ['default' => config('pwa.manifest.status_bar', '#0068e1')]) }}
            @endcomponent
        @endslot
        @slot('full')
            @component('setting::admin.settings.partials.section', [
                'icon' => 'fa-cog',
                'title' => trans('setting::settings.sections.pwa_behavior'),
                'columns' => 2,
            ])
                {{ Form::select('pwa_display', trans('setting::attributes.pwa_display'), $errors, $displays, $settings) }}
                {{ Form::select('pwa_orientation', trans('setting::attributes.pwa_orientation'), $errors, $orientations, $settings) }}
                {{ Form::select('translatable[pwa_direction]', trans('setting::attributes.pwa_direction'), $errors, $directions, $settings) }}
            @endcomponent
        @endslot
    @endcomponent
@endcomponent
