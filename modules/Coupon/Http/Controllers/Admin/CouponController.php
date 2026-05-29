<?php

namespace Modules\Coupon\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Coupon\Entities\Coupon;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Coupon\Http\Requests\SaveCouponRequest;

class CouponController
{
    use HasCrudActions;

    protected $model = Coupon::class;

    protected $label = 'coupon::coupons.coupon';

    protected $viewPath = 'coupon::admin.coupons';

    protected $validation = SaveCouponRequest::class;


    public function index(Request $request)
    {
        if ($request->has('query')) {
            return $this->getModel()
                ->search($request->get('query'))
                ->query()
                ->limit($request->get('limit', 10))
                ->get();
        }

        $query = Coupon::withoutGlobalScope('active');
        $today = today();

        $validNow = (clone $query)
            ->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->where(function ($q) use ($today) {
                    $q->whereNull('start_date')->orWhereDate('start_date', '<=', $today);
                })->where(function ($q) use ($today) {
                    $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
                });
            })
            ->count();

        return view("{$this->viewPath}.index", [
            'stats' => [
                'total' => (clone $query)->count(),
                'active' => (clone $query)->where('is_active', true)->count(),
                'valid_now' => $validNow,
                'free_shipping' => (clone $query)->where('free_shipping', true)->count(),
            ],
            'featured' => (clone $query)
                ->orderByDesc('created_at')
                ->limit(4)
                ->get(),
        ]);
    }
}
