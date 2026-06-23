@php
    $mediaType = old('storefront_mobile_home_promo_media_type', setting('storefront_mobile_home_promo_media_type', 'image'));
@endphp

<div class="st-fields-grid st-fields-grid--sections">
    <div class="st-fields-grid__full">
        <div class="st-enable-card">
            {{ Form::checkbox(
                'storefront_mobile_home_promo_enabled',
                trans('storefront::attributes.section_status'),
                trans('storefront::storefront.form.enable_mobile_home_promo_section'),
                $errors,
                $settings
            ) }}
        </div>
    </div>

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-mobile',
        'title' => trans('storefront::storefront.form.mobile_home_promo_media'),
        'class' => 'st-section--compact',
    ])
        <div class="form-group">
            <label class="control-label">{{ trans('storefront::attributes.type') }}</label>

            <div class="radio">
                <input
                    type="radio"
                    name="storefront_mobile_home_promo_media_type"
                    id="mobile-home-promo-type-image"
                    value="image"
                    {{ $mediaType === 'image' ? 'checked' : '' }}
                >
                <label for="mobile-home-promo-type-image">
                    {{ trans('storefront::storefront.form.mobile_home_promo_type_image') }}
                </label>
            </div>

            <div class="radio">
                <input
                    type="radio"
                    name="storefront_mobile_home_promo_media_type"
                    id="mobile-home-promo-type-video"
                    value="video"
                    {{ $mediaType === 'video' ? 'checked' : '' }}
                >
                <label for="mobile-home-promo-type-video">
                    {{ trans('storefront::storefront.form.mobile_home_promo_type_video') }}
                </label>
            </div>
        </div>
    @endcomponent

    <div class="mobile-promo-image-fields{{ $mediaType === 'video' ? ' hide' : '' }}">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-picture-o',
            'title' => trans('storefront::storefront.form.mobile_home_promo_poster_image'),
            'class' => 'st-section--compact st-section--media',
        ])
            @include('media::admin.image_picker.single', [
                'title' => trans('storefront::storefront.form.mobile_home_promo_poster_image'),
                'inputName' => 'storefront_mobile_home_promo_image_file_id',
                'file' => $promoImage,
                'aspect' => 'banner',
            ])
        @endcomponent
    </div>

    <div class="mobile-promo-video-fields{{ $mediaType === 'image' ? ' hide' : '' }}">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-film',
            'title' => trans('storefront::storefront.form.mobile_home_promo_video'),
            'class' => 'st-section--compact st-section--media',
        ])
            @include('storefront::admin.storefront.tabs.partials.video_picker', [
                'inputName' => 'storefront_mobile_home_promo_video_file_id',
                'file' => $promoVideo,
            ])
        @endcomponent

        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-picture-o',
            'title' => trans('storefront::storefront.form.mobile_home_promo_video_poster'),
            'class' => 'st-section--compact st-section--media',
        ])
            @include('media::admin.image_picker.single', [
                'title' => trans('storefront::storefront.form.mobile_home_promo_video_poster'),
                'inputName' => 'storefront_mobile_home_promo_video_poster_file_id',
                'file' => $promoVideoPoster,
                'aspect' => 'banner',
            ])

            <p class="help-block">{{ trans('storefront::storefront.form.mobile_home_promo_video_poster_help') }}</p>
        @endcomponent
    </div>

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-link',
        'title' => trans('storefront::storefront.form.mobile_home_promo_link'),
        'class' => 'st-section--compact',
        'columns' => 2,
    ])
        {{ Form::text(
            'storefront_mobile_home_promo_call_to_action_url',
            trans('storefront::attributes.call_to_action_url'),
            $errors,
            $settings,
            ['placeholder' => 'https://']
        ) }}

        <div class="checkbox">
            <input type="hidden" name="storefront_mobile_home_promo_open_in_new_window" value="0">
            <input
                type="checkbox"
                name="storefront_mobile_home_promo_open_in_new_window"
                value="1"
                id="mobile-home-promo-open-in-new-window"
                {{ old('storefront_mobile_home_promo_open_in_new_window', setting('storefront_mobile_home_promo_open_in_new_window')) ? 'checked' : '' }}
            >
            <label for="mobile-home-promo-open-in-new-window">
                {{ trans('storefront::attributes.open_in_new_window') }}
            </label>
        </div>
    @endcomponent
</div>
