@component('setting::admin.settings.partials.settings-wrap')

    <div class="st-enable-card">
        {{ Form::checkbox('pwa_enabled', trans('setting::attributes.pwa_enabled'), trans('setting::settings.form.enable_pwa'), $errors, $settings) }}
        <p class="st-enable-card__hint">{{ trans('setting::settings.form.pwa_enabled_help') }}</p>
    </div>

    <div class="{{ old('pwa_enabled', array_get($settings, 'pwa_enabled')) ? '' : 'hide' }}" id="pwa-fields">
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
                    <p class="help-block text-muted">{{ trans('setting::settings.form.pwa_icon_help') }}</p>
                @endcomponent
            @endslot
            @slot('right')
                @component('setting::admin.settings.partials.section', [
                    'icon' => 'fa-paint-brush',
                    'title' => trans('setting::settings.sections.pwa_appearance'),
                ])
                    {{ Form::color('pwa_theme_color', trans('setting::attributes.pwa_theme_color'), $errors, $settings, ['default' => config('pwa.manifest.theme_color', '#0068e1')]) }}
                    {{ Form::color('pwa_background_color', trans('setting::attributes.pwa_background_color'), $errors, $settings, ['default' => config('pwa.manifest.background_color', '#ffffff')]) }}
                    {{ Form::select('pwa_status_bar', trans('setting::attributes.pwa_status_bar'), $errors, $statusBarStyles, $settings, ['default' => config('pwa.manifest.status_bar', 'black')]) }}
                    <p class="help-block text-muted">{{ trans('setting::settings.form.pwa_status_bar_help') }}</p>
                @endcomponent
            @endslot
            @slot('full')
                @component('setting::admin.settings.partials.section', [
                    'icon' => 'fa-cog',
                    'title' => trans('setting::settings.sections.pwa_behavior'),
                    'columns' => 2,
                ])
                    {{ Form::select('pwa_display', trans('setting::attributes.pwa_display'), $errors, $displays, $settings, ['default' => config('pwa.manifest.display', 'standalone')]) }}
                    {{ Form::select('pwa_orientation', trans('setting::attributes.pwa_orientation'), $errors, $orientations, $settings, ['default' => config('pwa.manifest.orientation', 'any')]) }}
                    {{ Form::select('translatable[pwa_direction]', trans('setting::attributes.pwa_direction'), $errors, $directions, $settings, ['default' => config('pwa.manifest.dir', 'auto')]) }}
                @endcomponent
            @endslot
        @endcomponent
    </div>
@endcomponent
