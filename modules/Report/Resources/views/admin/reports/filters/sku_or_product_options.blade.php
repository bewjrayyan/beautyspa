@php
    $showOptions = ($selectedProduct ?? null)?->isVirtualTreatment() ?? false;
@endphp

<div id="report-sku-field" class="form-group report-field @if ($showOptions) hide @endif">
    <label class="report-field__label" for="sku">{{ trans('report::admin.filters.sku') }}</label>
    <input
        type="text"
        name="sku"
        class="form-control"
        id="sku"
        value="{{ $showOptions ? '' : $request->sku }}"
    >
</div>

<div
    id="report-purchase-options"
    class="form-group report-field report-product-options-inline @if (! $showOptions) hide @endif"
    data-selected-option-values="{{ json_encode(array_values(array_filter((array) request('option_value_ids', [])))) }}"
    data-selected-variation-values="{{ json_encode(array_values(array_filter((array) request('variation_value_ids', [])))) }}"
>
    <label class="report-field__label">{{ trans('report::admin.filters.options') }}</label>

    <div class="sales-report-option-groups report-product-options-inline__groups"></div>

    <p class="report-field__hint sales-report-options-empty hide">
        {{ trans('report::admin.filters.no_product_options') }}
    </p>
</div>
