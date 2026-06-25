@extends('admin::layout')

@section('title', trans('setting::settings.onesender_queue.title'))

@section('content_header')
    <h3>{{ trans('setting::settings.onesender_queue.title') }}</h3>

    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li><a href="{{ route('admin.settings.edit') }}?tab=sms">{{ trans('setting::settings.settings') }}</a></li>
        <li class="active">{{ trans('setting::settings.onesender_queue.title') }}</li>
    </ol>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            @include('admin::partials.alert_close', ['times' => true])
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            @include('admin::partials.alert_close', ['times' => true])
            {{ session('error') }}
        </div>
    @endif

    <div class="box box-primary">
        <div class="box-header with-border">
            <p class="text-muted" style="margin: 0;">{{ trans('setting::settings.onesender_queue.intro') }}</p>
            @if ($pendingCount > 0)
                <p class="text-warning" style="margin: 8px 0 0;">
                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                    {{ trans('setting::settings.onesender_queue.pending_count', ['count' => $pendingCount]) }}
                </p>
            @endif
        </div>

        <div class="box-body">
            <div style="margin-bottom: 16px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                <a href="{{ route('admin.onesender_logs.index') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-list-alt" aria-hidden="true"></i>
                    {{ trans('setting::settings.form.view_onesender_logs') }}
                </a>

                @if ($pendingCount > 0)
                    <form method="POST" action="{{ route('admin.onesender_queue.cancel_all') }}" style="display: inline;"
                        onsubmit="return confirm(@js(trans('setting::settings.onesender_queue.cancel_all_confirm')));">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="fa fa-stop" aria-hidden="true"></i>
                            {{ trans('setting::settings.onesender_queue.cancel_all') }}
                        </button>
                    </form>
                @endif
            </div>

            <form method="GET" action="{{ route('admin.onesender_queue.index') }}" class="form-inline" style="margin-bottom: 16px;">
                <div class="form-group" style="margin-right: 8px;">
                    <select name="status" class="form-control">
                        <option value="">{{ trans('setting::settings.onesender_queue.all_statuses') }}</option>
                        @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>
                                {{ trans('setting::settings.onesender_queue.statuses.' . $statusOption) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-right: 8px;">
                    <input type="text" name="recipient" class="form-control" placeholder="{{ trans('setting::settings.onesender_queue.recipient') }}"
                        value="{{ request('recipient') }}">
                </div>
                <div class="form-group" style="margin-right: 8px;">
                    <input type="text" name="source" class="form-control" placeholder="{{ trans('setting::settings.onesender_queue.source') }}"
                        value="{{ request('source') }}">
                </div>
                <button type="submit" class="btn btn-default">{{ trans('setting::settings.onesender_queue.filter') }}</button>
                <a href="{{ route('admin.onesender_queue.index') }}" class="btn btn-link">{{ trans('setting::settings.onesender_queue.reset') }}</a>
            </form>

            <div class="index-table">
                <table class="table table-hover table-condensed">
                    <thead>
                        <tr>
                            <th>{{ trans('setting::settings.onesender_queue.queued_at') }}</th>
                            <th>{{ trans('setting::settings.onesender_queue.send_at') }}</th>
                            <th>{{ trans('setting::settings.onesender_queue.status') }}</th>
                            <th>{{ trans('setting::settings.onesender_queue.recipient') }}</th>
                            <th>{{ trans('setting::settings.onesender_queue.source') }}</th>
                            <th>{{ trans('setting::settings.onesender_queue.preview') }}</th>
                            <th>{{ trans('setting::settings.onesender_queue.dedupe_key') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($messages as $message)
                            <tr>
                                <td style="white-space: nowrap;">{{ $message->created_at?->format('Y-m-d H:i:s') }}</td>
                                <td style="white-space: nowrap;">{{ $message->scheduled_at?->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    @php
                                        $labelClass = match ($message->status) {
                                            'pending' => 'label-info',
                                            'processing' => 'label-primary',
                                            'sent' => 'label-success',
                                            'failed' => 'label-danger',
                                            'cancelled' => 'label-default',
                                            default => 'label-default',
                                        };
                                    @endphp
                                    <span class="label {{ $labelClass }}" @if ($message->error_message) title="{{ $message->error_message }}" @endif>
                                        {{ trans('setting::settings.onesender_queue.statuses.' . $message->status) }}
                                    </span>
                                </td>
                                <td>
                                    <code>{{ $message->recipient }}</code>
                                    <small class="text-muted">{{ $message->message_type }}</small>
                                </td>
                                <td>{{ $message->source ?: '—' }}</td>
                                <td style="max-width: 280px;">
                                    <small style="white-space: pre-wrap;">{{ \Illuminate\Support\Str::limit($message->message_preview, 140) }}</small>
                                </td>
                                <td><small>{{ $message->dedupe_key ?: '—' }}</small></td>
                                <td style="white-space: nowrap;">
                                    @if ($message->canBeCancelled())
                                        <form method="POST" action="{{ route('admin.onesender_queue.cancel', $message) }}" style="display: inline;"
                                            onsubmit="return confirm(@js(trans('setting::settings.onesender_queue.cancel_confirm')));">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-xs" title="{{ trans('setting::settings.onesender_queue.cancel') }}">
                                                <i class="fa fa-stop"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if ($message->canBeDeleted())
                                        <form method="POST" action="{{ route('admin.onesender_queue.destroy', $message) }}" style="display: inline;"
                                            onsubmit="return confirm(@js(trans('setting::settings.onesender_queue.delete_confirm')));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs" title="{{ trans('setting::settings.onesender_queue.delete') }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    <p style="margin-bottom: 6px;">{{ trans('setting::settings.onesender_queue.empty') }}</p>
                                    <p style="margin: 0; font-size: 12px;">{{ trans('setting::settings.onesender_queue.empty_hint') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($messages->hasPages())
            <div class="box-footer">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
@endsection
