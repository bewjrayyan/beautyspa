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
            {{ Form::checkbox('storefront_product_tabs_2_section_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_product_tabs_two_section'), $errors, $settings) }}
        </div>
    </div>

    <div class="st-fields-grid__full">
        @component('setting::admin.settings.partials.section', [
            'class' => 'st-section--compact',
        ])
            {{ Form::text('translatable[storefront_product_tabs_2_section_title]', trans('storefront::attributes.title'), $errors, $settings) }}
        @endcomponent
    </div>

    @for ($tabNumber = 1; $tabNumber <= 4; $tabNumber++)
        <div class="st-fields-grid__col">
            @include('storefront::admin.storefront.tabs.partials.product_tab_block', [
                'title' => trans('storefront::storefront.form.tab_' . $tabNumber),
                'titleField' => 'translatable[storefront_product_tabs_2_section_tab_' . $tabNumber . '_title]',
                'fieldNamePrefix' => 'storefront_product_tabs_2_section_tab_' . $tabNumber,
                'products' => $tabProducts[$tabNumber],
            ])
        </div>
    @endfor
</div>

@include('admin::partials.selectize_remote')
