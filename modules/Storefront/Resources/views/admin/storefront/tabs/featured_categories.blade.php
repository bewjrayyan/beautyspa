@php
    $featuredCategoryProducts = [
        1 => $categoryOneProducts,
        2 => $categoryTwoProducts,
        3 => $categoryThreeProducts,
        4 => $categoryFourProducts,
        5 => $categoryFiveProducts,
        6 => $categorySixProducts,
    ];
@endphp

<div class="st-fields-grid st-fields-grid--sections st-fields-grid--tiles">
    <div class="st-fields-grid__full">
        <div class="st-enable-card">
            {{ Form::checkbox('storefront_featured_categories_section_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_featured_categories_section'), $errors, $settings) }}
        </div>
    </div>

    <div class="st-fields-grid__full">
        @component('setting::admin.settings.partials.section', [
            'class' => 'st-section--compact',
            'columns' => 2,
        ])
            {{ Form::text('translatable[storefront_featured_categories_section_title]', trans('storefront::attributes.section_title'), $errors, $settings) }}
            {{ Form::text('translatable[storefront_featured_categories_section_subtitle]', trans('storefront::attributes.section_subtitle'), $errors, $settings) }}
        @endcomponent
    </div>

    @for ($categoryNumber = 1; $categoryNumber <= 6; $categoryNumber++)
        <div class="st-fields-grid__col">
            @component('setting::admin.settings.partials.section', [
                'icon' => 'fa-folder-o',
                'title' => trans('storefront::storefront.form.category_' . $categoryNumber),
                'class' => 'st-section--compact',
            ])
                <div class="st-featured-category-fields">
                    {{ Form::select('storefront_featured_categories_section_category_' . $categoryNumber . '_category_id', trans('storefront::attributes.category'), $errors, $categories, $settings) }}

                    @include('storefront::admin.storefront.tabs.partials.products', [
                        'fieldNamePrefix' => 'storefront_featured_categories_section_category_' . $categoryNumber,
                        'products' => $featuredCategoryProducts[$categoryNumber],
                        'featuredCategories' => true,
                    ])
                </div>
            @endcomponent
        </div>
    @endfor
</div>
