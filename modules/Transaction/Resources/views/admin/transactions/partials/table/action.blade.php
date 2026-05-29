<a
    href="{{ route('admin.orders.show', $transaction->order_id) }}"
    class="btn btn-default btn-sm transactions-table__view-btn"
    title="{{ trans('transaction::transactions.view_order') }}"
>
    <i class="fa fa-eye" aria-hidden="true"></i>
    <span class="hidden-xs">{{ trans('transaction::transactions.view_order') }}</span>
</a>
