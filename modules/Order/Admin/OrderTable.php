<?php

namespace Modules\Order\Admin;

use Modules\Admin\Ui\AdminTable;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Exceptions\Exception;

class OrderTable extends AdminTable
{
    /**
     * Raw columns that will not be escaped.
     *
     * @var array
     */
    protected array $rawColumns = [
        'id',
        'status',
        'payment_status',
        'action',
    ];

    /**
     * Make table response for the resource.
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function make()
    {
        $table = $this->newTable()
            ->editColumn('id', function ($order) {
                $html = '#' . $order->id;

                if ($order->trashed()) {
                    $html .= ' <span class="badge badge-warning orders-index__archived-badge">'
                        . e(trans('order::orders.archived_label'))
                        . '</span>';
                }

                return $html;
            })
            ->addColumn('customer_name', function ($order) {
                return $order->customer_full_name;
            })
            ->editColumn('total', function ($order) {
                return $order->total->format();
            })
            ->editColumn('status', function ($order) {
                return '<span class="badge ' . order_status_badge_class($order->status) . '">' . $order->status() . '</span>';
            })
            ->editColumn('payment_status', function ($order) {
                return '<span class="badge ' . payment_status_badge_class($order->payment_status) . '">'
                    . e($order->paymentStatusLabel())
                    . '</span>';
            });

        if (is_module_enabled('SpaBranch')) {
            $table->addColumn('spa_branch', function ($order) {
                return e($order->spaBranch?->name ?? '—');
            });
        }

        return $table->addColumn('action', function ($order) {
                return view('order::admin.orders.partials.table.action', compact('order'));
            });
    }
}
