@php
    $meta = $appVersionMeta ?? [];
    $localVersion = $meta['local_version'] ?? \AestheticCart\AestheticCart::VERSION;
    $git = $meta['git'] ?? ['available' => false];
    $github = $meta['github'] ?? null;
    $gitRemoteVersion = $git['remote_version'] ?? null;
    $githubRemoteVersion = $github['version'] ?? null;
    $latestVersion = $gitRemoteVersion ?: $githubRemoteVersion;
    $latestSource = $gitRemoteVersion ? 'git' : ($githubRemoteVersion ? 'github' : null);
    $updateAvailable = $latestSource === 'git'
        ? ! empty($git['update_available'])
        : ($latestSource === 'github' ? ! empty($github['update_available']) : false);
    $commitsBehind = (int) ($git['commits_behind'] ?? 0);
    $latestCommit = $git['remote_commit'] ?? ($github['commit'] ?? null);
    $githubCheckedAt = $github['checked_at'] ?? null;
    $installedNotes = $meta['installed_notes'] ?? null;
    $pendingNotes = $meta['pending_notes'] ?? null;
    $recentNotes = $meta['recent_notes'] ?? [];
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

            @if ($latestVersion)
                <div class="app-version-card {{ $updateAvailable ? 'is-out-of-sync' : 'is-synced' }}">
                    <span class="app-version-card__label">{{ trans('setting::settings.form.app_version_latest') }}</span>
                    <strong class="app-version-card__value">v{{ $latestVersion }}</strong>
                    <span class="app-version-card__hint">
                        @if ($updateAvailable && $latestSource === 'git')
                            {{ trans('setting::settings.form.app_version_update_available', ['count' => $commitsBehind]) }}
                        @elseif ($updateAvailable && $latestSource === 'github')
                            {{ trans('setting::settings.form.app_version_github_update_available') }}
                        @else
                            {{ trans('setting::settings.form.app_version_up_to_date') }}
                        @endif

                        @if ($latestSource === 'github')
                            · {{ trans('setting::settings.form.app_version_github_source') }}
                        @endif
                    </span>
                </div>
            @else
                <div class="app-version-card">
                    <span class="app-version-card__label">{{ trans('setting::settings.form.app_version_latest') }}</span>
                    <strong class="app-version-card__value">—</strong>
                    <span class="app-version-card__hint">{{ trans('setting::settings.form.app_version_latest_check_github') }}</span>
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
            @elseif ($latestCommit)
                <div class="app-version-card">
                    <span class="app-version-card__label">{{ trans('setting::settings.form.app_version_github_commit') }}</span>
                    <strong class="app-version-card__value">{{ $latestCommit }}</strong>
                    <span class="app-version-card__hint">
                        {{ trans('setting::settings.form.app_version_github_branch', ['branch' => $github['branch'] ?? 'main']) }}
                        @if ($githubCheckedAt)
                            · {{ trans('setting::settings.form.app_version_github_checked_at', ['time' => \Illuminate\Support\Carbon::parse($githubCheckedAt)->diffForHumans()]) }}
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

        <div class="app-version-actions">
            <button
                type="submit"
                name="app_version_action"
                value="check_github"
                class="btn btn-default"
                formnovalidate
            >
                <i class="fa fa-github"></i>
                {{ trans('setting::settings.form.app_version_check_github') }}
            </button>
        </div>

        @if (! empty($github['repository_url']))
            <p class="help-block text-muted app-version-workflow">
                {{ trans('setting::settings.form.app_version_github_repo', ['repo' => $github['repo'] ?? '']) }}
                <a href="{{ $github['repository_url'] }}" target="_blank" rel="noopener noreferrer">{{ $github['repository_url'] }}</a>
            </p>
        @endif
    @endcomponent

    @if ($installedNotes || $pendingNotes || ! empty($recentNotes))
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-list-alt',
            'title' => trans('setting::settings.sections.app_release_notes'),
            'description' => trans('setting::settings.form.app_version_release_notes_help'),
            'class' => 'st-section--release-notes',
        ])
            @if ($installedNotes)
                <div class="app-release-notes-block">
                    <span class="app-release-notes-block__label">{{ trans('setting::settings.form.app_version_installed_notes') }}</span>
                    @include('setting::admin.settings.partials.release-notes', ['entry' => $installedNotes])
                </div>
            @endif

            @if ($pendingNotes)
                <div class="app-release-notes-block">
                    <span class="app-release-notes-block__label">{{ trans('setting::settings.form.app_version_pending_notes') }}</span>
                    @include('setting::admin.settings.partials.release-notes', ['entry' => $pendingNotes, 'highlight' => true])
                </div>
            @endif

            @if (! empty($recentNotes))
                <details class="app-release-notes-history">
                    <summary>{{ trans('setting::settings.form.app_version_release_history') }}</summary>
                    <div class="app-release-notes-history__body">
                        @foreach ($recentNotes as $note)
                            @include('setting::admin.settings.partials.release-notes', ['entry' => $note])
                        @endforeach
                    </div>
                </details>
            @endif
        @endcomponent
    @endif

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
    @else
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-cloud-download',
            'title' => trans('setting::settings.sections.app_version_deploy'),
            'description' => trans('setting::settings.form.app_version_deploy_help'),
        ])
            <div class="app-version-actions">
                <button
                    type="submit"
                    name="app_version_action"
                    value="github_update"
                    class="btn btn-primary"
                    formnovalidate
                    onclick="return confirm(@json(trans('setting::settings.form.app_version_github_update_confirm')));"
                >
                    <i class="fa fa-cloud-download"></i>
                    {{ trans('setting::settings.form.app_version_github_update') }}
                </button>

                <button
                    type="submit"
                    name="app_version_action"
                    value="sync_version"
                    class="btn btn-default"
                    formnovalidate
                >
                    <i class="fa fa-refresh"></i>
                    {{ trans('setting::settings.form.app_version_sync_installed') }}
                </button>
            </div>

            <p class="help-block text-muted app-version-workflow">{{ trans('setting::settings.form.app_version_github_update_workflow') }}</p>
            <p class="help-block text-muted app-version-workflow">{{ trans('setting::settings.form.app_version_deploy_workflow') }}</p>
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

    @include('setting::admin.settings.partials.catalog_sync', ['catalogSync' => $catalogSync ?? []])
</div>
