<div class="st-fields-grid st-fields-grid--sections">
    <div class="st-fields-grid__full">
        <div class="st-enable-card">
            {{ Form::checkbox('storefront_one_column_banner_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_one_column_banner_section'), $errors, $settings) }}
        </div>
    </div>

    @include('storefront::admin.storefront.tabs.partials.banner_block', [
        'label' => trans('storefront::storefront.form.banner'),
        'name' => 'storefront_one_column_banner',
        'banner' => $banner,
    ])
</div>
