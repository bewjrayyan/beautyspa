<div class="st-fields-grid st-fields-grid--sections">
    @include('storefront::admin.storefront.tabs.partials.banner_block', [
        'label' => trans('storefront::storefront.form.product_page_banner'),
        'name' => 'storefront_product_page_banner',
        'banner' => $banner,
    ])
</div>
