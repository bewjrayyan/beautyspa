@php
    $configuredBaseUrl = rtrim((string) config('app.url'), '/');
@endphp

<script>
    window.AestheticCart = {
        version: '{{ aestheticcart_version() }}',
        csrfToken: '{{ csrf_token() }}',
        baseUrl: '{{ $configuredBaseUrl }}',
        appUrl: @json(rtrim(config('app.url'), '/')),
        rtl: {{ is_rtl() ? 'true' : 'false' }},
        locale: '{{ locale() }}',
        defaultPhoneCountry: '{{ strtolower(setting('default_country', 'MY')) }}',
        supportedLocales: @json(supported_locales()),
        langs: {},
        data: {},
        errors: {},
        selectize: [],
        defaultCurrencySymbol: '{{ currency_symbol(setting("default_currency")) }}'
    };

    const adminPathIndex = (window.location.pathname || '').indexOf('/admin');

    if (adminPathIndex !== -1) {
        const installPrefix = window.location.pathname.substring(0, adminPathIndex);
        AestheticCart.baseUrl = `${window.location.origin}${installPrefix}`.replace(/\/$/, '');
    } else {
        AestheticCart.baseUrl = (AestheticCart.baseUrl || window.location.origin).replace(/\/$/, '');
    }

    AestheticCart.langs['admin::admin.buttons.delete'] = '{{ trans('admin::admin.buttons.delete') }}';
    AestheticCart.langs['admin::admin.buttons.media_gallery'] = '{{ trans('admin::admin.buttons.media_gallery') }}';
    AestheticCart.langs['admin::admin.buttons.replace_image'] = '{{ trans('admin::admin.buttons.replace_image') }}';
    AestheticCart.langs['media::media.file_manager.title'] = '{{ trans('media::media.file_manager.title') }}';
    AestheticCart.langs['media::media.file_manager.insert'] = '{{ trans('media::media.file_manager.insert') }}';
    AestheticCart.langs['admin::admin.table.search_here'] = '{{ trans('admin::admin.table.search_here') }}';
    AestheticCart.langs['admin::admin.table.showing_start_end_total_entries'] = '{{ trans('admin::admin.table.showing_start_end_total_entries') }}';
    AestheticCart.langs['admin::admin.table.showing_empty_entries'] = '{{ trans('admin::admin.table.showing_empty_entries') }}';
    AestheticCart.langs['admin::admin.table.show_menu_entries'] = '{{ trans('admin::admin.table.show_menu_entries') }}';
    AestheticCart.langs['admin::admin.table.filtered_from_max_total_entries'] = '{{ trans('admin::admin.table.filtered_from_max_total_entries') }}';
    AestheticCart.langs['admin::admin.table.no_data_available_table'] = '{{ trans('admin::admin.table.no_data_available_table') }}';
    AestheticCart.langs['admin::admin.table.loading'] = '{{ trans('admin::admin.table.loading') }}';
    AestheticCart.langs['admin::admin.table.processing'] = '{{ trans('admin::admin.table.processing') }}';
    AestheticCart.langs['admin::admin.table.no_matching_records_found'] = '{{ trans('admin::admin.table.no_matching_records_found') }}';
    AestheticCart.langs['admin::admin.pagination.previous'] = '{{ trans('admin::admin.pagination.previous') }}';
    AestheticCart.langs['admin::admin.pagination.next'] = '{{ trans('admin::admin.pagination.next') }}';
    AestheticCart.langs['media::media.open_file'] = '{{ trans('media::media.open_file') }}';
    AestheticCart.langs['media::media.dropzone_title'] = '{{ trans('media::media.dropzone_title') }}';
    AestheticCart.langs['media::media.dropzone_hint'] = '{{ trans('media::media.dropzone_hint') }}';
    AestheticCart.langs['media::media.browse_library'] = '{{ trans('media::media.browse_library') }}';
    AestheticCart.langs['media::media.replace_image'] = '{{ trans('media::media.replace_image') }}';
    AestheticCart.langs['media::media.remove_image'] = '{{ trans('media::media.remove_image') }}';
    AestheticCart.langs['media::media.invalid_image_type'] = '{{ trans('media::media.invalid_image_type') }}';
    AestheticCart.langs['media::media.grid.select_all'] = '{{ trans('media::media.grid.select_all') }}';
    AestheticCart.langs['media::media.grid.unlinked_products'] = '{{ trans('media::media.grid.unlinked_products') }}';
    AestheticCart.langs['media::media.grid.unlinked_products_active'] = '{{ trans('media::media.grid.unlinked_products_active') }}';
    AestheticCart.langs['media::media.grid.unlinked_badge'] = '{{ trans('media::media.grid.unlinked_badge') }}';
    AestheticCart.langs['media::media.grid.unlinked_products_empty'] = '{{ trans('media::media.grid.unlinked_products_empty') }}';
    AestheticCart.langs['media::media.grid.deleting'] = '{{ trans('media::media.grid.deleting') }}';
    AestheticCart.langs['media::media.grid.delete_done'] = '{{ trans('media::media.grid.delete_done') }}';
    AestheticCart.langs['media::media.grid.delete_failed'] = '{{ trans('media::media.grid.delete_failed') }}';
    AestheticCart.langs['media::media.grid.delete_partial'] = '{{ trans('media::media.grid.delete_partial') }}';
    AestheticCart.langs['core::messages.something_went_wrong'] = '{{ trans('core::messages.something_went_wrong') }}';

    AestheticCart.apiUrl = function (path) {
        const normalizedPath = path.startsWith('/') ? path : `/${path}`;
        const appUrl = (AestheticCart.appUrl || AestheticCart.baseUrl || '').replace(/\/$/, '');

        return `${appUrl}${normalizedPath}`;
    };

    AestheticCart.resolveAdminBaseUrl = function () {
        const pathnameParts = (window.location.pathname || "").split("/").filter(Boolean);
        const adminIndex = pathnameParts.indexOf("admin");

        if (adminIndex > 0) {
            return `${window.location.origin}/${pathnameParts.slice(0, adminIndex).join("/")}`.replace(/\/$/, "");
        }

        try {
            const appPath = new URL(AestheticCart.appUrl || "").pathname.replace(/\/$/, "");

            return `${window.location.origin}${appPath}`.replace(/\/$/, "");
        } catch (e) {
            return window.location.origin.replace(/\/$/, "");
        }
    };

    // Guard against stale cached bundles still using an outdated AestheticCart.baseUrl.
    $(document).on("click", ".image-picker, .multiple-image-picker, .file-picker", function () {
        AestheticCart.baseUrl = AestheticCart.resolveAdminBaseUrl();
    });

    AestheticCart.normalizeFileManagerIframeSrc = function (src) {
        try {
            const url = new URL(src, window.location.origin);
            const currentBase = AestheticCart.resolveAdminBaseUrl();
            const currentBasePath = new URL(currentBase).pathname.replace(/\/$/, "");

            if (!url.pathname.includes("/admin/file-manager")) {
                return src;
            }

            if (currentBasePath && !url.pathname.startsWith(`${currentBasePath}/`)) {
                url.pathname = `${currentBasePath}${url.pathname.startsWith("/") ? "" : "/"}${url.pathname}`;
            }

            return url.toString();
        } catch (e) {
            return src;
        }
    };

    // Final safeguard: rewrite iframe src before load if stale code generates wrong origin/path.
    const fileManagerIframeObserver = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (!(node instanceof Element)) {
                    return;
                }

                const iframe = node.matches("iframe.file-manager-iframe")
                    ? node
                    : node.querySelector("iframe.file-manager-iframe");

                if (!iframe) {
                    return;
                }

                const currentSrc = iframe.getAttribute("src");

                if (!currentSrc) {
                    return;
                }

                const normalizedSrc = AestheticCart.normalizeFileManagerIframeSrc(currentSrc);

                if (normalizedSrc !== currentSrc) {
                    iframe.setAttribute("src", normalizedSrc);
                }
            });
        });
    });

    const startFileManagerIframeObserver = function () {
        const targetNode = document.body || document.documentElement;

        if (!(targetNode instanceof Node)) {
            return;
        }

        fileManagerIframeObserver.observe(targetNode, {
            childList: true,
            subtree: true,
        });
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", startFileManagerIframeObserver);
    } else {
        startFileManagerIframeObserver();
    }

    AestheticCart.patchMediaPickerGetFrame = function () {
        if (!window.MediaPicker || !window.MediaPicker.prototype) {
            return false;
        }

        const patchedMarker = "__fleetcartGetFramePatched";

        if (window.MediaPicker.prototype[patchedMarker]) {
            return true;
        }

        window.MediaPicker.prototype.getFrame = function () {
            const pathnameParts = (window.location.pathname || "").split("/").filter(Boolean);
            const adminIndex = pathnameParts.indexOf("admin");
            const installPrefix =
                adminIndex > 0
                    ? `/${pathnameParts.slice(0, adminIndex).join("/")}`
                    : (() => {
                          try {
                              return new URL(AestheticCart.appUrl || "").pathname.replace(/\/$/, "");
                          } catch (e) {
                              return "";
                          }
                      })();

            const fileManagerUrl = new URL(
                `${installPrefix}/admin/${this.options.routePrefix}`,
                window.location.origin
            );

            fileManagerUrl.searchParams.set("type", this.options.type);
            fileManagerUrl.searchParams.set("multiple", this.options.multiple);

            return $(
                `<iframe class="file-manager-iframe" frameborder="0" src="${fileManagerUrl.toString()}"></iframe>`
            );
        };

        window.MediaPicker.prototype[patchedMarker] = true;

        return true;
    };

    // Ensure patch applies even if MediaPicker script loads later.
    if (!AestheticCart.patchMediaPickerGetFrame()) {
        const patchInterval = setInterval(function () {
            if (AestheticCart.patchMediaPickerGetFrame()) {
                clearInterval(patchInterval);
            }
        }, 100);

        setTimeout(function () {
            clearInterval(patchInterval);
        }, 10000);
    }
</script>

@stack('globals')
