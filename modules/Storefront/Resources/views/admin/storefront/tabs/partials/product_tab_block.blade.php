@component('setting::admin.settings.partials.section', [
    'icon' => $icon ?? 'fa-th-list',
    'title' => $title,
    'class' => 'st-section--compact',
])
    @if (! empty($titleField))
        {{ Form::text($titleField, trans('storefront::attributes.title'), $errors, $settings) }}
    @endif

    <div class="st-featured-category-fields">
        @include('storefront::admin.storefront.tabs.partials.products', [
            'fieldNamePrefix' => $fieldNamePrefix,
            'products' => $products,
            'featuredCategories' => $featuredCategories ?? false,
        ])
    </div>
@endcomponent
