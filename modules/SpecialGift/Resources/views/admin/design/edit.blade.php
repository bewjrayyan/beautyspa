@extends('admin::layout')

@section('title', trans('specialgift::admin.tab_design'))

@section('content_header')
    <h3>{{ trans('specialgift::admin.tab_design') }}</h3>

    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li><a href="{{ route('admin.gift_voucher_submissions.index') }}">{{ trans('specialgift::admin.submissions') }}</a></li>
        <li class="active">{{ trans('specialgift::admin.tab_design') }}</li>
    </ol>
@endsection

@section('content')
    @include('specialgift::admin.partials.hub-nav', [
        'activeTab' => 'design',
        'sendGiftUrl' => $sendGiftUrl,
    ])

    <form
        method="POST"
        action="{{ route('admin.gift_voucher_submissions.settings.update') }}"
        class="gv-hub-form"
        id="gift-voucher-design-form"
    >
        @csrf
        @method('PUT')
        <input type="hidden" name="section" value="design">

        <div class="box box-primary gv-hub-panel">
            <div class="box-body gv-hub-panel__body">
                <div class="gv-hub-layout gv-hub-layout--design">
                    <div class="gv-hub-layout__main">
                        <div class="st-tab st-tab--gift-hub">
                            <p class="st-tab__lead">{{ trans('specialgift::admin.design_lead') }}</p>

                            @component('setting::admin.settings.partials.section', [
                                'icon' => 'fa-paint-brush',
                                'title' => trans('specialgift::admin.design_section_appearance'),
                                'description' => trans('specialgift::admin.design_section_appearance_help'),
                                'class' => 'gv-section',
                            ])
                                <div class="gv-field-stack">
                                    {{ Form::select('specialgift_page_preset', trans('specialgift::admin.field_design_preset'), $errors, \Modules\SpecialGift\Support\SpecialGiftPageSettings::presetOptions(), $settings, [
                                        'id' => 'specialgift-page-preset',
                                    ]) }}

                                    <p class="help-block gv-design-preset-note{{ $isCustomPreset ? ' hide' : '' }}" id="specialgift-design-preset-note">
                                        {{ trans('specialgift::admin.design_preset_locked_help') }}
                                    </p>

                                    {{ Form::select('specialgift_page_color_source', trans('specialgift::admin.field_design_color_source'), $errors, \Modules\SpecialGift\Support\SpecialGiftPageSettings::colorSourceOptions(), $settings, [
                                        'id' => 'specialgift-page-color-source',
                                    ]) }}

                                    <div class="gv-color-theme-note" id="specialgift-theme-color-note">
                                        <span class="gv-color-swatch" style="background: {{ $storeThemeColor }}"></span>
                                        {{ trans('specialgift::admin.design_color_store_theme_help', ['color' => $storeThemeColor]) }}
                                    </div>

                                    <div id="specialgift-custom-color-field" class="gv-custom-color-field{{ ($settings['specialgift_page_color_source'] ?? 'store_theme') === 'custom' ? '' : ' hide' }}">
                                        {{ Form::color('specialgift_page_accent_color', trans('specialgift::admin.field_design_accent_color'), $errors, $settings, [
                                            'default' => $storeThemeColor,
                                            'help' => trans('specialgift::admin.design_accent_color_help'),
                                        ]) }}
                                    </div>

                                    <div id="specialgift-custom-effects-panel" class="gv-design-effects{{ $isCustomPreset ? '' : ' hide' }}">
                                        <h6 class="gv-design-effects__title">{{ trans('specialgift::admin.design_effects_title') }}</h6>

                                        <div class="gv-toggle-grid">
                                            {{ Form::checkbox('specialgift_page_gradient_enabled', trans('specialgift::admin.field_design_gradient'), trans('specialgift::admin.design_gradient_label'), $errors, $settings) }}
                                            {{ Form::checkbox('specialgift_page_bokeh_enabled', trans('specialgift::admin.field_design_bokeh'), trans('specialgift::admin.design_bokeh_label'), $errors, $settings) }}
                                            {{ Form::checkbox('specialgift_page_sparkles_enabled', trans('specialgift::admin.field_design_sparkles'), trans('specialgift::admin.design_sparkles_label'), $errors, $settings) }}
                                        </div>
                                    </div>
                                </div>
                            @endcomponent
                        </div>
                    </div>

                    <div class="gv-hub-layout__aside">
                        @include('specialgift::admin.partials.design-preview', [
                            'storeThemeColor' => $storeThemeColor,
                        ])
                    </div>
                </div>
            </div>

            <div class="box-footer gv-hub-panel__footer">
                @include('specialgift::admin.partials.hub-sticky-footer', [
                    'sendGiftUrl' => $sendGiftUrl,
                    'hint' => trans('specialgift::admin.design_save_hint'),
                ])
            </div>
        </div>
    </form>
@endsection

@push('globals')
    @vite([
        'modules/SpecialGift/Resources/assets/admin/sass/main.scss',
        'modules/Setting/Resources/assets/admin/sass/main.scss',
        'modules/SpecialGift/Resources/assets/admin/js/main.js',
    ])
@endpush
