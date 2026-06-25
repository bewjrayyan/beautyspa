@php
    use Modules\GoogleIntegration\Services\GoogleCalendarService;
    use Modules\GoogleIntegration\Services\GoogleServiceAccountClient;
    use Modules\Order\Entities\Order;

    $calendarEnabled = (bool) old('google_calendar_enabled', array_get($settings, 'google_calendar_enabled'));
    $credentialsConfigured = GoogleServiceAccountClient::isConfigured();
    $sheetsSettingsUrl = route('admin.settings.edit', ['tab' => 'google_sheets']);
    $pendingCalendarSyncCount = GoogleCalendarService::isEnabled()
        ? Order::query()
            ->where('status', Order::COMPLETED)
            ->whereNotNull('appointment_date')
            ->whereNull('google_calendar_event_id')
            ->count()
        : 0;
@endphp

<div class="st-tab st-tab--google-calendar settings-form" data-google-calendar-settings>
    <p class="st-tab__lead">{{ trans('setting::settings.tab_leads.google_calendar') }}</p>

    <div class="gs-settings">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-key',
            'title' => trans('setting::settings.form.google_calendar_credentials_title'),
            'description' => trans('setting::settings.form.google_calendar_credentials_intro'),
            'class' => 'gs-settings__section gs-settings__section--credentials',
        ])
            <div class="gc-credentials-card {{ $credentialsConfigured ? 'is-ready' : 'is-missing' }}">
                <div class="gc-credentials-card__status">
                    <i class="fa {{ $credentialsConfigured ? 'fa-check-circle' : 'fa-exclamation-circle' }}" aria-hidden="true"></i>
                    <span>
                        {{ $credentialsConfigured
                            ? trans('setting::settings.form.google_calendar_credentials_ready')
                            : trans('setting::settings.form.google_calendar_credentials_missing') }}
                    </span>
                </div>
                <p class="help-block text-muted gs-settings__field-hint">{{ trans('setting::settings.form.google_calendar_credentials_help') }}</p>
                <a href="{{ $sheetsSettingsUrl }}" class="btn btn-default btn-sm">
                    <i class="fa fa-table" aria-hidden="true"></i>
                    {{ trans('setting::settings.form.google_calendar_open_sheets_tab') }}
                </a>
            </div>
        @endcomponent

        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-calendar',
            'title' => trans('setting::settings.form.google_calendar_settings'),
            'description' => trans('setting::settings.form.google_calendar_panel_intro'),
            'class' => 'gs-settings__section gs-settings__section--calendar',
        ])
            <div class="st-enable-card gs-settings__enable-card--compact">
                {{ Form::checkbox('google_calendar_enabled', trans('setting::attributes.google_calendar_enabled'), trans('setting::settings.form.enable_google_calendar_sync'), $errors, $settings) }}
            </div>

            <div class="{{ $calendarEnabled ? '' : 'hide' }}" id="google-calendar-fields">
                {{ Form::text('google_calendar_id', trans('setting::attributes.google_calendar_id'), $errors, $settings, [
                    'placeholder' => 'xxxx@group.calendar.google.com',
                ]) }}
                <p class="help-block text-muted gs-settings__field-hint">{{ trans('setting::settings.form.google_calendar_id_help') }}</p>

                <div class="st-notice gs-settings__notice">
                    <i class="fa fa-share-alt" aria-hidden="true"></i>
                    <p>{{ trans('setting::settings.form.google_calendar_share_help') }}</p>
                </div>

                <div class="gs-settings__actions">
                    <button
                        type="button"
                        class="btn btn-default"
                        id="google-calendar-test-btn"
                        data-test-url="{{ route('admin.settings.google_calendar.test_connection') }}"
                        data-testing-text="{{ trans('setting::settings.form.google_calendar_test_connection_running') }}"
                    >
                        <i class="fa fa-plug" aria-hidden="true"></i>
                        {{ trans('setting::settings.form.google_calendar_test_connection') }}
                    </button>
                    <p class="gs-settings__actions-hint">{{ trans('setting::settings.form.google_calendar_test_connection_help') }}</p>
                </div>

                <div id="google-calendar-test-result" class="google-sheets-test-result hide" role="status" aria-live="polite"></div>

                <div class="google-calendar-sync-all-wrap gs-action-card">
                    <div class="gs-action-card__head">
                        <div>
                            <h6 class="gs-action-card__title">{{ trans('setting::settings.form.google_calendar_sync_all') }}</h6>
                            <p class="gs-action-card__desc">{{ trans('setting::settings.form.google_calendar_sync_all_help') }}</p>
                        </div>
                        <button
                            type="button"
                            class="btn btn-primary"
                            id="google-calendar-sync-all-btn"
                            data-sync-url="{{ route('admin.settings.google_calendar.sync_all_chunk') }}"
                            data-count-url="{{ route('admin.settings.google_calendar.sync_all_count') }}"
                            data-chunk-size="25"
                            data-syncing-text="{{ trans('setting::settings.form.google_calendar_sync_all_running') }}"
                            data-confirm-text="{{ trans('setting::settings.form.google_calendar_sync_all_confirm') }}"
                        >
                            <i class="fa fa-refresh" aria-hidden="true"></i>
                            {{ trans('setting::settings.form.google_calendar_sync_all') }}
                        </button>
                    </div>

                    @if ($pendingCalendarSyncCount > 0)
                        <p class="help-block text-muted gs-settings__field-hint">
                            {{ trans('setting::settings.form.google_calendar_sync_all_pending', ['count' => number_format($pendingCalendarSyncCount)]) }}
                        </p>
                    @endif

                    <div id="google-calendar-sync-all-progress" class="google-sheets-sync-all-progress hide" aria-hidden="true">
                        <div class="progress">
                            <div
                                id="google-calendar-sync-all-progress-bar"
                                class="progress-bar progress-bar-striped active"
                                role="progressbar"
                                style="width: 0%"
                            >
                                <span id="google-calendar-sync-all-progress-label">0%</span>
                            </div>
                        </div>
                    </div>
                    <div id="google-calendar-sync-all-result" class="google-sheets-test-result hide" role="status" aria-live="polite"></div>
                </div>
            </div>
        @endcomponent

        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-info-circle',
            'title' => trans('setting::settings.form.google_calendar_workflow_title'),
            'description' => trans('setting::settings.form.google_calendar_workflow_intro'),
            'class' => 'gs-settings__section gs-settings__section--workflow',
        ])
            <ul class="gc-workflow-list">
                <li>{{ trans('setting::settings.form.google_calendar_workflow_step_1') }}</li>
                <li>{{ trans('setting::settings.form.google_calendar_workflow_step_2') }}</li>
                <li>{{ trans('setting::settings.form.google_calendar_workflow_step_3') }}</li>
                <li>{{ trans('setting::settings.form.google_calendar_workflow_step_4') }}</li>
            </ul>
        @endcomponent
    </div>
</div>
