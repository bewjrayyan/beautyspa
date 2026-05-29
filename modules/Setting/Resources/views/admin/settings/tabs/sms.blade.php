<div class="st-tab st-tab--whatsapp settings-form">
    <p class="st-tab__lead">{{ trans('setting::settings.sms.lead') }}</p>

    <div class="wa-settings">
        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-whatsapp',
            'title' => trans('setting::settings.sms.sections.api.title'),
            'description' => trans('setting::settings.sms.sections.api.description'),
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

                {{ Form::text('onesender_admin_phones', trans('setting::attributes.onesender_admin_phones'), $errors, $settings, [
                    'placeholder' => config('setting.whatsapp_notifications.onesender_admin_phones') ?: trans('setting::settings.sms.placeholders.admin_phones'),
                ]) }}
                {{ Form::text('onesender_whatsapp_group_id', trans('setting::attributes.onesender_whatsapp_group_id'), $errors, $settings, [
                    'placeholder' => config('setting.whatsapp_notifications.onesender_whatsapp_group_id') ?: trans('setting::settings.sms.placeholders.whatsapp_group_id'),
                ]) }}
                {{ Form::text('whatsapp_group_staff_name', trans('setting::attributes.whatsapp_group_staff_name'), $errors, $settings, [
                    'placeholder' => config('setting.whatsapp_notifications.whatsapp_group_staff_name'),
                ]) }}
                <p class="help-block text-muted wa-settings__full-width">{{ trans('setting::settings.form.whatsapp_group_staff_name_help') }}</p>

                {{ Form::text('whatsapp_order_tracking_url', trans('setting::attributes.whatsapp_order_tracking_url'), $errors, $settings, [
                    'placeholder' => config('setting.whatsapp_notifications.whatsapp_order_tracking_url'),
                ]) }}
                <p class="help-block text-muted wa-settings__full-width">{{ trans('setting::settings.sms.sections.api.tracking_help') }}</p>
            </div>
        @endcomponent

        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-shield',
            'title' => trans('setting::settings.sms.sections.delivery.title'),
            'description' => trans('setting::settings.sms.sections.delivery.description'),
            'class' => 'st-section--compact',
        ])
            @component('setting::admin.settings.partials.wa-notification-item', [
                'enabledName' => 'onesender_sending_paused',
                'enabledLabel' => trans('setting::settings.form.pause_onesender_sending'),
                'hint' => trans('setting::settings.form.pause_onesender_sending_help'),
            ])
            @endcomponent

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
        @endcomponent

        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-user',
            'title' => trans('setting::settings.sms.sections.customer.title'),
            'description' => trans('setting::settings.sms.sections.customer.description'),
        ])
            @component('setting::admin.settings.partials.wa-notification-item', [
                'enabledName' => 'welcome_sms',
                'enabledLabel' => trans('setting::settings.form.send_welcome_whatsapp_after_registration'),
                'hint' => trans('setting::settings.sms.sections.customer.welcome_help'),
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
                {{ Form::textarea('whatsapp_customer_completed_message', trans('setting::attributes.whatsapp_customer_completed_message'), $errors, $settings, [
                    'rows' => 5,
                    'placeholder' => config('setting.whatsapp_notifications.whatsapp_customer_completed_message'),
                ]) }}
                <p class="help-block text-muted">{{ trans('setting::settings.sms.placeholders_hint') }}</p>
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
                {{ Form::textarea('whatsapp_customer_followup_message', trans('setting::attributes.whatsapp_customer_followup_message'), $errors, $settings, [
                    'rows' => 5,
                    'placeholder' => config('setting.whatsapp_notifications.whatsapp_customer_followup_message'),
                ]) }}
                <p class="help-block text-muted">{{ trans('setting::settings.sms.placeholders_hint') }}</p>
            @endcomponent
        @endcomponent

        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-shopping-cart',
            'title' => trans('setting::settings.sms.sections.order.title'),
            'description' => trans('setting::settings.sms.sections.order.description'),
        ])
            @component('setting::admin.settings.partials.wa-notification-item', [
                'enabledName' => 'new_order_admin_sms',
                'enabledLabel' => trans('setting::settings.form.send_new_order_notification_to_admin'),
            ])
            @endcomponent

            @component('setting::admin.settings.partials.wa-notification-item', [
                'enabledName' => 'new_order_sms',
                'enabledLabel' => trans('setting::settings.form.send_new_order_notification_to_customer'),
            ])
            @endcomponent

            <div class="st-wa-item st-wa-item--plain">
                {{ Form::select('sms_order_statuses', trans('setting::attributes.sms_order_statuses'), $errors, $orderStatuses, $settings, [
                    'class' => 'selectize prevent-creation',
                    'multiple' => true,
                ]) }}
                <p class="help-block text-muted">{{ trans('setting::settings.sms.sections.order.statuses_help') }}</p>
            </div>

            @component('setting::admin.settings.partials.wa-notification-item', [
                'enabledName' => 'whatsapp_completed_group_enabled',
                'enabledLabel' => trans('setting::settings.form.send_completed_order_to_whatsapp_group'),
            ])
            @endcomponent

            @component('setting::admin.settings.partials.wa-notification-item', [
                'enabledName' => 'whatsapp_completed_beautician_enabled',
                'enabledLabel' => trans('setting::settings.form.send_completed_order_to_beautician'),
                'hint' => trans('setting::settings.form.whatsapp_group_note_format_help'),
            ])
            @endcomponent
        @endcomponent

        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-scissors',
            'title' => trans('setting::settings.sms.sections.beautician.title'),
            'description' => trans('setting::settings.sms.sections.beautician.description'),
        ])
            @component('setting::admin.settings.partials.wa-notification-item', [
                'enabledName' => 'whatsapp_beautician_new_booking_enabled',
                'enabledLabel' => trans('setting::settings.form.send_new_booking_to_beautician'),
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
            @endcomponent
        @endcomponent
    </div>
</div>
