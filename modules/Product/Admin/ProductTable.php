<?php

namespace Modules\Product\Admin;

use Modules\Admin\Ui\AdminTable;
use Illuminate\Http\JsonResponse;
use Modules\Product\Entities\Product;

class ProductTable extends AdminTable
{
    /**
     * Raw columns that will not be escaped.
     *
     * @var array
     */
    protected array $rawColumns = ['name', 'price', 'in_stock', 'action'];


    /**
     * Make table response for the resource.
     *
     * @return JsonResponse
     */
    public function make()
    {
        return $this->newTable()
            ->editColumn('name', function (Product $product) {
                $status = $product->is_active
                    ? "<span class='badge badge-success'>" . trans('admin::admin.table.active') . '</span>'
                    : "<span class='badge badge-danger'>" . trans('admin::admin.table.inactive') . '</span>';

                return "<div class='product-name-with-status'><span class='product-table-name'>" . e($product->name) . "</span>{$status}</div>";
            })
            ->editColumn('thumbnail', function ($product) {
                return view('admin::partials.table.image', [
                    'file' => ($product->variant && $product->variant->base_image->id) ? $product->variant->base_image : $product->base_image,
                ]);
            })
            ->editColumn('price', function (Product $product) {
                return product_price_formatted($product->variant ?? $product, function ($price, $specialPrice) use ($product) {
                    if ($product->variant ? $product->variant->hasSpecialPrice() : $product->hasSpecialPrice()) {
                        return "<span class='m-r-5'>{$specialPrice}</span>
                            <del class='text-red'>{$price}</del>";
                    }

                    return "<span class='m-r-5'>{$price}</span>";
                });
            })
            ->editColumn('in_stock', function (Product $product) {
                if ($product->isVirtualTreatment()) {
                    return "<span class='badge badge-info'>" . trans('product::products.table.virtual_treatment') . "</span>";
                }

                $item = $product->variant ?? $product;
                $in_stock = $item->in_stock && (!$item->manage_stock || $item->qty > 0);

                if ($item->manage_stock && $item->qty > 0) {
                    return "<span class='badge badge-primary'>" . trans('product::products.table.in_stock_qty', ['qty' => $item->qty]) . "</span>";
                }

                return $in_stock
                    ? "<span class='badge badge-primary'>" . trans('product::products.form.stock_availability_states.1') . "</span>"
                    : "<span class='badge badge-danger'>" . trans('product::products.form.stock_availability_states.0') . "</span>";
            })
            ->addColumn('action', function (Product $product) {
                return view('product::admin.products.partials.table.action', compact('product'));
            });
    }
}
