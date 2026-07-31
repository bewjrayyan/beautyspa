@extends('admin::layout')

@section('title', trans('setting::operations.title'))

@section('content_header')
    <h3>{{ trans('setting::operations.title') }}</h3>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li><a href="{{ route('admin.settings.edit') }}">{{ trans('setting::settings.settings') }}</a></li>
        <li class="active">{{ trans('setting::operations.title') }}</li>
    </ol>
@endsection

@section('content')
    <div class="alert {{ $snapshot['healthy'] ? 'alert-success' : 'alert-warning' }}">
        <strong>{{ trans('setting::operations.overall') }}:</strong>
        {{ trans($snapshot['healthy'] ? 'setting::operations.healthy' : 'setting::operations.degraded') }}
        <div>{{ trans('setting::operations.intro') }}</div>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6"><div class="small-box bg-{{ $snapshot['scheduler']['healthy'] ? 'green' : 'yellow' }}"><div class="inner"><h3>{{ $snapshot['scheduler']['healthy'] ? 'OK' : '!' }}</h3><p>{{ trans('setting::operations.scheduler') }}</p></div><div class="small-box-footer">{{ $snapshot['scheduler']['last_seen_at'] ? trans('setting::operations.last_seen', ['time' => $snapshot['scheduler']['last_seen_at']]) : trans('setting::operations.never_seen') }}</div></div></div>
        <div class="col-md-3 col-sm-6"><div class="small-box bg-{{ $snapshot['queue']['pending'] > $snapshot['queue']['limits']['pending'] ? 'yellow' : 'aqua' }}"><div class="inner"><h3>{{ $snapshot['queue']['pending'] }}</h3><p>{{ trans('setting::operations.pending') }}</p></div><div class="small-box-footer">{{ trans('setting::operations.oldest_minutes') }}: {{ $snapshot['queue']['oldest_minutes'] }}</div></div></div>
        <div class="col-md-3 col-sm-6"><div class="small-box bg-{{ $snapshot['queue']['failed'] > 0 ? 'red' : 'green' }}"><div class="inner"><h3>{{ $snapshot['queue']['failed'] }}</h3><p>{{ trans('setting::operations.failed') }}</p></div><div class="small-box-footer">{{ trans('setting::operations.queue') }}</div></div></div>
        <div class="col-md-3 col-sm-6"><div class="small-box bg-{{ $snapshot['queue']['stuck_onesender'] > 0 ? 'red' : 'green' }}"><div class="inner"><h3>{{ $snapshot['queue']['stuck_onesender'] }}</h3><p>{{ trans('setting::operations.stuck_onesender') }}</p></div><div class="small-box-footer">OneSender</div></div></div>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title">{{ trans('setting::operations.queue') }}</h3></div>
        <div class="box-body table-responsive">
            <table class="table table-hover table-condensed">
                <thead><tr><th>{{ trans('setting::operations.job') }}</th><th>{{ trans('setting::operations.queue_name') }}</th><th>{{ trans('setting::operations.attempts') }}</th><th>{{ trans('setting::operations.queued_at') }}</th><th>{{ trans('setting::operations.actions') }}</th></tr></thead>
                <tbody>
                    @forelse($pendingJobs?->items() ?? [] as $job)
                        <tr>
                            <td><code>{{ $job->display_name }}</code></td><td>{{ $job->queue }}</td><td>{{ $job->attempts }}</td><td>{{ date('Y-m-d H:i:s', $job->created_at) }}</td>
                            <td>@if($canManageQueue && $job->reserved_at === null)<form method="POST" action="{{ route('admin.operations.queue.cancel', $job->id) }}" onsubmit="return confirm(@js(trans('setting::operations.cancel_confirm')));">@csrf @method('DELETE')<button class="btn btn-warning btn-xs" type="submit">{{ trans('setting::operations.cancel') }}</button></form>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">{{ trans('setting::operations.empty_pending') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($pendingJobs && $pendingJobs->hasPages()) {{ $pendingJobs->appends(request()->except('pending_page'))->links() }} @endif
        </div>
    </div>

    <div class="box box-danger">
        <div class="box-header with-border"><h3 class="box-title">{{ trans('setting::operations.failed') }}</h3></div>
        <div class="box-body table-responsive">
            <table class="table table-hover table-condensed">
                <thead><tr><th>{{ trans('setting::operations.job') }}</th><th>{{ trans('setting::operations.queue_name') }}</th><th>{{ trans('setting::operations.failed_at') }}</th><th>{{ trans('setting::operations.actions') }}</th></tr></thead>
                <tbody>
                    @forelse($failedJobs?->items() ?? [] as $job)
                        <tr><td><code>{{ $job->display_name }}</code></td><td>{{ $job->queue }}</td><td>{{ $job->failed_at }}</td><td>@if($canManageQueue)<form method="POST" action="{{ route('admin.operations.failed.retry', $job->uuid) }}" onsubmit="return confirm(@js(trans('setting::operations.retry_confirm')));">@csrf<button class="btn btn-primary btn-xs" type="submit">{{ trans('setting::operations.retry') }}</button></form>@endif</td></tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">{{ trans('setting::operations.empty_failed') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($failedJobs && $failedJobs->hasPages()) {{ $failedJobs->appends(request()->except('failed_page'))->links() }} @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="box box-info">
                <div class="box-header with-border"><h3 class="box-title">{{ trans('setting::operations.retention') }}</h3></div>
                <div class="box-body">
                    <p><span class="label {{ $snapshot['retention']['enabled'] ? 'label-success' : 'label-default' }}">{{ trans($snapshot['retention']['enabled'] ? 'setting::operations.retention_enabled' : 'setting::operations.retention_disabled') }}</span></p>
                    <p>{{ $snapshot['retention']['completed_days'] ? trans('setting::operations.completed_days', ['days' => $snapshot['retention']['completed_days']]) : trans('setting::operations.completed_disabled') }}</p>
                    @if($snapshot['retention']['preview'])
                        <div class="row">@foreach($snapshot['retention']['preview'] as $key => $count)<div class="col-sm-3"><strong>{{ $count }}</strong><br><small>{{ trans('setting::operations.'.$key) }}</small></div>@endforeach</div>
                    @else
                        <p class="text-muted">{{ trans('setting::operations.preview_unavailable') }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="box box-warning">
                <div class="box-header with-border"><h3 class="box-title">{{ trans('setting::operations.legal_hold') }}</h3></div>
                <div class="box-body">
                    @if($canManageRetention)
                        <form method="POST" action="{{ route('admin.operations.legal_hold.place', ['submission' => '__ID__']) }}" data-legal-hold-form onsubmit="this.action=this.action.replace('__ID__', this.elements.submission_id.value); return confirm(@js(trans('setting::operations.place_hold_confirm')));">
                            @csrf
                            <div class="form-group"><label>{{ trans('setting::operations.submission_id') }}</label><input class="form-control" type="number" min="1" name="submission_id" required></div>
                            <div class="form-group"><label>{{ trans('setting::operations.reason') }}</label><textarea class="form-control" name="reason" maxlength="500" required></textarea></div>
                            <button class="btn btn-warning" type="submit">{{ trans('setting::operations.place_hold') }}</button>
                        </form>
                    @else
                        <p class="text-muted">{{ trans('setting::operations.reason_protected') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="box box-warning"><div class="box-header with-border"><h3 class="box-title">{{ trans('setting::operations.active_holds') }}</h3></div><div class="box-body table-responsive"><table class="table table-condensed"><thead><tr><th>{{ trans('setting::operations.submission_id') }}</th><th>{{ trans('setting::operations.held_at') }}</th><th>{{ trans('setting::operations.held_by') }}</th><th>{{ trans('setting::operations.reason') }}</th><th></th></tr></thead><tbody>
        @forelse($legalHolds as $hold)<tr><td>#{{ $hold->id }}</td><td>{{ $hold->legal_hold_at }}</td><td>{{ $hold->legal_hold_by ?: '—' }}</td><td>{{ $canManageRetention ? $hold->legal_hold_reason : trans('setting::operations.reason_protected') }}</td><td>@if($canManageRetention)<form method="POST" action="{{ route('admin.operations.legal_hold.release', $hold->id) }}" onsubmit="return confirm(@js(trans('setting::operations.release_hold_confirm')));">@csrf @method('DELETE')<button class="btn btn-default btn-xs" type="submit">{{ trans('setting::operations.release_hold') }}</button></form>@endif</td></tr>@empty<tr><td colspan="5" class="text-center text-muted">{{ trans('setting::operations.no_holds') }}</td></tr>@endforelse
    </tbody></table></div></div>

    <div class="row">
        @foreach(['csp' => 'csp_reports', 'slow_queries' => 'slow_queries'] as $logKey => $label)
            @php($log = $snapshot['logs'][$logKey])
            <div class="col-md-6"><div class="box box-default"><div class="box-header with-border"><h3 class="box-title">{{ trans('setting::operations.'.$label) }}</h3></div><div class="box-body">@if($log['exists'])<p><strong>{{ trans('setting::operations.recent_matches') }}:</strong> {{ $log['recent_matches'] }}</p><p><strong>{{ trans('setting::operations.log_size') }}:</strong> {{ number_format($log['size_bytes'] / 1024, 1) }} KB</p><p><strong>{{ trans('setting::operations.updated_at') }}:</strong> {{ $log['updated_at'] }}</p>@else<p class="text-muted">{{ trans('setting::operations.log_missing') }}</p>@endif</div></div></div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-md-6"><div class="box box-default"><div class="box-header with-border"><h3 class="box-title">{{ trans('setting::operations.retention_runs') }}</h3></div><div class="box-body table-responsive"><table class="table table-condensed"><thead><tr><th>{{ trans('setting::operations.mode') }}</th><th>{{ trans('setting::operations.status') }}</th><th>{{ trans('setting::operations.started_at') }}</th><th>{{ trans('setting::operations.counts') }}</th></tr></thead><tbody>@forelse($retentionRuns as $run)<tr><td>{{ $run->mode }}</td><td>{{ $run->status }}</td><td>{{ $run->started_at }}</td><td><small>{{ $run->counts ?: '—' }}</small></td></tr>@empty<tr><td colspan="4" class="text-center text-muted">{{ trans('setting::operations.no_retention_runs') }}</td></tr>@endforelse</tbody></table></div></div></div>
        <div class="col-md-6"><div class="box box-default"><div class="box-header with-border"><h3 class="box-title">{{ trans('setting::operations.audit') }}</h3></div><div class="box-body table-responsive"><table class="table table-condensed"><thead><tr><th>{{ trans('setting::operations.actor') }}</th><th>{{ trans('setting::operations.action') }}</th><th>{{ trans('setting::operations.target') }}</th><th>{{ trans('setting::operations.time') }}</th></tr></thead><tbody>@forelse($audits as $audit)<tr><td>{{ $audit->user_id ?: '—' }}</td><td><code>{{ $audit->action }}</code></td><td>{{ $audit->target_type }} #{{ $audit->target_id }}</td><td>{{ $audit->created_at }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted">{{ trans('setting::operations.no_audits') }}</td></tr>@endforelse</tbody></table></div></div></div>
    </div>
@endsection
