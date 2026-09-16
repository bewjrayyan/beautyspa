@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;

    $crmDateFilter = $crmDateFilter ?? ($filters['date_filter'] ?? 'today');
    $crmPickerDate = $crmPickerDate ?? (
        ($crmDateFilter === 'custom' && ! empty($filters['filter_date']))
            ? $filters['filter_date']
            : match ($crmDateFilter) {
                'tomorrow' => now()->addDay()->toDateString(),
                'yesterday' => now()->subDay()->toDateString(),
                'today' => now()->toDateString(),
                default => '',
            }
    );
    $portalFilterContext = $portalFilterContext ?? ['locked' => false];
    $crmRoutes = $crmRoutes ?? [];
@endphp

<div class="tr-crm-toolbar">
    <form class="tr-crm-toolbar__filters-form" method="get" action="{{ $crmRoutes['formAction'] ?? '' }}" id="tr-crm-header-form">
        <input type="hidden" name="date_filter" id="tr-crm-date-filter" value="{{ $crmDateFilter }}">
        <input type="hidden" name="filter_date" id="tr-crm-filter-date" value="{{ $filters['filter_date'] ?? '' }}">
        <input type="hidden" name="filter_date_to" id="tr-crm-filter-date-to" value="{{ $filters['filter_date_to'] ?? '' }}">
        <input type="hidden" name="treatment_category_id" id="tr-crm-hidden-category" value="{{ $filters['treatment_category_id'] ?? '' }}">
        <input type="hidden" name="beautician_id" value="">

        @if (! empty($portalFilterContext['locked']))
            <div class="tr-crm-toolbar__context tr-crm-toolbar__context--beautician">
                <span class="tr-crm-toolbar__field-icon" aria-hidden="true">
                    <i class="fa fa-user"></i>
                </span>
                <span class="tr-crm-toolbar__context-chip">
                    @if (! empty($portalFilterContext['beautician_avatar']))
                        <img
                            src="{{ $portalFilterContext['beautician_avatar'] }}"
                            alt=""
                            class="tr-crm-toolbar__context-avatar"
                        >
                    @else
                        <span
                            class="tr-crm-toolbar__context-avatar tr-crm-toolbar__context-avatar--initial"
                            style="background-color: {{ $portalFilterContext['beautician_color'] ?? '#6366f1' }}"
                        >{{ $portalFilterContext['beautician_initial'] ?? '?' }}</span>
                    @endif
                    <span class="tr-crm-toolbar__context-label">{{ $portalFilterContext['beautician_name'] ?? ($beautician->name ?? '') }}</span>
                </span>
            </div>
            <span class="tr-crm-toolbar__divider" aria-hidden="true"></span>
        @endif

        @if (! empty($portalFilterContext['branch_locked']) && ! empty($portalFilterContext['branch_name']))
            <input type="hidden" name="spa_branch_id" value="{{ $filters['spa_branch_id'] ?? '' }}">
            <div class="tr-crm-toolbar__context tr-crm-toolbar__context--branch">
                <span class="tr-crm-toolbar__field-icon" aria-hidden="true">
                    <i class="fa fa-map-marker"></i>
                </span>
                <span class="tr-crm-toolbar__context-chip tr-crm-toolbar__context-chip--branch">
                    <span class="tr-crm-toolbar__context-label">{{ $portalFilterContext['branch_name'] }}</span>
                </span>
            </div>
            <span class="tr-crm-toolbar__divider" aria-hidden="true"></span>
        @elseif (! empty($portalFilterContext['branch_picker']) && ($spaBranches ?? collect())->isNotEmpty())
            <div class="tr-crm-toolbar__field tr-crm-toolbar__field--branch">
                <span class="tr-crm-toolbar__field-icon" aria-hidden="true">
                    <i class="fa fa-map-marker"></i>
                </span>
                <label class="sr-only" for="tr-crm-filter-branch">{{ TrLang::trans('admin.filters.spa_branch') }}</label>
                <select class="tr-crm-toolbar__select" id="tr-crm-filter-branch" name="spa_branch_id" onchange="this.form.requestSubmit()">
                    <option value="">{{ TrLang::trans('admin.portal.filter_my_branches') }}</option>
                    @foreach ($spaBranches as $branchId => $branchName)
                        <option value="{{ $branchId }}" @selected(($filters['spa_branch_id'] ?? null) == $branchId)>
                            {{ $branchName }}
                        </option>
                    @endforeach
                </select>
            </div>
            <span class="tr-crm-toolbar__divider" aria-hidden="true"></span>
        @elseif (empty($portalFilterContext['locked']) && ($spaBranches ?? collect())->isNotEmpty())
            <div class="tr-crm-toolbar__field tr-crm-toolbar__field--branch">
                <span class="tr-crm-toolbar__field-icon" aria-hidden="true">
                    <i class="fa fa-map-marker"></i>
                </span>
                <label class="sr-only" for="tr-crm-filter-branch">{{ TrLang::trans('admin.filters.spa_branch') }}</label>
                <select class="tr-crm-toolbar__select" id="tr-crm-filter-branch" name="spa_branch_id" onchange="this.form.requestSubmit()">
                    <option value="">{{ TrLang::trans('admin.filters.all_branches') }}</option>
                    @foreach ($spaBranches as $branchId => $branchName)
                        <option value="{{ $branchId }}" @selected(($filters['spa_branch_id'] ?? null) == $branchId)>
                            {{ $branchName }}
                        </option>
                    @endforeach
                </select>
            </div>
            <span class="tr-crm-toolbar__divider" aria-hidden="true"></span>
        @endif

        @if (($categories ?? collect())->isNotEmpty())
            <div class="tr-crm-toolbar__field">
                <span class="tr-crm-toolbar__field-icon" aria-hidden="true">
                    <i class="fa fa-tags"></i>
                </span>
                <label class="sr-only" for="tr-crm-filter-category">{{ TrLang::trans('admin.filters.category') }}</label>
                <select class="tr-crm-toolbar__select" id="tr-crm-filter-category" name="treatment_category_id" onchange="this.form.requestSubmit()">
                    <option value="">{{ TrLang::trans('admin.filters.all_categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['treatment_category_id'] ?? null) == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <span class="tr-crm-toolbar__divider" aria-hidden="true"></span>
        @endif

        <div class="tr-crm-toolbar__field tr-crm-toolbar__field--dates">
            <span class="tr-crm-toolbar__field-icon" aria-hidden="true">
                <i class="fa fa-calendar-o"></i>
            </span>
            <div class="tr-crm-toolbar__dates" role="group" aria-label="{{ TrLang::trans('admin.crm.date_filter_aria') }}">
                @foreach (['all' => 'date_all', 'today' => 'date_today', 'tomorrow' => 'date_tomorrow'] as $value => $labelKey)
                    <button
                        type="button"
                        class="tr-crm-toolbar__date-pill{{ $crmDateFilter === $value ? ' is-active' : '' }}"
                        data-date-filter="{{ $value }}"
                    >
                        {{ TrLang::trans('admin.crm.' . $labelKey) }}
                    </button>
                @endforeach

                <label class="tr-crm-toolbar__date-picker{{ $crmDateFilter === 'custom' ? ' is-active' : '' }}">
                    <i class="fa fa-calendar" aria-hidden="true"></i>
                    <input
                        type="text"
                        id="tr-crm-date-picker"
                        class="tr-crm-toolbar__date-input tr-crm-toolbar__date-input--range"
                        value=""
                        data-date-from="{{ $filters['filter_date'] ?? '' }}"
                        data-date-to="{{ $filters['filter_date_to'] ?? '' }}"
                        placeholder="{{ TrLang::trans('admin.crm.date_pick_placeholder') }}"
                        autocomplete="off"
                        aria-label="{{ TrLang::trans('admin.crm.date_pick_aria') }}"
                        readonly
                    >
                </label>
            </div>
        </div>
    </form>

</div>
