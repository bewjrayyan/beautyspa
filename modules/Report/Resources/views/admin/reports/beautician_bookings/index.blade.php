@extends('report::admin.reports.layout')

@section('filters')
    @include('report::admin.reports.filters.from')
    @include('report::admin.reports.filters.to')
    @include('report::admin.reports.filters.status')
    @include('report::admin.reports.filters.beautician')
@endsection

@section('report_result')
    <div class="box-header report-bookings-table-header">
        <div>
            <h5>{{ trans('report::admin.filters.report_types.beautician_bookings_report') }}</h5>
            <span class="report-bookings-count">{{ trans('report::admin.bookings.list_hint') }}</span>
        </div>
    </div>

    <div class="box-body">
        <div class="table-responsive anchor-table">
            <table class="table report-bookings-table report-bookings-table--saas">
                <thead>
                    <tr>
                        <th>{{ trans('report::admin.table.appointment') }}</th>
                        <th>{{ trans('report::admin.table.customer') }}</th>
                        <th>{{ trans('report::admin.table.product') }}</th>
                        <th>{{ trans('report::admin.table.beautician') }}</th>
                        <th>{{ trans('report::admin.table.contact') }}</th>
                        <th>{{ trans('report::admin.table.order_status') }}</th>
                        <th>{{ trans('report::admin.table.payment_status') }}</th>
                        <th class="text-right">{{ trans('report::admin.table.total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report as $booking)
                        <tr>
                            <td data-label="{{ trans('report::admin.table.appointment') }}">
                                <span class="report-booking-date">{{ $booking->appointment_date->format('d M Y') }}</span>
                                <span class="report-booking-time">{{ $booking->appointment_time }}</span>
                            </td>
                            <td data-label="{{ trans('report::admin.table.customer') }}">
                                <a href="{{ route('admin.orders.show', $booking->id) }}" class="report-booking-customer">
                                    {{ $booking->customer_first_name }} {{ $booking->customer_last_name }}
                                </a>
                                <span class="report-booking-order">#{{ $booking->id }}</span>
                            </td>
                            <td data-label="{{ trans('report::admin.table.product') }}">
                                {{ $booking->products->pluck('name')->filter()->unique()->implode(', ') ?: '—' }}
                            </td>
                            <td data-label="{{ trans('report::admin.table.beautician') }}">
                                <span class="report-booking-beautician">{{ $booking->beautician_name }}</span>
                                @if ($booking->beautician_job_title)
                                    <span class="report-booking-role">{{ $booking->beautician_job_title }}</span>
                                @endif
                            </td>
                            <td data-label="{{ trans('report::admin.table.contact') }}">
                                @if ($booking->customer_phone)
                                    <span class="report-booking-phone">{{ $booking->customer_phone }}</span>
                                @endif
                                @if ($booking->customer_email)
                                    <span class="report-booking-email">{{ $booking->customer_email }}</span>
                                @endif
                            </td>
                            <td data-label="{{ trans('report::admin.table.order_status') }}">
                                <span class="report-status-pill report-status-pill--{{ $booking->status }}">
                                    {{ trans("order::statuses.{$booking->status}") }}
                                </span>
                            </td>
                            <td data-label="{{ trans('report::admin.table.payment_status') }}">
                                @include('report::admin.reports.partials.payment_status', ['paymentStatus' => $booking->payment_status])
                            </td>
                            <td class="text-right" data-label="{{ trans('report::admin.table.total') }}">
                                <strong>{{ $booking->total->format() }}</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="empty" colspan="8">
                                <div class="report-empty-state">
                                    <i class="fa fa-calendar-o"></i>
                                    <p>{{ trans('report::admin.no_bookings') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($report->hasPages())
                <div class="report-pagination">
                    {!! $report->links() !!}
                </div>
            @endif
        </div>
    </div>
@endsection
