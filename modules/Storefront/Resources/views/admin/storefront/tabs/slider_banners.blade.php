<div class="st-fields-grid st-fields-grid--sections st-fields-grid--tiles">
    <div class="st-fields-grid__full">
        <div class="st-enable-card">
            {{ Form::checkbox('storefront_slider_banners_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_slider_side_banners'), $errors, $settings) }}
        </div>
    </div>

    <div class="st-fields-grid__col">
        @include('storefront::admin.storefront.tabs.partials.banner_block', [
            'label' => trans('storefront::storefront.form.banner_1'),
            'name' => 'storefront_slider_banner_1',
            'banner' => $banners['banner_1'],
        ])
    </div>

    <div class="st-fields-grid__col">
        @include('storefront::admin.storefront.tabs.partials.banner_block', [
            'label' => trans('storefront::storefront.form.banner_2'),
            'name' => 'storefront_slider_banner_2',
            'banner' => $banners['banner_2'],
        ])
    </div>
</div>
