<div class="st-fields-grid st-fields-grid--sections">
    <div class="st-fields-grid__col">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-picture-o',
            'title' => trans('setting::settings.form.logo'),
        ])
            @include('media::admin.image_picker.single', [
                'title' => trans('setting::settings.form.logo'),
                'inputName' => 'translatable[admin_logo]',
                'file' => $logo,
            ])
        @endcomponent
    </div>

    <div class="st-fields-grid__col">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-compress',
            'title' => trans('setting::settings.form.small_logo'),
        ])
            @include('media::admin.image_picker.single', [
                'title' => trans('setting::settings.form.small_logo'),
                'inputName' => 'translatable[admin_small_logo]',
                'file' => $shortLogo,
            ])
        @endcomponent
    </div>
</div>
