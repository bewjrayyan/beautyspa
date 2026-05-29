<?php

namespace Modules\Coupon\Admin;

use Modules\Admin\Ui\AdminTable;
use Modules\Coupon\Entities\Coupon;

class CouponTable extends AdminTable
{
    public function make()
    {
        return $this->newTable()
            ->addColumn('name', function (Coupon $coupon) {
                $url = route('admin.coupons.edit', $coupon);

                return '<a href="' . $url . '" class="coupon-admin-table__name"><strong>' . e($coupon->name) . '</strong></a>';
            })
            ->addColumn('code', function (Coupon $coupon) {
                return '<code class="coupon-admin-table__code">' . e($coupon->code) . '</code>';
            })
            ->addColumn('discount', function (Coupon $coupon) {
                $value = $this->formatDiscountDisplay($coupon);

                $type = $coupon->is_percent
                    ? trans('coupon::coupons.form.price_types.1')
                    : trans('coupon::coupons.form.price_types.0');

                $shipping = $coupon->free_shipping
                    ? '<span class="coupon-admin-table__tag coupon-admin-table__tag--shipping">'
                        . '<i class="fa fa-truck" aria-hidden="true"></i> '
                        . e(trans('coupon::coupons.index.free_shipping'))
                        . '</span>'
                    : '';

                return '<div class="coupon-admin-table__discount">'
                    . '<span class="coupon-admin-table__value">' . e($value) . '</span>'
                    . '<span class="coupon-admin-table__type">' . e($type) . '</span>'
                    . $shipping
                    . '</div>';
            })
            ->addColumn('validity', function (Coupon $coupon) {
                return $this->validityBadge($coupon);
            })
            ->addColumn('usage', function (Coupon $coupon) {
                $used = number_format($coupon->used);
                $limit = $coupon->usage_limit_per_coupon;

                if ($limit) {
                    return '<span class="coupon-admin-table__usage">'
                        . $used . ' / ' . number_format($limit)
                        . '</span>';
                }

                return '<span class="coupon-admin-table__usage">' . $used . '</span>';
            })
            ->editColumn('status', function (Coupon $coupon) {
                if ($coupon->is_active) {
                    return '<span class="badge badge-success coupon-admin-table__enabled">'
                        . e(trans('admin::admin.table.active'))
                        . '</span>';
                }

                return '<span class="badge badge-danger coupon-admin-table__enabled">'
                    . e(trans('admin::admin.table.inactive'))
                    . '</span>';
            })
            ->rawColumns(['name', 'code', 'discount', 'validity', 'usage', 'status']);
    }


    private function formatDiscountDisplay(Coupon $coupon): string
    {
        if ($coupon->is_percent) {
            $raw = (float) ($coupon->getAttributes()['value'] ?? 0);

            if (fmod($raw, 1.0) === 0.0) {
                return (int) $raw . '%';
            }

            return rtrim(rtrim(number_format($raw, 2, '.', ''), '0'), '.') . '%';
        }

        return $coupon->value->format();
    }


    private function validityBadge(Coupon $coupon): string
    {
        if (! $coupon->is_active) {
            return '<span class="coupon-admin-table__validity coupon-admin-table__status coupon-admin-table__status--inactive">'
                . e(trans('coupon::coupons.index.status_inactive'))
                . '</span>';
        }

        if (! $coupon->valid()) {
            $scheduled = $coupon->start_date && today()->lt($coupon->start_date);

            return '<span class="coupon-admin-table__validity coupon-admin-table__status coupon-admin-table__status--'
                . ($scheduled ? 'scheduled' : 'expired') . '">'
                . e(trans($scheduled ? 'coupon::coupons.index.status_scheduled' : 'coupon::coupons.index.status_expired'))
                . '</span>';
        }

        return '<span class="coupon-admin-table__validity coupon-admin-table__status coupon-admin-table__status--active">'
            . e(trans('coupon::coupons.index.status_valid'))
            . '</span>';
    }
}
