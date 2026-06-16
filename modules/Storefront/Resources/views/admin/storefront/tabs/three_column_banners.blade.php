<div class="st-fields-grid st-fields-grid--sections st-fields-grid--tiles st-fields-grid--tiles-3">
    <div class="st-fields-grid__full">
        <div class="st-enable-card">
            {{ Form::checkbox('storefront_three_column_banners_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_three_column_banners_section'), $errors, $settings) }}
        </div>
    </div>

    @foreach ([1, 2, 3] as $bannerNumber)
        <div class="st-fields-grid__col">
            @include('storefront::admin.storefront.tabs.partials.banner_block', [
                'label' => trans('storefront::storefront.form.banner_' . $bannerNumber),
                'name' => 'storefront_three_column_banners_' . $bannerNumber,
                'banner' => $banners['banner_' . $bannerNumber],
            ])
        </div>
    @endforeach
</div>
