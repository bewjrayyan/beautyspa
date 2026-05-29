<div class="dropdown order-table-actions">
    <button
        type="button"
        class="btn btn-default btn-table-actions-toggle"
        aria-haspopup="true"
        aria-expanded="false"
        title="{{ trans('order::orders.table.actions') }}"
        data-order-id="{{ $order->id }}"
        data-current-status="{{ $order->status }}"
        data-show-url="{{ route('admin.orders.show', $order) }}"
        data-print-url="{{ route('admin.orders.print.show', $order) }}"
        data-status-url="{{ route('admin.orders.status.update', $order) }}"
    >
        <span class="actions-dots" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </button>
</div>
