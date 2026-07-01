<div class="dashboard-panel dashboard-recent-customers">
    <div class="grid-header dashboard-panel__head">
        <h5>
            <i class="fa fa-user-plus" aria-hidden="true"></i>
            {{ trans('admin::dashboard.recent_customers') }}
        </h5>
        <a href="{{ route('admin.users.index') }}" class="dashboard-panel__view-all">
            {{ trans('admin::dashboard.view_all') }}
            <i class="fa fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>

    <div class="table-responsive anchor-table">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ trans('admin::dashboard.table.customer') }}</th>
                    <th>{{ trans('admin::dashboard.table.recent_customers.joined') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentCustomers as $customer)
                    <tr>
                        <td>
                            <a href="{{ route('admin.users.edit', $customer) }}" class="dashboard-members__name">
                                <strong>{{ trim($customer->first_name . ' ' . $customer->last_name) }}</strong>
                                <small>{{ $customer->email ?: $customer->phone ?: '—' }}</small>
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.edit', $customer) }}">
                                {{ $customer->created_at->format('d M Y') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="empty" colspan="2">{{ trans('admin::dashboard.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
