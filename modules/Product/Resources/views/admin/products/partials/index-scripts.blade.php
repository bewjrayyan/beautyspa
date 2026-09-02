
    <script>
        (function () {
            const config = {
                createUrl: @json(route('admin.products.create')),
                bulkStatusUrl: @json(route('admin.products.bulk_status')),
                createLabel: @json(trans('admin::resource.create', ['resource' => trans('product::products.product')])),
                editLabel: @json(trans('product::products.table.edit')),
                cloneLabel: @json(trans('product::products.table.clone')),
                statusLabel: @json(trans('product::products.table.status')),
                enableLabel: @json(trans('product::products.table.enable')),
                disableLabel: @json(trans('product::products.table.disable')),
                viewLabel: @json(trans('product::products.table.view')),
                deleteLabel: @json(trans('product::products.table.delete')),
                cloneSuccess: @json(trans('product::products.clone_success')),
                statusEnabledMessage: @json(trans('product::products.status_enabled')),
                statusDisabledMessage: @json(trans('product::products.status_disabled')),
                bulkSelectHint: @json(trans('product::products.bulk_status_select_hint')),
                bulkDisableConfirm: @json(trans('product::products.bulk_disable_confirm')),
                tableUrl: @json(route('admin.products.table')),
                filterLabels: @json(trans('product::products.filters')),
                categories: @json($categories),
                brands: @json($brands),
                tags: @json($tags),
            };

            const urlParams = new URLSearchParams(window.location.search);
            let activeStatus = ['0', '1'].includes(urlParams.get('is_active') || '') ? urlParams.get('is_active') : '';
            let activeType = ['physical', 'virtual', 'variable'].includes(urlParams.get('type') || '') ? urlParams.get('type') : '';
            let activeStock = ['in_stock', 'out_of_stock', 'low_stock'].includes(urlParams.get('stock') || '') ? urlParams.get('stock') : '';
            let activeCategoryId = urlParams.get('category_id') || '';
            let activeBrandId = urlParams.get('brand_id') || '';
            let activeTagId = urlParams.get('tag_id') || '';
            let activePriceFrom = urlParams.get('price_from') || '';
            let activePriceTo = urlParams.get('price_to') || '';
            let activeSku = urlParams.get('sku') || '';
            let activeOnSale = urlParams.get('on_sale') === '1';
            let activeSort = urlParams.get('sort') || 'latest';
            let activeSearch = urlParams.get('search') || '';
            let activeMonth = /^\d{4}-(0[1-9]|1[0-2])$/.test(urlParams.get('month') || '') ? urlParams.get('month') : '';
            let activeUpdatedFrom = urlParams.get('updated_from') || '';
            let activeUpdatedTo = urlParams.get('updated_to') || '';

            function initProductsIndex() {
                const $ = window.jQuery;

                if (!$ || !window.DataTable || !window.keypressAction) {
                    setTimeout(initProductsIndex, 50);

                    return;
                }

                const $productsTable = $('#products-table');
                const $productsTableEl = $('#products-table .table');
                let $activeActionsMenu = null;
                let $activeActionsToggle = null;

                function toYmd(date) {
                    if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
                        return '';
                    }

                    const pad = (n) => String(n).padStart(2, '0');

                    return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
                }

                function getUpdatedRangePicker() {
                    const el = document.getElementById('products-filter-updated-range');

                    if (!el) {
                        return null;
                    }

                    if (!el._flatpickr && window.admin && typeof window.admin.dateTimePicker === 'function') {
                        window.admin.dateTimePicker(el);
                    }

                    return el._flatpickr || null;
                }

                function readUpdatedRangeFromPicker() {
                    const picker = getUpdatedRangePicker();

                    if (picker && picker.selectedDates.length) {
                        activeUpdatedFrom = toYmd(picker.selectedDates[0]);
                        activeUpdatedTo = picker.selectedDates.length > 1
                            ? toYmd(picker.selectedDates[1])
                            : activeUpdatedFrom;

                        return;
                    }

                    const raw = String($('#products-filter-updated-range').val() || '').trim();

                    if (!raw) {
                        activeUpdatedFrom = '';
                        activeUpdatedTo = '';

                        return;
                    }

                    const parts = raw.split(/\s+to\s+/i).map((part) => part.trim()).filter(Boolean);

                    activeUpdatedFrom = parts[0] || '';
                    activeUpdatedTo = parts[1] || parts[0] || '';
                }

                function readAdvancedInputs() {
                    activeCategoryId = String($('#products-filter-category').val() || '');
                    activeBrandId = String($('#products-filter-brand').val() || '');
                    activeTagId = String($('#products-filter-tag').val() || '');
                    activePriceFrom = String($('#products-filter-price-from').val() || '').trim();
                    activePriceTo = String($('#products-filter-price-to').val() || '').trim();
                    activeSku = String($('#products-filter-sku').val() || '').trim();
                    activeOnSale = $('#products-filter-on-sale').is(':checked');
                    activeSort = String($('#products-filter-sort').val() || 'latest');
                    activeMonth = String($('#products-filter-month').val() || '');
                    activeSearch = String($('#products-quick-search').val() || $('#products-filter-search').val() || '').trim();
                    $('#products-quick-search').val(activeSearch);
                    $('#products-filter-search').val(activeSearch);
                    readUpdatedRangeFromPicker();
                }

                function syncAdvancedInputs() {
                    $('#products-filter-category').val(activeCategoryId);
                    $('#products-filter-brand').val(activeBrandId);
                    $('#products-filter-tag').val(activeTagId);
                    $('#products-filter-price-from').val(activePriceFrom);
                    $('#products-filter-price-to').val(activePriceTo);
                    $('#products-filter-sku').val(activeSku);
                    $('#products-filter-on-sale').prop('checked', activeOnSale);
                    $('#products-filter-sort').val(activeSort || 'latest');
                    $('#products-filter-month').val(activeMonth);
                    $('#products-quick-search').val(activeSearch);
                    $('#products-filter-search').val(activeSearch);

                    const picker = getUpdatedRangePicker();

                    if (picker) {
                        if (activeUpdatedFrom && activeUpdatedTo) {
                            picker.setDate([activeUpdatedFrom, activeUpdatedTo], false);
                        } else if (activeUpdatedFrom) {
                            picker.setDate([activeUpdatedFrom], false);
                        } else {
                            picker.clear();
                        }
                    } else {
                        const $range = $('#products-filter-updated-range');

                        if (activeUpdatedFrom && activeUpdatedTo) {
                            $range.val(activeUpdatedFrom + ' to ' + activeUpdatedTo);
                        } else if (activeUpdatedFrom) {
                            $range.val(activeUpdatedFrom);
                        } else {
                            $range.val('');
                        }
                    }
                }

                function renderActiveFilterPills() {
                    const $wrap = $('#products-active-filters');
                    const pills = [];

                    if (activeStatus === '1') {
                        pills.push(config.filterLabels.active);
                    }

                    if (activeStatus === '0') {
                        pills.push(config.filterLabels.inactive);
                    }

                    if (activeType) {
                        pills.push(config.filterLabels['type_' + activeType] || activeType);
                    }

                    if (activeStock) {
                        const stockLabels = {
                            in_stock: config.filterLabels.stock_in,
                            out_of_stock: config.filterLabels.stock_out,
                            low_stock: config.filterLabels.stock_low,
                        };
                        pills.push(stockLabels[activeStock] || activeStock);
                    }

                    if (activeCategoryId && config.categories[activeCategoryId]) {
                        pills.push(config.categories[activeCategoryId]);
                    }

                    if (activeBrandId && config.brands[activeBrandId]) {
                        pills.push(config.brands[activeBrandId]);
                    }

                    if (activeTagId && config.tags[activeTagId]) {
                        pills.push(config.tags[activeTagId]);
                    }

                    if (activePriceFrom || activePriceTo) {
                        pills.push((activePriceFrom || '0') + ' – ' + (activePriceTo || '∞'));
                    }

                    if (activeSku) {
                        pills.push('SKU: ' + activeSku);
                    }

                    if (activeOnSale) {
                        pills.push(config.filterLabels.on_sale);
                    }

                    if (activeSearch) {
                        pills.push(activeSearch);
                    }

                    if (!pills.length) {
                        $wrap.empty().prop('hidden', true);

                        return;
                    }

                    $wrap.prop('hidden', false).html(
                        '<span class="products-index__active-label">' + config.filterLabels.active_filters + '</span>'
                        + pills.map((label) => '<span class="products-index__active-pill">' + label + '</span>').join('')
                        + '<button type="button" class="products-index__active-clear" id="products-active-clear-all">' + config.filterLabels.clear + '</button>'
                    );
                }

                function updateFilterUrl() {
                    const url = new URL(window.location.href);
                    const setOrDelete = (key, val) => {
                        if (val) {
                            url.searchParams.set(key, val);
                        } else {
                            url.searchParams.delete(key);
                        }
                    };

                    setOrDelete('is_active', activeStatus);
                    setOrDelete('type', activeType);
                    setOrDelete('stock', activeStock);
                    setOrDelete('category_id', activeCategoryId);
                    setOrDelete('brand_id', activeBrandId);
                    setOrDelete('tag_id', activeTagId);
                    setOrDelete('price_from', activePriceFrom);
                    setOrDelete('price_to', activePriceTo);
                    setOrDelete('sku', activeSku);
                    setOrDelete('on_sale', activeOnSale ? '1' : '');
                    setOrDelete('sort', activeSort !== 'latest' ? activeSort : '');
                    setOrDelete('month', activeMonth);
                    setOrDelete('updated_from', activeUpdatedFrom);
                    setOrDelete('updated_to', activeUpdatedTo);
                    setOrDelete('search', activeSearch);
                    window.history.replaceState({}, '', url);
                    renderActiveFilterPills();
                    updateAdvancedBadge();
                }

                function syncChipGroup(selector, attr, activeValue) {
                    $(selector + ' .products-index__chip').each(function () {
                        const val = String($(this).attr(attr) ?? '');
                        const isActive = val === activeValue;

                        $(this).toggleClass('is-active', isActive).attr('aria-pressed', isActive ? 'true' : 'false');
                    });
                }

                function reloadProductsTable() {
                    window.DataTable.reload('#products-table .table', null, true);
                }

                function bindChipFilters() {
                    $('#products-status-filters').on('click', '.products-index__chip', function () {
                        const val = String($(this).attr('data-status') ?? '');
                        activeStatus = (val === '' || val === activeStatus) ? '' : val;
                        syncChipGroup('#products-status-filters', 'data-status', activeStatus);
                        updateFilterUrl();
                        reloadProductsTable();
                    });

                    $('#products-type-filters').on('click', '.products-index__chip', function () {
                        const val = String($(this).attr('data-type') ?? '');
                        activeType = (val === '' || val === activeType) ? '' : val;
                        syncChipGroup('#products-type-filters', 'data-type', activeType);
                        updateFilterUrl();
                        reloadProductsTable();
                    });

                    $('#products-stock-filters').on('click', '.products-index__chip', function () {
                        const val = String($(this).attr('data-stock') ?? '');
                        activeStock = (val === '' || val === activeStock) ? '' : val;
                        syncChipGroup('#products-stock-filters', 'data-stock', activeStock);
                        updateFilterUrl();
                        reloadProductsTable();
                    });
                }

                function countAdvancedFilters() {
                    let count = 0;

                    if (activeCategoryId) count++;
                    if (activeBrandId) count++;
                    if (activeTagId) count++;
                    if (activePriceFrom) count++;
                    if (activePriceTo) count++;
                    if (activeSku) count++;
                    if (activeOnSale) count++;
                    if (activeMonth) count++;
                    if (activeUpdatedFrom || activeUpdatedTo) count++;
                    if (activeSort && activeSort !== 'latest') count++;
                    if (activeSearch) count++;

                    return count;
                }

                function updateAdvancedBadge() {
                    const count = countAdvancedFilters();
                    const $badge = $('#products-advanced-badge');

                    if (!$badge.length) {
                        return;
                    }

                    if (count > 0) {
                        $badge.text(String(count)).prop('hidden', false);
                    } else {
                        $badge.prop('hidden', true);
                    }
                }

                function bindAdvancedPanel() {
                    const $toggle = $('#products-advanced-toggle');
                    const $panel = $('#products-advanced-panel');

                    $toggle.on('click', function () {
                        const open = !$panel.hasClass('is-open');

                        $panel.toggleClass('is-open', open).prop('hidden', !open);
                        $toggle.toggleClass('is-open', open).attr('aria-expanded', open ? 'true' : 'false');
                    });

                    $('#products-quick-search').on('keydown', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            $('#products-filter-apply').trigger('click');
                        }
                    });

                    $('#products-filter-apply').on('click', function () {
                        readAdvancedInputs();
                        updateFilterUrl();
                        reloadProductsTable();
                    });

                    $('#products-filter-clear').on('click', function () {
                        activeStatus = '';
                        activeType = '';
                        activeStock = '';
                        activeCategoryId = '';
                        activeBrandId = '';
                        activeTagId = '';
                        activePriceFrom = '';
                        activePriceTo = '';
                        activeSku = '';
                        activeSearch = '';
                        activeMonth = '';
                        activeUpdatedFrom = '';
                        activeUpdatedTo = '';
                        activeOnSale = false;
                        activeSort = 'latest';
                        syncChipGroup('#products-status-filters', 'data-status', '');
                        syncChipGroup('#products-type-filters', 'data-type', '');
                        syncChipGroup('#products-stock-filters', 'data-stock', '');
                        syncAdvancedInputs();
                        updateFilterUrl();
                        reloadProductsTable();
                    });

                    $(document).on('click', '#products-active-clear-all', function () {
                        $('#products-filter-clear').trigger('click');
                    });
                }

                function appendFilterParams(data) {
                    if (activeStatus) {
                        data.is_active = activeStatus;
                    }

                    if (activeType) {
                        data.type = activeType;
                    }

                    if (activeStock) {
                        data.stock = activeStock;
                    }

                    if (activeCategoryId) {
                        data.category_id = activeCategoryId;
                    }

                    if (activeBrandId) {
                        data.brand_id = activeBrandId;
                    }

                    if (activeTagId) {
                        data.tag_id = activeTagId;
                    }

                    if (activePriceFrom) {
                        data.price_from = activePriceFrom;
                    }

                    if (activePriceTo) {
                        data.price_to = activePriceTo;
                    }

                    if (activeSku) {
                        data.sku = activeSku;
                    }

                    if (activeOnSale) {
                        data.on_sale = '1';
                    }

                    if (activeSort && activeSort !== 'latest') {
                        data.sort = activeSort;
                    }

                    if (activeMonth) {
                        data.month = activeMonth;
                    }

                    if (activeUpdatedFrom) {
                        data.updated_from = activeUpdatedFrom;
                    }

                    if (activeUpdatedTo) {
                        data.updated_to = activeUpdatedTo;
                    }

                    if (activeSearch) {
                        data.search = activeSearch;
                    }
                }


                function closeProductActionsMenu() {
                    if ($activeActionsMenu) {
                        $activeActionsMenu.remove();
                        $activeActionsMenu = null;
                    }

                    if ($activeActionsToggle) {
                        $activeActionsToggle
                            .closest('.product-table-actions')
                            .removeClass('open')
                            .attr('aria-expanded', 'false');
                        $activeActionsToggle = null;
                    }

                    $productsTable.find('.product-table-actions.open').removeClass('open');
                }

                function openProductActionsMenu($toggle) {
                    closeProductActionsMenu();

                    const rect = $toggle[0].getBoundingClientRect();
                    const isActive = String($toggle.data('is-active')) === '1';
                    const statusUrl = $toggle.data('status-url');

                    $activeActionsMenu = $(
                        '<ul class="dropdown-menu dropdown-menu-right product-table-actions-portal">'
                        + '<li><a href="' + $toggle.data('edit-url') + '">' + config.editLabel + '</a></li>'
                        + '<li><a href="#" class="clone-product-row" data-url="' + $toggle.data('clone-url') + '">' + config.cloneLabel + '</a></li>'
                        + '<li class="divider"></li>'
                        + '<li class="dropdown-header">' + config.statusLabel + '</li>'
                        + '<li' + (isActive ? ' class="active"' : '') + '><a href="#" class="set-product-status" data-url="' + statusUrl + '" data-active="1">' + config.enableLabel + '</a></li>'
                        + '<li' + (!isActive ? ' class="active"' : '') + '><a href="#" class="set-product-status" data-url="' + statusUrl + '" data-active="0">' + config.disableLabel + '</a></li>'
                        + '<li class="divider"></li>'
                        + '<li><a href="' + $toggle.data('view-url') + '" target="_blank" rel="noopener noreferrer">' + config.viewLabel + '</a></li>'
                        + '<li><a href="#" class="delete-product-row" data-id="' + $toggle.data('delete-id') + '">' + config.deleteLabel + '</a></li>'
                        + '</ul>'
                    )
                        .appendTo('body')
                        .css({
                            position: 'fixed',
                            display: 'block',
                            top: (rect.bottom + 4) + 'px',
                            left: rect.right + 'px',
                            transform: 'translateX(-100%)',
                            zIndex: 10000,
                        });

                    $toggle.closest('.product-table-actions').addClass('open');
                    $toggle.attr('aria-expanded', 'true');
                    $activeActionsToggle = $toggle;
                }

                window.keypressAction([{ key: 'c', route: config.createUrl }]);

                DataTable.set('#products-table .table', {
                    routePrefix: 'products',
                    routes: {
                        table: 'table',
                        edit: 'edit',
                        destroy: 'destroy',
                    },
                });

                new window.DataTable('#products-table .table', {
                    stateSave: false,
                    ajax: {
                        url: config.tableUrl,
                        data: function (data) {
                            data.table = true;
                            appendFilterParams(data);
                        },
                    },
                    columnDefs: [{ targets: 'price', width: '1px' }],
                    columns: [
                        { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                        { data: 'thumbnail', orderable: false, searchable: false, width: '10%' },
                        { data: 'name', name: 'translations.name', class: 'name', orderable: false, defaultContent: '' },
                        { data: 'price', searchable: false, orderable: false, className: 'price' },
                        { data: 'in_stock', name: 'in_stock', searchable: false },
                        { data: 'updated', name: 'updated_at' },
                        {
                            data: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'table-row-actions text-center',
                            width: '8%',
                        },
                    ],
                }, function () {
                    const $length = this.element.closest('.dt-container').find('.dt-length');

                    $('<button type="button" class="btn btn-default btn-bulk-disable"><span>' + config.disableLabel + '</span></button>')
                        .appendTo($length)
                        .on('click', function () {
                            const checked = $productsTableEl.find('.select-row:checked');

                            if (!checked.length) {
                                if (typeof window.error === 'function') {
                                    window.error(config.bulkSelectHint);
                                }

                                return;
                            }

                            const ids = window.DataTable.getRowIds(checked);

                            if (!confirm(config.bulkDisableConfirm.replace(':count', String(ids.length)))) {
                                return;
                            }

                            const $button = $(this);

                            $button.prop('disabled', true);

                            axios
                                .put(config.bulkStatusUrl, { ids: ids.join(','), is_active: 0 })
                                .then(function (response) {
                                    window.DataTable.setSelectedIds('#products-table .table', []);
                                    window.DataTable.reload('#products-table .table');

                                    if (typeof window.success === 'function') {
                                        window.success(
                                            response.data.message || config.statusDisabledMessage
                                        );
                                    }
                                })
                                .catch(function (err) {
                                    if (typeof window.error === 'function') {
                                        window.error(
                                            err.response && err.response.data && err.response.data.message
                                                ? err.response.data.message
                                                : 'Something went wrong.'
                                        );
                                    }
                                })
                                .finally(function () {
                                    $button.prop('disabled', false);
                                });
                        });

                });

                bindChipFilters();
                bindAdvancedPanel();
                syncChipGroup('#products-status-filters', 'data-status', activeStatus);
                syncChipGroup('#products-type-filters', 'data-type', activeType);
                syncChipGroup('#products-stock-filters', 'data-stock', activeStock);
                syncAdvancedInputs();
                updateFilterUrl();
                updateAdvancedBadge();

                if (window.admin && typeof window.admin.dateTimePicker === 'function') {
                    window.admin.dateTimePicker(document.getElementById('products-filter-updated-range'));
                }

                $productsTableEl.on('draw.dt', function () {
                    closeProductActionsMenu();

                    if ($.fn.dataTable.isDataTable($productsTableEl[0])) {
                        const info = $productsTableEl.DataTable().page.info();

                        if ($('#products-filtered-count').length) {
                            $('#products-filtered-count').text(String(info.recordsDisplay));
                        }
                    }
                });

                $productsTable.on('click', '.btn-table-actions-toggle', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const $toggle = $(this);

                    if ($activeActionsToggle && $activeActionsToggle[0] === $toggle[0]) {
                        closeProductActionsMenu();

                        return;
                    }

                    openProductActionsMenu($toggle);
                });

                $(document).on('mousedown.productsTableActions', function (e) {
                    if (
                        $activeActionsMenu
                        && !$(e.target).closest('.product-table-actions-portal, .btn-table-actions-toggle').length
                    ) {
                        closeProductActionsMenu();
                    }
                });

                $(document).on('click', '.product-table-actions-portal .set-product-status', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const url = $(this).data('url');
                    const isActive = $(this).data('active');
                    const $link = $(this);

                    if ($link.parent().hasClass('active')) {
                        closeProductActionsMenu();

                        return;
                    }

                    closeProductActionsMenu();

                    $link.addClass('disabled');

                    axios
                        .put(url, { is_active: isActive })
                        .then(function (response) {
                            window.DataTable.reload('#products-table .table');

                            if (typeof window.success === 'function') {
                                window.success(
                                    response.data.message
                                        || (isActive
                                            ? config.statusEnabledMessage
                                            : config.statusDisabledMessage)
                                );
                            }
                        })
                        .catch(function (err) {
                            window.error(err.response && err.response.data && err.response.data.message
                                ? err.response.data.message
                                : 'Something went wrong.');
                        })
                        .finally(function () {
                            $link.removeClass('disabled');
                        });
                });

                $(document).on('click', '.product-table-actions-portal .clone-product-row', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const url = $(this).data('url');
                    const $link = $(this);

                    closeProductActionsMenu();

                    $link.addClass('disabled');

                    axios
                        .post(url)
                        .then(function (response) {
                            if (response.data.redirect) {
                                window.location.href = response.data.redirect;

                                return;
                            }

                            window.DataTable.reload('#products-table .table');
                        })
                        .catch(function (err) {
                            $link.removeClass('disabled');

                            window.error(err.response && err.response.data && err.response.data.message
                                ? err.response.data.message
                                : 'Something went wrong.');
                        });
                });

                $(document).on('click', '.product-table-actions-portal .delete-product-row', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const id = $(this).data('id');
                    const confirmationModal = $('#confirmation-modal');

                    closeProductActionsMenu();

                    confirmationModal
                        .modal('show')
                        .find('form')
                        .off('submit')
                        .on('submit', function (event) {
                            event.preventDefault();

                            confirmationModal.modal('hide');

                            axios
                                .delete(window.AestheticCart.baseUrl + '/admin/products/' + id)
                                .then(function () {
                                    window.DataTable.reload('#products-table .table');
                                })
                                .catch(function (err) {
                                    window.error(err.response && err.response.data && err.response.data.message
                                        ? err.response.data.message
                                        : 'Something went wrong.');
                                });
                        });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initProductsIndex);
            } else {
                initProductsIndex();
            }
        })();
    </script>
