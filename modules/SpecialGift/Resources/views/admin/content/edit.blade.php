@extends('admin::layout')

@section('title', trans('specialgift::admin.tab_content'))

@section('content_header')
    <h3>{{ trans('specialgift::admin.tab_content') }}</h3>

    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li><a href="{{ route('admin.gift_voucher_submissions.index') }}">{{ trans('specialgift::admin.submissions') }}</a></li>
        <li class="active">{{ trans('specialgift::admin.tab_content') }}</li>
    </ol>
@endsection

@section('content')
    @include('specialgift::admin.partials.hub-nav', [
        'activeTab' => 'content',
        'sendGiftUrl' => $sendGiftUrl,
    ])

    <form
        method="POST"
        action="{{ route('admin.gift_voucher_submissions.settings.update') }}"
        class="gv-hub-form"
        id="gift-voucher-content-form"
    >
        @csrf
        @method('PUT')
        <input type="hidden" name="section" value="content">

        <div class="box box-primary gv-hub-panel">
            <div class="box-body gv-hub-panel__body">
                <div class="gv-hub-layout">
                    <div class="gv-hub-layout__main">
                        <div class="st-tab st-tab--gift-hub">
                            <div class="gv-hub-intro">
                                <p class="st-tab__lead">{{ trans('specialgift::admin.content_lead') }}</p>
                                <span class="gv-locale-chip">
                                    <i class="fa fa-globe" aria-hidden="true"></i>
                                    {{ trans('specialgift::admin.editing_locale', ['locale' => strtoupper(locale())]) }}
                                </span>
                            </div>

                            @component('setting::admin.settings.partials.section', [
                                'icon' => 'fa-header',
                                'title' => trans('specialgift::admin.content_section_hero'),
                                'description' => trans('specialgift::admin.content_section_hero_help'),
                                'class' => 'gv-section',
                            ])
                                <div class="gv-field-stack">
                                {{ Form::text('translatable[specialgift_page_tagline]', trans('specialgift::admin.field_page_tagline'), $errors, $settings, [
                                    'placeholder' => trans('specialgift::messages.page_tagline'),
                                    'data-preview-input' => 'tagline',
                                    'data-preview-default' => trans('specialgift::messages.page_tagline'),
                                ]) }}
                                {{ Form::text('translatable[specialgift_page_title]', trans('specialgift::admin.field_page_title'), $errors, $settings, [
                                    'placeholder' => trans('specialgift::messages.page_title'),
                                    'data-preview-input' => 'title',
                                    'data-preview-default' => trans('specialgift::messages.page_title'),
                                ]) }}
                                {{ Form::textarea('translatable[specialgift_page_lead]', trans('specialgift::admin.field_page_lead'), $errors, $settings, [
                                    'rows' => 3,
                                    'placeholder' => trans('specialgift::messages.page_lead'),
                                    'data-preview-input' => 'lead',
                                    'data-preview-default' => trans('specialgift::messages.page_lead'),
                                ]) }}
                                </div>
                            @endcomponent

                            @component('setting::admin.settings.partials.section', [
                                'icon' => 'fa-list-ol',
                                'title' => trans('specialgift::admin.content_section_steps'),
                                'description' => trans('specialgift::admin.content_section_steps_help'),
                                'class' => 'gv-section',
                            ])
                                <div class="gv-field-stack">
                                {{ Form::text('translatable[specialgift_step_order]', trans('specialgift::admin.field_step_order'), $errors, $settings, [
                                    'placeholder' => trans('specialgift::messages.step_order'),
                                    'data-preview-input' => 'step-order',
                                    'data-preview-default' => trans('specialgift::messages.step_order'),
                                ]) }}
                                {{ Form::text('translatable[specialgift_step_details]', trans('specialgift::admin.field_step_details'), $errors, $settings, [
                                    'placeholder' => trans('specialgift::messages.step_details'),
                                    'data-preview-input' => 'step-details',
                                    'data-preview-default' => trans('specialgift::messages.step_details'),
                                ]) }}
                                {{ Form::text('translatable[specialgift_step_send]', trans('specialgift::admin.field_step_send'), $errors, $settings, [
                                    'placeholder' => trans('specialgift::messages.step_send'),
                                    'data-preview-input' => 'step-send',
                                    'data-preview-default' => trans('specialgift::messages.step_send'),
                                ]) }}
                                </div>
                            @endcomponent

                            @component('setting::admin.settings.partials.section', [
                                'icon' => 'fa-edit',
                                'title' => trans('specialgift::admin.content_section_form'),
                                'description' => trans('specialgift::admin.content_section_form_help'),
                                'class' => 'gv-section',
                            ])
                                <div class="gv-field-stack">
                                {{ Form::text('translatable[specialgift_form_title]', trans('specialgift::admin.field_form_title'), $errors, $settings, [
                                    'placeholder' => trans('specialgift::messages.form_title'),
                                    'data-preview-input' => 'form-title',
                                    'data-preview-default' => trans('specialgift::messages.form_title'),
                                ]) }}
                                {{ Form::text('translatable[specialgift_submit_label]', trans('specialgift::admin.field_submit_label'), $errors, $settings, [
                                    'placeholder' => trans('specialgift::messages.submit'),
                                    'data-preview-input' => 'submit',
                                    'data-preview-default' => trans('specialgift::messages.submit'),
                                ]) }}
                                {{ Form::text('translatable[specialgift_preview_label]', trans('specialgift::admin.field_preview_label'), $errors, $settings, [
                                    'placeholder' => trans('specialgift::messages.preview_label'),
                                    'data-preview-input' => 'preview-label',
                                    'data-preview-default' => trans('specialgift::messages.preview_label'),
                                ]) }}
                                {{ Form::text('translatable[specialgift_trust_note]', trans('specialgift::admin.field_trust_note'), $errors, $settings, [
                                    'placeholder' => trans('specialgift::messages.trust_note'),
                                    'data-preview-input' => 'trust',
                                    'data-preview-default' => trans('specialgift::messages.trust_note'),
                                ]) }}
                                </div>
                            @endcomponent

                            <aside class="st-notice gv-hub-notice">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                                <p>{{ trans('specialgift::admin.content_locale_note', ['locale' => strtoupper(locale())]) }}</p>
                            </aside>
                        </div>
                    </div>

                    <div class="gv-hub-layout__aside">
                        @include('specialgift::admin.partials.content-preview')
                    </div>
                </div>
            </div>

            <div class="box-footer gv-hub-panel__footer">
                @include('specialgift::admin.partials.hub-sticky-footer', [
                    'sendGiftUrl' => $sendGiftUrl,
                    'hint' => trans('specialgift::admin.content_save_hint'),
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
