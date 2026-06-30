@php
    $isEnabled = (bool) old('specialgift_enabled', array_get($settings, 'specialgift_enabled'));
@endphp

<div class="st-tab st-tab--gift st-tab--gift-hub st-tab--gift-settings">
    <p class="st-tab__lead">{{ trans('specialgift::settings.lead') }}</p>

    <div class="gv-settings-grid">
        <div class="gv-settings-grid__main">
            <div class="st-card st-card--status gv-status-card">
                <div class="st-card__header">
                    <span class="st-card__icon" aria-hidden="true">
                        <i class="fa fa-gift"></i>
                    </span>
                    <div>
                        <h5 class="st-card__title">{{ trans('specialgift::settings.section_status') }}</h5>
                        <p class="st-card__subtitle">{{ trans('specialgift::settings.flow_help') }}</p>
                    </div>
                    @if ($isEnabled)
                        <span class="gv-status-pill gv-status-pill--on">{{ trans('specialgift::settings.status_active') }}</span>
                    @else
                        <span class="gv-status-pill">{{ trans('specialgift::settings.status_inactive') }}</span>
                    @endif
                </div>

                <div class="st-enable-card">
                    {{ Form::checkbox('specialgift_enabled', ' ', trans('specialgift::settings.enable'), $errors, $settings, ['labelCol' => 0]) }}
                </div>

                <ol class="st-steps gv-status-steps">
                    <li>{{ trans('specialgift::settings.step_order') }}</li>
                    <li>{{ trans('specialgift::settings.step_whatsapp') }}</li>
                    <li>{{ trans('specialgift::settings.step_send') }}</li>
                </ol>

                <div class="st-public-link gv-public-link">
                    <span class="st-public-link__label">{{ trans('specialgift::settings.public_page') }}</span>
                    <a href="{{ $sendGiftUrl }}" class="st-public-link__url" target="_blank" rel="noopener noreferrer">
                        {{ $sendGiftUrl }}
                        <i class="fa fa-external-link" aria-hidden="true"></i>
                    </a>
                </div>
            </div>

            @component('setting::admin.settings.partials.section', [
                'icon' => 'fa-commenting-o',
                'title' => trans('specialgift::settings.section_message'),
                'description' => trans('specialgift::settings.message_template_help'),
                'class' => 'gv-section gv-section--message',
            ])
                <div class="gv-placeholder-chips" data-target="#specialgift-message-template">
                    <span class="gv-placeholder-chips__label">{{ trans('specialgift::admin.whatsapp_placeholders') }}</span>
                    @foreach (['{recipient_name}', '{order_number}', '{sender_name}', '{voucher_value}'] as $placeholder)
                        <button type="button" class="gv-placeholder-chip" data-placeholder="{{ $placeholder }}">
                            {{ $placeholder }}
                        </button>
                    @endforeach
                </div>

                {{ Form::textarea('specialgift_message_template', trans('specialgift::settings.message_template'), $errors, $specialGiftSettings, [
                    'rows' => 5,
                    'placeholder' => trans('specialgift::settings.message_template_placeholder'),
                    'labelCol' => 0,
                    'id' => 'specialgift-message-template',
                ]) }}
            @endcomponent

            <aside class="st-notice gv-hub-notice">
                <i class="fa fa-info-circle" aria-hidden="true"></i>
                <p>
                    {{ trans('specialgift::settings.whatsapp_help') }}
                    <a href="{{ route('admin.settings.edit', ['tab' => 'whatsapp']) }}">{{ trans('specialgift::settings.whatsapp_settings_link') }}</a>
                </p>
            </aside>
        </div>

        <div class="gv-settings-grid__aside">
            @component('setting::admin.settings.partials.section', [
                'icon' => 'fa-picture-o',
                'title' => trans('specialgift::settings.section_voucher'),
                'description' => trans('specialgift::settings.voucher_background_help'),
                'class' => 'st-section--media gv-section gv-section--voucher',
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
        </div>
    </div>
</div>
