@php
    use Modules\Product\Entities\Product;

    $inputId = $id ?? 'report-product';
    $requireCategory = $requireCategory ?? false;
    $categorySelector = $categorySelector ?? '#category_id';
    $fieldClass = $class ?? '';
    $selectedProduct = $selectedProduct ?? null;

    if (! $selectedProduct && request()->filled('product_id')) {
        $selectedProduct = Product::withoutGlobalScope('active')
            ->withName()
            ->find(request('product_id'));
    }
@endphp

<div
    class="form-group report-field report-product-field report-product-autocomplete {{ $fieldClass }}"
    data-url="{{ route('admin.reports.products.search') }}"
    data-require-category="{{ $requireCategory ? '1' : '0' }}"
    data-category-selector="{{ $requireCategory ? $categorySelector : '' }}"
    data-not-found="{{ trans('report::admin.filters.product_not_found') }}"
    data-results-count="{{ trans('report::admin.filters.product_results_count') }}"
    @if (! empty($optionsUrl))
        data-options-url="{{ $optionsUrl }}"
        data-options-target="{{ $optionsTarget ?? '#sales-report-options-wrapper' }}"
    @endif
    @if (! empty($skuOptionsSwap))
        data-sku-options-swap="1"
        data-sku-field="{{ $skuField ?? '#report-sku-field' }}"
    @endif
>
    <label class="report-field__label" for="{{ $inputId }}">{{ trans('report::admin.filters.product') }}</label>

    <div class="report-product-autocomplete__wrap">
        <input
            type="text"
            id="{{ $inputId }}"
            class="form-control report-product-autocomplete__input"
            value="{{ $selectedProduct->name ?? '' }}"
            placeholder="{{ trans('report::admin.filters.product_search_placeholder') }}"
            autocomplete="off"
            @if ($requireCategory && ! request()->filled('category_id'))
                disabled
            @endif
        >
        <input
            type="hidden"
            name="product_id"
            class="report-product-autocomplete__value"
            value="{{ $selectedProduct->id ?? request('product_id') }}"
        >
        <div class="report-product-autocomplete__results" role="listbox" hidden></div>
    </div>

    @if ($requireCategory)
        <p class="report-field__hint report-product-category-help @if (request()->filled('category_id')) hide @endif">
            {{ trans('report::admin.filters.select_category_first') }}
        </p>
    @endif
</div>
