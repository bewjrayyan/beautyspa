@php
    use Modules\GoogleIntegration\Support\GoogleSheetsColumnConfig;
    use Modules\GoogleIntegration\Support\GoogleSheetsStatusConfig;

    $perStatusEnabled = (bool) old('google_sheets_per_status_columns_enabled', array_get($settings, 'google_sheets_per_status_columns_enabled'));
    $statuses = GoogleSheetsStatusConfig::statuses();
    $showTitle = $showTitle ?? true;
@endphp

<div class="google-sheets-per-status-columns">
    @if ($showTitle)
        <h4 class="section-title">{{ trans('setting::settings.form.google_sheets_per_status_columns_title') }}</h4>
        <p class="help-block text-muted gs-settings__field-hint">{{ trans('setting::settings.form.google_sheets_per_status_columns_intro') }}</p>
    @endif

    <div class="st-enable-card gs-settings__enable-card--compact">
        {{ Form::checkbox('google_sheets_per_status_columns_enabled', trans('setting::attributes.google_sheets_per_status_columns_enabled'), trans('setting::settings.form.google_sheets_per_status_columns_enable'), $errors, $settings) }}
    </div>

    <div class="{{ $perStatusEnabled ? '' : 'hide' }}" id="google-sheets-per-status-columns-panel">
        <p class="help-block text-muted gs-settings__field-hint">{{ trans('setting::settings.form.google_sheets_per_status_columns_help') }}</p>

        <div class="gs-accordion" id="google-sheets-per-status-columns-accordion">
            @foreach ($statuses as $status => $label)
                @php
                    $columnInputName = GoogleSheetsColumnConfig::statusColumnsKey($status);
                    $panelId = 'google-sheets-status-columns-' . $status;
                @endphp
                <details class="gs-accordion__item">
                    <summary class="gs-accordion__summary">
                        <span class="gs-accordion__title">{{ $label }}</span>
                        <span class="gs-accordion__hint">{{ trans('setting::settings.form.google_sheets_per_status_columns_expand') }}</span>
                    </summary>
                    <div class="gs-accordion__body" id="{{ $panelId }}">
                        @include('setting::admin.settings.partials.google_sheets_columns', [
                            'columnInputName' => $columnInputName,
                            'columnsScope' => $status,
                            'columnsTitle' => trans('setting::settings.form.google_sheets_per_status_columns_status_title', ['status' => $label]),
                            'columnsIntro' => trans('setting::settings.form.google_sheets_per_status_columns_status_intro'),
                            'columnsHelp' => trans('setting::settings.form.google_sheets_per_status_columns_status_help'),
                            'showTitle' => true,
                            'emptyValueUsesGlobal' => true,
                        ])
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</div>
