@component('setting::admin.settings.partials.settings-wrap')
    <div class="box-content clearfix">
        <h4 class="section-title">{{ trans('setting::settings.sections.smtp') }}</h4>

        @component('setting::admin.settings.partials.fields-grid')
            @slot('left')
                {{ Form::text('mail_from_address', trans('setting::attributes.mail_from_address'), $errors, $settings) }}
                {{ Form::text('mail_username', trans('setting::attributes.mail_username'), $errors, $settings) }}
                {{ Form::password('mail_password', trans('setting::attributes.mail_password'), $errors, $settings) }}
            @endslot
            @slot('right')
                {{ Form::text('mail_from_name', trans('setting::attributes.mail_from_name'), $errors, $settings) }}
                {{ Form::text('mail_host', trans('setting::attributes.mail_host'), $errors, $settings) }}
                {{ Form::text('mail_port', trans('setting::attributes.mail_port'), $errors, $settings) }}
                {{ Form::select('mail_encryption', trans('setting::attributes.mail_encryption'), $errors, $encryptionProtocols, $settings) }}
            @endslot
        @endcomponent
    </div>

    <div class="st-fields-grid st-fields-grid--sections">
        <div class="st-fields-grid__col">
            <div class="box-content clearfix">
                <h4 class="section-title">{{ trans('setting::settings.form.customer_notification_settings') }}</h4>
                {{ Form::checkbox('welcome_email', trans('setting::attributes.welcome_email'), trans('setting::settings.form.send_welcome_email_after_registration'), $errors, $settings) }}
            </div>
        </div>

        <div class="st-fields-grid__col">
            <div class="box-content clearfix">
                <h4 class="section-title">{{ trans('setting::settings.form.order_notification_settings') }}</h4>
                {{ Form::checkbox('admin_order_email', trans('setting::attributes.admin_order_email'), trans('setting::settings.form.send_new_order_notification_to_admin'), $errors, $settings) }}
                {{ Form::checkbox('invoice_email', trans('setting::attributes.invoice_email'), trans('setting::settings.form.send_invoice_email'), $errors, $settings) }}
            </div>
        </div>

        <div class="st-fields-grid__full">
            <div class="box-content clearfix">
                {{ Form::select('email_order_statuses', trans('setting::attributes.email_order_statuses'), $errors, $orderStatuses, $settings, ['class' => 'selectize prevent-creation', 'multiple' => true]) }}
            </div>
        </div>
    </div>
@endcomponent
