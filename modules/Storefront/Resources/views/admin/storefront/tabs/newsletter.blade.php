@component('setting::admin.settings.partials.section', [
    'icon' => 'fa-envelope-o',
    'title' => trans('storefront::storefront.tabs.newsletter'),
    'class' => 'st-section--media',
])
    @include('media::admin.image_picker.single', [
        'title' => trans('storefront::storefront.form.newsletter_bg_image'),
        'aspect' => 'banner',
        'inputName' => 'storefront_newsletter_bg_image',
        'file' => $newsletterBgImage,
    ])
@endcomponent
