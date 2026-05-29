<div
    id="sales-report-options-wrapper"
    class="report-filter-zone report-filter-zone--options hide"
    data-url="{{ route('admin.reports.products.options') }}"
    data-selected-option-values="{{ json_encode(array_values(array_filter((array) request('option_value_ids', [])))) }}"
    data-selected-variation-values="{{ json_encode(array_values(array_filter((array) request('variation_value_ids', [])))) }}"
>
    <div class="report-filter-zone__head">
        <span class="report-filter-zone__icon" aria-hidden="true">
            <i class="fa fa-tags"></i>
        </span>
        <div class="report-filter-zone__titles">
            <h5 class="report-filter-zone__title">{{ trans('report::admin.filters.options') }}</h5>
            <p class="report-filter-zone__hint">{{ trans('report::admin.filters.zone_options_hint') }}</p>
        </div>
    </div>

    <div id="sales-report-options" class="sales-report-option-groups"></div>

    <p class="report-filter-empty-note sales-report-options-empty hide">
        <i class="fa fa-info-circle" aria-hidden="true"></i>
        {{ trans('report::admin.filters.no_product_options') }}
    </p>
</div>
