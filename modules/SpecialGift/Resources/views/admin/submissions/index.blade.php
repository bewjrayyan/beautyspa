@extends('admin::layout')

@section('title', trans('specialgift::admin.submissions'))

@section('content_header')
    <h3>{{ trans('specialgift::admin.submissions') }}</h3>

    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li class="active">{{ trans('specialgift::admin.submissions') }}</li>
    </ol>
@endsection

@section('content')
    @include('specialgift::admin.partials.hub-nav', [
        'activeTab' => 'submissions',
        'sendGiftUrl' => route('specialgift.send.create'),
    ])

    <div class="box box-primary gv-hub-panel">
        <div class="box-body index-table">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>{{ trans('specialgift::admin.date') }}</th>
                        <th>{{ trans('specialgift::admin.recipient') }}</th>
                        <th>{{ trans('specialgift::admin.order_number') }}</th>
                        <th>{{ trans('specialgift::admin.whatsapp') }}</th>
                        <th>{{ trans('specialgift::admin.sender') }}</th>
                        <th>{{ trans('specialgift::admin.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submissions as $submission)
                        <tr>
                            <td>{{ $submission->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $submission->recipient_name }}</td>
                            <td>#{{ $submission->order_number }}</td>
                            <td>{{ $submission->whatsapp_number }}</td>
                            <td>{{ $submission->sender_name ?: '—' }}</td>
                            <td>
                                @if ($submission->delivery_status === 'sent')
                                    <span class="label label-success">{{ trans('specialgift::admin.status_sent') }}</span>
                                @elseif ($submission->delivery_status === 'failed')
                                    <span class="label label-danger" title="{{ $submission->error_message }}">{{ trans('specialgift::admin.status_failed') }}</span>
                                @else
                                    <span class="label label-default">{{ trans('specialgift::admin.status_processing') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">{{ trans('specialgift::admin.no_submissions') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($submissions->hasPages())
            <div class="box-footer">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>
@endsection

@push('globals')
    @vite('modules/SpecialGift/Resources/assets/admin/sass/main.scss')
@endpush
