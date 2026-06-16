@php
    $tabProducts = [
        1 => $tabOneProducts,
        2 => $tabTwoProducts,
        3 => $tabThreeProducts,
        4 => $tabFourProducts,
    ];
@endphp

<div class="st-fields-grid st-fields-grid--sections st-fields-grid--tiles">
    <div class="st-fields-grid__full">
        <div class="st-enable-card">
            {{ Form::checkbox('storefront_product_tabs_1_section_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_product_tabs_one_section'), $errors, $settings) }}
        </div>
    </div>

    @for ($tabNumber = 1; $tabNumber <= 4; $tabNumber++)
        <div class="st-fields-grid__col">
            @include('storefront::admin.storefront.tabs.partials.product_tab_block', [
                'title' => trans('storefront::storefront.form.tab_' . $tabNumber),
                'titleField' => 'translatable[storefront_product_tabs_1_section_tab_' . $tabNumber . '_title]',
                'fieldNamePrefix' => 'storefront_product_tabs_1_section_tab_' . $tabNumber,
                'products' => $tabProducts[$tabNumber],
            ])
        </div>
    @endfor
</div>
