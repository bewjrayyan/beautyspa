@php
    use Modules\GoogleIntegration\Support\GoogleSheetsColumnConfig;
    use Modules\GoogleIntegration\Support\GoogleSheetsStatusConfig;

    $perStatusEnabled = (bool) old('google_sheets_per_status_columns_enabled', array_get($settings, 'google_sheets_per_status_columns_enabled'));
    $statuses = GoogleSheetsStatusConfig::statuses();
@endphp

<div class="google-sheets-per-status-columns box-content clearfix">
    <h4 class="section-title">{{ trans('setting::settings.form.google_sheets_per_status_columns_title') }}</h4>
    <p class="help-block text-muted">{{ trans('setting::settings.form.google_sheets_per_status_columns_intro') }}</p>

    {{ Form::checkbox('google_sheets_per_status_columns_enabled', trans('setting::attributes.google_sheets_per_status_columns_enabled'), trans('setting::settings.form.google_sheets_per_status_columns_enable'), $errors, $settings) }}

    <div class="{{ $perStatusEnabled ? '' : 'hide' }}" id="google-sheets-per-status-columns-panel">
        <p class="help-block text-muted">{{ trans('setting::settings.form.google_sheets_per_status_columns_help') }}</p>

        <div class="panel-group google-sheets-per-status-columns__accordion" id="google-sheets-per-status-columns-accordion">
            @foreach ($statuses as $status => $label)
                @php
                    $columnInputName = GoogleSheetsColumnConfig::statusColumnsKey($status);
                    $panelId = 'google-sheets-status-columns-' . $status;
                @endphp
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a
                                class="collapsed"
                                data-toggle="collapse"
                                data-parent="#google-sheets-per-status-columns-accordion"
                                href="#{{ $panelId }}"
                            >
                                {{ $label }}
                            </a>
                        </h4>
                    </div>
                    <div id="{{ $panelId }}" class="panel-collapse collapse">
                        <div class="panel-body">
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
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
