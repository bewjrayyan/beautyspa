@extends('admin::layout')

@section('title', trans('setting::settings.onesender_logs.title'))

@section('content_header')
    <h3>{{ trans('setting::settings.onesender_logs.title') }}</h3>

    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li><a href="{{ route('admin.settings.edit') }}?tab=sms">{{ trans('setting::settings.settings') }}</a></li>
        <li class="active">{{ trans('setting::settings.onesender_logs.title') }}</li>
    </ol>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    <div class="box box-primary">
        <div class="box-header with-border">
            <p class="text-muted" style="margin: 0;">{{ trans('setting::settings.onesender_logs.intro') }}</p>
        </div>

        <div class="box-body">
            <div style="margin-bottom: 16px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                <a href="{{ route('admin.onesender_queue.index') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                    {{ trans('setting::settings.form.view_onesender_queue') }}
                </a>

                @if ($filteredCount > 0)
                    <form method="POST" action="{{ route('admin.onesender_logs.destroy_filtered') }}" style="display: inline;"
                        onsubmit="return confirm(@json(trans('setting::settings.onesender_logs.delete_filtered_confirm', ['count' => $filteredCount])));">
                        @csrf
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <input type="hidden" name="recipient" value="{{ request('recipient') }}">
                        <input type="hidden" name="source" value="{{ request('source') }}">
                        <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                        <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="fa fa-filter" aria-hidden="true"></i>
                            {{ trans('setting::settings.onesender_logs.delete_filtered', ['count' => $filteredCount]) }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.onesender_logs.destroy_all') }}" style="display: inline;"
                        onsubmit="return confirm(@json(trans('setting::settings.onesender_logs.delete_all_confirm')));">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                            {{ trans('setting::settings.onesender_logs.delete_all') }}
                        </button>
                    </form>
                @endif
            </div>

            <form method="GET" action="{{ route('admin.onesender_logs.index') }}" class="form-inline" style="margin-bottom: 16px;">
                <div class="form-group" style="margin-right: 8px;">
                    <label class="sr-only" for="filter-status">{{ trans('setting::settings.onesender_logs.status') }}</label>
                    <select name="status" id="filter-status" class="form-control">
                        <option value="">{{ trans('setting::settings.onesender_logs.all_statuses') }}</option>
                        @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>
                                {{ trans('setting::settings.onesender_logs.statuses.' . $statusOption) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-right: 8px;">
                    <input type="text" name="recipient" class="form-control" placeholder="{{ trans('setting::settings.onesender_logs.recipient') }}"
                        value="{{ request('recipient') }}">
                </div>
                <div class="form-group" style="margin-right: 8px;">
                    <input type="text" name="source" class="form-control" placeholder="{{ trans('setting::settings.onesender_logs.source') }}"
                        value="{{ request('source') }}">
                </div>
                <div class="form-group" style="margin-right: 8px;">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="form-group" style="margin-right: 8px;">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <button type="submit" class="btn btn-default">{{ trans('setting::settings.onesender_logs.filter') }}</button>
                <a href="{{ route('admin.onesender_logs.index') }}" class="btn btn-link">{{ trans('setting::settings.onesender_logs.reset') }}</a>
            </form>

            <div class="index-table">
                <table class="table table-hover table-condensed">
                    <thead>
                        <tr>
                            <th>{{ trans('setting::settings.onesender_logs.time') }}</th>
                            <th>{{ trans('setting::settings.onesender_logs.status') }}</th>
                            <th>{{ trans('setting::settings.onesender_logs.recipient') }}</th>
                            <th>{{ trans('setting::settings.onesender_logs.source') }}</th>
                            <th>{{ trans('setting::settings.onesender_logs.preview') }}</th>
                            <th>{{ trans('setting::settings.onesender_logs.dedupe_key') }}</th>
                            <th>{{ trans('setting::settings.onesender_logs.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td style="white-space: nowrap;">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    @php
                                        $labelClass = match ($log->status) {
                                            'sent' => 'label-success',
                                            'failed' => 'label-danger',
                                            'skipped_duplicate' => 'label-warning',
                                            'skipped_paused' => 'label-default',
                                            'skipped_disabled' => 'label-default',
                                            'skipped_cancelled' => 'label-default',
                                            default => 'label-default',
                                        };
                                    @endphp
                                    <span class="label {{ $labelClass }}" @if ($log->error_message) title="{{ $log->error_message }}" @endif>
                                        {{ trans('setting::settings.onesender_logs.statuses.' . $log->status) }}
                                    </span>
                                    @if ($log->http_status)
                                        <small class="text-muted">HTTP {{ $log->http_status }}</small>
                                    @endif
                                </td>
                                <td>
                                    <code>{{ $log->recipient }}</code>
                                    <small class="text-muted">{{ $log->message_type }}</small>
                                </td>
                                <td>{{ $log->source ?: '—' }}</td>
                                <td style="max-width: 320px;">
                                    <small style="white-space: pre-wrap;">{{ \Illuminate\Support\Str::limit($log->message_preview, 160) }}</small>
                                </td>
                                <td><small>{{ $log->dedupe_key ?: '—' }}</small></td>
                                <td style="white-space: nowrap;">
                                    <form method="POST" action="{{ route('admin.onesender_logs.destroy', $log) }}" style="display: inline;"
                                        onsubmit="return confirm(@json(trans('setting::settings.onesender_logs.delete_confirm')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs" title="{{ trans('setting::settings.onesender_logs.delete') }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    <p style="margin-bottom: 6px;">{{ trans('setting::settings.onesender_logs.empty') }}</p>
                                    <p style="margin: 0; font-size: 12px;">{{ trans('setting::settings.onesender_logs.empty_hint') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($logs->hasPages())
            <div class="box-footer">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
