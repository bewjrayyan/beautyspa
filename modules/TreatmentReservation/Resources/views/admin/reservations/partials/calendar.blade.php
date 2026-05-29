@php
    $embedded = $embedded ?? false;
@endphp

<div class="tr-calendar box {{ $embedded ? 'tr-calendar--embedded' : 'tr-calendar--page' }}">
    <div class="tr-calendar-top">
        <div class="tr-calendar-intro">
            <h4>{{ trans('treatmentreservation::admin.calendar.title') }}</h4>
            <p class="tr-calendar-intro__text">
                {{ $embedded ? trans('treatmentreservation::admin.calendar.embedded_subtitle') : trans('treatmentreservation::admin.calendar.subtitle') }}
            </p>
            @unless ($embedded)
                <p class="tr-calendar-intro__hint">
                    <i class="fa fa-hand-pointer-o"></i>
                    {{ trans('treatmentreservation::admin.calendar.click_hint') }}
                </p>
            @endunless
        </div>

        <div class="tr-calendar-month-nav">
            @if ($embedded && ($fullViewUrl ?? true))
                <a
                    href="{{ $fullViewUrl ?? route('admin.treatment_reservations.index', ['view' => 'calendar']) }}"
                    class="tr-calendar-month-nav__expand"
                >
                    <i class="fa {{ $fullViewIcon ?? 'fa-expand' }}"></i>
                    {{ $fullViewLabel ?? trans('treatmentreservation::admin.calendar.full_view') }}
                </a>
            @endif

            <div class="tr-calendar-month-nav__group">
                <button type="button" class="tr-calendar-month-nav__btn" id="tr-cal-prev" aria-label="{{ trans('treatmentreservation::admin.calendar.prev') }}">
                    <i class="fa fa-chevron-left"></i>
                </button>
                <h4 class="tr-calendar-month-nav__label" id="tr-cal-month-label"></h4>
                <button type="button" class="tr-calendar-month-nav__btn" id="tr-cal-next" aria-label="{{ trans('treatmentreservation::admin.calendar.next') }}">
                    <i class="fa fa-chevron-right"></i>
                </button>
            </div>

            <button type="button" class="tr-calendar-month-nav__today" id="tr-cal-today">
                {{ trans('treatmentreservation::admin.calendar.today') }}
            </button>
        </div>
    </div>

    <div class="tr-calendar-meta">
        <div class="tr-calendar-status-legend">
            <span class="tr-calendar-status-legend__label">{{ trans('treatmentreservation::admin.calendar.status_legend') }}</span>
            <div class="tr-calendar-status-legend__items">
                <span class="tr-calendar-status-legend__item">
                    <span class="tr-cal-event-status-dot tr-cal-event-status-dot--pending"></span>
                    {{ trans('treatmentreservation::admin.kanban.pending') }}
                </span>
                <span class="tr-calendar-status-legend__item">
                    <span class="tr-cal-event-status-dot tr-cal-event-status-dot--in_progress"></span>
                    {{ trans('treatmentreservation::admin.kanban.in_progress') }}
                </span>
                <span class="tr-calendar-status-legend__item">
                    <span class="tr-cal-event-status-dot tr-cal-event-status-dot--completed"></span>
                    {{ trans('treatmentreservation::admin.kanban.completed') }}
                </span>
            </div>
        </div>

        <div class="tr-calendar-legend" id="tr-calendar-legend" hidden></div>
    </div>

    <div class="tr-calendar-board">
        <div class="tr-calendar-weekdays">
            <span>{{ trans('treatmentreservation::admin.calendar.weekdays.mon') }}</span>
            <span>{{ trans('treatmentreservation::admin.calendar.weekdays.tue') }}</span>
            <span>{{ trans('treatmentreservation::admin.calendar.weekdays.wed') }}</span>
            <span>{{ trans('treatmentreservation::admin.calendar.weekdays.thu') }}</span>
            <span>{{ trans('treatmentreservation::admin.calendar.weekdays.fri') }}</span>
            <span class="tr-calendar-weekdays__weekend">{{ trans('treatmentreservation::admin.calendar.weekdays.sat') }}</span>
            <span class="tr-calendar-weekdays__weekend">{{ trans('treatmentreservation::admin.calendar.weekdays.sun') }}</span>
        </div>

        <div class="tr-calendar-grid" id="tr-calendar-grid">
            <div class="tr-calendar-loading">
                <i class="fa fa-spinner fa-spin"></i>
                <span>{{ trans('treatmentreservation::admin.calendar.loading') }}</span>
            </div>
        </div>
    </div>
</div>
