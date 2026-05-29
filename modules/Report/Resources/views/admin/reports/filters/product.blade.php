@include('report::admin.reports.filters.product_select', [
    'id' => 'sales-report-product',
    'requireCategory' => true,
    'class' => 'report-filter-field--half sales-report-product-field',
    'optionsUrl' => route('admin.reports.products.options'),
])
