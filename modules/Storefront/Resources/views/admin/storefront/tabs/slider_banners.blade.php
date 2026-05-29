<div class="accordion-box-content">
    <div class="row">
        <div class="col-md-8">
            {{ Form::checkbox('storefront_slider_banners_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_slider_side_banners'), $errors, $settings) }}
        </div>
    </div>

    <div class="tab-content clearfix">
        <div class="panel-wrap">
            @include('storefront::admin.storefront.tabs.partials.single_banner', [
                'label' => trans('storefront::storefront.form.banner_1'),
                'name' => 'storefront_slider_banner_1',
                'banner' => $banners['banner_1'],
            ])

            @include('storefront::admin.storefront.tabs.partials.single_banner', [
                'label' => trans('storefront::storefront.form.banner_2'),
                'name' => 'storefront_slider_banner_2',
                'banner' => $banners['banner_2'],
            ])
        </div>
    </div>
</div>
