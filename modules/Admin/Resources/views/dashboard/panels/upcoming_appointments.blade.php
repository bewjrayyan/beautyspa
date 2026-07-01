<div class="dashboard-panel dashboard-upcoming-appointments">
    <div class="grid-header dashboard-panel__head">
        <h5>
            <i class="fa fa-calendar-o" aria-hidden="true"></i>
            {{ trans('admin::dashboard.upcoming_appointments') }}
        </h5>
        @hasAccess('admin.treatment_reservations.calendar')
            <a href="{{ route('admin.treatment_reservations.calendar') }}" class="dashboard-panel__view-all">
                {{ trans('admin::dashboard.view_all') }}
                <i class="fa fa-arrow-right" aria-hidden="true"></i>
            </a>
        @endHasAccess
    </div>

    <div class="table-responsive anchor-table">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ trans('admin::dashboard.table.upcoming_appointments.date') }}</th>
                    <th>{{ trans('admin::dashboard.table.customer') }}</th>
                    <th>{{ trans('admin::dashboard.table.latest_orders.status') }}</th>
                    <th>{{ trans('admin::dashboard.table.latest_orders.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($upcomingAppointments as $appointment)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $appointment) }}">
                                {{ $appointment->appointment_date?->format('d M Y') }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.show', $appointment) }}">
                                {{ $appointment->customer_full_name }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.show', $appointment) }}">
                                <span class="badge {{ order_status_badge_class($appointment->status) }}">
                                    {{ $appointment->status() }}
                                </span>
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.show', $appointment) }}">
                                {{ $appointment->total->format() }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="empty" colspan="4">{{ trans('admin::dashboard.upcoming_appointments_empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
