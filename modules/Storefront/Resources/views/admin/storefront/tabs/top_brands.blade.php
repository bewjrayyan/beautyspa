<div class="st-fields-grid st-fields-grid--sections">
    <div class="st-enable-card">
        {{ Form::checkbox('storefront_top_brands_section_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_brands_section'), $errors, $settings) }}
    </div>

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-tags',
        'title' => trans('storefront::storefront.tabs.top_brands'),
        'class' => 'st-section--compact',
    ])
        {{ Form::select('storefront_top_brands', trans('storefront::attributes.storefront_top_brands'), $errors, $brands, setting(), ['class' => 'selectize prevent-creation', 'multiple' => true]) }}
    @endcomponent
</div>
