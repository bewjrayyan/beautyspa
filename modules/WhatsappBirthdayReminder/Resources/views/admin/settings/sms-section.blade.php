@php
    use Modules\WhatsappBirthdayReminder\Enums\RewardType;
    use Modules\WhatsappBirthdayReminder\Support\BirthdayReminderSettingsDefaults;

    BirthdayReminderSettingsDefaults::applyMissingOnly();

    $rewardTypes = [
        RewardType::POINTS => trans('whatsappbirthday::settings.reward_type_points'),
        RewardType::DISCOUNT => trans('whatsappbirthday::settings.reward_type_discount'),
        RewardType::VOUCHER => trans('whatsappbirthday::settings.reward_type_voucher'),
        RewardType::NONE => trans('whatsappbirthday::settings.reward_type_none'),
    ];

    $formSettings = $settings;
    if (trim((string) ($formSettings['wabr_message_template'] ?? '')) === '') {
        $formSettings['wabr_message_template'] = trans('whatsappbirthday::settings.message_template_default');
    }
@endphp

@component('setting::admin.settings.partials.section', [
    'icon' => 'fa-birthday-cake',
    'title' => trans('whatsappbirthday::settings.section_title'),
    'description' => trans('whatsappbirthday::settings.lead'),
    'class' => 'wa-section--birthday',
    ])
    <div class="wa-settings__notifications-grid">
    @component('setting::admin.settings.partials.wa-notification-item', [
        'enabledName' => 'wabr_enabled',
        'enabledLabel' => trans('whatsappbirthday::settings.enable'),
        'hint' => trans('whatsappbirthday::settings.loyalty_note'),
    ])
        <p class="help-block text-muted">
            {{ trans('whatsappbirthday::settings.placeholders') }}:
            <code>{first_name}</code>
            <code>{last_name}</code>
            <code>{full_name}</code>
            <code>{points}</code>
            <code>{coupon_code}</code>
            <code>{reward_label}</code>
            <code>{reward_line}</code>
            <code>{store_name}</code>
        </p>

        @include('setting::admin.settings.partials.wa-message-template', [
            'messageName' => 'wabr_message_template',
            'rows' => 5,
            'settings' => $formSettings,
            'hint' => trans('whatsappbirthday::settings.message_template_help'),
            'showDefaultPreview' => false,
            'previewType' => 'image',
            'previewImageUrl' => $wabrImageFile?->path,
            'editorPrefix' => view('media::admin.image_picker.single', [
                'title' => trans('whatsappbirthday::settings.image'),
                'inputName' => 'wabr_image_file_id',
                'file' => $wabrImageFile ?? \Modules\Media\Entities\File::findOrNew((int) setting('wabr_image_file_id')),
            ])->render(),
            'editorHint' => trans('whatsappbirthday::settings.image_help'),
        ])

        <div class="wa-settings__birthday-reward-grid">
            {{ Form::select('wabr_reward_type', trans('whatsappbirthday::settings.reward_type'), $errors, $rewardTypes, $settings) }}
            {{ Form::number('wabr_reward_points', trans('whatsappbirthday::settings.reward_points'), $errors, $settings, ['min' => 0]) }}
            {{ Form::number('wabr_discount_value', trans('whatsappbirthday::settings.discount_value'), $errors, $settings, ['min' => 0, 'step' => '0.01']) }}
            {{ Form::number('wabr_voucher_value', trans('whatsappbirthday::settings.voucher_value'), $errors, $settings, ['min' => 0, 'step' => '0.01']) }}
            {{ Form::number('wabr_coupon_validity_days', trans('whatsappbirthday::settings.coupon_validity_days'), $errors, $settings, ['min' => 1, 'max' => 365]) }}
            {{ Form::text('wabr_schedule_time', trans('whatsappbirthday::settings.schedule_time'), $errors, $settings, ['placeholder' => '09:00']) }}
            <div class="wa-settings__birthday-percent">
                {{ Form::checkbox('wabr_discount_is_percent', trans('whatsappbirthday::settings.discount_is_percent'), trans('whatsappbirthday::settings.discount_is_percent'), $errors, $settings) }}
            </div>
            <p class="help-block text-muted wa-settings__birthday-help">{{ trans('whatsappbirthday::settings.schedule_time_help') }}</p>
        </div>

        @if (auth()->user()?->hasAccess('admin.whatsapp_birthday.index'))
            <p class="help-block">
                <a href="{{ route('admin.whatsapp_birthday.index') }}">{{ trans('whatsappbirthday::admin.view_logs') }}</a>
            </p>
        @endif
    @endcomponent
    </div>
@endcomponent
