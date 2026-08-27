@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;

    $weekdayShort = [
        TrLang::trans('admin.appointment_availability.weekdays_short.mon'),
        TrLang::trans('admin.appointment_availability.weekdays_short.tue'),
        TrLang::trans('admin.appointment_availability.weekdays_short.wed'),
        TrLang::trans('admin.appointment_availability.weekdays_short.thu'),
        TrLang::trans('admin.appointment_availability.weekdays_short.fri'),
        TrLang::trans('admin.appointment_availability.weekdays_short.sat'),
        TrLang::trans('admin.appointment_availability.weekdays_short.sun'),
    ];

    $overrideCalendarPayload = $overrides->map(function ($override) {
        return [
            'id' => $override->id,
            'date' => $override->override_date?->format('Y-m-d'),
            'status' => $override->status,
            'reason' => $override->reason,
            'product_id' => (int) $override->product_id,
            'scope_label' => $override->isBranchWide()
                ? TrLang::trans('admin.appointment_availability.scope_branch')
                : TrLang::trans('admin.appointment_availability.scope_treatment_named', [
                    'treatment' => $override->product?->name ?? TrLang::trans('admin.appointment_availability.unknown_treatment'),
                ]),
            'times' => $override->slots->map(fn ($s) => \Illuminate\Support\Carbon::parse($s->start_time)->format('g:i A'))->values()->all(),
        ];
    })->values();

    $branchDaysCollection = collect($branchDays);
    $branchOpenDays = $branchDaysCollection->filter(fn ($day) => (bool) data_get($day, 'is_open'))->count();
    $branchSlotCount = $branchDaysCollection->sum(fn ($day) => count((array) data_get($day, 'times', [])));
    $selectedBranch = collect($branches)->first(fn ($branch) => (int) $branch->id === (int) $branchId);
    $selectedProduct = collect($products)->first(fn ($product) => (int) data_get($product, 'id') === (int) $productId);
@endphp

@extends('admin::layout')

@section('title', TrLang::trans('admin.appointment_availability.title'))

@section('content_header')
@endsection

@section('content')
    <div
        class="tr-avail-page tr-avail-page--modern"
        id="tr-appointment-availability"
        data-branch-url="{{ route('admin.treatment_reservations.availability.branch') }}"
        data-treatment-url="{{ route('admin.treatment_reservations.availability.treatment') }}"
        data-override-url="{{ route('admin.treatment_reservations.availability.overrides.store') }}"
        data-override-delete-url="{{ route('admin.treatment_reservations.availability.overrides.destroy', ['id' => '__ID__']) }}"
        data-preview-url="{{ route('admin.treatment_reservations.availability.preview_slots') }}"
        data-msg-confirm-all-closed="{{ TrLang::trans('admin.appointment_availability.confirm_all_closed') }}"
        data-label-preview-slots="{{ TrLang::trans('admin.appointment_availability.preview_slots') }}"
        data-label-preview-loading="{{ TrLang::trans('admin.appointment_availability.preview_loading') }}"
        data-label-preview-empty="{{ TrLang::trans('admin.appointment_availability.preview_empty') }}"
        data-label-preview-select="{{ TrLang::trans('admin.appointment_availability.preview_select_treatment') }}"
        data-label-preview-source="{{ TrLang::trans('admin.appointment_availability.preview_source') }}"
        data-label-preview-capacity="{{ TrLang::trans('admin.appointment_availability.preview_capacity') }}"
        data-label-preview-duration="{{ TrLang::trans('admin.appointment_availability.preview_duration') }}"
        data-label-preview-closed="{{ TrLang::trans('admin.appointment_availability.preview_closed') }}"
        data-label-preview-hint="{{ TrLang::trans('admin.appointment_availability.preview_slots_hint') }}"
        data-label-preview-open-inherit="{{ TrLang::trans('admin.appointment_availability.preview_open_inherit') }}"
        data-label-preview-closed-result="{{ TrLang::trans('admin.appointment_availability.preview_closed_result') }}"
        data-label-preview-custom-empty="{{ TrLang::trans('admin.appointment_availability.preview_custom_empty') }}"
        data-csrf="{{ csrf_token() }}"
        data-msg-saved="{{ TrLang::trans('admin.appointment_availability.saved') }}"
        data-msg-error="{{ TrLang::trans('admin.appointment_availability.error') }}"
        data-label-open="{{ TrLang::trans('admin.appointment_availability.status_open') }}"
        data-label-closed="{{ TrLang::trans('admin.appointment_availability.status_closed') }}"
        data-label-add-time="{{ TrLang::trans('admin.appointment_availability.add_time') }}"
        data-label-select-time="{{ TrLang::trans('admin.appointment_availability.select_time') }}"
        data-label-time-picker-title="{{ TrLang::trans('admin.appointment_availability.time_picker_title') }}"
        data-label-quick-times="{{ TrLang::trans('admin.appointment_availability.quick_times') }}"
        data-msg-invalid-time="{{ TrLang::trans('admin.appointment_availability.invalid_time') }}"
        data-msg-duplicate-time="{{ TrLang::trans('admin.appointment_availability.duplicate_time') }}"
        data-label-remove="{{ TrLang::trans('admin.appointment_availability.remove') }}"
        data-label-click-date="{{ TrLang::trans('admin.appointment_availability.click_date') }}"
        data-label-closed-hint="{{ TrLang::trans('admin.appointment_availability.closed_hint') }}"
        data-label-weekly-open="{{ TrLang::trans('admin.appointment_availability.weekly_open') }}"
        data-label-weekly-closed="{{ TrLang::trans('admin.appointment_availability.weekly_closed') }}"
        data-label-custom="{{ TrLang::trans('admin.appointment_availability.status_custom') }}"
        data-label-saving="{{ TrLang::trans('admin.appointment_availability.saving') }}"
        data-label-search-branch="{{ TrLang::trans('admin.appointment_availability.search_branch') }}"
        data-label-search-treatment="{{ TrLang::trans('admin.appointment_availability.search_treatment') }}"
        data-msg-date-required="{{ TrLang::trans('admin.appointment_availability.date_required') }}"
        data-msg-open-day-needs-times="{{ TrLang::trans('admin.appointment_availability.open_day_needs_times') }}"
        data-msg-custom-times-required="{{ TrLang::trans('admin.appointment_availability.custom_times_required') }}"
        data-msg-open-needs-weekly="{{ TrLang::trans('admin.appointment_availability.open_needs_weekly_or_times') }}"
        data-weekday-short='@json($weekdayShort)'
    >
        <header class="tr-avail-page__header">
            <div class="tr-avail-page__intro">
                <div class="tr-avail-page__icon" aria-hidden="true"><i class="fa fa-calendar-check-o"></i></div>
                <div>
                    <span class="tr-avail-page__eyebrow">{{ TrLang::trans('admin.appointment_availability.eyebrow') }}</span>
                    <h1>{{ TrLang::trans('admin.appointment_availability.title') }}</h1>
                    <p>{{ TrLang::trans('admin.appointment_availability.subtitle') }}</p>
                </div>
            </div>
            <div class="tr-avail-page__status">
                <span aria-hidden="true"></span>
                {{ TrLang::trans('admin.appointment_availability.live_status') }}
            </div>
        </header>

        <section class="tr-avail-overview" aria-label="{{ TrLang::trans('admin.appointment_availability.workspace_overview') }}">
            <div class="tr-avail-overview__item tr-avail-overview__item--scope">
                <span class="tr-avail-overview__icon" aria-hidden="true"><i class="fa fa-map-marker"></i></span>
                <div>
                    <small>{{ TrLang::trans('admin.appointment_availability.active_branch') }}</small>
                    <strong>{{ $selectedBranch?->name ?? '—' }}</strong>
                </div>
            </div>
            <div class="tr-avail-overview__item">
                <span class="tr-avail-overview__value"><span id="tr-open-days-count">{{ $branchOpenDays }}</span><small>/7</small></span>
                <div>
                    <small>{{ TrLang::trans('admin.appointment_availability.open_days') }}</small>
                    <strong>{{ TrLang::trans('admin.appointment_availability.weekly_coverage') }}</strong>
                </div>
            </div>
            <div class="tr-avail-overview__item">
                <span class="tr-avail-overview__value" id="tr-start-times-count">{{ $branchSlotCount }}</span>
                <div>
                    <small>{{ TrLang::trans('admin.appointment_availability.start_times') }}</small>
                    <strong>{{ TrLang::trans('admin.appointment_availability.per_week') }}</strong>
                </div>
            </div>
            <div class="tr-avail-overview__item">
                <span class="tr-avail-overview__value">{{ $overrides->count() }}</span>
                <div>
                    <small>{{ TrLang::trans('admin.appointment_availability.upcoming_changes') }}</small>
                    <strong>{{ TrLang::trans('admin.appointment_availability.saved_exceptions') }}</strong>
                </div>
            </div>
        </section>

        <form method="GET" class="card tr-avail-filters" id="tr-scope">
            <div class="card-body">
                <div class="tr-avail-filters__intro">
                    <span class="tr-avail-section-icon" aria-hidden="true"><i class="fa fa-crosshairs"></i></span>
                    <div>
                        <span class="tr-avail-kicker">{{ TrLang::trans('admin.appointment_availability.scope_context') }}</span>
                        <h2>{{ TrLang::trans('admin.appointment_availability.choose_scope') }}</h2>
                        <p>{{ TrLang::trans('admin.appointment_availability.choose_scope_hint') }}</p>
                    </div>
                </div>
                <div class="tr-avail-filters__fields">
                <div>
                    <label class="form-label" for="tr-branch-select">{{ TrLang::trans('admin.appointment_availability.branch') }}</label>
                    <div class="tr-avail-select-wrap">
                        <i class="fa fa-map-marker" aria-hidden="true"></i>
                        <select id="tr-branch-select" name="spa_branch_id" class="form-control">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label" for="tr-treatment-select">{{ TrLang::trans('admin.appointment_availability.treatment') }}</label>
                    <div class="tr-avail-select-wrap">
                        <i class="fa fa-heartbeat" aria-hidden="true"></i>
                        <select id="tr-treatment-select" name="product_id" class="form-control">
                        <option value="">{{ TrLang::trans('admin.appointment_availability.select_treatment') }}</option>
                        @foreach ($products as $product)
                            <option value="{{ $product['id'] }}" @selected($productId == $product['id'])>
                                {{ $product['name'] }}
                            </option>
                        @endforeach
                        </select>
                    </div>
                    <small>{{ TrLang::trans('admin.appointment_availability.treatment_optional_hint') }}</small>
                </div>
                </div>
            </div>
        </form>

        <div
            class="tr-avail-nav"
            role="tablist"
            aria-label="{{ TrLang::trans('admin.appointment_availability.page_navigation') }}"
        >
            <button
                type="button"
                class="tr-avail-nav__tab is-active"
                id="tr-tab-weekly"
                role="tab"
                aria-selected="true"
                aria-controls="tr-weekly-rules"
                data-tr-tab="tr-weekly-rules"
            >
                <i class="fa fa-calendar" aria-hidden="true"></i>
                <span>{{ TrLang::trans('admin.appointment_availability.nav_weekly') }}</span>
            </button>
            <button
                type="button"
                class="tr-avail-nav__tab"
                id="tr-tab-overrides"
                role="tab"
                aria-selected="false"
                aria-controls="tr-date-overrides"
                tabindex="-1"
                data-tr-tab="tr-date-overrides"
            >
                <i class="fa fa-calendar-plus-o" aria-hidden="true"></i>
                <span>{{ TrLang::trans('admin.appointment_availability.nav_overrides') }}</span>
                <span class="tr-avail-nav__count" aria-label="{{ $overrides->count() }} {{ TrLang::trans('admin.appointment_availability.saved_exceptions') }}">
                    {{ $overrides->count() }}
                </span>
            </button>
        </div>

        <section
            class="tr-avail-weekly-workspace tr-avail-tab-panel"
            id="tr-weekly-rules"
            role="tabpanel"
            aria-labelledby="tr-tab-weekly"
        >
            <div class="tr-avail-settings-grid">
            <div id="tr-branch-editor" role="region" aria-label="{{ TrLang::trans('admin.appointment_availability.branch_schedule') }}" @if ($productId > 0) hidden @endif>
                <div class="tr-avail-panel">
                    <div class="tr-avail-panel__head">
                        <div class="tr-avail-panel__title">
                            <span class="tr-avail-section-icon" aria-hidden="true"><i class="fa fa-building-o"></i></span>
                            <div>
                                <span class="tr-avail-kicker">{{ TrLang::trans('admin.appointment_availability.branch_defaults') }}</span>
                                <strong>{{ TrLang::trans('admin.appointment_availability.branch_schedule') }}</strong>
                                <small>{{ TrLang::trans('admin.appointment_availability.branch_schedule_hint') }}</small>
                            </div>
                        </div>
                        <span class="tr-avail-panel__badge">{{ TrLang::trans('admin.appointment_availability.branch_badge') }}</span>
                    </div>
                    <div class="tr-avail-panel__body">
                        <div class="tr-avail-callout">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                            <p>{{ TrLang::trans('admin.appointment_availability.checkout_filter_hint') }}</p>
                        </div>
                        <div id="tr-branch-days" data-days='@json($branchDays)'></div>
                    </div>
                    <div class="tr-avail-panel__foot">
                        <button type="button" class="btn btn-primary" id="tr-save-branch" @disabled($branchId < 1)>
                            <i class="fa fa-check" aria-hidden="true"></i>
                            {{ TrLang::trans('admin.appointment_availability.save_branch') }}
                        </button>
                    </div>
                </div>
            </div>

            @if ($productId > 0)
            <div id="tr-treatment-editor" role="region" aria-label="{{ TrLang::trans('admin.appointment_availability.treatment_schedule') }}">
                <div class="tr-avail-panel">
                    <div class="tr-avail-panel__head">
                        <div class="tr-avail-panel__title">
                            <span class="tr-avail-section-icon" aria-hidden="true"><i class="fa fa-heartbeat"></i></span>
                            <div>
                                <span class="tr-avail-kicker">{{ TrLang::trans('admin.appointment_availability.treatment_rules') }}</span>
                                <strong>{{ TrLang::trans('admin.appointment_availability.treatment_schedule') }}</strong>
                                <small>{{ TrLang::trans('admin.appointment_availability.treatment_schedule_hint') }}</small>
                            </div>
                        </div>
                        <span class="tr-avail-panel__badge">{{ TrLang::trans('admin.appointment_availability.treatment_badge') }}</span>
                    </div>
                    <div class="tr-avail-panel__body">
                        <div class="tr-avail-selected-treatment">
                            <span class="tr-avail-selected-treatment__icon" aria-hidden="true"><i class="fa fa-check"></i></span>
                            <div>
                                <span>{{ TrLang::trans('admin.appointment_availability.selected_treatment') }}</span>
                                <strong>{{ data_get($selectedProduct, 'name', '—') }}</strong>
                            </div>
                        </div>
                        <div class="tr-avail-meta">
                            <div>
                                <label class="form-label" for="tr-duration">{{ TrLang::trans('admin.appointment_availability.duration') }}</label>
                                <input type="number" class="form-control" id="tr-duration" min="15" max="600" step="15"
                                       value="{{ $treatment?->duration_minutes ?? 180 }}">
                            </div>
                            <div>
                                <label class="form-label" for="tr-capacity">{{ TrLang::trans('admin.appointment_availability.capacity') }}</label>
                                <input type="number" class="form-control" id="tr-capacity" min="1" max="100"
                                       value="{{ $treatment?->capacity_per_slot ?? 1 }}">
                            </div>
                            <div class="tr-avail-toggles">
                                <label>
                                    <input type="checkbox" id="tr-allow-tba" @checked($treatment?->allow_tba ?? true)>
                                    {{ TrLang::trans('admin.appointment_availability.allow_tba') }}
                                </label>
                                <label>
                                    <input type="checkbox" id="tr-bookable" @checked($treatment?->is_bookable ?? true)>
                                    {{ TrLang::trans('admin.appointment_availability.bookable') }}
                                </label>
                            </div>
                        </div>
                        <div class="tr-avail-callout">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                            <p>{{ TrLang::trans('admin.appointment_availability.checkout_filter_hint') }}</p>
                        </div>
                        <div id="tr-treatment-days" data-days='@json($treatmentDays)'></div>
                    </div>
                    <div class="tr-avail-panel__foot">
                        <button type="button" class="btn btn-primary" id="tr-save-treatment">
                            <i class="fa fa-check" aria-hidden="true"></i>
                            {{ TrLang::trans('admin.appointment_availability.save_treatment') }}
                        </button>
                    </div>
                </div>
            </div>
            @endif
            </div>
        </section>

        <div
            class="tr-avail-panel tr-avail-tab-panel mb-4"
            id="tr-date-overrides"
            role="tabpanel"
            aria-labelledby="tr-tab-overrides"
            hidden
        >
            <div class="tr-avail-panel__head">
                <div class="tr-avail-panel__title">
                    <span class="tr-avail-section-icon" aria-hidden="true"><i class="fa fa-calendar-plus-o"></i></span>
                    <div>
                        <span class="tr-avail-kicker">{{ TrLang::trans('admin.appointment_availability.exceptions') }}</span>
                        <strong>{{ TrLang::trans('admin.appointment_availability.date_overrides') }}</strong>
                        <small>{{ TrLang::trans('admin.appointment_availability.date_overrides_hint') }}</small>
                    </div>
                </div>
            </div>
            <div class="tr-avail-panel__body">
                <div class="tr-avail-callout">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                    <p>{{ TrLang::trans('admin.appointment_availability.calendar_hint') }}</p>
                </div>

                <div class="tr-avail-cal__layout">
                    <div
                        id="tr-avail-calendar"
                        class="tr-avail-cal"
                        data-overrides='@json($overrideCalendarPayload)'
                    >
                        <div class="tr-avail-cal__toolbar">
                            <div>
                                <span class="tr-avail-cal__eyebrow">{{ TrLang::trans('admin.appointment_availability.monthly_calendar') }}</span>
                                <strong id="tr-avail-cal-label" aria-live="polite"></strong>
                            </div>
                            <div class="tr-avail-cal__actions">
                            <button type="button" class="btn btn-sm btn-default tr-avail-cal__today" id="tr-avail-cal-today">{{ TrLang::trans('admin.appointment_availability.today') }}</button>
                            <button type="button" class="btn btn-sm btn-default" id="tr-avail-cal-prev" aria-label="{{ TrLang::trans('admin.appointment_availability.prev_month') }}"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
                            <button type="button" class="btn btn-sm btn-default" id="tr-avail-cal-next" aria-label="{{ TrLang::trans('admin.appointment_availability.next_month') }}"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
                            </div>
                        </div>
                        <div class="tr-avail-cal__grid"></div>
                        <div class="tr-avail-cal__legend">
                            <span class="tr-avail-cal__legend-item tr-avail-cal__legend-item--weekly-open">{{ TrLang::trans('admin.appointment_availability.weekly_open') }}</span>
                            <span class="tr-avail-cal__legend-item tr-avail-cal__legend-item--weekly-closed">{{ TrLang::trans('admin.appointment_availability.weekly_closed') }}</span>
                            <span class="tr-avail-cal__legend-item tr-avail-cal__legend-item--open">{{ TrLang::trans('admin.appointment_availability.status_open') }}</span>
                            <span class="tr-avail-cal__legend-item tr-avail-cal__legend-item--closed">{{ TrLang::trans('admin.appointment_availability.status_closed') }}</span>
                            <span class="tr-avail-cal__legend-item tr-avail-cal__legend-item--custom">{{ TrLang::trans('admin.appointment_availability.status_custom') }}</span>
                        </div>
                    </div>

                    <aside class="tr-avail-overrides">
                        <div class="tr-avail-overrides__head">
                            <div>
                                <span>{{ TrLang::trans('admin.appointment_availability.upcoming_changes') }}</span>
                                <h4>{{ TrLang::trans('admin.appointment_availability.overrides_list') }}</h4>
                            </div>
                            <span class="tr-avail-overrides__count">{{ $overrides->count() }}</span>
                        </div>
                        <div class="table-responsive tr-avail-overrides-table">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ TrLang::trans('admin.appointment_availability.date') }}</th>
                                        <th>{{ TrLang::trans('admin.appointment_availability.status') }}</th>
                                        <th>{{ TrLang::trans('admin.appointment_availability.times') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="tr-override-rows">
                                    @forelse ($overrides as $override)
                                        <tr data-id="{{ $override->id }}" data-override-date="{{ $override->override_date?->format('Y-m-d') }}">
                                            <td>
                                                <strong>{{ $override->override_date?->format('Y-m-d') }}</strong>
                                                <div class="text-muted tr-avail-overrides-table__scope">
                                                    {{ (int) $override->product_id === 0
                                                        ? TrLang::trans('admin.appointment_availability.scope_branch')
                                                        : TrLang::trans('admin.appointment_availability.scope_treatment_named', [
                                                            'treatment' => $override->product?->name ?? TrLang::trans('admin.appointment_availability.unknown_treatment'),
                                                        ]) }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $override->status }}">
                                                    {{ TrLang::trans('admin.appointment_availability.status_' . $override->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $override->slots->map(fn ($s) => \Illuminate\Support\Carbon::parse($s->start_time)->format('g:i A'))->implode(', ') ?: '—' }}
                                            </td>
                                            <td class="text-right">
                                                <button type="button" class="btn btn-sm btn-danger tr-delete-override" data-id="{{ $override->id }}">
                                                    {{ TrLang::trans('admin.appointment_availability.remove') }}
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="tr-override-empty">
                                            <td colspan="4">{{ TrLang::trans('admin.appointment_availability.no_overrides') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <details class="tr-avail-advanced">
                            <summary>{{ TrLang::trans('admin.appointment_availability.advanced_override') }}</summary>
                            <div class="tr-avail-advanced__body">
                                <p class="form-text">{{ TrLang::trans('admin.appointment_availability.override_hint') }}</p>
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ TrLang::trans('admin.appointment_availability.date') }}</label>
                                        <input type="date" class="form-control" id="tr-override-date">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ TrLang::trans('admin.appointment_availability.status') }}</label>
                                        <select class="form-control" id="tr-override-status">
                                            <option value="custom">{{ TrLang::trans('admin.appointment_availability.status_custom') }}</option>
                                            <option value="open">{{ TrLang::trans('admin.appointment_availability.status_open') }}</option>
                                            <option value="closed">{{ TrLang::trans('admin.appointment_availability.status_closed') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ TrLang::trans('admin.appointment_availability.times') }}</label>
                                        <input type="text" class="form-control" id="tr-override-times" placeholder="{{ TrLang::trans('admin.appointment_availability.time_placeholder') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ TrLang::trans('admin.appointment_availability.reason') }}</label>
                                        <input type="text" class="form-control" id="tr-override-reason" placeholder="{{ TrLang::trans('admin.appointment_availability.reason_placeholder') }}">
                                    </div>
                                    <div class="col-12">
                                        <button type="button" class="btn btn-primary" id="tr-save-override" @disabled($branchId < 1)>
                                            {{ TrLang::trans('admin.appointment_availability.save_override') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </details>
                    </aside>
                </div>
            </div>
        </div>
    </div>

    <div id="tr-avail-override-modal" class="tr-avail-modal" hidden>
        <div class="tr-avail-modal__backdrop" data-ov-backdrop></div>
        <div class="tr-avail-modal__panel" role="dialog" aria-modal="true" aria-labelledby="tr-avail-ov-title">
            <header class="tr-avail-modal__head">
                <h3 id="tr-avail-ov-title">{{ TrLang::trans('admin.appointment_availability.override_modal_title') }}</h3>
                <button type="button" class="btn btn-sm btn-default" data-ov-close aria-label="{{ TrLang::trans('admin.appointment_availability.cancel') }}">&times;</button>
            </header>
            <div class="tr-avail-modal__body">
                <p class="tr-avail-modal__date" data-ov-date-label></p>
                <input type="hidden" id="tr-ov-date">
                <input type="hidden" id="tr-ov-status" value="custom">

                <div class="tr-avail-status-pills" role="radiogroup" aria-label="{{ TrLang::trans('admin.appointment_availability.status') }}">
                    <label>
                        <input type="radio" name="tr-ov-status-pill" value="open">
                        {{ TrLang::trans('admin.appointment_availability.status_open') }}
                    </label>
                    <label>
                        <input type="radio" name="tr-ov-status-pill" value="closed">
                        {{ TrLang::trans('admin.appointment_availability.status_closed') }}
                    </label>
                    <label>
                        <input type="radio" name="tr-ov-status-pill" value="custom" checked>
                        {{ TrLang::trans('admin.appointment_availability.status_custom') }}
                    </label>
                </div>

                <input type="hidden" id="tr-ov-times">
                <div class="form-group mb-2 tr-ov-time-editor" data-ov-time-editor hidden>
                    <label class="form-label" for="tr-ov-time-input">{{ TrLang::trans('admin.appointment_availability.times') }}</label>
                    <div class="tr-ov-time-editor__controls">
                        <div class="tr-avail-time-field">
                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                            <input
                                type="text"
                                class="tr-day-time-input"
                                id="tr-ov-time-input"
                                placeholder="{{ TrLang::trans('admin.appointment_availability.select_time') }}"
                                autocomplete="off"
                            >
                        </div>
                        <button type="button" class="btn btn-default tr-day-add-time" data-ov-add-time>
                            {{ TrLang::trans('admin.appointment_availability.add_time') }}
                        </button>
                    </div>
                    <div class="tr-ov-time-editor__chips" data-ov-time-chips aria-live="polite"></div>
                    <small class="form-text">{{ TrLang::trans('admin.appointment_availability.custom_time_help') }}</small>
                </div>
                <div class="form-group mb-2">
                    <label class="form-label" for="tr-ov-reason">{{ TrLang::trans('admin.appointment_availability.reason') }}</label>
                    <input type="text" class="form-control" id="tr-ov-reason" placeholder="{{ TrLang::trans('admin.appointment_availability.reason_placeholder') }}">
                </div>

                <div class="tr-avail-preview" id="tr-ov-preview">
                    <div class="tr-avail-preview__head">
                        <strong>{{ TrLang::trans('admin.appointment_availability.preview_slots') }}</strong>
                        <small>{{ TrLang::trans('admin.appointment_availability.preview_slots_hint') }}</small>
                    </div>
                    <div class="tr-avail-preview__body" data-ov-preview-body aria-live="polite"></div>
                </div>

            </div>
            <footer class="tr-avail-modal__foot">
                <button type="button" class="btn btn-default" data-ov-close>{{ TrLang::trans('admin.appointment_availability.cancel') }}</button>
                <button type="button" class="btn btn-primary" data-ov-save>{{ TrLang::trans('admin.appointment_availability.save_override') }}</button>
            </footer>
        </div>
    </div>
@endsection

@push('scripts')
    @vite([
        'modules/TreatmentReservation/Resources/assets/admin/sass/main.scss',
        'modules/TreatmentReservation/Resources/assets/admin/js/appointment-availability.js',
    ])
    <script>
        window.trAppointmentAvailabilityBoot = {
            spaBranchId: {{ (int) $branchId }},
            productId: {{ (int) $productId }},
            calendarMonth: @json(now()->format('Y-m')),
        };
    </script>
@endpush
