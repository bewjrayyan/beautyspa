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

        <div
            id="orders-archived-notice"
            class="orders-index__archived-notice alert alert-warning"
            role="status"
            hidden
        >
            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
            <span>{{ trans('order::orders.archived_index_notice') }}</span>
        </div>

        <div class="box-body index-table" id="orders-table">
            @component('admin::components.table')
                @slot('thead')
                    <tr>
                        <th>{{ trans('admin::admin.table.id') }}</th>
                        <th>{{ trans('order::orders.table.customer_name') }}</th>
                        <th>{{ trans('order::orders.table.customer_email') }}</th>
                        <th>{{ trans('admin::admin.table.status') }}</th>
                        <th>{{ trans('order::orders.table.payment_status') }}</th>
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
                changeStatusLabel: @json(trans('order::orders.table.change_status')),
                deleteLabel: @json(trans('order::orders.table.delete')),
                forceDeleteLabel: @json(trans('order::orders.table.force_delete')),
                forceDeleteConfirmMessage: @json(trans('order::orders.force_delete_confirm')),
                statusUpdatedMessage: @json(trans('order::messages.status_updated')),
                deletedMessage: @json(trans('order::messages.deleted')),
                forceDeletedMessage: @json(trans('order::messages.force_deleted')),
                errorMessage: @json(trans('core::messages.something_went_wrong')),
                statuses: @json(trans('order::statuses')),
                showArchivedLabel: @json(trans('order::orders.show_archived')),
                showArchivedCountLabel: @json(trans('order::orders.show_archived_count', ['count' => '__COUNT__'])),
                showActiveOrdersLabel: @json(trans('order::orders.show_active_orders')),
                archivedCount: {{ (int) $archivedCount }},
            };

            const urlParams = new URLSearchParams(window.location.search);
            let showArchived = urlParams.get('archived') === '1';
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
                const $archivedNotice = $('#orders-archived-notice');
                const $toggleArchivedLabel = $toggleArchivedBtn.find('.orders-index__toggle-archived-label');
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
                        $archivedNotice.removeAttr('hidden');
                        $ordersTable.addClass('orders-index--archived');
                    } else {
                        $archivedNotice.attr('hidden', 'hidden');
                        $ordersTable.removeClass('orders-index--archived');
                    }

                    const url = new URL(window.location.href);

                    if (showArchived) {
                        url.searchParams.set('archived', '1');
                    } else {
                        url.searchParams.delete('archived');
                    }

                    window.history.replaceState({}, '', url);
                }

                syncArchivedUi();

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

                function buildStatusItems($toggle) {
                    const statusUrl = toggleAttr($toggle, 'status-url');

                    if (!statusUrl) {
                        return '';
                    }

                    const currentStatus = toggleAttr($toggle, 'current-status');
                    let html = '<li class="divider"></li>'
                        + '<li class="dropdown-header">' + config.changeStatusLabel + '</li>';

                    Object.keys(config.statuses).forEach(function (statusKey) {
                        const isActive = statusKey === currentStatus;
                        const label = config.statuses[statusKey];

                        html += '<li' + (isActive ? ' class="active"' : '') + '>'
                            + '<a href="#" class="set-order-status" data-url="' + statusUrl + '" data-status="' + statusKey + '">'
                            + label
                            + '</a></li>';
                    });

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
                    },
                });

                new DataTable('#orders-table .table', {
                    ajax: {
                        url: window.AestheticCart.baseUrl + '/admin/orders/index/table',
                        data: function (data) {
                            data.table = true;
                            data.archived = showArchived ? 1 : 0;
                        },
                    },
                    columns: [
                        { data: 'id', width: '5%' },
                        { data: 'customer_name', orderable: false, searchable: false },
                        { data: 'customer_email' },
                        { data: 'status' },
                        { data: 'payment_status', orderable: false, searchable: false },
                        { data: 'total' },
                        { data: 'created', name: 'created_at' },
                        {
                            data: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'table-row-actions text-center',
                            width: '8%',
                        },
                    ],
                });

                $toggleArchivedBtn.on('click', function () {
                    showArchived = !showArchived;
                    syncArchivedUi();
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

                $(document).on('click', '.order-table-actions-portal .set-order-status', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const $link = $(this);

                    if ($link.parent().hasClass('active')) {
                        closeOrderActionsMenu();

                        return;
                    }

                    const url = $link.data('url');
                    const status = $link.data('status');

                    closeOrderActionsMenu();
                    $link.addClass('disabled');

                    axios
                        .put(url, { status: status })
                        .then(function (response) {
                            window.DataTable.reload('#orders-table .table');

                            if (typeof window.success === 'function') {
                                window.success(
                                    typeof response.data === 'string'
                                        ? response.data
                                        : config.statusUpdatedMessage
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
