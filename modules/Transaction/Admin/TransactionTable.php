<?php

namespace Modules\Transaction\Admin;

use Illuminate\Http\JsonResponse;
use Modules\Admin\Ui\AdminTable;
use Modules\Order\Entities\Order;

class TransactionTable extends AdminTable
{
    /**
     * Raw columns that will not be escaped.
     *
     * @var array
     */
    protected array $rawColumns = [
        'order_id',
        'customer',
        'beautician_name',
        'spa_branch',
        'transaction_id',
        'payment_method',
        'payment_status',
        'order_total',
        'action',
    ];

    /**
     * Orders have no is_active flag.
     *
     * @var bool
     */
    protected bool $editDefaultStatusColumn = false;


    /**
     * @return JsonResponse
     */
    public function make()
    {
        return $this->newTable()
            ->addColumn('order_id', function (Order $order) {
                $orderUrl = route('admin.orders.show', $order->id);

                return '<a href="'.e($orderUrl).'" class="transactions-table__order-link">'
                    .'<span class="transactions-table__order-num">#'.e((string) $order->id).'</span>'
                    .'</a>';
            })
            ->addColumn('customer', function (Order $order) {
                $name = trim((string) $order->customer_full_name);

                if ($name === '') {
                    return '<span class="transactions-table__muted">—</span>';
                }

                return '<span class="transactions-table__customer">'.e($name).'</span>';
            })
            ->addColumn('beautician_name', function (Order $order) {
                if (! $order->beautician) {
                    return '<span class="transactions-table__muted">—</span>';
                }

                $name = trim($order->beautician->first_name.' '.$order->beautician->last_name);

                if ($name === '') {
                    return '<span class="transactions-table__muted">—</span>';
                }

                return '<span class="transactions-table__meta">'.e($name).'</span>';
            })
            ->addColumn('spa_branch', function (Order $order) {
                $name = trim((string) ($order->spaBranch?->name ?? ''));

                if ($name === '') {
                    return '<span class="transactions-table__muted">—</span>';
                }

                return '<span class="transactions-table__meta">'.e($name).'</span>';
            })
            ->addColumn('transaction_id', function (Order $order) {
                $id = trim((string) ($order->transaction?->getRawOriginal('transaction_id')
                    ?? $order->transaction?->transaction_id
                    ?? ''));

                if ($id === '') {
                    return '<span class="transactions-table__muted">—</span>';
                }

                $display = $this->shortTransactionId($id);

                return '<div class="transactions-table__tx-id-wrap">'
                    .'<code class="transactions-table__tx-id" title="'.e($id).'">'.e($display).'</code>'
                    .'<button type="button" class="transactions-table__copy js-copy-tx-id" data-copy="'.e($id).'" title="'.e(trans('transaction::transactions.copy_id')).'">'
                    .'<i class="fa fa-clone" aria-hidden="true"></i>'
                    .'</button>'
                    .'</div>';
            })
            ->editColumn('payment_method', function (Order $order) {
                $label = e((string) $order->payment_method);

                if ($label === '') {
                    return '<span class="transactions-table__muted">—</span>';
                }

                return '<span class="transactions-table__payment-badge">'.$label.'</span>';
            })
            ->addColumn('payment_status', function (Order $order) {
                return '<span class="badge '.e(payment_status_badge_class($order->payment_status)).'">'
                    .e($order->paymentStatusLabel())
                    .'</span>';
            })
            ->addColumn('order_total', function (Order $order) {
                if (! $order->total) {
                    return '<span class="transactions-table__muted">—</span>';
                }

                return '<span class="transactions-table__total">'.$order->total->format().'</span>';
            })
            ->addColumn('action', function (Order $order) {
                return view('transaction::admin.transactions.partials.table.action', compact('order'));
            });
    }


    private function shortTransactionId(string $id): string
    {
        if (strlen($id) <= 20) {
            return $id;
        }

        return substr($id, 0, 10).'…'.substr($id, -6);
    }
}
