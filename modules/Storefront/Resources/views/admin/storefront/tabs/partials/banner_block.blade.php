@component('setting::admin.settings.partials.section', [
    'icon' => 'fa-picture-o',
    'title' => $label,
    'class' => 'st-section--compact st-section--media',
])
    @include('storefront::admin.storefront.tabs.partials.single_banner', [
        'name' => $name,
        'banner' => $banner,
    ])
@endcomponent
