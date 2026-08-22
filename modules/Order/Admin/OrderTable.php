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
        'checkbox',
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
                $html = '<a href="' . e(route('admin.orders.show', $order->id)) . '" class="orders-index__id-link">#'
                    . $order->id
                    . '</a>';

                if ($order->trashed()) {
                    $html .= ' <span class="badge badge-warning orders-index__archived-badge">'
                        . e(trans('order::orders.archived_label'))
                        . '</span>';
                }

                if (is_module_enabled('GoogleIntegration') && $order->google_sheets_sync_error) {
                    $html .= ' <span class="badge badge-danger orders-index__sheets-failed-badge" title="'
                        . e($order->google_sheets_sync_error)
                        . '"><i class="fa fa-table" aria-hidden="true"></i></span>';
                }

                return $html;
            })
            ->addColumn('customer_name', function ($order) {
                $name = $order->customer_full_name;
                return mb_strlen($name) > 20 ? mb_substr($name, 0, 20) . '...' : $name;
            })
            ->addColumn('beautician_name', function ($order) {
                if (!$order->beautician) {
                    return '—';
                }
                $name = trim($order->beautician->first_name . ' ' . $order->beautician->last_name);
                return mb_strlen($name) > 20 ? mb_substr($name, 0, 20) . '...' : e($name);
            })
            ->editColumn('total', function ($order) {
                return $order->total->format();
            })
            ->editColumn('status', function ($order) {
                if (is_module_enabled('TreatmentReservation')) {
                    $bookings = $order->relationLoaded('treatmentBookings') ? $order->treatmentBookings : collect();
                    if ($bookings->isEmpty() && !empty($order->treatmentBooking)) {
                        $bookings = collect([$order->treatmentBooking]);
                    }
                    if ($bookings->isEmpty()) {
                        return '';
                    }
                    if ($bookings->count() > 1) {
                        return '<span class="badge badge-info">' . e(trans('order::orders.appointments_count', ['count' => $bookings->count()])) . '</span>';
                    }
                    $treatmentBooking = $bookings->first();

                    return '<span class="badge ' . treatment_status_badge_class($treatmentBooking->status) . '">'
                        . e($treatmentBooking->treatmentStatusLabel())
                        . '</span>';
                }

                return '<span class="badge ' . order_status_badge_class($order->status) . '">' . e($order->status()) . '</span>';
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

        return $table
            ->setRowId(function ($order) {
                return (string) $order->id;
            })
            ->addColumn('action', function ($order) {
                return view('order::admin.orders.partials.table.action', compact('order'));
            });
    }
}
