<?php

namespace Modules\Shipping\Admin;

use Modules\Admin\Ui\AdminTable;
use Modules\Support\Money;
use Modules\Shipping\Entities\ShippingClass;
use Yajra\DataTables\Exceptions\Exception;
use Illuminate\Http\JsonResponse;

class ShippingClassTable extends AdminTable
{
    /**
     * @throws Exception
     */
    public function make(): JsonResponse
    {
        return $this->newTable()
            ->editColumn('cost', function (ShippingClass $shippingClass) {
                return Money::inDefaultCurrency($shippingClass->cost)->format();
            });
    }
}
