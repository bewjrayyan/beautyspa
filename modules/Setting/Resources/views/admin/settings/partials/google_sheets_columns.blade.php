@php
    use Modules\GoogleIntegration\Support\GoogleSheetsColumnConfig;

    $stored = old('google_sheets_columns', setting('google_sheets_columns'));
    $decoded = is_string($stored) ? json_decode($stored, true) : $stored;
    $enabledKeys = is_array($decoded)
        ? array_values(array_filter($decoded, fn ($key) => is_string($key) && array_key_exists($key, GoogleSheetsColumnConfig::definitions())))
        : GoogleSheetsColumnConfig::enabledKeys();

    if ($enabledKeys === []) {
        $enabledKeys = GoogleSheetsColumnConfig::defaultEnabledKeys();
    }

    $allKeys = array_keys(GoogleSheetsColumnConfig::definitions());
    $orderedKeys = array_values(array_unique(array_merge(
        $enabledKeys,
        array_values(array_diff($allKeys, $enabledKeys)),
    )));
@endphp

<div class="google-sheets-columns box-content clearfix">
    <h4 class="section-title">{{ trans('setting::settings.form.google_sheets_columns_title') }}</h4>
    <p class="help-block text-muted">{{ trans('setting::settings.form.google_sheets_columns_intro') }}</p>

    <input
        type="hidden"
        name="google_sheets_columns"
        id="google-sheets-columns-input"
        value="{{ json_encode($enabledKeys) }}"
    >

    <div class="table-responsive">
        <table class="table table-striped google-sheets-columns__table">
            <thead>
                <tr>
                    <th class="google-sheets-columns__order-col">{{ trans('setting::settings.form.google_sheets_columns_order') }}</th>
                    <th>{{ trans('setting::settings.form.google_sheets_columns_name') }}</th>
                    <th class="text-center">{{ trans('setting::settings.form.google_sheets_columns_include') }}</th>
                </tr>
            </thead>
            <tbody id="google-sheets-columns-list">
                @foreach ($orderedKeys as $key)
                    <tr class="google-sheets-columns__row" data-column-key="{{ $key }}">
                        <td class="google-sheets-columns__order">
                            <button type="button" class="btn btn-default btn-xs google-sheets-columns__move-up" title="{{ trans('setting::settings.form.google_sheets_columns_move_up') }}">
                                <i class="fa fa-arrow-up" aria-hidden="true"></i>
                            </button>
                            <button type="button" class="btn btn-default btn-xs google-sheets-columns__move-down" title="{{ trans('setting::settings.form.google_sheets_columns_move_down') }}">
                                <i class="fa fa-arrow-down" aria-hidden="true"></i>
                            </button>
                        </td>
                        <td class="google-sheets-columns__label">{{ GoogleSheetsColumnConfig::label($key) }}</td>
                        <td class="text-center google-sheets-columns__enabled">
                            <input
                                type="checkbox"
                                class="google-sheets-columns__checkbox"
                                value="1"
                                @checked(in_array($key, $enabledKeys, true))
                            >
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="help-block text-muted">{{ trans('setting::settings.form.google_sheets_columns_help') }}</p>
</div>
