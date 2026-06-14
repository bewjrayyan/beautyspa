@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
@endphp

<aside class="tr-crm-day-drawer" id="tr-crm-day-drawer" hidden aria-hidden="true">
    <div class="tr-crm-day-drawer__backdrop" data-close-drawer></div>
    <div class="tr-crm-day-drawer__panel" role="dialog" aria-labelledby="tr-crm-day-drawer-title">
        <header class="tr-crm-day-drawer__head">
            <div>
                <p class="tr-crm-day-drawer__eyebrow">{{ TrLang::trans('admin.crm.day_schedule') }}</p>
                <h3 class="tr-crm-day-drawer__title" id="tr-crm-day-drawer-title">—</h3>
            </div>
            <button type="button" class="tr-crm-day-drawer__close" data-close-drawer aria-label="{{ trans('admin::admin.buttons.close') }}">
                <i class="fa fa-times"></i>
            </button>
        </header>
        <div class="tr-crm-day-drawer__body">
            <ul class="tr-crm-day-drawer__list" id="tr-crm-day-drawer-list"></ul>
            <p class="tr-crm-day-drawer__empty" id="tr-crm-day-drawer-empty" hidden>
                {{ TrLang::trans('admin.calendar.no_bookings') }}
            </p>
        </div>
    </div>
</aside>
