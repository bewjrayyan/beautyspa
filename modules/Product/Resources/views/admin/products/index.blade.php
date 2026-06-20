@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('product::products.products'))

    <li class="active">{{ trans('product::products.products') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons', [])
    @slot('resource', 'products')
    @slot('name', trans('product::products.product'))

    @slot('thead')
        @include('product::admin.products.partials.thead', ['name' => 'products-index'])
    @endslot
@endcomponent

@if (session()->has('exit_flash'))
    @push('notifications')
        <div class="alert alert-success fade in alert-dismissible clearfix">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M12 2C6.49 2 2 6.49 2 12C2 17.51 6.49 22 12 22C17.51 22 22 17.51 22 12C22 6.49 17.51 2 12 2ZM11.25 8C11.25 7.59 11.59 7.25 12 7.25C12.41 7.25 12.75 7.59 12.75 8V13C12.75 13.41 12.41 13.75 12 13.75C11.59 13.75 11.25 13.41 11.25 13V8ZM12.92 16.38C12.87 16.51 12.8 16.61 12.71 16.71C12.61 16.8 12.5 16.87 12.38 16.92C12.26 16.97 12.13 17 12 17C11.87 17 11.74 16.97 11.62 16.92C11.5 16.87 11.39 16.8 11.29 16.71C11.2 16.61 11.13 16.51 11.08 16.38C11.03 16.26 11 16.13 11 16C11 15.87 11.03 15.74 11.08 15.62C11.13 15.5 11.2 15.39 11.29 15.29C11.39 15.2 11.5 15.13 11.62 15.08C11.86 14.98 12.14 14.98 12.38 15.08C12.5 15.13 12.61 15.2 12.71 15.29C12.8 15.39 12.87 15.5 12.92 15.62C12.97 15.74 13 15.87 13 16C13 16.13 12.97 16.26 12.92 16.38Z" fill="#555555"/>
            </svg>
            
            @include('admin::partials.alert_close')

            <span class="alert-text">{{ session('exit_flash') }}</span>
        </div>
    @endpush
@endif

@push('scripts')
    <script>
        (function () {
            const config = {
                createUrl: @json(route('admin.products.create')),
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
            };

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

                new window.DataTable('#products-table .table', {
                    stateSave: false,
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
                    $('<a href="' + config.createUrl + '" class="btn btn-primary btn-actions btn-create"><span>' + config.createLabel + '</span></a>')
                        .appendTo(this.element.closest('.dt-container').find('.dt-length'));
                });

                $productsTableEl.on('draw.dt', closeProductActionsMenu);

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
@endpush
