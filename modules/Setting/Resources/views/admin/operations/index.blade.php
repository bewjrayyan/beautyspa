@extends('admin::layout')

@section('title', trans('setting::operations.title'))

@section('content_header')
    <div class="operations-page-header">
        <div>
            <h3>{{ trans('setting::operations.title') }}</h3>
            <p>{{ trans('setting::operations.intro') }}</p>
        </div>
    </div>

    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li><a href="{{ route('admin.settings.edit') }}">{{ trans('setting::settings.settings') }}</a></li>
        <li class="active">{{ trans('setting::operations.title') }}</li>
    </ol>
@endsection

@section('content')
    <div class="operations-dashboard">
        <section class="operations-health-banner operations-health-banner--{{ $snapshot['healthy'] ? 'healthy' : 'warning' }}" aria-live="polite">
            <span class="operations-health-banner__icon" aria-hidden="true">
                <i class="fa {{ $snapshot['healthy'] ? 'fa-check' : 'fa-exclamation-triangle' }}"></i>
            </span>
            <div class="operations-health-banner__copy">
                <span class="operations-eyebrow">{{ trans('setting::operations.overall') }}</span>
                <strong>{{ trans($snapshot['healthy'] ? 'setting::operations.healthy' : 'setting::operations.degraded') }}</strong>
                <p>{{ trans('setting::operations.intro') }}</p>
            </div>
            <span class="operations-status-pill operations-status-pill--{{ $snapshot['healthy'] ? 'healthy' : 'warning' }}">
                <span></span>{{ trans($snapshot['healthy'] ? 'setting::operations.healthy' : 'setting::operations.degraded') }}
            </span>
        </section>

        <div class="operations-metrics">
            <article class="operations-metric operations-metric--{{ $snapshot['scheduler']['healthy'] ? 'success' : 'warning' }}">
                <span class="operations-metric__icon"><i class="fa fa-clock-o"></i></span>
                <div class="operations-metric__body">
                    <span class="operations-metric__label">{{ trans('setting::operations.scheduler') }}</span>
                    <strong class="operations-metric__value">{{ $snapshot['scheduler']['healthy'] ? 'OK' : '—' }}</strong>
                    <small>{{ $snapshot['scheduler']['last_seen_at'] ? trans('setting::operations.last_seen', ['time' => $snapshot['scheduler']['last_seen_at']]) : trans('setting::operations.never_seen') }}</small>
                </div>
            </article>

            <article class="operations-metric operations-metric--{{ $snapshot['queue']['pending'] > $snapshot['queue']['limits']['pending'] ? 'warning' : 'primary' }}">
                <span class="operations-metric__icon"><i class="fa fa-hourglass-half"></i></span>
                <div class="operations-metric__body">
                    <span class="operations-metric__label">{{ trans('setting::operations.pending') }}</span>
                    <strong class="operations-metric__value">{{ number_format($snapshot['queue']['pending']) }}</strong>
                    <small>{{ trans('setting::operations.oldest_minutes') }}: {{ $snapshot['queue']['oldest_minutes'] }}</small>
                </div>
            </article>

            <article class="operations-metric operations-metric--{{ $snapshot['queue']['failed'] > 0 ? 'danger' : 'success' }}">
                <span class="operations-metric__icon"><i class="fa fa-times-circle-o"></i></span>
                <div class="operations-metric__body">
                    <span class="operations-metric__label">{{ trans('setting::operations.failed') }}</span>
                    <strong class="operations-metric__value">{{ number_format($snapshot['queue']['failed']) }}</strong>
                    <small>{{ trans('setting::operations.queue') }}</small>
                </div>
            </article>

            <article class="operations-metric operations-metric--{{ $snapshot['queue']['stuck_onesender'] > 0 ? 'danger' : 'success' }}">
                <span class="operations-metric__icon"><i class="fa fa-whatsapp"></i></span>
                <div class="operations-metric__body">
                    <span class="operations-metric__label">{{ trans('setting::operations.stuck_onesender') }}</span>
                    <strong class="operations-metric__value">{{ number_format($snapshot['queue']['stuck_onesender']) }}</strong>
                    <small>OneSender</small>
                </div>
            </article>
        </div>

        <section class="operations-panel">
            <header class="operations-panel__header">
                <span class="operations-panel__icon operations-panel__icon--primary"><i class="fa fa-list-ul"></i></span>
                <div>
                    <h4>{{ trans('setting::operations.queue') }}</h4>
                    <p>{{ number_format($snapshot['queue']['pending']) }} {{ strtolower(trans('setting::operations.pending')) }}</p>
                </div>
            </header>
            <div class="operations-panel__body operations-panel__body--flush operations-table-wrap">
                <table class="table operations-table">
                    <thead><tr><th>{{ trans('setting::operations.job') }}</th><th>{{ trans('setting::operations.queue_name') }}</th><th>{{ trans('setting::operations.attempts') }}</th><th>{{ trans('setting::operations.queued_at') }}</th><th class="text-right">{{ trans('setting::operations.actions') }}</th></tr></thead>
                    <tbody>
                        @forelse($pendingJobs?->items() ?? [] as $job)
                            <tr>
                                <td><code class="operations-code">{{ $job->display_name }}</code></td>
                                <td><span class="operations-tag">{{ $job->queue }}</span></td>
                                <td>{{ $job->attempts }}</td>
                                <td>{{ date('Y-m-d H:i:s', $job->created_at) }}</td>
                                <td class="text-right">
                                    @if($canManageQueue && $job->reserved_at === null)
                                        <form class="operations-inline-form" method="POST" action="{{ route('admin.operations.queue.cancel', $job->id) }}" onsubmit="return confirm(@js(trans('setting::operations.cancel_confirm')));">
                                            @csrf @method('DELETE')
                                            <button class="btn operations-btn operations-btn--warning operations-btn--sm" type="submit"><i class="fa fa-ban"></i>{{ trans('setting::operations.cancel') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="operations-empty"><i class="fa fa-check-circle-o"></i><span>{{ trans('setting::operations.empty_pending') }}</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($pendingJobs && $pendingJobs->hasPages())
                    <div class="operations-pagination">{{ $pendingJobs->appends(request()->except('pending_page'))->links() }}</div>
                @endif
            </div>
        </section>

        <section class="operations-panel">
            <header class="operations-panel__header">
                <span class="operations-panel__icon operations-panel__icon--danger"><i class="fa fa-exclamation-circle"></i></span>
                <div>
                    <h4>{{ trans('setting::operations.failed') }}</h4>
                    <p>{{ number_format($snapshot['queue']['failed']) }} {{ strtolower(trans('setting::operations.failed')) }}</p>
                </div>
            </header>
            <div class="operations-panel__body operations-panel__body--flush operations-table-wrap">
                <table class="table operations-table">
                    <thead><tr><th>{{ trans('setting::operations.job') }}</th><th>{{ trans('setting::operations.queue_name') }}</th><th>{{ trans('setting::operations.failed_at') }}</th><th class="text-right">{{ trans('setting::operations.actions') }}</th></tr></thead>
                    <tbody>
                        @forelse($failedJobs?->items() ?? [] as $job)
                            <tr>
                                <td><code class="operations-code">{{ $job->display_name }}</code></td>
                                <td><span class="operations-tag">{{ $job->queue }}</span></td>
                                <td>{{ $job->failed_at }}</td>
                                <td class="text-right">
                                    @if($canManageQueue)
                                        <form class="operations-inline-form" method="POST" action="{{ route('admin.operations.failed.retry', $job->uuid) }}" onsubmit="return confirm(@js(trans('setting::operations.retry_confirm')));">
                                            @csrf
                                            <button class="btn operations-btn operations-btn--primary operations-btn--sm" type="submit"><i class="fa fa-repeat"></i>{{ trans('setting::operations.retry') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="operations-empty"><i class="fa fa-check-circle-o"></i><span>{{ trans('setting::operations.empty_failed') }}</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($failedJobs && $failedJobs->hasPages())
                    <div class="operations-pagination">{{ $failedJobs->appends(request()->except('failed_page'))->links() }}</div>
                @endif
            </div>
        </section>

        <div class="operations-grid operations-grid--retention">
            <section class="operations-panel">
                <header class="operations-panel__header">
                    <span class="operations-panel__icon operations-panel__icon--violet"><i class="fa fa-shield"></i></span>
                    <div><h4>{{ trans('setting::operations.retention') }}</h4><p>{{ trans($snapshot['retention']['enabled'] ? 'setting::operations.retention_enabled' : 'setting::operations.retention_disabled') }}</p></div>
                    <span class="operations-status-pill operations-status-pill--{{ $snapshot['retention']['enabled'] ? 'healthy' : 'neutral' }}"><span></span>{{ trans($snapshot['retention']['enabled'] ? 'setting::operations.healthy' : 'setting::operations.status') }}</span>
                </header>
                <div class="operations-panel__body">
                    <p class="operations-supporting-copy">{{ $snapshot['retention']['completed_days'] ? trans('setting::operations.completed_days', ['days' => $snapshot['retention']['completed_days']]) : trans('setting::operations.completed_disabled') }}</p>
                    @if($snapshot['retention']['preview'])
                        <div class="operations-mini-metrics">
                            @foreach($snapshot['retention']['preview'] as $key => $count)
                                <div><strong>{{ number_format($count) }}</strong><span>{{ trans('setting::operations.'.$key) }}</span></div>
                            @endforeach
                        </div>
                    @else
                        <div class="operations-empty operations-empty--compact"><i class="fa fa-info-circle"></i><span>{{ trans('setting::operations.preview_unavailable') }}</span></div>
                    @endif
                </div>
            </section>

            <section class="operations-panel operations-panel--action">
                <header class="operations-panel__header">
                    <span class="operations-panel__icon operations-panel__icon--warning"><i class="fa fa-lock"></i></span>
                    <div><h4>{{ trans('setting::operations.legal_hold') }}</h4><p>{{ trans('setting::operations.reason_protected') }}</p></div>
                </header>
                <div class="operations-panel__body">
                    @if($canManageRetention)
                        <form method="POST" action="{{ route('admin.operations.legal_hold.place', ['submission' => '__ID__']) }}" data-legal-hold-form onsubmit="this.action=this.action.replace('__ID__', this.elements.submission_id.value); return confirm(@js(trans('setting::operations.place_hold_confirm')));">
                            @csrf
                            <div class="operations-form-row">
                                <div class="form-group"><label>{{ trans('setting::operations.submission_id') }}</label><input class="form-control" type="number" min="1" name="submission_id" required></div>
                                <div class="form-group operations-form-row__reason"><label>{{ trans('setting::operations.reason') }}</label><textarea class="form-control" name="reason" maxlength="500" rows="2" required></textarea></div>
                            </div>
                            <button class="btn operations-btn operations-btn--warning" type="submit"><i class="fa fa-lock"></i>{{ trans('setting::operations.place_hold') }}</button>
                        </form>
                    @else
                        <div class="operations-empty operations-empty--compact"><i class="fa fa-lock"></i><span>{{ trans('setting::operations.reason_protected') }}</span></div>
                    @endif
                </div>
            </section>
        </div>

        <section class="operations-panel">
            <header class="operations-panel__header">
                <span class="operations-panel__icon operations-panel__icon--warning"><i class="fa fa-gavel"></i></span>
                <div><h4>{{ trans('setting::operations.active_holds') }}</h4><p>{{ count($legalHolds) }} {{ strtolower(trans('setting::operations.active_holds')) }}</p></div>
            </header>
            <div class="operations-panel__body operations-panel__body--flush operations-table-wrap">
                <table class="table operations-table">
                    <thead><tr><th>{{ trans('setting::operations.submission_id') }}</th><th>{{ trans('setting::operations.held_at') }}</th><th>{{ trans('setting::operations.held_by') }}</th><th>{{ trans('setting::operations.reason') }}</th><th></th></tr></thead>
                    <tbody>
                        @forelse($legalHolds as $hold)
                            <tr><td><strong>#{{ $hold->id }}</strong></td><td>{{ $hold->legal_hold_at }}</td><td>{{ $hold->legal_hold_by ?: '—' }}</td><td>{{ $canManageRetention ? $hold->legal_hold_reason : trans('setting::operations.reason_protected') }}</td><td class="text-right">@if($canManageRetention)<form class="operations-inline-form" method="POST" action="{{ route('admin.operations.legal_hold.release', $hold->id) }}" onsubmit="return confirm(@js(trans('setting::operations.release_hold_confirm')));">@csrf @method('DELETE')<button class="btn operations-btn operations-btn--secondary operations-btn--sm" type="submit"><i class="fa fa-unlock"></i>{{ trans('setting::operations.release_hold') }}</button></form>@endif</td></tr>
                        @empty
                            <tr><td colspan="5"><div class="operations-empty"><i class="fa fa-shield"></i><span>{{ trans('setting::operations.no_holds') }}</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="operations-panel">
            <header class="operations-panel__header">
                <span class="operations-panel__icon operations-panel__icon--indigo"><i class="fa fa-line-chart"></i></span>
                <div><h4>{{ trans('setting::operations.observability') }}</h4><p>{{ trans('setting::operations.csp_reports') }} &amp; {{ trans('setting::operations.slow_queries') }}</p></div>
            </header>
            <div class="operations-panel__body">
                <div class="operations-log-grid">
                    @foreach(['csp' => ['csp_reports', 'fa-code'], 'slow_queries' => ['slow_queries', 'fa-database']] as $logKey => [$label, $icon])
                        @php($log = $snapshot['logs'][$logKey])
                        <article class="operations-log-card">
                            <span class="operations-log-card__icon"><i class="fa {{ $icon }}"></i></span>
                            <div class="operations-log-card__content">
                                <h5>{{ trans('setting::operations.'.$label) }}</h5>
                                @if($log['exists'])
                                    <div class="operations-log-stats">
                                        <span><strong>{{ number_format($log['recent_matches']) }}</strong>{{ trans('setting::operations.recent_matches') }}</span>
                                        <span><strong>{{ number_format($log['size_bytes'] / 1024, 1) }} KB</strong>{{ trans('setting::operations.log_size') }}</span>
                                    </div>
                                    <small>{{ trans('setting::operations.updated_at') }}: {{ $log['updated_at'] }}</small>
                                @else
                                    <p class="text-muted">{{ trans('setting::operations.log_missing') }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="operations-grid operations-grid--history">
            <section class="operations-panel">
                <header class="operations-panel__header"><span class="operations-panel__icon operations-panel__icon--violet"><i class="fa fa-history"></i></span><div><h4>{{ trans('setting::operations.retention_runs') }}</h4></div></header>
                <div class="operations-panel__body operations-panel__body--flush operations-table-wrap"><table class="table operations-table operations-table--compact"><thead><tr><th>{{ trans('setting::operations.mode') }}</th><th>{{ trans('setting::operations.status') }}</th><th>{{ trans('setting::operations.started_at') }}</th><th>{{ trans('setting::operations.counts') }}</th></tr></thead><tbody>@forelse($retentionRuns as $run)<tr><td><span class="operations-tag">{{ $run->mode }}</span></td><td>{{ $run->status }}</td><td>{{ $run->started_at }}</td><td><small>{{ $run->counts ?: '—' }}</small></td></tr>@empty<tr><td colspan="4"><div class="operations-empty"><i class="fa fa-clock-o"></i><span>{{ trans('setting::operations.no_retention_runs') }}</span></div></td></tr>@endforelse</tbody></table></div>
            </section>
            <section class="operations-panel">
                <header class="operations-panel__header"><span class="operations-panel__icon operations-panel__icon--primary"><i class="fa fa-user-secret"></i></span><div><h4>{{ trans('setting::operations.audit') }}</h4></div></header>
                <div class="operations-panel__body operations-panel__body--flush operations-table-wrap"><table class="table operations-table operations-table--compact"><thead><tr><th>{{ trans('setting::operations.actor') }}</th><th>{{ trans('setting::operations.action') }}</th><th>{{ trans('setting::operations.target') }}</th><th>{{ trans('setting::operations.time') }}</th></tr></thead><tbody>@forelse($audits as $audit)<tr><td>{{ $audit->user_id ?: '—' }}</td><td><code class="operations-code">{{ $audit->action }}</code></td><td>{{ $audit->target_type }} #{{ $audit->target_id }}</td><td>{{ $audit->created_at }}</td></tr>@empty<tr><td colspan="4"><div class="operations-empty"><i class="fa fa-clipboard"></i><span>{{ trans('setting::operations.no_audits') }}</span></div></td></tr>@endforelse</tbody></table></div>
            </section>
        </div>
    </div>
@endsection

@push('globals')
    @vite(['modules/Setting/Resources/assets/admin/sass/main.scss'])
@endpush
