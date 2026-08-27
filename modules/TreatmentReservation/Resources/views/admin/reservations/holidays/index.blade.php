@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
@endphp

@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', TrLang::trans('admin.holidays_title'))
    @slot('subtitle', TrLang::trans('admin.holidays_subtitle'))
@endcomponent

@section('content')
    <div class="tr-holidays-page">
        <div class="row">
            <div class="col-lg-8">
                <div class="card tr-holidays-page__hero-card">
                    <div class="card-header tr-holidays-page__hero-header">
                        <div>
                            <div class="tr-holidays-page__eyebrow">{{ TrLang::trans('admin.holidays_import_title') }}</div>
                            <h3 class="tr-holidays-page__hero-title">{{ TrLang::trans('admin.holidays_title') }}</h3>
                        </div>
                        <span class="tr-holidays-page__hero-badge">{{ $year }}</span>
                    </div>

                    <div class="card-body tr-holidays-page__hero-body">
                        <form method="POST" action="{{ route('admin.treatment_reservations.holidays.import') }}" class="tr-holidays-page__import-form">
                            @csrf

                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">{{ TrLang::trans('admin.holidays_import_year_label') }}</label>
                                    <input
                                        type="number"
                                        name="year"
                                        class="form-control"
                                        min="1900"
                                        max="3000"
                                        value="{{ $year }}"
                                        required
                                    />
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ TrLang::trans('admin.holidays_import_state_label') }}</label>
                                    <input
                                        type="text"
                                        name="state"
                                        class="form-control"
                                        placeholder="{{ TrLang::trans('admin.holidays_import_state_placeholder') }}"
                                    />
                                    <div class="form-text">{{ TrLang::trans('admin.holidays_import_state_hint') }}</div>
                                </div>

                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100 tr-holidays-page__import-btn">
                                        {{ TrLang::trans('admin.holidays_import_submit') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="tr-holidays-page__stats-card">
                    <div class="tr-holidays-page__stats-value">{{ number_format($rowCount) }}</div>
                    <div class="tr-holidays-page__stats-label">
                        {{ TrLang::trans('admin.holidays_db_rows_for_year', ['count' => $rowCount, 'year' => $year]) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card tr-holidays-page__table-card mt-4">
                <div class="card-header tr-holidays-page__table-header">
                    {{ TrLang::trans('admin.holidays_add_title') }}
                </div>
                <div class="card-body">
                    <form
                        method="POST"
                        action="{{ route('admin.treatment_reservations.holidays.store') }}"
                        class="row g-3 align-items-end"
                    >
                        @csrf

                        <div class="col-md-3">
                            <label class="form-label">{{ TrLang::trans('admin.holidays_add_date_label') }}</label>
                            <input
                                type="date"
                                name="date"
                                class="form-control"
                                required
                                value="{{ $year . '-01-01' }}"
                                min="{{ $year . '-01-01' }}"
                                max="{{ $year . '-12-31' }}"
                            />
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">{{ TrLang::trans('admin.holidays_add_name_label') }}</label>
                            <input type="text" name="name" class="form-control" required />
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">{{ TrLang::trans('admin.holidays_add_color_label') }}</label>
                            <input type="color" name="color" class="form-control form-control-color" value="#3b82f6" required />
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">{{ TrLang::trans('admin.holidays_add_states_label') }}</label>
                            <input type="text" name="state_codes" class="form-control" />
                        </div>

                        <div class="col-md-1 d-grid">
                            <button type="submit" class="btn btn-primary tr-holidays-page__import-btn">
                                {{ TrLang::trans('admin.holidays_add_submit') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <div class="card tr-holidays-page__table-card">
                <div class="card-header tr-holidays-page__table-header">
                    {{ TrLang::trans('admin.holidays_table_title') }}
                </div>
                <div class="card-body p-0">
                    <div class="p-3 border-bottom" style="background: rgba(248, 250, 252, 0.9);">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="sr-only" for="tr-holidays-search">{{ TrLang::trans('admin.holidays_search_aria') }}</label>
                                <input
                                    id="tr-holidays-search"
                                    type="search"
                                    class="form-control form-control-sm"
                                    autocomplete="off"
                                    aria-label="{{ TrLang::trans('admin.holidays_search_aria') }}"
                                />
                            </div>
                            <div class="col-md-3">
                                <select id="tr-holidays-sort" class="form-select form-select-sm">
                                    <option value="date_desc" selected>{{ TrLang::trans('admin.holidays_sort_date') }}</option>
                                    <option value="name_asc">{{ TrLang::trans('admin.holidays_sort_name') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="tr-holidays-page-size" class="form-select form-select-sm">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex justify-content-end">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="tr-holidays-prev" aria-label="{{ TrLang::trans('admin.holidays_prev_aria') }}">
                                        <i class="fa fa-chevron-left" aria-hidden="true"></i>
                                    </button>
                                    <div id="tr-holidays-page-indicator" class="text-muted" style="min-width:56px; text-align:center;"></div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="tr-holidays-next" aria-label="{{ TrLang::trans('admin.holidays_next_aria') }}">
                                        <i class="fa fa-chevron-right" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped mb-0 tr-holidays-table">
                            <thead>
                                <tr>
                                    <th style="width: 170px;">{{ TrLang::trans('admin.holidays_table_date') }}</th>
                                    <th>{{ TrLang::trans('admin.holidays_table_name') }}</th>
                                    <th style="width: 170px;">{{ TrLang::trans('admin.holidays_table_color') }}</th>
                                    <th style="width: 180px;">{{ TrLang::trans('admin.holidays_table_states') }}</th>
                                    <th style="width: 190px;">{{ TrLang::trans('admin.holidays_table_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($holidays as $holiday)
                                    @php $formId = 'holiday-edit-' . $holiday->id; @endphp
                                    <tr data-date="{{ $holiday->date->toDateString() }}" data-name="{{ $holiday->name }}">
                                        <td>
                                            <div class="tr-holidays-table__date">{{ $holiday->date->toDateString() }}</div>
                                            @if ($holiday->day_name)
                                                <div class="tr-holidays-table__meta">{{ $holiday->day_name }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <form id="{{ $formId }}" method="POST" action="{{ route('admin.treatment_reservations.holidays.update', ['holiday' => $holiday->id]) }}">
                                                @csrf
                                            </form>
                                            <input
                                                type="text"
                                                name="name"
                                                form="{{ $formId }}"
                                                class="form-control form-control-sm tr-holidays-table__name-input"
                                                value="{{ $holiday->name }}"
                                                required
                                            />
                                        </td>
                                        <td>
                                            <div class="tr-holidays-table__color-field">
                                                <span class="tr-holidays-table__color-swatch" style="background: {{ $holiday->color }}"></span>
                                                <input
                                                    type="color"
                                                    name="color"
                                                    form="{{ $formId }}"
                                                    class="form-control form-control-color"
                                                    value="{{ $holiday->color }}"
                                                    required
                                                />
                                            </div>
                                        </td>
                                        <td>
                                            <input
                                                type="text"
                                                name="state_codes"
                                                form="{{ $formId }}"
                                                class="form-control form-control-sm"
                                                value="{{ implode(',', $holiday->state_codes ?? []) }}"
                                            />
                                        </td>
                                        <td>
                                            <div class="tr-holidays-table__actions">
                                                <button type="submit" form="{{ $formId }}" class="btn btn-sm btn-primary">
                                                    {{ TrLang::trans('admin.holidays_save_changes') }}
                                                </button>

                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.treatment_reservations.holidays.delete', ['holiday' => $holiday->id]) }}"
                                                    onsubmit="return confirm(@json(TrLang::trans('admin.holidays_delete_confirm')))">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        {{ TrLang::trans('admin.holidays_delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            {{ TrLang::trans('admin.holidays_empty_for_year') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('globals')
    @vite('modules/TreatmentReservation/Resources/assets/admin/sass/main.scss')

    <script>
        (function () {
            const table = document.querySelector('.tr-holidays-table');
            if (!table) return;

            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            const searchEl = document.getElementById('tr-holidays-search');
            const sortEl = document.getElementById('tr-holidays-sort');
            const pageSizeEl = document.getElementById('tr-holidays-page-size');
            const prevBtn = document.getElementById('tr-holidays-prev');
            const nextBtn = document.getElementById('tr-holidays-next');
            const indicator = document.getElementById('tr-holidays-page-indicator');

            let page = 1;

            function parsePageSize() {
                const v = parseInt(pageSizeEl?.value || '10', 10);
                return Number.isFinite(v) && v > 0 ? v : 10;
            }

            function getQuery() {
                return (searchEl?.value || '').trim().toLowerCase();
            }

            function sortRows(list) {
                const sort = sortEl?.value || 'date_desc';

                const getDate = (tr) => tr.dataset.date || '';
                const getName = (tr) => (tr.dataset.name || '').toLowerCase();

                return list.sort((a, b) => {
                    if (sort === 'name_asc') return getName(a).localeCompare(getName(b));
                    if (sort === 'date_asc') return getDate(a).localeCompare(getDate(b));
                    // default date_desc
                    return getDate(b).localeCompare(getDate(a));
                });
            }

            function render() {
                const query = getQuery();
                const pageSize = parsePageSize();

                let filtered = rows;
                if (query) {
                    filtered = rows.filter((tr) => {
                        const date = (tr.dataset.date || '').toLowerCase();
                        const name = (tr.dataset.name || '').toLowerCase();
                        return date.includes(query) || name.includes(query);
                    });
                }

                filtered = sortRows(filtered);

                const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
                page = Math.min(page, totalPages);

                const start = (page - 1) * pageSize;
                const end = start + pageSize;
                const slice = filtered.slice(start, end);

                rows.forEach((tr) => (tr.style.display = 'none'));
                slice.forEach((tr) => (tr.style.display = ''));

                if (indicator) indicator.textContent = `${filtered.length ? page : 0}/${filtered.length ? totalPages : 0}`;

                if (prevBtn) prevBtn.disabled = page <= 1;
                if (nextBtn) nextBtn.disabled = page >= totalPages;
            }

            function resetPage() {
                page = 1;
                render();
            }

            searchEl?.addEventListener('input', resetPage);
            sortEl?.addEventListener('change', resetPage);
            pageSizeEl?.addEventListener('change', resetPage);
            prevBtn?.addEventListener('click', function () {
                page = Math.max(1, page - 1);
                render();
            });
            nextBtn?.addEventListener('click', function () {
                const pageSize = parsePageSize();
                const query = getQuery();
                const filteredCount = query
                    ? rows.filter((tr) => {
                        const date = (tr.dataset.date || '').toLowerCase();
                        const name = (tr.dataset.name || '').toLowerCase();
                        return date.includes(query) || name.includes(query);
                    }).length
                    : rows.length;

                const totalPages = Math.max(1, Math.ceil(filteredCount / pageSize));
                page = Math.min(totalPages, page + 1);
                render();
            });

            render();
        })();
    </script>
@endpush

