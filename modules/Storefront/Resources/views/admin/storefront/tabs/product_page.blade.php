<div class="st-fields-grid st-fields-grid--sections">
    @include('storefront::admin.storefront.tabs.partials.banner_block', [
        'label' => trans('storefront::storefront.form.product_page_banner'),
        'name' => 'storefront_product_page_banner',
        'banner' => $banner,
    ])

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-whatsapp',
        'title' => trans('storefront::storefront.form.product_social_share'),
        'columns' => 1,
    ])
        <div class="st-fields-grid__full">
            {{ Form::checkbox('storefront_product_share_whatsapp_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_product_share_whatsapp'), $errors, $settings) }}
        </div>

        {{ Form::textarea('storefront_product_share_whatsapp_message', trans('storefront::attributes.storefront_product_share_whatsapp_message'), $errors, $settings, [
            'rows' => 4,
            'placeholder' => trans('storefront::storefront.form.product_share_whatsapp_message_placeholder'),
        ]) }}

        <p class="help-block text-muted st-fields-grid__full">{{ trans('storefront::storefront.form.product_share_whatsapp_og_help') }}</p>
    @endcomponent
</div>
