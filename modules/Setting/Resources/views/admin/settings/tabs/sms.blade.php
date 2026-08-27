<div class="st-tab st-tab--whatsapp settings-form">
    @php
        $onesenderEnabled = filter_var($settings['onesender_enabled'] ?? setting('onesender_enabled'), FILTER_VALIDATE_BOOLEAN);
    @endphp

    <div class="wa-settings__hero">
        <div class="wa-settings__hero-icon" aria-hidden="true"><i class="fa fa-whatsapp"></i></div>
        <div class="wa-settings__hero-copy">
            <span class="wa-settings__eyebrow">{{ trans('setting::settings.sms.hero_eyebrow') }}</span>
            <h3>{{ trans('setting::settings.tabs.whatsapp') }}</h3>
            <p>{{ trans('setting::settings.sms.lead') }}</p>
        </div>
        <div class="wa-settings__hero-meta">
            <span class="wa-settings__status wa-settings__status--{{ $onesenderEnabled ? 'on' : 'off' }}">
                <i class="fa fa-{{ $onesenderEnabled ? 'check-circle' : 'exclamation-circle' }}" aria-hidden="true"></i>
                {{ trans('setting::settings.sms.hero_status_' . ($onesenderEnabled ? 'on' : 'off')) }}
            </span>
            <span class="wa-settings__live"><i class="fa fa-eye" aria-hidden="true"></i> {{ trans('setting::settings.sms.hero_live_preview') }}</span>
        </div>
    </div>

    <div class="wa-settings">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-whatsapp',
            'title' => trans('setting::settings.sms.sections.api.title'),
            'description' => trans('setting::settings.sms.sections.api.description'),
            'class' => 'wa-section--api',
        ])
            <div class="wa-settings__toolbar">
                <a href="{{ route('admin.onesender_queue.index') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                    {{ trans('setting::settings.form.view_onesender_queue') }}
                </a>
                <a href="{{ route('admin.onesender_logs.index') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-list-alt" aria-hidden="true"></i>
                    {{ trans('setting::settings.form.view_onesender_logs') }}
                </a>
                <a href="https://documenter.getpostman.com/view/11282121/Uyr8md8U" target="_blank" rel="noopener" class="btn btn-link btn-sm">
                    {{ trans('setting::settings.sms.sections.api.docs_link') }}
                    <i class="fa fa-external-link" aria-hidden="true"></i>
                </a>
            </div>

            <div class="st-enable-card">
                {{ Form::checkbox('onesender_enabled', trans('setting::attributes.onesender_enabled'), trans('setting::settings.form.enable_onesender'), $errors, $settings) }}
                <p class="st-enable-card__hint">{{ trans('setting::settings.form.onesender_enabled_help') }}</p>
            </div>

            <div class="wa-settings__fields-grid">
                {{ Form::text('onesender_api_url', trans('setting::attributes.onesender_api_url'), $errors, $settings, [
                    'placeholder' => config('setting.whatsapp_notifications.onesender_api_url'),
                ]) }}
                {{ Form::password('onesender_api_key', trans('setting::attributes.onesender_api_key'), $errors, $settings) }}
                <p class="help-block text-muted wa-settings__full-width">{{ trans('setting::settings.form.onesender_api_key_help') }}</p>
            </div>

            <div class="wa-settings__subsection">
                <div class="wa-settings__subsection-head">
                    <span class="wa-settings__subsection-icon"><i class="fa fa-random" aria-hidden="true"></i></span>
                    <div>
                        <h6>{{ trans('setting::settings.sms.sections.routing.title') }}</h6>
                        <p>{{ trans('setting::settings.sms.sections.routing.description') }}</p>
                    </div>
                </div>
                <div class="wa-settings__fields-grid">
                    {{ Form::text('onesender_admin_phones', trans('setting::attributes.onesender_admin_phones'), $errors, $settings, [
                        'placeholder' => config('setting.whatsapp_notifications.onesender_admin_phones') ?: trans('setting::settings.sms.placeholders.admin_phones'),
                    ]) }}
                    {{ Form::text('onesender_whatsapp_group_id', trans('setting::attributes.onesender_whatsapp_group_id'), $errors, $settings, [
                        'placeholder' => config('setting.whatsapp_notifications.onesender_whatsapp_group_id') ?: trans('setting::settings.sms.placeholders.whatsapp_group_id'),
                    ]) }}
                </div>
            </div>

            <div class="wa-settings__subsection wa-settings__subsection--payment">
                <div class="wa-settings__subsection-head">
                    <span class="wa-settings__subsection-icon"><i class="fa fa-credit-card" aria-hidden="true"></i></span>
                    <div>
                        <h6>{{ trans('setting::settings.sms.sections.payment_proof.title') }}</h6>
                        <p>{{ trans('setting::settings.sms.sections.payment_proof.description') }}</p>
                    </div>
                </div>
                <div class="wa-settings__fields-grid">
                    {{ Form::checkbox('bank_transfer_payment_proof_whatsapp_enabled', trans('setting::attributes.bank_transfer_payment_proof_whatsapp_enabled'), trans('setting::settings.form.enable_bank_transfer_payment_proof_whatsapp'), $errors, $settings) }}
                    {{ Form::text('bank_transfer_payment_proof_whatsapp_group_id', trans('setting::attributes.bank_transfer_payment_proof_whatsapp_group_id'), $errors, $settings, [
                        'placeholder' => config('setting.whatsapp_notifications.bank_transfer_payment_proof_whatsapp_group_id') ?: trans('setting::settings.sms.placeholders.whatsapp_group_id'),
                    ]) }}
                </div>
                @include('setting::admin.settings.partials.wa-message-template', [
                    'messageName' => 'bank_transfer_payment_proof_whatsapp_message',
                    'rows' => 6,
                    'hint' => trans('setting::settings.form.bank_transfer_payment_proof_whatsapp_message_help'),
                ])
            </div>

            <div class="wa-settings__subsection">
                <div class="wa-settings__subsection-head">
                    <span class="wa-settings__subsection-icon"><i class="fa fa-link" aria-hidden="true"></i></span>
                    <div>
                        <h6>{{ trans('setting::settings.sms.sections.links.title') }}</h6>
                        <p>{{ trans('setting::settings.sms.sections.links.description') }}</p>
                    </div>
                </div>
                <div class="wa-settings__fields-grid">
                    {{ Form::text('whatsapp_group_staff_name', trans('setting::attributes.whatsapp_group_staff_name'), $errors, $settings, [
                        'placeholder' => config('setting.whatsapp_notifications.whatsapp_group_staff_name'),
                    ]) }}
                    {{ Form::text('whatsapp_order_tracking_url', trans('setting::attributes.whatsapp_order_tracking_url'), $errors, $settings, [
                        'placeholder' => config('setting.whatsapp_notifications.whatsapp_order_tracking_url'),
                    ]) }}
                    <p class="help-block text-muted wa-settings__full-width">{{ trans('setting::settings.form.whatsapp_group_staff_name_help') }}</p>
                    <p class="help-block text-muted wa-settings__full-width">{{ trans('setting::settings.sms.sections.api.tracking_help') }}</p>
                </div>
            </div>
        @endcomponent

        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-shield',
            'title' => trans('setting::settings.sms.sections.delivery.title'),
            'description' => trans('setting::settings.sms.sections.delivery.description'),
            'class' => 'st-section--compact wa-section--delivery',
        ])
            @component('setting::admin.settings.partials.wa-notification-item', [
                'enabledName' => 'onesender_sending_paused',
                'enabledLabel' => trans('setting::settings.form.pause_onesender_sending'),
                'hint' => trans('setting::settings.form.pause_onesender_sending_help'),
            ])
            @endcomponent

            <div class="wa-settings__controls-grid">
                @component('setting::admin.settings.partials.wa-notification-item', [
                    'enabledName' => 'onesender_dedupe_enabled',
                    'enabledLabel' => trans('setting::settings.form.enable_onesender_dedupe'),
                    'hint' => trans('setting::settings.form.onesender_dedupe_help'),
                ])
                    {{ Form::number('onesender_dedupe_minutes', trans('setting::attributes.onesender_dedupe_minutes'), $errors, $settings, [
                        'min' => 1,
                        'max' => 10080,
                        'placeholder' => '1440',
                    ]) }}
                @endcomponent

                @component('setting::admin.settings.partials.wa-notification-item', [
                    'enabledName' => 'onesender_outbound_queue_enabled',
                    'enabledLabel' => trans('setting::settings.form.enable_onesender_outbound_queue'),
                    'hint' => trans('setting::settings.form.onesender_outbound_queue_help'),
                ])
                    {{ Form::number('onesender_outbound_delay_seconds', trans('setting::attributes.onesender_outbound_delay_seconds'), $errors, $settings, [
                        'min' => 0,
                        'max' => 3600,
                        'placeholder' => (string) config('setting.whatsapp_notifications.onesender_outbound_delay_seconds'),
                    ]) }}
                    <p class="help-block text-muted">{{ trans('setting::settings.form.onesender_outbound_delay_help') }}</p>
                @endcomponent
            </div>
        @endcomponent

        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-user',
            'title' => trans('setting::settings.sms.sections.customer.title'),
            'description' => trans('setting::settings.sms.sections.customer.description'),
            'class' => 'wa-section--customer',
        ])
            <div class="wa-settings__notifications-grid">
                @component('setting::admin.settings.partials.wa-notification-item', [
                    'enabledName' => 'welcome_sms',
                    'enabledLabel' => trans('setting::settings.form.send_welcome_whatsapp_after_registration'),
                    'hint' => trans('setting::settings.sms.sections.customer.welcome_help'),
                ])
                    @include('setting::admin.settings.partials.wa-message-template', [
                        'messageName' => 'whatsapp_welcome_message',
                        'hint' => trans('setting::settings.sms.template_hints.welcome'),
                    ])
                @endcomponent

                @component('setting::admin.settings.partials.wa-notification-item', [
                    'enabledName' => 'whatsapp_customer_reminder_enabled',
                    'enabledLabel' => trans('setting::settings.form.send_appointment_reminder_to_customer'),
                    'hint' => trans('setting::settings.form.customer_reminder_minutes_help'),
                ])
                    {{ Form::number('whatsapp_customer_reminder_minutes', trans('setting::attributes.whatsapp_customer_reminder_minutes'), $errors, $settings, [
                        'min' => 15,
                        'max' => 1440,
                        'step' => 15,
                        'placeholder' => (string) config('setting.whatsapp_notifications.whatsapp_customer_reminder_minutes'),
                    ]) }}
                    @include('setting::admin.settings.partials.wa-message-template', [
                        'messageName' => 'whatsapp_customer_reminder_message',
                        'hint' => trans('setting::settings.sms.template_hints.customer_reminder'),
                    ])
                @endcomponent

                @component('setting::admin.settings.partials.wa-notification-item', [
                    'enabledName' => 'whatsapp_customer_completed_enabled',
                    'enabledLabel' => trans('setting::settings.form.send_completed_thankyou_to_customer'),
                    'hint' => trans('setting::settings.form.customer_completed_message_help'),
                    'badge' => [
                        'type' => 'off',
                        'text' => trans('setting::settings.sms.badges.auto_off'),
                    ],
                ])
                    @include('setting::admin.settings.partials.wa-message-template', [
                        'messageName' => 'whatsapp_customer_completed_message',
                        'hint' => trans('setting::settings.sms.template_hints.customer_completed'),
                    ])
                @endcomponent

                @component('setting::admin.settings.partials.wa-notification-item', [
                    'enabledName' => 'whatsapp_customer_followup_enabled',
                    'enabledLabel' => trans('setting::settings.form.send_followup_to_customer'),
                    'hint' => trans('setting::settings.form.customer_followup_message_help'),
                ])
                    {{ Form::number('whatsapp_customer_followup_days', trans('setting::attributes.whatsapp_customer_followup_days'), $errors, $settings, [
                        'min' => 1,
                        'max' => 90,
                        'placeholder' => (string) config('setting.whatsapp_notifications.whatsapp_customer_followup_days'),
                    ]) }}
                    @include('setting::admin.settings.partials.wa-message-template', [
                        'messageName' => 'whatsapp_customer_followup_message',
                        'hint' => trans('setting::settings.sms.template_hints.customer_followup'),
                    ])
                @endcomponent
            </div>
        @endcomponent

        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-shopping-cart',
            'title' => trans('setting::settings.sms.sections.order.title'),
            'description' => trans('setting::settings.sms.sections.order.description'),
            'class' => 'wa-section--orders',
        ])
            <div class="wa-settings__notifications-grid">
            @component('setting::admin.settings.partials.wa-notification-item', [
                'enabledName' => 'new_order_admin_sms',
                'enabledLabel' => trans('setting::settings.form.send_new_order_notification_to_admin'),
            ])
                @include('setting::admin.settings.partials.wa-message-template', [
                    'messageName' => 'whatsapp_new_order_admin_message',
                    'rows' => 12,
                    'hint' => trans('setting::settings.sms.template_hints.new_order_admin'),
                ])
            @endcomponent

            @component('setting::admin.settings.partials.wa-notification-item', [
                'enabledName' => 'new_order_sms',
                'enabledLabel' => trans('setting::settings.form.send_new_order_notification_to_customer'),
            ])
                @include('setting::admin.settings.partials.wa-message-template', [
                    'messageName' => 'whatsapp_new_order_customer_message',
                    'rows' => 12,
                    'hint' => trans('setting::settings.sms.template_hints.new_order_customer'),
                    'previewType' => 'document',
                    'previewDocument' => ['filename' => 'receipt-FC-1042.pdf'],
                ])
            @endcomponent

            @component('setting::admin.settings.partials.wa-notification-item', [
                'enabledName' => 'whatsapp_status_notify_customer_enabled',
                'enabledLabel' => trans('setting::settings.form.whatsapp_status_notify_customer'),
                'hint' => trans('setting::settings.form.whatsapp_status_notify_customer_help'),
            ])
                {{ Form::select('sms_order_statuses', trans('setting::attributes.sms_order_statuses'), $errors, $orderStatuses, $settings, [
                    'class' => 'selectize prevent-creation',
                    'multiple' => true,
                ]) }}
                <p class="help-block text-muted st-wa-item__inline-help">{{ trans('setting::settings.sms.sections.order.statuses_help') }}</p>
                @include('setting::admin.settings.partials.wa-message-template', [
                    'messageName' => 'whatsapp_order_status_message',
                    'hint' => trans('setting::settings.sms.template_hints.order_status'),
                ])
            @endcomponent

            @component('setting::admin.settings.partials.wa-notification-item', [
                'enabledName' => 'whatsapp_status_notify_beautician_enabled',
                'enabledLabel' => trans('setting::settings.form.whatsapp_status_notify_beautician'),
                'hint' => trans('setting::settings.form.whatsapp_status_notify_beautician_help'),
            ])
                @include('setting::admin.settings.partials.wa-message-template', [
                    'messageName' => 'whatsapp_status_beautician_message',
                    'rows' => 10,
                    'hint' => trans('setting::settings.sms.template_hints.status_beautician'),
                ])
            @endcomponent

            @component('setting::admin.settings.partials.wa-notification-item', [
                'enabledName' => 'whatsapp_completed_group_enabled',
                'enabledLabel' => trans('setting::settings.form.send_completed_order_to_whatsapp_group'),
            ])
                @include('setting::admin.settings.partials.wa-message-template', [
                    'messageName' => 'whatsapp_completed_group_message',
                    'rows' => 10,
                    'hint' => trans('setting::settings.sms.template_hints.completed_group'),
                ])
            @endcomponent

            @component('setting::admin.settings.partials.wa-notification-item', [
                'enabledName' => 'whatsapp_completed_beautician_enabled',
                'enabledLabel' => trans('setting::settings.form.send_completed_order_to_beautician'),
                'hint' => trans('setting::settings.form.whatsapp_group_note_format_help'),
            ])
                @include('setting::admin.settings.partials.wa-message-template', [
                    'messageName' => 'whatsapp_completed_beautician_message',
                    'rows' => 10,
                    'hint' => trans('setting::settings.sms.template_hints.completed_beautician'),
                ])
            @endcomponent
            </div>
        @endcomponent

        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-scissors',
            'title' => trans('setting::settings.sms.sections.beautician.title'),
            'description' => trans('setting::settings.sms.sections.beautician.description'),
            'class' => 'wa-section--beautician',
        ])
            <div class="wa-settings__notifications-grid">
            @component('setting::admin.settings.partials.wa-notification-item', [
                'enabledName' => 'whatsapp_beautician_new_booking_enabled',
                'enabledLabel' => trans('setting::settings.form.send_new_booking_to_beautician'),
            ])
                @include('setting::admin.settings.partials.wa-message-template', [
                    'messageName' => 'whatsapp_beautician_new_booking_message',
                    'hint' => trans('setting::settings.sms.template_hints.beautician_new_booking'),
                ])
            @endcomponent

            @component('setting::admin.settings.partials.wa-notification-item', [
                'enabledName' => 'whatsapp_beautician_reminder_enabled',
                'enabledLabel' => trans('setting::settings.form.send_appointment_reminder_to_beautician'),
                'hint' => trans('setting::settings.form.beautician_reminder_minutes_help'),
            ])
                {{ Form::number('whatsapp_beautician_reminder_minutes', trans('setting::attributes.whatsapp_beautician_reminder_minutes'), $errors, $settings, [
                    'min' => 15,
                    'max' => 1440,
                    'step' => 15,
                    'placeholder' => (string) config('setting.whatsapp_notifications.whatsapp_beautician_reminder_minutes'),
                ]) }}
                @include('setting::admin.settings.partials.wa-message-template', [
                    'messageName' => 'whatsapp_beautician_reminder_message',
                    'hint' => trans('setting::settings.sms.template_hints.beautician_reminder'),
                ])
            @endcomponent
            </div>
        @endcomponent

        @if (\Nwidart\Modules\Facades\Module::isEnabled('WhatsappBirthdayReminder'))
            @include('whatsappbirthday::admin.settings.sms-section', [
                'settings' => $settings,
                'errors' => $errors,
                'wabrImageFile' => $wabrImageFile ?? null,
            ])
        @endif
    </div>
</div>
