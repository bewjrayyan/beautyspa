<aside class="tr-portal-quick-actions">
    <div class="tr-portal-quick-actions__head">
        <h3>{{ trans('treatmentreservation::admin.portal.quick_links') }}</h3>
        <p>{{ trans('treatmentreservation::admin.portal.quick_links_help') }}</p>
    </div>

    <nav class="tr-portal-quick-actions__list">
        @if (empty($adminPortalPreview))
            @hasAccess('admin.treatment_reservations.portal.create')
                <button
                    type="button"
                    class="tr-portal-quick-actions__item tr-portal-quick-actions__item--button"
                    data-toggle="modal"
                    data-target="#tr-portal-manual-booking-modal"
                >
                    <span class="tr-portal-quick-actions__icon tr-portal-quick-actions__icon--create">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                    </span>
                    <span class="tr-portal-quick-actions__body">
                        <strong>{{ trans('treatmentreservation::admin.manual_booking.portal_open') }}</strong>
                        <span>{{ trans('treatmentreservation::admin.manual_booking.portal_quick_hint') }}</span>
                    </span>
                    <i class="fa fa-chevron-right tr-portal-quick-actions__arrow" aria-hidden="true"></i>
                </button>
            @endHasAccess
        @endif

        <a href="#" class="tr-portal-quick-actions__item" data-schedule-view="kanban" data-scroll-schedule>
            <span class="tr-portal-quick-actions__icon tr-portal-quick-actions__icon--kanban">
                <i class="fa fa-columns" aria-hidden="true"></i>
            </span>
            <span class="tr-portal-quick-actions__body">
                <strong>{{ trans('treatmentreservation::admin.portal.tab_kanban') }}</strong>
                <span>{{ trans('treatmentreservation::admin.portal.subtitle') }}</span>
            </span>
            <i class="fa fa-chevron-right tr-portal-quick-actions__arrow" aria-hidden="true"></i>
        </a>

        <a href="#" class="tr-portal-quick-actions__item" data-schedule-view="calendar" data-scroll-schedule>
            <span class="tr-portal-quick-actions__icon tr-portal-quick-actions__icon--calendar">
                <i class="fa fa-calendar" aria-hidden="true"></i>
            </span>
            <span class="tr-portal-quick-actions__body">
                <strong>{{ trans('treatmentreservation::admin.portal.tab_calendar') }}</strong>
                <span>{{ trans('treatmentreservation::admin.portal.dashboard_calendar_hint') }}</span>
            </span>
            <i class="fa fa-chevron-right tr-portal-quick-actions__arrow" aria-hidden="true"></i>
        </a>

        @if (empty($adminPortalPreview))
            <a href="{{ route('admin.treatment_reservations.portal.availability') }}" class="tr-portal-quick-actions__item">
                <span class="tr-portal-quick-actions__icon tr-portal-quick-actions__icon--availability">
                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                </span>
                <span class="tr-portal-quick-actions__body">
                    <strong>{{ trans('treatmentreservation::admin.availability.title') }}</strong>
                    <span>{{ trans('treatmentreservation::admin.portal.dashboard_availability_hint') }}</span>
                </span>
                <i class="fa fa-chevron-right tr-portal-quick-actions__arrow" aria-hidden="true"></i>
            </a>

            <a href="{{ route('admin.treatment_reservations.portal.account') }}" class="tr-portal-quick-actions__item">
                <span class="tr-portal-quick-actions__icon tr-portal-quick-actions__icon--account">
                    <i class="fa fa-user" aria-hidden="true"></i>
                </span>
                <span class="tr-portal-quick-actions__body">
                    <strong>{{ trans('treatmentreservation::admin.portal.account_title') }}</strong>
                    <span>{{ trans('treatmentreservation::admin.portal.account_subtitle') }}</span>
                </span>
                <i class="fa fa-chevron-right tr-portal-quick-actions__arrow" aria-hidden="true"></i>
            </a>
        @endif
    </nav>
</aside>
