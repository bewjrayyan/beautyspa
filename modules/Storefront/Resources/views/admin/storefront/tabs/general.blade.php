<div class="st-fields-grid st-fields-grid--sections">
    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-font',
        'title' => trans('storefront::storefront.sections.branding'),
        'description' => trans('storefront::storefront.sections.branding_desc'),
        'columns' => 2,
    ])
        {{ Form::text('translatable[storefront_welcome_text]', trans('storefront::attributes.storefront_welcome_text'), $errors, $settings) }}
        {{ Form::text('translatable[storefront_products_listing_title]', trans('storefront::attributes.storefront_products_listing_title'), $errors, $settings, ['placeholder' => trans('storefront::storefront.form.products_listing_title_placeholder')]) }}

        <div class="st-fields-grid__full">
            {{ Form::textarea('translatable[storefront_address]', trans('storefront::attributes.storefront_address'), $errors, $settings, ['rows' => 4]) }}
        </div>
    @endcomponent

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-paint-brush',
        'title' => trans('storefront::storefront.sections.appearance'),
        'description' => trans('storefront::storefront.sections.appearance_desc'),
        'columns' => 2,
    ])
        {{ Form::select('storefront_display_font', trans('storefront::attributes.storefront_display_font'), $errors, $display_fonts, $settings) }}
        {{ Form::select('storefront_slider', trans('storefront::attributes.storefront_slider'), $errors, $sliders, $settings) }}
        {{ Form::select('storefront_theme_color', trans('storefront::attributes.storefront_theme_color'), $errors, trans('storefront::themes'), $settings) }}
        {{ Form::select('storefront_mail_theme_color', trans('storefront::attributes.storefront_mail_theme_color'), $errors, trans('storefront::themes'), $settings) }}

        <div class="st-fields-grid__full st-theme-color-field {{ old('storefront_theme_color', array_get($settings, 'storefront_theme_color')) === 'custom_color' ? '' : 'hide' }}"
            id="custom-theme-color">
            {{ Form::color('storefront_custom_theme_color', trans('storefront::attributes.storefront_custom_theme_color'), $errors, $settings) }}
        </div>

        <div class="st-fields-grid__full st-theme-color-field {{ old('storefront_mail_theme_color', array_get($settings, 'storefront_mail_theme_color')) === 'custom_color' ? '' : 'hide' }}"
            id="custom-mail-theme-color">
            {{ Form::color('storefront_custom_mail_theme_color', trans('storefront::attributes.storefront_custom_mail_theme_color'), $errors, $settings) }}
        </div>
    @endcomponent

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-file-text-o',
        'title' => trans('storefront::storefront.sections.legal'),
        'description' => trans('storefront::storefront.sections.legal_desc'),
        'columns' => 2,
    ])
        {{ Form::select('storefront_terms_page', trans('storefront::attributes.storefront_terms_page'), $errors, $pages, $settings) }}
        {{ Form::select('storefront_privacy_page', trans('storefront::attributes.storefront_privacy_page'), $errors, $pages, $settings) }}
    @endcomponent

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-search',
        'title' => trans('storefront::storefront.sections.search'),
        'description' => trans('storefront::storefront.sections.search_desc'),
    ])
        {{ Form::checkbox('storefront_most_searched_keywords_enabled', trans('storefront::attributes.storefront_most_searched_keywords'), trans('storefront::storefront.form.enable_most_searched_keywords'), $errors, $settings) }}

        <div class="st-section__tool">
            @include('product::admin.partials.reset_search_terms')
        </div>
    @endcomponent
</div>
