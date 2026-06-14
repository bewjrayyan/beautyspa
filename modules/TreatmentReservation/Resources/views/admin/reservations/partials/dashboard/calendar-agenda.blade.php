@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
@endphp

<section class="tr-crm-panel tr-crm-panel--calendar-agenda">
    <header class="tr-crm-panel__head tr-crm-panel__head--calendar">
        <div>
            <h2 class="tr-crm-panel__title tr-crm-panel__title--lg">{{ TrLang::trans('admin.crm.calendar_title_long') }}</h2>
            <p class="tr-crm-panel__lead">{{ TrLang::trans('admin.crm.calendar_lead_long') }}</p>
        </div>
    </header>

    <div class="tr-crm-calendar-agenda">
        <div class="tr-crm-calendar-agenda__calendar" data-crm-compact-calendar="1">
            @include('treatmentreservation::admin.reservations.partials.calendar', ['embedded' => true])
        </div>

        <aside class="tr-crm-calendar-agenda__agenda" id="tr-crm-agenda-panel" aria-live="polite">
            <header class="tr-crm-agenda__head">
                <p class="tr-crm-agenda__eyebrow">{{ TrLang::trans('admin.crm.agenda_title') }}</p>
                <h4 class="tr-crm-agenda__title" id="tr-crm-agenda-title">—</h4>
            </header>
            <ul class="tr-crm-agenda__list" id="tr-crm-agenda-list" data-crm-list></ul>
            <p class="tr-crm-agenda__empty" id="tr-crm-agenda-empty" hidden>
                {{ TrLang::trans('admin.calendar.no_bookings') }}
            </p>
        </aside>
    </div>
</section>
