@component('setting::admin.settings.partials.section', [
    'icon' => 'fa-share-alt',
    'title' => trans('storefront::storefront.tabs.social_links'),
    'columns' => 2,
])
    {{ Form::text('storefront_facebook_link', trans('storefront::attributes.storefront_facebook_link'), $errors, $settings) }}
    {{ Form::text('storefront_twitter_link', trans('storefront::attributes.storefront_twitter_link'), $errors, $settings) }}
    {{ Form::text('storefront_instagram_link', trans('storefront::attributes.storefront_instagram_link'), $errors, $settings) }}
    {{ Form::text('storefront_youtube_link', trans('storefront::attributes.storefront_youtube_link'), $errors, $settings) }}
@endcomponent
