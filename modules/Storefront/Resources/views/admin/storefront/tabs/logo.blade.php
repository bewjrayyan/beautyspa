<div class="st-fields-grid st-fields-grid--sections st-fields-grid--tiles">
    <div class="st-fields-grid__col">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-picture-o',
            'title' => trans('storefront::storefront.form.favicon'),
            'class' => 'st-section--media',
        ])
            @include('media::admin.image_picker.single', [
                'title' => '',
                'aspect' => 'square',
                'inputName' => 'storefront_favicon',
                'file' => $favicon,
            ])
        @endcomponent
    </div>

    <div class="st-fields-grid__col">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-image',
            'title' => trans('storefront::storefront.form.header_logo'),
            'class' => 'st-section--media',
        ])
            @include('media::admin.image_picker.single', [
                'title' => '',
                'aspect' => 'logo',
                'inputName' => 'translatable[storefront_header_logo]',
                'file' => $headerLogo,
            ])
        @endcomponent
    </div>

    <div class="st-fields-grid__col">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-envelope-o',
            'title' => trans('storefront::storefront.form.mail_logo'),
            'class' => 'st-section--media',
        ])
            @include('media::admin.image_picker.single', [
                'title' => '',
                'aspect' => 'logo',
                'inputName' => 'translatable[storefront_mail_logo]',
                'file' => $mailLogo,
            ])
        @endcomponent
    </div>
</div>
