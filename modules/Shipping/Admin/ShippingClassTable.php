<?php

namespace Modules\Shipping\Admin;

use Modules\Admin\Ui\AdminTable;
use Modules\Support\Money;
use Modules\Shipping\Entities\ShippingClass;
use Yajra\DataTables\Exceptions\Exception;

class ShippingClassTable extends AdminTable
{
    /**
     * @throws Exception
     */
    public function make()
    {
        return $this->newTable()
            ->editColumn('cost', function (ShippingClass $shippingClass) {
                return Money::inDefaultCurrency($shippingClass->cost)->format();
            });
    }
}
