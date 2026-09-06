<?php

namespace Modules\TreatmentReservation\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Product\Entities\Product;
use Modules\TreatmentReservation\Entities\TreatmentCategory;
use Modules\TreatmentReservation\Services\TreatmentProductDurationService;

class PosController extends Controller
{
    public function index(Request $request, TreatmentProductDurationService $durations): View
    {
        $products = Product::query()
            ->where('is_virtual', true)
            ->where('is_active', true)
            ->with([
                'files',
                'variants' => fn ($query) => $query->where('is_active', true)->orderBy('position'),
                'variations.values',
                'options.values',
            ])
            ->orderBy('id')
            ->get();
        $categoryNames = TreatmentCategory::query()
            ->whereIn('id', $products->pluck('treatment_category_id')->filter()->unique())
            ->pluck('name', 'id');

        return view('treatmentreservation::admin.pos.index', [
            'isPortal' => str_starts_with((string) optional($request->route())->getName(), 'admin.treatment_reservations.portal.'),
            'pageTitle' => trans('treatmentreservation::sidebar.pos_booking'),
            'catalog' => $products->map(function (Product $product) use ($durations, $categoryNames) {
                [$minutes] = $durations->resolveMinutesForProduct($product);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->base_image?->path,
                    'price' => (float) $product->selling_price->amount(),
                    'duration_minutes' => $minutes,
                    'category_id' => $product->treatment_category_id,
                    'category_name' => $categoryNames[(int) $product->treatment_category_id] ?? 'Treatment',
                    'variations' => $product->variations->map(fn ($variation) => [
                        'uid' => $variation->uid,
                        'name' => $variation->name,
                        'values' => $variation->values->map(fn ($value) => [
                            'uid' => $value->uid,
                            'label' => $value->label,
                        ])->values(),
                    ])->values(),
                    'variants' => $product->variants->map(fn ($variant) => [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'uid' => $variant->uid,
                        'uids' => $variant->uids,
                        'price' => (float) $variant->selling_price->amount(),
                    ])->values(),
                    'options' => $product->options->map(fn ($option) => [
                        'id' => $option->id,
                        'name' => $option->name,
                        'type' => $option->type,
                        'is_required' => (bool) $option->is_required,
                        'values' => $option->values->map(fn ($value) => [
                            'id' => $value->id,
                            'label' => $value->label,
                            'price' => (float) ($value->price?->amount() ?? 0),
                        ])->values(),
                    ])->values(),
                ];
            })->values()->all(),
        ]);
    }
}
