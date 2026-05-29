<?php

namespace Modules\Report;

use Modules\Product\Entities\Product;

class ProductsViewReport extends Report
{
    protected $filters = ['product_id', 'sku'];


    protected function view()
    {
        return 'report::admin.reports.products_view_report.index';
    }


    protected function query()
    {
        return Product::select('id', 'viewed')
            ->withName()
            ->orderByDesc('viewed');
    }

    protected function sku($sku)
    {
        if ($sku === '' || $sku === null) {
            return;
        }

        $this->query->where('sku', $sku);
    }

    protected function product_id($productId)
    {
        if ($productId === '' || $productId === null) {
            return;
        }

        $this->query->where('products.id', $productId);
    }
}
