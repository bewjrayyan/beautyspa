<div class="st-fields-grid st-fields-grid--sections">
    <div class="st-enable-card">
        {{ Form::checkbox('storefront_features_section_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_features_section'), $errors, $settings) }}
    </div>

    @for ($featureNumber = 1; $featureNumber <= 5; $featureNumber++)
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-star-o',
            'title' => trans('storefront::storefront.form.feature_' . $featureNumber),
            'class' => 'st-section--compact',
            'columns' => 3,
        ])
            {{ Form::text('translatable[storefront_feature_' . $featureNumber . '_title]', trans('storefront::attributes.title'), $errors, $settings) }}
            {{ Form::text('translatable[storefront_feature_' . $featureNumber . '_subtitle]', trans('storefront::attributes.subtitle'), $errors, $settings) }}
            {{ Form::text('storefront_feature_' . $featureNumber . '_icon', trans('storefront::attributes.icon'), $errors, $settings) }}
        @endcomponent
    @endfor

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-comments-o',
        'title' => trans('storefront::storefront.form.product_consultation_cta'),
        'columns' => 2,
    ])
        <div class="st-fields-grid__full">
            {{ Form::checkbox('storefront_product_consultation_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_product_consultation_cta'), $errors, $settings) }}
        </div>

        {{ Form::text('translatable[storefront_product_consultation_label]', trans('storefront::attributes.storefront_product_consultation_label'), $errors, $settings, ['placeholder' => trans('storefront::product.get_free_consultations')]) }}
        {{ Form::text('storefront_product_consultation_url', trans('storefront::attributes.storefront_product_consultation_url'), $errors, $settings, ['placeholder' => trans('storefront::storefront.form.product_consultation_url_placeholder')]) }}
    @endcomponent
</div>
