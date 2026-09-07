<?php

namespace Modules\Shipping\Http\Controllers\Admin;

use Modules\Admin\Traits\HasCrudActions;
use Modules\Shipping\Entities\ShippingClass;
use Modules\Shipping\Http\Requests\SaveShippingClassRequest;

class ShippingClassController
{
    use HasCrudActions;

    protected $model = ShippingClass::class;

    protected $label = 'shipping::shipping_classes.shipping_class';

    protected $viewPath = 'shipping::admin.shipping_classes';

    protected $routePrefix = 'admin.shipping_classes';

    protected $validation = SaveShippingClassRequest::class;
}
