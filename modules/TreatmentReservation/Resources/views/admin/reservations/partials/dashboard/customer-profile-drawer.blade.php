@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;

    $crmCustomerProfileUrl = $crmCustomerProfileUrl ?? route('admin.treatment_reservations.crm.customer_profile');
    $crmReminderUrlTemplate = $crmReminderUrlTemplate ?? route('admin.treatment_reservations.send_reminder', ['id' => '__ID__']);
@endphp

<aside
    class="tr-crm-customer-profile"
    id="tr-crm-customer-profile"
    hidden
    aria-hidden="true"
    data-profile-url="{{ $crmCustomerProfileUrl }}"
    data-reminder-url-template="{{ $crmReminderUrlTemplate }}"
    data-profile-title="{{ TrLang::trans('admin.crm.profile_title') }}"
    data-profile-loading="{{ TrLang::trans('admin.crm.profile_loading') }}"
    data-profile-failed="{{ TrLang::trans('admin.crm.profile_failed') }}"
    data-profile-visits="{{ TrLang::trans('admin.crm.profile_visits_title') }}"
    data-profile-upcoming="{{ TrLang::trans('admin.crm.profile_upcoming_title') }}"
    data-profile-reminders="{{ TrLang::trans('admin.crm.profile_reminders_title') }}"
    data-profile-no-visits="{{ TrLang::trans('admin.crm.profile_no_visits') }}"
    data-profile-no-upcoming="{{ TrLang::trans('admin.crm.profile_no_upcoming') }}"
    data-profile-view-user="{{ TrLang::trans('admin.crm.profile_view_user') }}"
    data-profile-send-reminder="{{ TrLang::trans('admin.crm.action_send_reminder') }}"
    data-profile-resend-reminder="{{ TrLang::trans('admin.crm.action_resend_reminder') }}"
    data-profile-reminder-sent="{{ TrLang::trans('admin.crm.reminder_sent_label') }}"
    data-profile-reminder-due="{{ TrLang::trans('admin.crm.reminder_due_label') }}"
    data-profile-reminder-sending="{{ TrLang::trans('admin.crm.reminder_sending') }}"
    data-profile-reminder-failed="{{ TrLang::trans('admin.crm.reminder_failed') }}"
    data-profile-reminder-success="{{ TrLang::trans('admin.crm.reminder_sent') }}"
>
    <div class="tr-crm-customer-profile__backdrop" data-close-customer-profile></div>
    <div class="tr-crm-customer-profile__panel" role="dialog" aria-labelledby="tr-crm-customer-profile-title">
        <header class="tr-crm-customer-profile__head">
            <div>
                <p class="tr-crm-customer-profile__eyebrow">{{ TrLang::trans('admin.crm.profile_eyebrow') }}</p>
                <h3 class="tr-crm-customer-profile__title" id="tr-crm-customer-profile-title">{{ TrLang::trans('admin.crm.profile_title') }}</h3>
            </div>
            <button type="button" class="tr-crm-customer-profile__close" data-close-customer-profile aria-label="{{ trans('admin::admin.buttons.close') }}">
                <i class="fa fa-times"></i>
            </button>
        </header>
        <div class="tr-crm-customer-profile__body" id="tr-crm-customer-profile-body">
            <div class="tr-crm-customer-profile__loading" id="tr-crm-customer-profile-loading" hidden>
                <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
                <span>{{ TrLang::trans('admin.crm.profile_loading') }}</span>
            </div>
            <div class="tr-crm-customer-profile__content" id="tr-crm-customer-profile-content"></div>
        </div>
    </div>
</aside>
