@php
    $meta = $appVersionMeta ?? [];
    $localVersion = $meta['local_version'] ?? \AestheticCart\AestheticCart::VERSION;
    $git = $meta['git'] ?? ['available' => false];
    $remoteVersion = $git['remote_version'] ?? null;
    $updateAvailable = ! empty($git['update_available']);
    $commitsBehind = (int) ($git['commits_behind'] ?? 0);
@endphp

<div class="settings-form">
    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-info-circle',
        'title' => trans('setting::settings.sections.app_version_status'),
        'description' => trans('setting::settings.form.app_version_status_help'),
        'class' => 'st-section--version-status',
    ])
        <div class="app-version-grid">
            <div class="app-version-card">
                <span class="app-version-card__label">{{ trans('setting::settings.form.app_version_installed') }}</span>
                <strong class="app-version-card__value">v{{ $localVersion }}</strong>
                <span class="app-version-card__hint">{{ trans('setting::settings.form.app_version_installed_help') }}</span>
            </div>

            @if (! empty($git['available']) && $remoteVersion)
                <div class="app-version-card {{ $updateAvailable ? 'is-out-of-sync' : 'is-synced' }}">
                    <span class="app-version-card__label">{{ trans('setting::settings.form.app_version_latest') }}</span>
                    <strong class="app-version-card__value">v{{ $remoteVersion }}</strong>
                    <span class="app-version-card__hint">
                        @if ($updateAvailable)
                            {{ trans('setting::settings.form.app_version_update_available', ['count' => $commitsBehind]) }}
                        @else
                            {{ trans('setting::settings.form.app_version_up_to_date') }}
                        @endif
                    </span>
                </div>
            @elseif (! empty($git['available']))
                <div class="app-version-card">
                    <span class="app-version-card__label">{{ trans('setting::settings.form.app_version_latest') }}</span>
                    <strong class="app-version-card__value">—</strong>
                    <span class="app-version-card__hint">{{ trans('setting::settings.form.app_version_latest_unknown') }}</span>
                </div>
            @endif

            @if (! empty($git['available']))
                <div class="app-version-card">
                    <span class="app-version-card__label">{{ trans('setting::settings.form.app_version_git') }}</span>
                    <strong class="app-version-card__value">{{ $git['commit'] ?? '—' }}</strong>
                    <span class="app-version-card__hint">
                        {{ trans('setting::settings.form.app_version_git_branch', ['branch' => $git['branch'] ?? '—']) }}
                        @if (! empty($git['remote_commit']))
                            · {{ trans('setting::settings.form.app_version_remote_commit', ['commit' => $git['remote_commit']]) }}
                        @endif
                    </span>
                </div>
            @else
                <div class="app-version-card">
                    <span class="app-version-card__label">{{ trans('setting::settings.form.app_version_git') }}</span>
                    <strong class="app-version-card__value">—</strong>
                    <span class="app-version-card__hint">{{ trans('setting::settings.form.app_version_git_unavailable') }}</span>
                </div>
            @endif
        </div>
    @endcomponent

    @if (! empty($git['available']))
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-cloud-download',
            'title' => trans('setting::settings.sections.app_version_pull'),
            'description' => trans('setting::settings.form.app_version_pull_help'),
        ])
            <div class="app-version-actions">
                <button
                    type="submit"
                    name="app_version_action"
                    value="pull_latest"
                    class="btn btn-primary"
                    formnovalidate
                    onclick="return confirm(@json(trans('setting::settings.form.app_version_pull_confirm')));"
                >
                    <i class="fa fa-cloud-download"></i>
                    {{ trans('setting::settings.form.app_version_pull_latest') }}
                </button>
            </div>

            <p class="help-block text-muted app-version-workflow">{{ trans('setting::settings.form.app_version_pull_workflow') }}</p>
        @endcomponent
    @endif

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-terminal',
        'title' => trans('setting::settings.sections.artisan_commands'),
        'description' => trans('setting::settings.form.artisan_commands_help'),
    ])
        <div class="artisan-command-grid">
            @foreach ($artisanCommands ?? [] as $command)
                <div class="artisan-command-card">
                    <div class="artisan-command-card__body">
                        <strong class="artisan-command-card__label">{{ $command['label'] }}</strong>
                        <p class="artisan-command-card__description">{{ $command['description'] }}</p>
                    </div>

                    <button
                        type="submit"
                        name="artisan_action"
                        value="{{ $command['key'] }}"
                        class="btn btn-default btn-sm artisan-command-card__button"
                        formnovalidate
                        @if ($command['confirm'])
                            onclick="return confirm(@json($command['confirm_message']));"
                        @endif
                    >
                        <i class="fa fa-play"></i>
                        {{ trans('setting::settings.form.artisan_run') }}
                    </button>
                </div>
            @endforeach
        </div>
    @endcomponent
</div>
