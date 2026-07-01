@php
    $pendingOrdersUrl = route('admin.orders.index', ['payment_status' => 'pending']);
@endphp

<div class="dashboard-panel dashboard-pending-orders">
    <div class="grid-header dashboard-panel__head">
        <h5>
            <i class="fa fa-clock-o" aria-hidden="true"></i>
            {{ trans('admin::dashboard.pending_orders') }}
        </h5>
        <a href="{{ $pendingOrdersUrl }}" class="dashboard-panel__view-all">
            {{ trans('admin::dashboard.view_all') }}
            <i class="fa fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>

    <div class="table-responsive anchor-table">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ trans('admin::dashboard.table.latest_orders.order_id') }}</th>
                    <th>{{ trans('admin::dashboard.table.customer') }}</th>
                    <th>{{ trans('admin::dashboard.table.latest_orders.status') }}</th>
                    <th>{{ trans('admin::dashboard.table.latest_orders.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pendingOrders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}">{{ $order->id }}</a>
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}">{{ $order->customer_full_name }}</a>
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}">
                                <span class="badge {{ payment_status_badge_class($order->payment_status) }}">
                                    {{ $order->paymentStatusLabel() }}
                                </span>
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}">{{ $order->total->format() }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="empty" colspan="4">{{ trans('admin::dashboard.pending_orders_empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
