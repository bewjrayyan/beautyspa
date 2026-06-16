<div class="st-fields-grid st-fields-grid--sections">
    <div class="st-enable-card">
        {{ Form::checkbox('storefront_blogs_section_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_blogs_section'), $errors, $settings) }}
    </div>

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-newspaper-o',
        'title' => trans('storefront::storefront.tabs.blogs'),
        'class' => 'st-section--compact',
        'columns' => 2,
    ])
        {{ Form::text('translatable[storefront_blogs_section_title]', trans('storefront::attributes.section_title'), $errors, $settings) }}
        {{ Form::select('storefront_recent_blogs', trans('storefront::attributes.recent_blogs'), $errors, trans('storefront::storefront.form.recent_blogs'), $settings) }}
    @endcomponent
</div>
