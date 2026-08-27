@extends('admin::layout')

@section('title', trans('whatsappbirthday::admin.title'))

@section('content_header')
    <h3>{{ trans('whatsappbirthday::admin.title') }}</h3>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li class="active">{{ trans('whatsappbirthday::admin.logs') }}</li>
    </ol>
@endsection

@section('content')
    @include('whatsappbirthday::admin.partials.hub-nav', ['activeTab' => 'logs'])

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">{{ trans('whatsappbirthday::admin.logs') }}</h3>
            @if (auth()->user()?->hasAccess('admin.whatsapp_birthday.send'))
                <div class="box-tools">
                    <form method="POST" action="{{ route('admin.whatsapp_birthday.send') }}" class="inline"
                          onsubmit="return confirm(@json(trans('whatsappbirthday::admin.send_confirm')))">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa fa-paper-plane"></i> {{ trans('whatsappbirthday::admin.send_now') }}
                        </button>
                    </form>
                </div>
            @endif
        </div>
        <div class="box-body table-responsive">
            @if ($logs->isEmpty())
                <p class="text-muted">{{ trans('whatsappbirthday::admin.empty_logs') }}</p>
            @else
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('whatsappbirthday::admin.col_customer') }}</th>
                            <th>{{ trans('whatsappbirthday::admin.col_phone') }}</th>
                            <th>{{ trans('whatsappbirthday::admin.col_reward') }}</th>
                            <th>{{ trans('whatsappbirthday::admin.col_status') }}</th>
                            <th>{{ trans('whatsappbirthday::admin.col_sent_at') }}</th>
                            <th>{{ trans('whatsappbirthday::admin.col_error') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td>
                                    #{{ $log->user_id }}
                                    {{ $log->user?->first_name }} {{ $log->user?->last_name }}
                                    <div class="text-muted">{{ $log->year }}</div>
                                </td>
                                <td>{{ $log->phone }}</td>
                                <td>
                                    {{ $log->reward_type }}
                                    @if ($log->points_awarded)
                                        <div>{{ number_format($log->points_awarded) }} pts</div>
                                    @endif
                                    @if ($log->coupon_code)
                                        <code>{{ $log->coupon_code }}</code>
                                    @endif
                                </td>
                                <td>
                                    <span class="label label-{{ $log->delivery_status === 'sent' ? 'success' : ($log->delivery_status === 'failed' ? 'danger' : 'default') }}">
                                        {{ trans('whatsappbirthday::admin.status_'.$log->delivery_status) }}
                                    </span>
                                </td>
                                <td>{{ $log->sent_at?->format('Y-m-d H:i') }}</td>
                                <td class="text-muted">{{ \Illuminate\Support\Str::limit($log->error_message, 80) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $logs->links() }}
            @endif
        </div>
    </div>
@endsection
