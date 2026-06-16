@php
    $verticalProducts = [
        1 => $verticalProductsOne,
        2 => $verticalProductsTwo,
        3 => $verticalProductsThree,
    ];
@endphp

<div class="st-fields-grid st-fields-grid--sections st-fields-grid--tiles">
    <div class="st-fields-grid__full">
        <div class="st-enable-card">
            {{ Form::checkbox('storefront_flash_sale_and_vertical_products_section_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_flash_sale_and_vertical_products_section'), $errors, $settings) }}
        </div>
    </div>

    <div class="st-fields-grid__full">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-bolt',
            'title' => trans('storefront::storefront.form.flash_sale'),
            'class' => 'st-section--compact',
            'columns' => 2,
        ])
            {{ Form::text('translatable[storefront_flash_sale_title]', trans('storefront::attributes.title'), $errors, $settings) }}
            {{ Form::select('storefront_active_flash_sale_campaign', trans('storefront::attributes.storefront_active_flash_sale_campaign'), $errors, $flashSales, $settings) }}
        @endcomponent
    </div>

    @for ($verticalNumber = 1; $verticalNumber <= 3; $verticalNumber++)
        <div class="st-fields-grid__col">
            @include('storefront::admin.storefront.tabs.partials.product_tab_block', [
                'icon' => 'fa-list',
                'title' => trans('storefront::storefront.form.vertical_products_' . $verticalNumber),
                'titleField' => 'translatable[storefront_vertical_products_' . $verticalNumber . '_title]',
                'fieldNamePrefix' => 'storefront_vertical_products_' . $verticalNumber,
                'products' => $verticalProducts[$verticalNumber],
            ])
        </div>
    @endfor
</div>
