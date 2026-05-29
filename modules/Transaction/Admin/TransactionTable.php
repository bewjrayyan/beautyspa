<?php

namespace Modules\Transaction\Admin;

use Illuminate\Http\JsonResponse;
use Modules\Admin\Ui\AdminTable;
use Modules\Transaction\Entities\Transaction;

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
        'transaction_id',
        'payment_method',
        'order_total',
        'action',
    ];


    /**
     * @return JsonResponse
     */
    public function make()
    {
        return $this->newTable()
            ->addColumn('order_id', function (Transaction $transaction) {
                $orderUrl = route('admin.orders.show', $transaction->order_id);

                return '<a href="'.e($orderUrl).'" class="transactions-table__order-link">'
                    .'<span class="transactions-table__order-num">#'.e((string) $transaction->order_id).'</span>'
                    .'</a>';
            })
            ->addColumn('customer', function (Transaction $transaction) {
                $name = $transaction->order?->customer_full_name;

                if (! $name) {
                    return '<span class="transactions-table__muted">—</span>';
                }

                return '<span class="transactions-table__customer">'.e($name).'</span>';
            })
            ->editColumn('transaction_id', function (Transaction $transaction) {
                $id = (string) $transaction->transaction_id;
                $display = $this->shortTransactionId($id);

                return '<div class="transactions-table__tx-id-wrap">'
                    .'<code class="transactions-table__tx-id" title="'.e($id).'">'.e($display).'</code>'
                    .'<button type="button" class="transactions-table__copy js-copy-tx-id" data-copy="'.e($id).'" title="'.e(trans('transaction::transactions.copy_id')).'">'
                    .'<i class="fa fa-clone" aria-hidden="true"></i>'
                    .'</button>'
                    .'</div>';
            })
            ->editColumn('payment_method', function (Transaction $transaction) {
                $label = e((string) $transaction->payment_method);

                return '<span class="transactions-table__payment-badge">'.$label.'</span>';
            })
            ->addColumn('order_total', function (Transaction $transaction) {
                if (! $transaction->order) {
                    return '<span class="transactions-table__muted">—</span>';
                }

                return '<span class="transactions-table__total">'.$transaction->order->total->format().'</span>';
            })
            ->addColumn('action', function (Transaction $transaction) {
                return view('transaction::admin.transactions.partials.table.action', compact('transaction'));
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
