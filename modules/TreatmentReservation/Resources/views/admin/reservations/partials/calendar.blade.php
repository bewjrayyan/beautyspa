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
                    href="{{ $fullViewUrl ?: route('admin.treatment_reservations.index', ['view' => 'calendar']) }}"
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
                <button type="button" class="tr-calendar-month-nav__sibling" id="tr-cal-month-prev" data-month-offset="-1"></button>
                <h4 class="tr-calendar-month-nav__label" id="tr-cal-month-label"></h4>
                <button type="button" class="tr-calendar-month-nav__sibling" id="tr-cal-month-next" data-month-offset="1"></button>
                <button type="button" class="tr-calendar-month-nav__btn" id="tr-cal-next" aria-label="{{ trans('treatmentreservation::admin.calendar.next') }}">
                    <i class="fa fa-chevron-right"></i>
                </button>
            </div>

            <div class="tr-calendar-month-nav__trailing">
                <button type="button" class="tr-calendar-month-nav__today" id="tr-cal-today">
                    {{ trans('treatmentreservation::admin.calendar.today') }}
                </button>

                <div class="tr-calendar-view-toggle" id="tr-cal-view-toggle" role="group" aria-label="{{ trans('treatmentreservation::admin.calendar.view_toggle_aria') }}">
                    <button type="button" class="tr-calendar-view-toggle__btn is-active" data-cal-view="month">
                        {{ trans('treatmentreservation::admin.calendar.view_month') }}
                    </button>
                    <button type="button" class="tr-calendar-view-toggle__btn" data-cal-view="week">
                        {{ trans('treatmentreservation::admin.calendar.view_week') }}
                    </button>
                </div>
            </div>
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

        <div class="tr-calendar-grid-viewport" id="tr-calendar-grid-viewport">
            <div class="tr-calendar-grid-track" id="tr-calendar-grid-track">
                <div class="tr-calendar-grid" id="tr-calendar-grid">
                    <div class="tr-calendar-loading">
                        <i class="fa fa-spinner fa-spin"></i>
                        <span>{{ trans('treatmentreservation::admin.calendar.loading') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tr-calendar-day-view" id="tr-cal-day-view" hidden>
        <header class="tr-calendar-day-view__header">
            <button type="button" class="tr-calendar-day-view__nav" id="tr-cal-day-prev" aria-label="{{ trans('treatmentreservation::admin.calendar.prev_week') }}">
                <i class="fa fa-chevron-left"></i>
            </button>
            <h3 class="tr-calendar-day-view__title" id="tr-cal-day-title"></h3>
            <button type="button" class="tr-calendar-day-view__nav" id="tr-cal-day-next" aria-label="{{ trans('treatmentreservation::admin.calendar.next_week') }}">
                <i class="fa fa-chevron-right"></i>
            </button>
        </header>
        <div class="tr-cal-week-grid" id="tr-cal-week-grid"></div>
    </div>
</div>
