@component('setting::admin.settings.partials.settings-wrap')
    @component('setting::admin.settings.partials.fields-grid', ['class' => 'st-fields-grid--sections'])
        @slot('left')
            <div class="box-content clearfix">
                <h4 class="section-title">{{ trans('setting::attributes.custom_header_assets') }}</h4>
                {{ Form::textarea('custom_header_assets', trans('setting::attributes.custom_header_assets'), $errors, $settings) }}
            </div>
        @endslot
        @slot('right')
            <div class="box-content clearfix">
                <h4 class="section-title">{{ trans('setting::attributes.custom_footer_assets') }}</h4>
                {{ Form::textarea('custom_footer_assets', trans('setting::attributes.custom_footer_assets'), $errors, $settings) }}
            </div>
        @endslot
    @endcomponent
@endcomponent
