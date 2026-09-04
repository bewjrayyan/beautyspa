<?php

namespace Modules\Report\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Order\Entities\Order;
use Modules\Report\TaxReport;
use Illuminate\Http\Response;
use Modules\Report\SalesReport;
use Modules\Report\SearchReport;
use Modules\Report\CouponsReport;
use Modules\Report\ShippingReport;
use Modules\Report\ProductsViewReport;
use Modules\Report\ProductsStockReport;
use Modules\Report\TaxedProductsReport;
use Modules\Report\CustomersOrderReport;
use Modules\Report\TaggedProductsReport;
use Modules\Report\BrandedProductsReport;
use Modules\Report\ProductsPurchaseReport;
use Modules\Report\CategorizedProductsReport;
use Modules\Report\BeauticianBookingsReport;
use Modules\Report\Services\ReportExportService;
use Nwidart\Modules\Facades\Module;

class ReportController
{
    /**
     * Array of available reports.
     *
     * @var array
     */
    private array $reports = [
        'coupons_report' => CouponsReport::class,
        'customers_order_report' => CustomersOrderReport::class,
        'products_purchase_report' => ProductsPurchaseReport::class,
        'products_stock_report' => ProductsStockReport::class,
        'products_view_report' => ProductsViewReport::class,
        'branded_products_report' => BrandedProductsReport::class,
        'categorized_products_report' => CategorizedProductsReport::class,
        'taxed_products_report' => TaxedProductsReport::class,
        'tagged_products_report' => TaggedProductsReport::class,
        'sales_report' => SalesReport::class,
        'search_report' => SearchReport::class,
        'shipping_report' => ShippingReport::class,
        'tax_report' => TaxReport::class,
        'beautician_bookings_report' => BeauticianBookingsReport::class,
    ];


    public function __construct()
    {
        if (Module::isEnabled('Loyalty')) {
            $this->reports['loyalty_report'] = \Modules\Loyalty\Reports\LoyaltyTransactionsReport::class;
        }
    }


    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $type = $request->query('type');

        if (! is_string($type) || ! $this->reportTypeExists($type)) {
            return redirect()->route('admin.reports.index', ['type' => 'coupons_report']);
        }

        if ($type === 'beautician_bookings_report' && !Module::isEnabled('Beautician')) {
            return redirect()->route('admin.reports.index', ['type' => 'sales_report']);
        }

        if ($type === 'loyalty_report' && !Module::isEnabled('Loyalty')) {
            return redirect()->route('admin.reports.index', ['type' => 'sales_report']);
        }

        $this->validateFilters($request);

        return $this->report($type)->render($request);
    }


    public function export(Request $request, ReportExportService $exporter)
    {
        $this->validateFilters($request, true);

        $type = $request->query('type');

        if (! $this->reportTypeExists($type)) {
            abort(404);
        }

        if ($type === 'beautician_bookings_report' && ! Module::isEnabled('Beautician')) {
            abort(404);
        }

        if ($type === 'loyalty_report' && ! Module::isEnabled('Loyalty')) {
            abort(404);
        }

        return $exporter->export($this->report($type), $request, $type, $request->query('format'));
    }


    private function validateFilters(Request $request, bool $export = false): void
    {
        $rules = [
            'type' => ['required', 'string', Rule::in(array_keys($this->reports))],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'status' => ['nullable', 'string', Rule::in(Order::statuses())],
            'group' => ['nullable', 'string', Rule::in(['years', 'months', 'weeks', 'days'])],
            'coupon_code' => ['nullable', 'string', 'max:191'],
            'customer_name' => ['nullable', 'string', 'max:191'],
            'customer_email' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:191'],
            'category' => ['nullable', 'string', 'max:191'],
            'tag' => ['nullable', 'string', 'max:191'],
            'keyword' => ['nullable', 'string', 'max:191'],
            'shipping_method' => ['nullable', 'string', 'max:191'],
            'tax_name' => ['nullable', 'string', 'max:191'],
            'sku' => ['nullable', 'string', 'max:191'],
            'stock_availability' => ['nullable', 'string', Rule::in(['in_stock', 'out_of_stock'])],
            'quantity_above' => ['nullable', 'numeric', 'min:0'],
            'quantity_below' => ['nullable', 'numeric', 'min:0'],
            'product_id' => ['nullable', 'integer', 'min:1'],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'tax_class' => ['nullable', 'integer', 'min:1'],
            'spa_branch_id' => ['nullable', 'integer', 'min:1'],
            'beautician_id' => ['nullable', 'integer', 'min:1'],
            'option_value_ids' => ['nullable', 'array', 'max:50'],
            'option_value_ids.*' => ['integer', 'distinct', 'min:1'],
            'variation_value_ids' => ['nullable', 'array', 'max:50'],
            'variation_value_ids.*' => ['integer', 'distinct', 'min:1'],
        ];

        if ($export) {
            $rules['format'] = ['required', 'string', Rule::in(['csv', 'xlsx', 'pdf'])];
        }

        $request->validate($rules);
    }


    /**
     * Determine if the report type exists.
     *
     * @param string $type
     *
     * @return bool
     */
    private function reportTypeExists($type)
    {
        return is_string($type) && array_key_exists($type, $this->reports);
    }


    /**
     * Returns a new instance of the given type of report.
     *
     * @param string $type
     *
     * @return mixed
     */
    private function report($type)
    {
        return new $this->reports[$type];
    }
}
