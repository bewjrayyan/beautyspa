@php
    use Modules\Setting\Support\MaintenancePageSettings;

    $storeThemeColor = function_exists('storefront_theme_color') ? storefront_theme_color() : '#ff749f';
    $isCustomPreset = ($settings['maintenance_page_effect_preset'] ?? MaintenancePageSettings::PRESET_AESTHETIC) === MaintenancePageSettings::PRESET_CUSTOM;
@endphp

@component('setting::admin.settings.partials.settings-wrap')
    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-wrench',
        'title' => trans('setting::settings.tabs.maintenance'),
    ])
        {{ Form::checkbox('maintenance_mode', trans('setting::attributes.maintenance_mode'), trans('setting::settings.form.put_the_application_into_maintenance_mode'), $errors, $settings) }}
    @endcomponent

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-paint-brush',
        'title' => trans('setting::settings.form.maintenance_page_appearance_title'),
        'description' => trans('setting::settings.form.maintenance_page_appearance_help'),
    ])
        {{ Form::select('maintenance_page_effect_preset', trans('setting::attributes.maintenance_page_effect_preset'), $errors, MaintenancePageSettings::presetOptions(), $settings, [
            'help' => trans('setting::settings.form.maintenance_page_effect_preset_help'),
        ]) }}

        <p class="maintenance-preset-note{{ $isCustomPreset ? ' hide' : '' }}" id="maintenance-preset-note">
            {{ trans('setting::settings.form.maintenance_page_preset_locked_help') }}
        </p>

        {{ Form::select('maintenance_page_color_source', trans('setting::attributes.maintenance_page_color_source'), $errors, MaintenancePageSettings::colorSourceOptions(), $settings, [
            'help' => trans('setting::settings.form.maintenance_page_color_source_help', ['color' => $storeThemeColor]),
        ]) }}

        <div id="maintenance-custom-color-field" class="maintenance-custom-color-field">
            {{ Form::color('maintenance_page_accent_color', trans('setting::attributes.maintenance_page_accent_color'), $errors, $settings, [
                'default' => $storeThemeColor,
                'help' => trans('setting::settings.form.maintenance_page_accent_color_help'),
            ]) }}
        </div>

        <div id="maintenance-custom-effects-panel" class="maintenance-custom-effects-panel{{ $isCustomPreset ? '' : ' hide' }}">
            <h6 class="maintenance-custom-effects-panel__title">{{ trans('setting::settings.form.maintenance_page_effects_title') }}</h6>
            <p class="maintenance-custom-effects-panel__help">{{ trans('setting::settings.form.maintenance_page_effects_help') }}</p>

            <div id="maintenance-custom-effects-fields">
                {{ Form::checkbox('maintenance_page_gradient_enabled', trans('setting::attributes.maintenance_page_gradient_enabled'), trans('setting::settings.form.maintenance_page_gradient_enabled_label'), $errors, $settings, [
                    'help' => '<span id="maintenance-gradient-help"></span>',
                ]) }}
                {{ Form::checkbox('maintenance_page_bokeh_enabled', trans('setting::attributes.maintenance_page_bokeh_enabled'), trans('setting::settings.form.maintenance_page_bokeh_enabled'), $errors, $settings) }}

                <div id="maintenance-bokeh-count-field">
                    {{ Form::number('maintenance_page_bokeh_count', trans('setting::attributes.maintenance_page_bokeh_count'), $errors, $settings, [
                        'min' => 1,
                        'max' => 12,
                        'help' => trans('setting::settings.form.maintenance_page_bokeh_count_help'),
                    ]) }}
                </div>

                {{ Form::checkbox('maintenance_page_shimmer_enabled', trans('setting::attributes.maintenance_page_shimmer_enabled'), trans('setting::settings.form.maintenance_page_shimmer_enabled'), $errors, $settings) }}
                {{ Form::checkbox('maintenance_page_grain_drift_enabled', trans('setting::attributes.maintenance_page_grain_drift_enabled'), trans('setting::settings.form.maintenance_page_grain_drift_enabled'), $errors, $settings) }}
                {{ Form::checkbox('maintenance_page_frosted_card_enabled', trans('setting::attributes.maintenance_page_frosted_card_enabled'), trans('setting::settings.form.maintenance_page_frosted_card_enabled'), $errors, $settings) }}
            </div>
        </div>

        <div class="maintenance-page-preview" id="maintenance-page-preview" aria-hidden="true">
            <div
                class="maintenance-page-preview__canvas"
                id="maintenance-page-preview-canvas"
                data-store-color="{{ $storeThemeColor }}"
                data-gradient-help-store="{{ trans('setting::settings.form.maintenance_page_gradient_help_store', ['color' => ':color']) }}"
                data-gradient-help-custom="{{ trans('setting::settings.form.maintenance_page_gradient_help_custom', ['color' => ':color']) }}"
            >
                <div class="maintenance-page-preview__orb maintenance-page-preview__orb--1"></div>
                <div class="maintenance-page-preview__orb maintenance-page-preview__orb--2"></div>
                <div class="maintenance-page-preview__card">{{ trans('setting::settings.form.maintenance_page_preview_card') }}</div>
            </div>
            <p class="help-block">{{ trans('setting::settings.form.maintenance_page_preview_help') }}</p>
        </div>
    @endcomponent
@endcomponent
