<div class="st-fields-grid st-fields-grid--sections st-fields-grid--tiles">
    <div class="st-fields-grid__full">
        <div class="st-enable-card">
            {{ Form::checkbox('storefront_two_column_banners_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_two_column_banners_section'), $errors, $settings) }}
        </div>
    </div>

    <div class="st-fields-grid__col">
        @include('storefront::admin.storefront.tabs.partials.banner_block', [
            'label' => trans('storefront::storefront.form.banner_1'),
            'name' => 'storefront_two_column_banners_1',
            'banner' => $banners['banner_1'],
        ])
    </div>

    <div class="st-fields-grid__col">
        @include('storefront::admin.storefront.tabs.partials.banner_block', [
            'label' => trans('storefront::storefront.form.banner_2'),
            'name' => 'storefront_two_column_banners_2',
            'banner' => $banners['banner_2'],
        ])
    </div>
</div>
