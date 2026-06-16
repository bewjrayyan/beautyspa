<div class="st-fields-grid st-fields-grid--sections st-fields-grid--tiles st-fields-grid--tiles-3">
    <div class="st-fields-grid__full">
        <div class="st-enable-card">
            {{ Form::checkbox('storefront_three_column_full_width_banners_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_three_column_full_width_banners_section'), $errors, $settings) }}
        </div>
    </div>

    <div class="st-fields-grid__full">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-image',
            'title' => trans('storefront::storefront.form.background'),
            'class' => 'st-section--media',
        ])
            @include('media::admin.image_picker.single', [
                'title' => '',
                'aspect' => 'banner',
                'inputName' => 'storefront_three_column_full_width_banners_background_file_id',
                'file' => $banners['background']->image,
            ])
        @endcomponent
    </div>

    @foreach ([1, 2, 3] as $bannerNumber)
        <div class="st-fields-grid__col">
            @include('storefront::admin.storefront.tabs.partials.banner_block', [
                'label' => trans('storefront::storefront.form.banner_' . $bannerNumber),
                'name' => 'storefront_three_column_full_width_banners_' . $bannerNumber,
                'banner' => $banners['banner_' . $bannerNumber],
            ])
        </div>
    @endforeach
</div>
