@php
    $setupLinks = [
        'cloud_console' => 'https://console.cloud.google.com/',
        'api_library' => 'https://console.cloud.google.com/apis/library',
        'sheets_api' => 'https://console.cloud.google.com/apis/library/sheets.googleapis.com',
        'calendar_api' => 'https://console.cloud.google.com/apis/library/calendar-json.googleapis.com',
        'credentials' => 'https://console.cloud.google.com/apis/credentials',
        'google_sheets' => 'https://sheets.google.com/',
    ];
@endphp

<details class="google-sheets-setup-guide">
    <summary class="google-sheets-setup-guide__summary">
        <i class="fa fa-book" aria-hidden="true"></i>
        {{ trans('setting::settings.form.google_sheets_setup_title') }}
    </summary>

    <div class="google-sheets-setup-guide__body">
        <p class="google-sheets-setup-guide__intro">{{ trans('setting::settings.form.google_sheets_setup_intro') }}</p>

        <ol class="google-sheets-setup-guide__steps">
            <li>{!! trans('setting::settings.form.google_sheets_setup_step_1', $setupLinks) !!}</li>
            <li>{!! trans('setting::settings.form.google_sheets_setup_step_2', $setupLinks) !!}</li>
            <li>{!! trans('setting::settings.form.google_sheets_setup_step_3', $setupLinks) !!}</li>
            <li>{!! trans('setting::settings.form.google_sheets_setup_step_4', $setupLinks) !!}</li>
            <li>{!! trans('setting::settings.form.google_sheets_setup_step_5', $setupLinks) !!}</li>
            <li>{{ trans('setting::settings.form.google_sheets_setup_step_6') }}</li>
            <li>{!! trans('setting::settings.form.google_sheets_setup_step_7', $setupLinks) !!}</li>
            <li>{{ trans('setting::settings.form.google_sheets_setup_step_8') }}</li>
        </ol>

        <p class="google-sheets-setup-guide__security">
            <i class="fa fa-lock" aria-hidden="true"></i>
            {{ trans('setting::settings.form.google_sheets_setup_security') }}
        </p>
    </div>
</details>
