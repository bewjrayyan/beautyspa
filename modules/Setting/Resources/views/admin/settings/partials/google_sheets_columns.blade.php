@php
    use Modules\GoogleIntegration\Support\GoogleSheetsColumnConfig;

    $columnInputName = $columnInputName ?? 'google_sheets_columns';
    $columnsScope = $columnsScope ?? 'global';
    $columnsTitle = $columnsTitle ?? trans('setting::settings.form.google_sheets_columns_title');
    $columnsIntro = $columnsIntro ?? trans('setting::settings.form.google_sheets_columns_intro');
    $columnsHelp = $columnsHelp ?? trans('setting::settings.form.google_sheets_columns_help');
    $showTitle = $showTitle ?? true;
    $emptyValueUsesGlobal = $emptyValueUsesGlobal ?? false;

    $stored = old($columnInputName, setting($columnInputName));
    $hasStoredOverride = is_string($stored)
        ? trim($stored) !== '' && trim($stored) !== '[]'
        : ($stored !== null && $stored !== []);

    if ($emptyValueUsesGlobal && ! $hasStoredOverride) {
        $enabledKeys = GoogleSheetsColumnConfig::enabledKeys();
        $inputValue = '[]';
    } else {
        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;
        $enabledKeys = is_array($decoded)
            ? array_values(array_filter($decoded, fn ($key) => is_string($key) && array_key_exists($key, GoogleSheetsColumnConfig::definitions())))
            : GoogleSheetsColumnConfig::enabledKeys();

        if ($enabledKeys === []) {
            $enabledKeys = GoogleSheetsColumnConfig::defaultEnabledKeys();
        }

        $inputValue = json_encode($enabledKeys);
    }

    $allKeys = array_keys(GoogleSheetsColumnConfig::definitions());
    $orderedKeys = array_values(array_unique(array_merge(
        $enabledKeys,
        array_values(array_diff($allKeys, $enabledKeys)),
    )));
    $enabledCount = count($enabledKeys);
@endphp

<div class="google-sheets-columns" data-google-sheets-columns-root data-columns-scope="{{ $columnsScope }}">
    @if ($showTitle)
        <div class="gs-subsection-head">
            <h4 class="section-title">{{ $columnsTitle }}</h4>
            <span class="gs-count-badge" data-count-template="{{ trans('setting::settings.form.google_sheets_columns_selected', ['count' => ':count']) }}">{{ trans('setting::settings.form.google_sheets_columns_selected', ['count' => $enabledCount]) }}</span>
        </div>
        <p class="help-block text-muted gs-settings__field-hint">{{ $columnsIntro }}</p>
    @endif

    <input
        type="hidden"
        name="{{ $columnInputName }}"
        class="google-sheets-columns__input"
        value="{{ $inputValue }}"
    >

    <div class="table-responsive gs-table-wrap">
        <table class="table gs-table google-sheets-columns__table">
            <thead>
                <tr>
                    <th class="google-sheets-columns__order-col">{{ trans('setting::settings.form.google_sheets_columns_order') }}</th>
                    <th>{{ trans('setting::settings.form.google_sheets_columns_name') }}</th>
                    <th class="text-center">{{ trans('setting::settings.form.google_sheets_columns_include') }}</th>
                </tr>
            </thead>
            <tbody class="google-sheets-columns__list">
                @foreach ($orderedKeys as $key)
                    <tr class="google-sheets-columns__row {{ in_array($key, $enabledKeys, true) ? 'is-included' : '' }}" data-column-key="{{ $key }}">
                        <td class="google-sheets-columns__order">
                            <div class="gs-order-btns">
                                <button type="button" class="btn btn-default btn-xs google-sheets-columns__move-up" title="{{ trans('setting::settings.form.google_sheets_columns_move_up') }}">
                                    <i class="fa fa-chevron-up" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="btn btn-default btn-xs google-sheets-columns__move-down" title="{{ trans('setting::settings.form.google_sheets_columns_move_down') }}">
                                    <i class="fa fa-chevron-down" aria-hidden="true"></i>
                                </button>
                            </div>
                        </td>
                        <td class="google-sheets-columns__label">{{ GoogleSheetsColumnConfig::label($key) }}</td>
                        <td class="text-center google-sheets-columns__enabled">
                            <label class="gs-toggle-check">
                                <input
                                    type="checkbox"
                                    class="google-sheets-columns__checkbox"
                                    value="1"
                                    @checked(in_array($key, $enabledKeys, true))
                                >
                                <span class="gs-toggle-check__ui" aria-hidden="true"></span>
                            </label>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($showTitle)
        <p class="help-block text-muted gs-settings__field-hint">{{ $columnsHelp }}</p>
    @endif
</div>
