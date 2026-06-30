@php
    $sendGiftUrl = route('specialgift.send.create');
    $isEnabled = (bool) old('specialgift_enabled', array_get($settings, 'specialgift_enabled'));
    $specialGiftSettings = $settings;
    $currentMessageTemplate = old('specialgift_message_template', array_get($settings, 'specialgift_message_template'));
    $specialGiftConfig = app(\Modules\SpecialGift\Services\SpecialGiftConfig::class);

    if (trim((string) $currentMessageTemplate) === '') {
        $specialGiftSettings['specialgift_message_template'] = trans('specialgift::settings.message_template_default');
    }
@endphp

<div class="st-tab st-tab--gift">
    <div class="alert alert-info gv-settings-hub-link">
        <i class="fa fa-info-circle" aria-hidden="true"></i>
        {{ trans('specialgift::settings.hub_notice') }}
        <a href="{{ route('admin.gift_voucher_submissions.index') }}" class="alert-link">{{ trans('specialgift::settings.hub_link') }}</a>
    </div>

    <p class="st-tab__lead">{{ trans('specialgift::settings.lead') }}</p>

    <div class="st-card st-card--status">
        <div class="st-card__header">
            <span class="st-card__icon" aria-hidden="true">
                <i class="fa fa-gift"></i>
            </span>
            <div>
                <h5 class="st-card__title">{{ trans('specialgift::settings.section_status') }}</h5>
                <p class="st-card__subtitle">{{ trans('specialgift::settings.flow_help') }}</p>
            </div>
        </div>

        <div class="st-enable-card">
            {{ Form::checkbox('specialgift_enabled', ' ', trans('specialgift::settings.enable'), $errors, $settings, ['labelCol' => 0]) }}
        </div>

        <ol class="st-steps">
            <li>{{ trans('specialgift::settings.step_order') }}</li>
            <li>{{ trans('specialgift::settings.step_whatsapp') }}</li>
            <li>{{ trans('specialgift::settings.step_send') }}</li>
        </ol>

        <div class="st-public-link">
            <span class="st-public-link__label">{{ trans('specialgift::settings.public_page') }}</span>
            <a href="{{ $sendGiftUrl }}" class="st-public-link__url" target="_blank" rel="noopener noreferrer">
                {{ $sendGiftUrl }}
                <i class="fa fa-external-link" aria-hidden="true"></i>
            </a>
            @if ($isEnabled)
                <span class="st-public-link__badge st-public-link__badge--on">{{ trans('specialgift::settings.status_active') }}</span>
            @else
                <span class="st-public-link__badge">{{ trans('specialgift::settings.status_inactive') }}</span>
            @endif
        </div>
    </div>

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-picture-o',
        'title' => trans('specialgift::settings.section_voucher'),
        'description' => trans('specialgift::settings.voucher_background_help'),
        'class' => 'st-section--media',
    ])
        @include('media::admin.image_picker.single', [
            'title' => trans('specialgift::settings.voucher_background'),
            'inputName' => 'specialgift_voucher_background',
            'file' => $voucherBackground,
            'aspect' => 'banner',
            'defaultPreviewUrl' => $specialGiftConfig->defaultVoucherBackgroundUrl(),
            'defaultPreviewBadge' => trans('specialgift::settings.voucher_background_default'),
        ])
    @endcomponent

    @component('setting::admin.settings.partials.section', [
        'icon' => 'fa-commenting-o',
        'title' => trans('specialgift::settings.section_message'),
        'description' => trans('specialgift::settings.message_template_help'),
    ])
        {{ Form::textarea('specialgift_message_template', trans('specialgift::settings.message_template'), $errors, $specialGiftSettings, [
            'rows' => 5,
            'placeholder' => trans('specialgift::settings.message_template_placeholder'),
            'labelCol' => 0,
        ]) }}
    @endcomponent

    <aside class="st-notice">
        <i class="fa fa-info-circle" aria-hidden="true"></i>
        <p>
            {{ trans('specialgift::settings.whatsapp_help') }}
            <a href="{{ route('admin.settings.edit', ['tab' => 'whatsapp']) }}">{{ trans('specialgift::settings.whatsapp_settings_link') }}</a>
        </p>
    </aside>
</div>
