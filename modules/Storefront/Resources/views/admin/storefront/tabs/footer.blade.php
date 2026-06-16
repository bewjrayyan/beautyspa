<div class="st-fields-grid st-fields-grid--sections st-fields-grid--tiles">
    <div class="st-fields-grid__col">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-columns',
            'title' => trans('storefront::storefront.sections.footer_content'),
            'description' => trans('storefront::storefront.sections.footer_content_desc'),
        ])
            {{ Form::select('storefront_footer_tags', trans('storefront::attributes.storefront_footer_tags'), $errors, $tags, $settings, ['class' => 'selectize prevent-creation', 'multiple' => true]) }}
            {{ Form::text('translatable[storefront_copyright_text]', trans('storefront::attributes.storefront_copyright_text'), $errors, $settings) }}
        @endcomponent
    </div>

    <div class="st-fields-grid__col">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-credit-card',
            'title' => trans('storefront::storefront.form.accepted_payment_methods_image'),
            'class' => 'st-section--media',
        ])
            @include('media::admin.image_picker.single', [
                'title' => '',
                'aspect' => 'banner',
                'inputName' => 'storefront_accepted_payment_methods_image',
                'file' => $acceptedPaymentMethodsImage,
            ])
        @endcomponent
    </div>
</div>
