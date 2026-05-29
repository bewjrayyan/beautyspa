<?php

namespace Modules\Report;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderProduct;
use Modules\Product\Entities\Product;
use Modules\Report\Concerns\JoinsOrderReportDetails;

class ProductsPurchaseReport extends Report
{
    use JoinsOrderReportDetails;

    protected $filters = ['from', 'to', 'status', 'product_id', 'sku'];

    protected $date = 'orders.created_at';


    public function report($request)
    {
        parent::report($request);

        $this->applyProductOptionFilters($request);

        return $this->query;
    }


    protected function data()
    {
        $selectedProduct = null;

        if (request()->filled('product_id')) {
            $selectedProduct = Product::withoutGlobalScope('active')
                ->withName()
                ->find(request('product_id'));
        }

        return compact('selectedProduct');
    }


    protected function view()
    {
        return 'report::admin.reports.products_purchase_report.index';
    }


    protected function query()
    {
        $query = Order::withTrashed()->select('orders.id');

        $this->applyOrderReportDetailJoins($query);
        $this->addOrderReportDetailSelects($query);

        return $query
            ->addSelect('orders.total')
            ->whereHas('products', fn (Builder $lineQuery) => $this->constrainOrderProductLines($lineQuery))
            ->with([
                'products' => fn ($lineQuery) => $this->constrainOrderProductLines(
                    $lineQuery->with(['product', 'options.values', 'variations'])
                ),
            ])
            ->orderByDesc('orders.created_at');
    }

    protected function constrainOrderProductLines($query): void
    {
        if (request()->filled('product_id')) {
            $query->where('product_id', request('product_id'));
        }

        if (request()->filled('sku')) {
            $query->whereHas('product', function (Builder $productQuery) {
                $productQuery->where('sku', request('sku'));
            });
        }
    }

    protected function sku($sku)
    {
        if ($sku === '' || $sku === null) {
            return;
        }

        $this->query->whereHas('products', function (Builder $lineQuery) use ($sku) {
            $lineQuery->whereHas('product', function (Builder $productQuery) use ($sku) {
                $productQuery->where('sku', $sku);
            });
        });
    }

    protected function product_id($productId)
    {
        if ($productId === '' || $productId === null) {
            return;
        }

        $this->query->whereHas('products', function (Builder $lineQuery) use ($productId) {
            $lineQuery->where('product_id', $productId);
        });
    }

    protected function applyProductOptionFilters(Request $request): void
    {
        $optionValueIds = array_values(array_filter(array_map('intval', (array) $request->get('option_value_ids', []))));
        $variationValueIds = array_values(array_filter(array_map('intval', (array) $request->get('variation_value_ids', []))));

        if ($optionValueIds === [] && $variationValueIds === []) {
            return;
        }

        $applyToOrder = function (Builder $orderQuery) use ($optionValueIds, $variationValueIds) {
            $orderQuery->whereHas('products', function (Builder $lineQuery) use ($optionValueIds, $variationValueIds) {
                $this->applyLineOptionFilters($lineQuery, $optionValueIds, $variationValueIds);
            });
        };

        $applyToOrder($this->query);

        $this->query->with([
            'products' => function ($lineQuery) use ($optionValueIds, $variationValueIds) {
                $this->constrainOrderProductLines($lineQuery);
                $this->applyLineOptionFilters($lineQuery, $optionValueIds, $variationValueIds);
                $lineQuery->with(['product', 'options.values', 'variations']);
            },
        ]);
    }

    protected function applyLineOptionFilters(Builder $lineQuery, array $optionValueIds, array $variationValueIds): void
    {
        if ($optionValueIds !== []) {
            $optionValuesByOption = DB::table('option_values')
                ->whereIn('id', $optionValueIds)
                ->get(['id', 'option_id'])
                ->groupBy('option_id');

            foreach ($optionValuesByOption as $optionId => $values) {
                $valueIds = $values->pluck('id')->all();

                $lineQuery->whereHas('options', function (Builder $optionQuery) use ($valueIds, $optionId) {
                    $optionQuery
                        ->where('option_id', $optionId)
                        ->whereHas('values', function (Builder $valueQuery) use ($valueIds) {
                            $valueQuery->whereIn('option_values.id', $valueIds);
                        });
                });
            }
        }

        if ($variationValueIds !== []) {
            $variationValuesByVariation = DB::table('variation_values')
                ->whereIn('id', $variationValueIds)
                ->get(['id', 'variation_id'])
                ->groupBy('variation_id');

            foreach ($variationValuesByVariation as $variationId => $values) {
                $valueIds = $values->pluck('id')->all();

                $lineQuery->whereHas('variations', function (Builder $variationQuery) use ($valueIds, $variationId) {
                    $variationQuery
                        ->where('variation_id', $variationId)
                        ->whereHas('values', function (Builder $valueQuery) use ($valueIds) {
                            $valueQuery->whereIn('variation_values.id', $valueIds);
                        });
                });
            }
        }
    }
}
