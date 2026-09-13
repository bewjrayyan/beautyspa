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


    public function destroy(?string $ids = null)
    {
        $idList = array_values(array_filter(array_map('intval', explode(',', (string) $ids))));

        if ($idList === []) {
            return back();
        }

        $blocked = ShippingClass::withoutGlobalScope('active')
            ->whereIn('id', $idList)
            ->withCount(['products' => function ($query) {
                $query->withoutGlobalScopes();
            }])
            ->get()
            ->filter(fn (ShippingClass $shippingClass) => (int) $shippingClass->products_count > 0);

        if ($blocked->isNotEmpty()) {
            $names = $blocked->pluck('name')->implode(', ');
            $message = trans('shipping::shipping_classes.messages.destroy_has_products', [
                'classes' => $names,
            ]);

            if (request()->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()
                ->route("{$this->routePrefix}.index")
                ->withError($message);
        }

        $this->getModel()
            ->withoutGlobalScope('active')
            ->whereIn('id', $idList)
            ->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => trans('admin::messages.resource_deleted', ['resource' => $this->getLabel()]),
            ]);
        }

        return redirect()
            ->route("{$this->routePrefix}.index")
            ->withSuccess(trans('admin::messages.resource_deleted', ['resource' => $this->getLabel()]));
    }
}
