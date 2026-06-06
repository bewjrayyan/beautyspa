<?php

namespace Modules\Report;

use Illuminate\Http\Request;
use Modules\Category\Entities\Category;
use Modules\Order\Entities\Order;
use Modules\Product\Entities\Product;
use Illuminate\Support\Facades\DB;
use Modules\Report\Concerns\FiltersBySpaBranch;
use Modules\Report\Concerns\JoinsOrderReportDetails;

class SalesReport extends Report
{
    use FiltersBySpaBranch;
    use JoinsOrderReportDetails;

    protected $filters = ['from', 'to', 'status', 'category_id', 'product_id', 'spa_branch_id'];

    protected $date = 'orders.created_at';

    public function report($request)
    {
        parent::report($request);

        $this->applyProductOptionFilters($request);

        return $this->query;
    }

    protected function data()
    {
        $selectedReportProduct = null;

        if (request()->filled('product_id')) {
            $selectedReportProduct = Product::withoutGlobalScope('active')
                ->withName()
                ->find(request('product_id'));
        }

        return [
            'categories' => Category::treeList(),
            'selectedReportProduct' => $selectedReportProduct,
            'spaBranches' => $this->spaBranchesForFilter(),
        ];
    }

    protected function view()
    {
        return 'report::admin.reports.sales_report.index';
    }


    protected function query()
    {
        $query = Order::withTrashed();

        $this->applyOrderReportDetailJoins($query);
        $this->addOrderReportDetailSelects($query);

        return $query
            ->selectRaw('(SELECT COALESCE(SUM(qty), 0) FROM order_products WHERE order_products.order_id = orders.id) as total_products')
            ->addSelect([
                'orders.sub_total',
                'orders.shipping_cost',
                'orders.discount',
                'orders.total',
            ])
            ->selectRaw('(SELECT COALESCE(SUM(amount), 0) FROM order_taxes WHERE order_taxes.order_id = orders.id) as tax')
            ->orderByDesc('orders.created_at');
    }

    protected function category_id($categoryId)
    {
        if ($categoryId === '' || $categoryId === null) {
            return;
        }

        $this->query->whereExists(function ($query) use ($categoryId) {
            $query->select(DB::raw(1))
                ->from('order_products')
                ->join('product_categories', 'product_categories.product_id', '=', 'order_products.product_id')
                ->whereColumn('order_products.order_id', 'orders.id')
                ->where('product_categories.category_id', $categoryId);
        });
    }

    protected function product_id($productId)
    {
        if ($productId === '' || $productId === null) {
            return;
        }

        $this->query->whereExists(function ($query) use ($productId) {
            $query->select(DB::raw(1))
                ->from('order_products')
                ->whereColumn('order_products.order_id', 'orders.id')
                ->where('order_products.product_id', $productId);
        });
    }

    protected function applyProductOptionFilters(Request $request): void
    {
        $productId = $request->get('product_id');
        $optionValueIds = array_values(array_filter(array_map('intval', (array) $request->get('option_value_ids', []))));
        $variationValueIds = array_values(array_filter(array_map('intval', (array) $request->get('variation_value_ids', []))));

        if ($optionValueIds === [] && $variationValueIds === []) {
            return;
        }

        if ($optionValueIds !== []) {
            $optionValuesByOption = DB::table('option_values')
                ->whereIn('id', $optionValueIds)
                ->get(['id', 'option_id'])
                ->groupBy('option_id');

            foreach ($optionValuesByOption as $optionId => $values) {
                $valueIds = $values->pluck('id')->all();

                $this->query->whereExists(function ($query) use ($valueIds, $optionId, $productId) {
                    $query->select(DB::raw(1))
                        ->from('order_products')
                        ->whereColumn('order_products.order_id', 'orders.id')
                        ->when($productId, function ($orderProductQuery) use ($productId) {
                            $orderProductQuery->where('order_products.product_id', $productId);
                        })
                        ->whereExists(function ($optionQuery) use ($valueIds, $optionId) {
                            $optionQuery->select(DB::raw(1))
                                ->from('order_product_options')
                                ->join(
                                    'order_product_option_values',
                                    'order_product_option_values.order_product_option_id',
                                    '=',
                                    'order_product_options.id'
                                )
                                ->whereColumn('order_product_options.order_product_id', 'order_products.id')
                                ->where('order_product_options.option_id', $optionId)
                                ->whereIn('order_product_option_values.option_value_id', $valueIds);
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

                $this->query->whereExists(function ($query) use ($valueIds, $variationId, $productId) {
                    $query->select(DB::raw(1))
                        ->from('order_products')
                        ->whereColumn('order_products.order_id', 'orders.id')
                        ->when($productId, function ($orderProductQuery) use ($productId) {
                            $orderProductQuery->where('order_products.product_id', $productId);
                        })
                        ->whereExists(function ($variationQuery) use ($valueIds, $variationId) {
                            $variationQuery->select(DB::raw(1))
                                ->from('order_product_variations')
                                ->join(
                                    'order_product_variation_values',
                                    'order_product_variation_values.order_product_variation_id',
                                    '=',
                                    'order_product_variations.id'
                                )
                                ->whereColumn('order_product_variations.order_product_id', 'order_products.id')
                                ->where('order_product_variations.variation_id', $variationId)
                                ->whereIn('order_product_variation_values.variation_value_id', $valueIds);
                        });
                });
            }
        }
    }
}
