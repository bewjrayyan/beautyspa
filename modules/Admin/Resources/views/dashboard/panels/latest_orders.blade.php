<div class="dashboard-panel latest-order">
    <div class="grid-header dashboard-panel__head">
        <h5>{{ trans('admin::dashboard.latest_orders') }}</h5>
        <a href="{{ route('admin.orders.index') }}" class="dashboard-panel__view-all">
            {{ trans('admin::dashboard.view_all') }}
            <i class="fa fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>

    <div class="clearfix"></div>

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
                @forelse ($latestOrders as $latestOrder)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $latestOrder) }}">
                                {{ $latestOrder->id }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.show', $latestOrder) }}">
                                {{
                                    mb_strlen($latestOrder->customer_full_name) > 20
                                        ? mb_substr($latestOrder->customer_full_name, 0, 20) . '...'
                                        : $latestOrder->customer_full_name
                                }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.show', $latestOrder) }}">
                                <span class="badge {{ order_status_badge_class($latestOrder->status) }}">
                                    {{ $latestOrder->status() }}
                                </span>
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.show', $latestOrder) }}">
                                {{ $latestOrder->total->format() }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="empty" colspan="5">{{ trans('admin::dashboard.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
