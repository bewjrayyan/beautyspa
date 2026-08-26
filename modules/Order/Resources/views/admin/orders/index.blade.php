@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('order::orders.orders'))

    <li class="active">{{ trans('order::orders.orders') }}</li>
@endcomponent

@section('content')
    <div class="box box-primary orders-index">
        <div class="box-header with-border orders-index__head">
            <h3 class="box-title">{{ trans('order::orders.orders') }}</h3>
            <div class="box-tools pull-right">
                @if (is_module_enabled('GoogleIntegration') && setting('google_sheets_enabled') && ($sheetsFailedCount > 0 || request()->boolean('google_sheets_failed')))
                    <button
                        type="button"
                        class="btn btn-default orders-index__toggle-sheets-failed"
                        id="orders-toggle-sheets-failed"
                        aria-pressed="false"
                        title="{{ trans('order::orders.sheets_failed_filter_help') }}"
                    >
                        <i class="fa fa-table" aria-hidden="true"></i>
                        <span class="orders-index__toggle-sheets-failed-label">
                            @if ($sheetsFailedCount > 0)
                                {{ trans('order::orders.show_sheets_failed_count', ['count' => $sheetsFailedCount]) }}
                            @else
                                {{ trans('order::orders.show_sheets_failed') }}
                            @endif
                        </span>
                    </button>
                @endif
                <button
                    type="button"
                    class="btn btn-default orders-index__toggle-archived"
                    id="orders-toggle-archived"
                    aria-pressed="false"
                >
                    <i class="fa fa-archive" aria-hidden="true"></i>
                    <span class="orders-index__toggle-archived-label">
                        @if ($archivedCount > 0)
                            {{ trans('order::orders.show_archived_count', ['count' => $archivedCount]) }}
                        @else
                            {{ trans('order::orders.show_archived') }}
                        @endif
                    </span>
                </button>
            </div>
        </div>

        @include('order::admin.orders.partials.payment_status_filters')

        <div class="box-body index-table" id="orders-table">
            @component('admin::components.table')
                @slot('thead')
                    <tr>
                        @hasAccess('admin.orders.destroy')
                            @include('admin::partials.table.select_all')
                        @endHasAccess

                        <th>{{ trans('admin::admin.table.id') }}</th>
                        <th>{{ trans('order::orders.table.customer_name') }}</th>
                        <th>{{ trans('order::orders.table.beautician') }}</th>
                        @if (is_module_enabled('SpaBranch'))
                            <th>{{ trans('order::orders.table.spa_branch') }}</th>
                        @endif
                        <th title="{{ trans('order::orders.order_status_help') }}">{{ trans('order::orders.table.order_status') }}</th>
                        <th title="{{ trans('order::orders.payment_status_help') }}">{{ trans('order::orders.table.payment_status') }}</th>
                        @if (is_module_enabled('TreatmentReservation'))
                            <th title="{{ trans('order::orders.treatment_status_help') }}">{{ trans('order::orders.table.treatment_status') }}</th>
                        @endif
                        <th>{{ trans('order::orders.table.total') }}</th>
                        <th data-sort>{{ trans('admin::admin.table.created') }}</th>
                        <th class="text-center">{{ trans('order::orders.table.actions') }}</th>
                    </tr>
                @endslot
            @endcomponent
        </div>
    </div>
@endsection

@push('styles')
    @vite(['modules/Order/Resources/assets/admin/sass/main.scss'])
@endpush

@push('scripts')
    <script>
        (function () {
            const config = {
                viewLabel: @json(trans('order::orders.table.view')),
                printLabel: @json(trans('order::orders.table.print')),
                receiptLabel: @json(trans('order::orders.table.print_receipt')),
                changeOrderStatusLabel: @json(trans('order::orders.table.change_order_status')),
                changeTreatmentStatusLabel: @json(trans('order::orders.table.change_treatment_status')),
                manageTreatmentsLabel: @json(trans('order::orders.table.manage_treatments')),
                deleteLabel: @json(trans('order::orders.table.delete')),
                forceDeleteLabel: @json(trans('order::orders.table.force_delete')),
                forceDeleteConfirmMessage: @json(trans('order::orders.force_delete_confirm')),
                statusUpdatedMessage: @json(trans('order::messages.status_updated')),
                treatmentStatusUpdatedMessage: @json(trans('order::messages.treatment_status_updated')),
                deletedMessage: @json(trans('order::messages.deleted')),
                forceDeletedMessage: @json(trans('order::messages.force_deleted')),
                errorMessage: @json(trans('core::messages.something_went_wrong')),
                statuses: @json(collect(\Modules\Order\Entities\Order::statuses())->mapWithKeys(fn ($s) => [$s => trans('order::statuses.' . $s)])->all()),
                treatmentStatuses: @json(
                    is_module_enabled('TreatmentReservation')
                        ? collect(\Modules\TreatmentReservation\Entities\TreatmentBooking::statuses())
                            ->mapWithKeys(fn ($status) => [
                                $status => $status === \Modules\TreatmentReservation\Entities\TreatmentBooking::STATUS_CANCELED
                                    ? trans('treatmentreservation::admin.crm.status_canceled')
                                    : trans('treatmentreservation::admin.kanban.' . $status),
                            ])
                            ->all()
                        : []
                ),
                hasTreatmentModule: @json(is_module_enabled('TreatmentReservation')),
                showArchivedLabel: @json(trans('order::orders.show_archived')),
                showArchivedCountLabel: @json(trans('order::orders.show_archived_count', ['count' => '__COUNT__'])),
                showActiveOrdersLabel: @json(trans('order::orders.show_active_orders')),
                showSheetsFailedLabel: @json(trans('order::orders.show_sheets_failed')),
                showSheetsFailedCountLabel: @json(trans('order::orders.show_sheets_failed_count', ['count' => '__COUNT__'])),
                showAllOrdersLabel: @json(trans('order::orders.show_all_orders')),
                bulkForceDeleteConfirm: @json(trans('order::orders.bulk_force_delete_confirm')),
                archivedCount: {{ (int) $archivedCount }},
                sheetsFailedCount: {{ (int) $sheetsFailedCount }},
                canBulkDelete: @json(auth()->user()->hasAccess('admin.orders.destroy')),
            };

            const urlParams = new URLSearchParams(window.location.search);
            let showArchived = urlParams.get('archived') === '1';
            let showSheetsFailed = urlParams.get('google_sheets_failed') === '1';
            let activePaymentStatus = urlParams.get('payment_status') || '';
            let activePaymentChannel = ['offline', 'online'].includes(urlParams.get('payment_channel') || '')
                ? (urlParams.get('payment_channel') || '')
                : '';
            let beauticianId = urlParams.get('beautician_id') || '';
            let dateFilter = urlParams.get('date') || '';
            let activeMonth = /^\d{4}-(0[1-9]|1[0-2])$/.test(urlParams.get('month') || '')
                ? (urlParams.get('month') || '')
                : '';
            let activeDateFrom = urlParams.get('date_from') || '';
            let activeDateTo = urlParams.get('date_to') || '';
            let activeSearch = urlParams.get('search') || '';
            let searchDebounceTimer = null;
            window.ordersIndexShowArchived = showArchived;

            function initOrdersIndex() {
                const $ = window.jQuery;

                if (!$ || !window.DataTable || !window.axios) {
                    setTimeout(initOrdersIndex, 50);

                    return;
                }

                const $ordersTable = $('#orders-table');
                const $ordersTableEl = $('#orders-table .table');
                const $toggleArchivedBtn = $('#orders-toggle-archived');
                const $toggleSheetsFailedBtn = $('#orders-toggle-sheets-failed');
                const $toggleArchivedLabel = $toggleArchivedBtn.find('.orders-index__toggle-archived-label');
                const $toggleSheetsFailedLabel = $toggleSheetsFailedBtn.find('.orders-index__toggle-sheets-failed-label');
                let $activeActionsMenu = null;
                let $activeActionsToggle = null;

                function archivedToggleLabel() {
                    if (showArchived) {
                        return config.showActiveOrdersLabel;
                    }

                    if (config.archivedCount > 0) {
                        return config.showArchivedCountLabel.replace('__COUNT__', String(config.archivedCount));
                    }

                    return config.showArchivedLabel;
                }

                function syncArchivedUi() {
                    window.ordersIndexShowArchived = showArchived;
                    $toggleArchivedBtn
                        .toggleClass('btn-primary', showArchived)
                        .toggleClass('btn-default', !showArchived)
                        .attr('aria-pressed', showArchived ? 'true' : 'false');
                    $toggleArchivedLabel.text(archivedToggleLabel());

                    if (showArchived) {
                        $ordersTable.addClass('orders-index--archived');
                    } else {
                        $ordersTable.removeClass('orders-index--archived');
                    }

                    updateOrdersFilterUrl();
                }

                function syncPaymentStatusUi() {
                    $('#orders-payment-filters .orders-index__chip').each(function () {
                        const $button = $(this);
                        const status = String($button.attr('data-payment-status') ?? '');
                        const isActive = status === activePaymentStatus;

                        $button
                            .toggleClass('is-active', isActive)
                            .attr('aria-pressed', isActive ? 'true' : 'false');
                    });
                }

                function syncPaymentChannelUi() {
                    $('#orders-payment-channel-filters .orders-index__chip').each(function () {
                        const $button = $(this);
                        const channel = String($button.attr('data-payment-channel') ?? '');
                        const isActive = channel === activePaymentChannel;

                        $button
                            .toggleClass('is-active', isActive)
                            .attr('aria-pressed', isActive ? 'true' : 'false');
                    });
                }

                function bindPaymentStatusFilters() {
                    const $filters = $('#orders-payment-filters');
                    const $channelFilters = $('#orders-payment-channel-filters');

                    syncPaymentStatusUi();
                    syncPaymentChannelUi();

                    if ($filters.length) {
                        $filters.off('click.paymentStatus').on('click.paymentStatus', '.orders-index__chip', function () {
                            const nextStatus = String($(this).attr('data-payment-status') ?? '');

                            if (nextStatus === '' || nextStatus === activePaymentStatus) {
                                activePaymentStatus = '';
                            } else {
                                activePaymentStatus = nextStatus;
                            }

                            syncPaymentStatusUi();
                            updateOrdersFilterUrl();
                            closeOrderActionsMenu();
                            window.DataTable.reload('#orders-table .table', null, true);
                        });
                    }

                    if ($channelFilters.length) {
                        $channelFilters.off('click.paymentChannel').on('click.paymentChannel', '.orders-index__chip', function () {
                            const nextChannel = String($(this).attr('data-payment-channel') ?? '');

                            if (nextChannel === '' || nextChannel === activePaymentChannel) {
                                activePaymentChannel = '';
                            } else {
                                activePaymentChannel = nextChannel;
                            }

                            syncPaymentChannelUi();
                            updateOrdersFilterUrl();
                            closeOrderActionsMenu();
                            window.DataTable.reload('#orders-table .table', null, true);
                        });
                    }
                }

                function updateOrdersFilterUrl() {
                    const url = new URL(window.location.href);

                    if (showArchived) {
                        url.searchParams.set('archived', '1');
                    } else {
                        url.searchParams.delete('archived');
                    }

                    if (showSheetsFailed) {
                        url.searchParams.set('google_sheets_failed', '1');
                    } else {
                        url.searchParams.delete('google_sheets_failed');
                    }

                    if (activePaymentStatus) {
                        url.searchParams.set('payment_status', activePaymentStatus);
                    } else {
                        url.searchParams.delete('payment_status');
                    }

                    if (activePaymentChannel) {
                        url.searchParams.set('payment_channel', activePaymentChannel);
                    } else {
                        url.searchParams.delete('payment_channel');
                    }

                    if (activeMonth) {
                        url.searchParams.set('month', activeMonth);
                    } else {
                        url.searchParams.delete('month');
                    }

                    if (activeDateFrom) {
                        url.searchParams.set('date_from', activeDateFrom);
                    } else {
                        url.searchParams.delete('date_from');
                    }

                    if (activeDateTo) {
                        url.searchParams.set('date_to', activeDateTo);
                    } else {
                        url.searchParams.delete('date_to');
                    }

                    if (activeSearch) {
                        url.searchParams.set('search', activeSearch);
                    } else {
                        url.searchParams.delete('search');
                    }

                    window.history.replaceState({}, '', url);
                }

                function syncSheetsFailedUi() {
                    if (!$toggleSheetsFailedBtn.length) {
                        return;
                    }

                    $toggleSheetsFailedBtn
                        .toggleClass('btn-primary', showSheetsFailed)
                        .toggleClass('btn-default', !showSheetsFailed)
                        .attr('aria-pressed', showSheetsFailed ? 'true' : 'false');
                    $toggleSheetsFailedLabel.text(
                        showSheetsFailed ? config.showAllOrdersLabel : sheetsFailedToggleLabel()
                    );

                    updateOrdersFilterUrl();
                }

                syncArchivedUi();
                syncSheetsFailedUi();

                function monthBounds(monthValue) {
                    if (!/^\d{4}-(0[1-9]|1[0-2])$/.test(monthValue || '')) {
                        return null;
                    }

                    const [year, month] = monthValue.split('-').map(Number);
                    const lastDay = new Date(year, month, 0).getDate();
                    const pad = (n) => String(n).padStart(2, '0');

                    return {
                        from: year + '-' + pad(month) + '-01',
                        to: year + '-' + pad(month) + '-' + pad(lastDay),
                    };
                }

                function toYmd(date) {
                    if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
                        return '';
                    }

                    const pad = (n) => String(n).padStart(2, '0');

                    return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
                }

                function getDateRangePicker() {
                    const el = document.getElementById('orders-filter-date-range');

                    if (!el) {
                        return null;
                    }

                    if (!el._flatpickr && window.admin && typeof window.admin.dateTimePicker === 'function') {
                        window.admin.dateTimePicker(el);
                    }

                    return el._flatpickr || null;
                }

                function readDateRangeFromPicker() {
                    const picker = getDateRangePicker();

                    if (picker && picker.selectedDates.length) {
                        activeDateFrom = toYmd(picker.selectedDates[0]);
                        activeDateTo = picker.selectedDates.length > 1
                            ? toYmd(picker.selectedDates[1])
                            : activeDateFrom;

                        return;
                    }

                    const raw = String($('#orders-filter-date-range').val() || '').trim();

                    if (!raw) {
                        activeDateFrom = '';
                        activeDateTo = '';

                        return;
                    }

                    const parts = raw.split(/\s+to\s+/i).map((part) => part.trim()).filter(Boolean);

                    activeDateFrom = parts[0] || '';
                    activeDateTo = parts[1] || parts[0] || '';
                }

                function setDateRangePicker(from, to) {
                    const picker = getDateRangePicker();

                    if (!picker) {
                        const $range = $('#orders-filter-date-range');

                        if (!$range.length) {
                            return;
                        }

                        if (from && to) {
                            $range.val(from + ' to ' + to);
                        } else if (from) {
                            $range.val(from);
                        } else {
                            $range.val('');
                        }

                        return;
                    }

                    if (from && to) {
                        picker.setDate([from, to], false);
                    } else if (from) {
                        picker.setDate([from], false);
                    } else {
                        picker.clear();
                    }
                }

                function readPeriodInputs() {
                    const $month = $('#orders-filter-month');
                    const $search = $('#orders-filter-search');

                    activeMonth = String($month.val() || '');
                    readDateRangeFromPicker();
                    activeSearch = String($search.val() || '').trim();

                    // Custom range supersedes legacy "today" deep-link.
                    if (activeMonth || activeDateFrom || activeDateTo) {
                        dateFilter = '';
                    }
                }

                function syncPeriodInputs() {
                    const $month = $('#orders-filter-month');
                    const $search = $('#orders-filter-search');

                    if ($month.length) {
                        $month.val(activeMonth);
                    }

                    setDateRangePicker(activeDateFrom, activeDateTo);

                    if ($search.length) {
                        $search.val(activeSearch);
                    }
                }

                function applyPeriodFilters(reload) {
                    readPeriodInputs();
                    updateOrdersFilterUrl();
                    closeOrderActionsMenu();

                    if (reload !== false) {
                        window.DataTable.reload('#orders-table .table', null, true);
                    }
                }

                function clearPeriodFilters() {
                    activeMonth = '';
                    activeDateFrom = '';
                    activeDateTo = '';
                    activeSearch = '';
                    dateFilter = '';
                    syncPeriodInputs();
                    updateOrdersFilterUrl();
                    closeOrderActionsMenu();
                    window.DataTable.reload('#orders-table .table', null, true);
                }

                function bindPeriodFilters() {
                    const $month = $('#orders-filter-month');
                    const $range = $('#orders-filter-date-range');
                    const $search = $('#orders-filter-search');
                    const $apply = $('#orders-filter-apply');
                    const $clear = $('#orders-filter-clear');

                    if (!$month.length && !$search.length && !$range.length) {
                        return;
                    }

                    getDateRangePicker();
                    syncPeriodInputs();

                    $month.off('change.ordersPeriod').on('change.ordersPeriod', function () {
                        const bounds = monthBounds(String($(this).val() || ''));

                        if (bounds) {
                            activeMonth = String($(this).val() || '');
                            activeDateFrom = bounds.from;
                            activeDateTo = bounds.to;
                            setDateRangePicker(activeDateFrom, activeDateTo);
                        } else {
                            activeMonth = '';
                            activeDateFrom = '';
                            activeDateTo = '';
                            setDateRangePicker('', '');
                        }

                        applyPeriodFilters(true);
                    });

                    $range.off('change.ordersPeriod').on('change.ordersPeriod', function () {
                        // Manual calendar range clears month preset.
                        activeMonth = '';
                        $month.val('');
                        applyPeriodFilters(true);
                    });

                    $apply.off('click.ordersPeriod').on('click.ordersPeriod', function () {
                        applyPeriodFilters(true);
                    });

                    $clear.off('click.ordersPeriod').on('click.ordersPeriod', function () {
                        clearPeriodFilters();
                    });

                    $search.off('keydown.ordersPeriod input.ordersPeriod').on('keydown.ordersPeriod', function (event) {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            applyPeriodFilters(true);
                        }
                    }).on('input.ordersPeriod', function () {
                        window.clearTimeout(searchDebounceTimer);
                        searchDebounceTimer = window.setTimeout(function () {
                            applyPeriodFilters(true);
                        }, 400);
                    });
                }


                bindPaymentStatusFilters();
                bindPeriodFilters();

                function sheetsFailedToggleLabel() {
                    if (config.sheetsFailedCount > 0) {
                        return config.showSheetsFailedCountLabel.replace('__COUNT__', String(config.sheetsFailedCount));
                    }

                    return config.showSheetsFailedLabel;
                }

                function bindOrdersBulkDelete(dtInstance) {
                    if (!config.canBulkDelete) {
                        return;
                    }

                    const $table = dtInstance.element;
                    const $deleteBtn = $table.closest('.dt-container').find('.btn-delete');

                    $deleteBtn.off('click.ordersBulkDelete').on('click.ordersBulkDelete', function () {
                        const checked = $table.find('.select-row:checked');

                        if (!checked.length) {
                            return;
                        }

                        const ids = window.DataTable.getRowIds(checked);
                        const confirmationModal = $('#confirmation-modal');
                        const $modalMessage = confirmationModal.find('.default-message');
                        const $deleteButton = confirmationModal.find('.btn.delete');
                        const isPermanent = showArchived;

                        if (!$modalMessage.data('default-message')) {
                            $modalMessage.data('default-message', $modalMessage.text());
                        }

                        if (!$deleteButton.data('default-label')) {
                            $deleteButton.data('default-label', $deleteButton.text());
                        }

                        $modalMessage.text(
                            isPermanent
                                ? config.bulkForceDeleteConfirm.replace(':count', String(ids.length))
                                : $modalMessage.data('default-message')
                        );
                        $deleteButton.text(
                            isPermanent ? config.forceDeleteLabel : $deleteButton.data('default-label')
                        );

                        confirmationModal
                            .modal('show')
                            .find('form')
                            .off('submit')
                            .on('submit', function (event) {
                                event.preventDefault();

                                confirmationModal.modal('hide');

                                const deleteUrl = isPermanent
                                    ? window.AestheticCart.baseUrl + '/admin/orders/' + ids.join(',') + '/force'
                                    : window.AestheticCart.baseUrl + '/admin/orders/' + ids.join(',');

                                axios
                                    .delete(deleteUrl)
                                    .then(function () {
                                        window.DataTable.setSelectedIds('#orders-table .table', []);
                                        window.DataTable.reload('#orders-table .table');

                                        if (typeof window.success === 'function') {
                                            window.success(
                                                isPermanent ? config.forceDeletedMessage : config.deletedMessage
                                            );
                                        }

                                        if (isPermanent && config.archivedCount > 0) {
                                            config.archivedCount = Math.max(0, config.archivedCount - ids.length);
                                            syncArchivedUi();
                                        }
                                    })
                                    .catch(function (err) {
                                        if (typeof window.error === 'function') {
                                            window.error(
                                                err.response && err.response.data && err.response.data.message
                                                    ? err.response.data.message
                                                    : config.errorMessage
                                            );
                                        }
                                    })
                                    .finally(function () {
                                        $modalMessage.text($modalMessage.data('default-message'));
                                        $deleteButton.text($deleteButton.data('default-label'));
                                    });
                            });
                    });
                }

                function closeOrderActionsMenu() {
                    if ($activeActionsMenu) {
                        $activeActionsMenu.remove();
                        $activeActionsMenu = null;
                    }

                    if ($activeActionsToggle) {
                        $activeActionsToggle
                            .closest('.order-table-actions')
                            .removeClass('open')
                            .attr('aria-expanded', 'false');
                        $activeActionsToggle = null;
                    }

                    $ordersTable.find('.order-table-actions.open').removeClass('open');
                }

                function toggleAttr($toggle, name) {
                    return $toggle.attr('data-' + name) || '';
                }

                function buildStatusGroup(headerLabel, url, currentValue, statuses, linkClass, dataAttr) {
                    if (!url || !statuses) {
                        return '';
                    }

                    let html = '<li class="divider"></li>'
                        + '<li class="dropdown-header">' + headerLabel + '</li>';

                    Object.keys(statuses).forEach(function (statusKey) {
                        const isActive = statusKey === currentValue;
                        const label = statuses[statusKey];

                        html += '<li' + (isActive ? ' class="active"' : '') + '>'
                            + '<a href="#" class="' + linkClass + '" data-url="' + url + '" ' + dataAttr + '="' + statusKey + '">'
                            + label
                            + '</a></li>';
                    });

                    return html;
                }

                function buildStatusItems($toggle) {
                    let html = '';

                    html += buildStatusGroup(
                        config.changeOrderStatusLabel,
                        toggleAttr($toggle, 'status-url'),
                        toggleAttr($toggle, 'current-status'),
                        config.statuses,
                        'set-order-status',
                        'data-status'
                    );


                    if (config.hasTreatmentModule) {
                        const treatmentUrl = toggleAttr($toggle, 'treatment-status-url');
                        const manageUrl = toggleAttr($toggle, 'treatment-manage-url');

                        if (treatmentUrl) {
                            html += buildStatusGroup(
                                config.changeTreatmentStatusLabel,
                                treatmentUrl,
                                toggleAttr($toggle, 'current-treatment-status'),
                                config.treatmentStatuses,
                                'set-treatment-status',
                                'data-treatment-status'
                            );
                        } else if (manageUrl) {
                            html += '<li class="divider"></li>'
                                + '<li class="dropdown-header">' + config.changeTreatmentStatusLabel + '</li>'
                                + '<li><a href="' + manageUrl + '">' + config.manageTreatmentsLabel + '</a></li>';
                        }
                    }

                    return html;
                }

                function positionOrderActionsMenu($menu, toggleEl) {
                    const rect = toggleEl.getBoundingClientRect();
                    const gap = 4;
                    const viewportPadding = 8;

                    // Measure off-screen so the menu never flashes at (0, 0) before placement.
                    $menu.css({
                        position: 'fixed',
                        display: 'block',
                        visibility: 'hidden',
                        opacity: 0,
                        pointerEvents: 'none',
                        right: 'auto',
                        left: '-10000px',
                        top: '0',
                        bottom: 'auto',
                        transform: 'none',
                        margin: 0,
                        transition: 'none',
                        zIndex: 10000,
                    });

                    const menuWidth = $menu.outerWidth();
                    const menuHeight = $menu.outerHeight();
                    const maxLeft = window.innerWidth - menuWidth - viewportPadding;
                    const maxTop = window.innerHeight - menuHeight - viewportPadding;

                    let top = rect.bottom + gap;
                    let left = rect.right - menuWidth;

                    if (top > maxTop) {
                        top = rect.top - menuHeight - gap;
                    }

                    if (left < viewportPadding) {
                        left = viewportPadding;
                    }

                    if (left > maxLeft) {
                        left = maxLeft;
                    }

                    if (top < viewportPadding) {
                        top = viewportPadding;
                    }

                    $menu.css({
                        top: top + 'px',
                        left: left + 'px',
                        visibility: 'visible',
                        opacity: 1,
                        pointerEvents: 'auto',
                    });
                }

                function openOrderActionsMenu($toggle) {
                    closeOrderActionsMenu();

                    let menuHtml = '<ul class="dropdown-menu order-table-actions-portal">';

                    const showUrl = toggleAttr($toggle, 'show-url');

                    if (showUrl) {
                        menuHtml += '<li><a href="' + showUrl + '">' + config.viewLabel + '</a></li>';

                        const printUrl = toggleAttr($toggle, 'print-url');

                        if (printUrl) {
                            menuHtml += '<li><a href="' + printUrl + '" target="_blank" rel="noopener noreferrer">'
                                + config.printLabel + '</a></li>';
                        }

                        const receiptUrl = toggleAttr($toggle, 'receipt-url');

                        if (receiptUrl) {
                            menuHtml += '<li><a href="' + receiptUrl + '" target="_blank" rel="noopener noreferrer">'
                                + config.receiptLabel + '</a></li>';
                        }
                    }

                    menuHtml += buildStatusItems($toggle);

                    const orderId = toggleAttr($toggle, 'order-id');

                    if (orderId) {
                        menuHtml += '<li class="divider"></li>';

                        if (showArchived) {
                            menuHtml += '<li><a href="#" class="text-danger delete-order-row delete-order-row--permanent" data-id="' + orderId + '" data-permanent="1">'
                                + config.forceDeleteLabel
                                + '</a></li>';
                        } else {
                            menuHtml += '<li><a href="#" class="text-danger delete-order-row" data-id="' + orderId + '">'
                                + config.deleteLabel
                                + '</a></li>';
                        }
                    }

                    menuHtml += '</ul>';

                    $activeActionsMenu = $(menuHtml).appendTo('body');
                    positionOrderActionsMenu($activeActionsMenu, $toggle[0]);

                    $toggle.closest('.order-table-actions').addClass('open');
                    $toggle.attr('aria-expanded', 'true');
                    $activeActionsToggle = $toggle;
                }

                DataTable.set('#orders-table .table', {
                    routePrefix: 'orders',
                    routes: {
                        table: 'table',
                        show: 'show',
                        destroy: 'destroy',
                    },
                });

                const orderColumns = [];

                if (config.canBulkDelete) {
                    orderColumns.push({
                        data: 'checkbox',
                        orderable: false,
                        searchable: false,
                        width: '3%',
                    });
                }

                orderColumns.push(
                    { data: 'id', width: '5%' },
                    { data: 'customer_name', orderable: false, searchable: false },
                    { data: 'beautician_name', orderable: false, searchable: false },
                    @if (is_module_enabled('SpaBranch'))
                    { data: 'spa_branch', orderable: false, searchable: false },
                    @endif
                    { data: 'status' },
                    { data: 'payment_status', orderable: false, searchable: false },
                    @if (is_module_enabled('TreatmentReservation'))
                    { data: 'treatment_status', orderable: false, searchable: false },
                    @endif
                    { data: 'total' },
                    { data: 'created', name: 'created_at' },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'table-row-actions text-center',
                        width: '8%',
                    }
                );

                new DataTable('#orders-table .table', {
                    ajax: {
                        url: window.AestheticCart.baseUrl + '/admin/orders/index/table',
                        data: function (data) {
                            data.table = true;
                            data.archived = showArchived ? 1 : 0;
                            data.google_sheets_failed = showSheetsFailed ? 1 : 0;

                            if (activePaymentStatus) {
                                data.payment_status = activePaymentStatus;
                            }

                            if (activePaymentChannel) {
                                data.payment_channel = activePaymentChannel;
                            }

                            if (beauticianId) {
                                data.beautician_id = beauticianId;
                            }

                            if (dateFilter) {
                                data.date = dateFilter;
                            }

                            if (activeMonth) {
                                data.month = activeMonth;
                            }

                            if (activeDateFrom) {
                                data.date_from = activeDateFrom;
                            }

                            if (activeDateTo) {
                                data.date_to = activeDateTo;
                            }

                            if (activeSearch) {
                                data.search = activeSearch;
                            }
                        },
                    },
                    columns: orderColumns,
                }, function () {
                    bindOrdersBulkDelete(this);
                });

                $toggleArchivedBtn.on('click', function () {
                    showArchived = !showArchived;
                    syncArchivedUi();
                    closeOrderActionsMenu();
                    window.DataTable.reload('#orders-table .table', null, true);
                });

                $toggleSheetsFailedBtn.on('click', function () {
                    showSheetsFailed = !showSheetsFailed;
                    syncSheetsFailedUi();
                    closeOrderActionsMenu();
                    window.DataTable.reload('#orders-table .table', null, true);
                });

                $ordersTableEl.on('draw.dt', closeOrderActionsMenu);

                $ordersTable.on('scroll', '.table-responsive', closeOrderActionsMenu);
                $(window).on('scroll.ordersTableActions resize.ordersTableActions', closeOrderActionsMenu);

                $ordersTable.on('click', '.btn-table-actions-toggle', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const $toggle = $(this);

                    if ($activeActionsToggle && $activeActionsToggle[0] === $toggle[0]) {
                        closeOrderActionsMenu();

                        return;
                    }

                    openOrderActionsMenu($toggle);
                });

                $(document).on('mousedown.ordersTableActions', function (e) {
                    if (
                        $activeActionsMenu
                        && !$(e.target).closest('.order-table-actions-portal, .btn-table-actions-toggle').length
                    ) {
                        closeOrderActionsMenu();
                    }
                });

                function orderStatusBadgeClass(status) {
                    const map = {
                        pending: 'badge-info',
                        processing: 'badge-primary',
                        completed: 'badge-success',
                        canceled: 'badge-danger',
                    };

                    return map[status] || 'badge-info';
                }

                function paymentStatusBadgeClass(status) {
                    const map = {
                        pending: 'badge-warning',
                        processing: 'badge-info',
                        paid: 'badge-success',
                        canceled: 'badge-danger',
                        refunded: 'badge-danger',
                    };

                    return map[status] || 'badge-secondary';
                }

                function treatmentStatusBadgeClass(status) {
                    const map = {
                        pending: 'badge-warning',
                        in_progress: 'badge-info',
                        completed: 'badge-success',
                        canceled: 'badge-danger',
                    };

                    return map[status] || 'badge-secondary';
                }

                function patchOrderRowStatus($toggle, payload, label) {
                    if (!$toggle || !$toggle.length) {
                        window.DataTable.reload('#orders-table .table');
                        return;
                    }

                    const $row = $toggle.closest('tr');

                    if (!$row.length) {
                        window.DataTable.reload('#orders-table .table');
                        return;
                    }

                    if (payload.status) {
                        $toggle.attr('data-current-status', payload.status);
                        const $badge = $row.find('[data-order-status-badge]');

                        if ($badge.length) {
                            $badge
                                .attr('class', 'badge ' + orderStatusBadgeClass(payload.status))
                                .attr('data-status', payload.status)
                                .text(label || (config.statuses[payload.status] || payload.status));
                        } else {
                            window.DataTable.reload('#orders-table .table');
                        }
                    }

                    if (payload.treatment_status) {
                        $toggle.attr('data-current-treatment-status', payload.treatment_status);
                        const $badge = $row.find('[data-treatment-status-badge]');

                        if ($badge.length) {
                            $badge
                                .attr('class', 'badge ' + treatmentStatusBadgeClass(payload.treatment_status))
                                .attr('data-status', payload.treatment_status)
                                .text(label || (config.treatmentStatuses[payload.treatment_status] || payload.treatment_status));
                        } else {
                            // Multi-appointment rows have no single badge — soft reload.
                            window.DataTable.reload('#orders-table .table');
                        }
                    }
                }

                function putOrderStatusUpdate($link, payload, fallbackMessage) {
                    if ($link.parent().hasClass('active')) {
                        closeOrderActionsMenu();
                        return;
                    }

                    const url = $link.data('url');
                    const $toggle = $activeActionsToggle;
                    const statusKey = payload.status || payload.treatment_status || payload.payment_status || '';
                    const label = $link.text().trim();

                    closeOrderActionsMenu();
                    $link.addClass('disabled');

                    axios
                        .put(url, payload)
                        .then(function (response) {
                            patchOrderRowStatus($toggle, payload, label);

                            if (typeof window.success === 'function') {
                                window.success(
                                    typeof response.data === 'string'
                                        ? response.data
                                        : fallbackMessage
                                );
                            }
                        })
                        .catch(function (err) {
                            if (typeof window.error === 'function') {
                                window.error(
                                    err.response && err.response.data && err.response.data.message
                                        ? err.response.data.message
                                        : config.errorMessage
                                );
                            }
                        })
                        .finally(function () {
                            $link.removeClass('disabled');
                        });
                }

                $(document).on('click', '.order-table-actions-portal .set-order-status', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    putOrderStatusUpdate($(this), { status: $(this).attr('data-status') }, config.statusUpdatedMessage);
                });

                $(document).on('click', '.order-table-actions-portal .set-treatment-status', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    putOrderStatusUpdate(
                        $(this),
                        { treatment_status: $(this).attr('data-treatment-status') },
                        config.treatmentStatusUpdatedMessage
                    );
                });

                $(document).on('click', '.order-table-actions-portal .delete-order-row', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const $link = $(this);
                    const id = $link.data('id');
                    const isPermanent = $link.data('permanent') === 1 || $link.data('permanent') === '1';
                    const confirmationModal = $('#confirmation-modal');
                    const $modalMessage = confirmationModal.find('.default-message');
                    const $deleteButton = confirmationModal.find('.btn.delete');

                    if (!$modalMessage.data('default-message')) {
                        $modalMessage.data('default-message', $modalMessage.text());
                    }

                    if (!$deleteButton.data('default-label')) {
                        $deleteButton.data('default-label', $deleteButton.text());
                    }

                    closeOrderActionsMenu();

                    $modalMessage.text(
                        isPermanent
                            ? config.forceDeleteConfirmMessage
                            : $modalMessage.data('default-message')
                    );
                    $deleteButton.text(
                        isPermanent ? config.forceDeleteLabel : $deleteButton.data('default-label')
                    );

                    confirmationModal
                        .modal('show')
                        .find('form')
                        .off('submit')
                        .on('submit', function (event) {
                            event.preventDefault();

                            confirmationModal.modal('hide');

                            const deleteUrl = isPermanent
                                ? window.AestheticCart.baseUrl + '/admin/orders/' + id + '/force'
                                : window.AestheticCart.baseUrl + '/admin/orders/' + id;

                            axios
                                .delete(deleteUrl)
                                .then(function () {
                                    window.DataTable.reload('#orders-table .table');

                                    if (typeof window.success === 'function') {
                                        window.success(
                                            isPermanent ? config.forceDeletedMessage : config.deletedMessage
                                        );
                                    }

                                    if (isPermanent && config.archivedCount > 0) {
                                        config.archivedCount -= 1;
                                        syncArchivedUi();
                                    }
                                })
                                .catch(function (err) {
                                    if (typeof window.error === 'function') {
                                        window.error(
                                            err.response && err.response.data && err.response.data.message
                                                ? err.response.data.message
                                                : config.errorMessage
                                        );
                                    }
                                })
                                .finally(function () {
                                    $modalMessage.text($modalMessage.data('default-message'));
                                    $deleteButton.text($deleteButton.data('default-label'));
                                });
                        });
                });
            }

            initOrdersIndex();
        })();
    </script>
@endpush
