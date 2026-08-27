@php
    $canViewOrders = auth()->user()?->hasAccess('admin.orders.index');
@endphp

<div class="admin-user-orders-tab">
    <div class="admin-user-orders-intro">
        <div>
            <h3>{{ trans('user::users.orders_tab.title') }}</h3>
            <p>{{ trans('user::users.orders_tab.lead') }}</p>
        </div>
        <div class="admin-user-orders-stats">
            <span>
                <strong>{{ $orders->total() }}</strong>
                {{ trans('user::users.orders_tab.total') }}
            </span>
            @if ($canViewOrders)
                <a
                    class="btn btn-default btn-sm"
                    href="{{ route('admin.orders.index', ['search' => $user->email]) }}"
                >
                    <i class="fa fa-external-link" aria-hidden="true"></i>
                    {{ trans('user::users.orders_tab.view_all') }}
                </a>
            @endif
        </div>
    </div>

    <div class="table-responsive admin-user-orders-table">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ trans('order::orders.order_id') }}</th>
                    <th>{{ trans('order::orders.order_date') }}</th>
                    <th>{{ trans('order::orders.table.spa_branch') }}</th>
                    <th>{{ trans('order::orders.table.beautician') }}</th>
                    <th>{{ trans('order::orders.table.order_status') }}</th>
                    <th>{{ trans('order::orders.table.payment_status') }}</th>
                    <th>{{ trans('order::orders.table.total') }}</th>
                    <th>{{ trans('order::orders.table.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>
                            @if ($canViewOrders)
                                <a href="{{ route('admin.orders.show', $order) }}">#{{ $order->id }}</a>
                            @else
                                #{{ $order->id }}
                            @endif
                        </td>
                        <td>{{ $order->created_at?->format('d M Y, H:i') }}</td>
                        <td>{{ $order->spaBranch?->name ?: '—' }}</td>
                        <td>{{ $order->beautician?->name ?: '—' }}</td>
                        <td>
                            <span class="badge {{ order_status_badge_class($order->status) }}">
                                {{ $order->status() }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ payment_status_badge_class($order->payment_status) }}">
                                {{ $order->paymentStatusLabel() }}
                            </span>
                        </td>
                        <td>{{ $order->total->format() }}</td>
                        <td>
                            @if ($canViewOrders)
                                <a
                                    class="btn btn-default btn-sm"
                                    href="{{ route('admin.orders.show', $order) }}"
                                >
                                    {{ trans('order::orders.table.view') }}
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="empty" colspan="8">{{ trans('user::users.orders_tab.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($orders->hasPages())
        <div class="pagination-wrapper">{{ $orders->links() }}</div>
    @endif
</div>
