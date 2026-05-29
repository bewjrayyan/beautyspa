export default class MediaGrid {
    static instances = {};

    constructor(selector, options = {}) {
        this.selector = selector;
        this.$el = $(selector);
        this.options = _.merge(
            {
                routePrefix: "media",
                perPage: 20,
                type: null,
                pickerMode: false,
            },
            options
        );

        this.page = 1;
        this.search = "";
        this.filterUnlinkedProducts = false;
        this.searchTimeout = null;
        this.selected = [];
        this.files = [];

        this.init();
        MediaGrid.instances[selector] = this;
    }

    init() {
        this.$el
            .find(".media-grid-per-page")
            .val(String(this.options.perPage));

        this.bindEvents();
        this.load();
    }

    bindEvents() {
        const self = this;

        this.$el.find(".media-grid-search-input").on("input", function () {
            clearTimeout(self.searchTimeout);

            self.searchTimeout = setTimeout(() => {
                self.search = $(this).val();
                self.page = 1;
                self.load();
            }, 300);
        });

        this.$el.find(".media-grid-per-page").on("change", function () {
            self.options.perPage = parseInt($(this).val(), 10) || 20;
            self.page = 1;
            self.load();
        });

        this.$el.find(".btn-filter-unlinked").on("click", function () {
            self.filterUnlinkedProducts = !self.filterUnlinkedProducts;
            self.page = 1;
            self.selected = [];
            self.$el.find("#media-grid-select-all").prop("checked", false);
            self.load();
        });

        this.$el.find("#media-grid-select-all").on("change", function () {
            const checked = $(this).prop("checked");

            self.$el.find(".media-grid-item .select-row").prop("checked", checked);
            self.$el
                .find(".media-grid-item")
                .toggleClass("selected", checked);

            if (checked) {
                self.selected = self.$el
                    .find(".media-grid-item .select-row")
                    .map((_, el) => parseInt(el.value, 10))
                    .get();
            } else {
                self.selected = [];
            }
        });

        this.$el.on("change", ".media-grid-item .select-row", function () {
            const id = parseInt($(this).val(), 10);

            if ($(this).prop("checked")) {
                if (!self.selected.includes(id)) {
                    self.selected.push(id);
                }

                $(this).closest(".media-grid-item").addClass("selected");
            } else {
                self.selected = self.selected.filter((item) => item !== id);
                $(this).closest(".media-grid-item").removeClass("selected");
            }

            self.syncSelectAll();
        });

        this.$el.on("click", ".media-grid-preview", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const id = parseInt(
                $(this).closest(".media-grid-item").data("id"),
                10
            );
            const file = self.files.find((item) => item.id === id);

            if (file) {
                self.openPreview(file);
            }
        });

        this.$el.find(".btn-delete").on("click", () => this.deleteSelected());

        this.$el.on("click", ".media-grid-pagination .page-link", function (e) {
            e.preventDefault();

            const $link = $(this);

            if ($link.parent().hasClass("disabled")) {
                return;
            }

            const page = $link.data("page");

            if (page === "first") {
                self.page = 1;
            } else if (page === "prev") {
                self.page = Math.max(1, self.page - 1);
            } else if (page === "next") {
                self.page = Math.min(self.lastPage, self.page + 1);
            } else if (page === "last") {
                self.page = self.lastPage;
            } else {
                const targetPage = parseInt(page, 10);

                if (!targetPage || targetPage === self.page) {
                    return;
                }

                self.page = targetPage;
            }

            self.load();
        });
    }

    syncSelectAll() {
        const items = this.$el.find(".media-grid-item .select-row");
        const checked = items.filter(":checked");

        this.$el
            .find("#media-grid-select-all")
            .prop("checked", items.length > 0 && checked.length === items.length);
    }

    route(name, params = {}) {
        if (name === "grid") {
            return `/${this.options.routePrefix}/grid`;
        }

        if (name === "destroy") {
            return `/${this.options.routePrefix}/${params.ids}`;
        }

        if (name === "bulkDestroy") {
            return `/${this.options.routePrefix}/bulk-destroy`;
        }

        return `/${this.options.routePrefix}`;
    }

    async load() {
        this.$el.find(".media-grid-loading").removeClass("hide");
        this.$el.find(".media-grid-items").empty();
        this.$el.find(".media-grid-empty").addClass("hide");

        try {
            const { data: payload } = await axios.get(this.route("grid"), {
                params: {
                    page: this.page,
                    per_page: this.options.perPage,
                    query: this.search || undefined,
                    type: this.options.type || undefined,
                    ...(this.filterUnlinkedProducts
                        ? { unlinked_products: 1 }
                        : {}),
                },
            });

            this.filterUnlinkedProducts = Boolean(
                payload.meta?.filter_unlinked_products
            );

            this.render(payload);
            this.updateFilterMeta(payload.meta);
        } catch (err) {
            const message =
                err.response?.data?.message ||
                trans("admin::admin.table.no_data_available_table");

            if (typeof window.error === "function") {
                window.error(message);
            } else if (typeof notify === "function") {
                notify("error", message);
            }
        } finally {
            this.$el.find(".media-grid-loading").addClass("hide");
        }
    }

    render(payload) {
        let items = Array.isArray(payload.data) ? payload.data : [];

        if (this.filterUnlinkedProducts) {
            items = items.filter(
                (file) => file.orphaned_for_cleanup === true
            );
        }

        this.files = items;
        this.lastPage = payload.last_page || 1;

        if (items.length === 0) {
            this.$el.find(".media-grid-empty").removeClass("hide");
            this.$el.find(".media-grid-empty p").text(
                this.filterUnlinkedProducts
                    ? trans("media::media.grid.unlinked_products_empty")
                    : trans("admin::admin.table.no_data_available_table")
            );
            this.$el.find(".media-grid-info").text(
                trans("admin::admin.table.showing_empty_entries")
            );
            this.$el.find(".media-grid-pagination").empty();

            return;
        }

        const html = items.map((file) => this.renderItem(file)).join("");

        this.$el.find(".media-grid-items").html(html);

        const start = (payload.current_page - 1) * payload.per_page + 1;
        const end = Math.min(
            payload.current_page * payload.per_page,
            payload.total
        );

        this.$el.find(".media-grid-info").text(
            trans("admin::admin.table.showing_start_end_total_entries")
                .replace("_START_", start)
                .replace("_END_", end)
                .replace("_TOTAL_", payload.total)
        );

        this.renderPagination(payload);
        this.checkSelected();
        this.syncSelectAll();

        this.$el.find(".media-grid-footer")[0]?.scrollIntoView({
            block: "nearest",
            behavior: "smooth",
        });
    }

    renderItem(file) {
        const preview = file.is_image
            ? `<button type="button" class="media-grid-preview" title="${_.escape(file.filename)}">
                    <img src="${file.url}" alt="${_.escape(file.filename)}">
               </button>`
            : `<button type="button" class="media-grid-preview media-grid-preview-file" title="${_.escape(file.filename)}">
                    <i class="file-icon fa ${file.icon}"></i>
               </button>`;

        const sizeLine = file.size
            ? `<span class="media-grid-item-size">${_.escape(file.size)}${file.dimensions ? ` · ${_.escape(file.dimensions)}` : ""}</span>`
            : file.dimensions
              ? `<span class="media-grid-item-size">${_.escape(file.dimensions)}</span>`
              : "";

        const insertButton = this.options.pickerMode
            ? `<button
                    type="button"
                    class="btn btn-primary select-media"
                    data-id="${file.id}"
                    data-path="${file.path}"
                    data-filename="${_.escape(file.filename)}"
                    data-type="${file.type || ""}"
                    data-icon="${file.icon || ""}"
                >${trans("media::media.file_manager.insert")}</button>`
            : "";

        const unlinkedBadge =
            this.filterUnlinkedProducts && file.orphaned_for_cleanup
                ? `<span class="media-grid-item-badge">${trans("media::media.grid.unlinked_badge")}</span>`
                : "";

        return `
            <div class="media-grid-item${this.options.pickerMode ? " media-grid-item--picker" : ""}" data-id="${file.id}">
                <div class="media-grid-item-checkbox checkbox">
                    <input type="checkbox" class="select-row" value="${file.id}" id="media-item-${file.id}" aria-label="${_.escape(file.filename)}">
                    <label for="media-item-${file.id}" title="${_.escape(file.filename)}"></label>
                </div>
                ${unlinkedBadge}
                <div class="media-grid-item-preview">
                    ${preview}
                </div>
                <div class="media-grid-item-meta">
                    <span class="media-grid-item-filename" title="${_.escape(file.filename)}">${_.escape(file.filename)}</span>
                    ${sizeLine}
                    <span class="media-grid-item-date">${file.created}</span>
                    ${insertButton ? `<div class="media-grid-item-actions">${insertButton}</div>` : ""}
                </div>
            </div>
        `;
    }

    getPaginationPages(currentPage, lastPage) {
        if (lastPage <= 7) {
            return Array.from({ length: lastPage }, (_, index) => index + 1);
        }

        const windowSize = 5;
        let start = Math.max(1, currentPage - Math.floor(windowSize / 2));
        let end = Math.min(lastPage, start + windowSize - 1);

        start = Math.max(1, end - windowSize + 1);

        const pages = [];

        if (start > 1) {
            pages.push(1);

            if (start > 2) {
                pages.push("...");
            }
        }

        for (let page = start; page <= end; page++) {
            pages.push(page);
        }

        if (end < lastPage) {
            if (end < lastPage - 1) {
                pages.push("...");
            }

            pages.push(lastPage);
        }

        return pages;
    }

    renderPagination(payload) {
        const currentPage = payload.current_page;
        const lastPage = payload.last_page;

        let html = '<ul class="pagination">';

        html += `<li class="first ${currentPage === 1 ? "disabled" : ""}">
            <a href="#" class="page-link" data-page="first" aria-label="First">&laquo;</a>
        </li>`;

        html += `<li class="previous ${currentPage === 1 ? "disabled" : ""}">
            <a href="#" class="page-link" data-page="prev" aria-label="Previous">&lsaquo;</a>
        </li>`;

        this.getPaginationPages(currentPage, lastPage).forEach((page) => {
            if (page === "...") {
                html += '<li class="ellipsis disabled"><span>...</span></li>';

                return;
            }

            const active = page === currentPage ? "active" : "";

            html += `<li class="${active}"><a href="#" class="page-link" data-page="${page}">${page}</a></li>`;
        });

        html += `<li class="next ${currentPage === lastPage ? "disabled" : ""}">
            <a href="#" class="page-link" data-page="next" aria-label="Next">&rsaquo;</a>
        </li>`;

        html += `<li class="last ${currentPage === lastPage ? "disabled" : ""}">
            <a href="#" class="page-link" data-page="last" aria-label="Last">&raquo;</a>
        </li>`;

        html += "</ul>";

        this.$el.find(".media-grid-pagination").html(html);
    }

    checkSelected() {
        this.$el.find(".media-grid-item .select-row").each((_, el) => {
            const id = parseInt(el.value, 10);

            if (this.selected.includes(id)) {
                $(el).prop("checked", true);
                $(el).closest(".media-grid-item").addClass("selected");
            }
        });
    }

    openPreview(file) {
        const $modal = $("#media-preview-modal");
        const $body = $modal.find(".media-preview-body");

        $modal.find(".media-preview-filename").text(file.filename);

        if (file.is_image) {
            $body.html(
                `<img src="${file.url}" alt="${_.escape(file.filename)}" class="media-preview-image">`
            );
        } else {
            $body.html(`
                <div class="media-preview-non-image">
                    <i class="file-icon fa ${file.icon}"></i>
                    <p>${_.escape(file.filename)}</p>
                    <a href="${file.url}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                        ${trans("media::media.open_file")}
                    </a>
                </div>
            `);
        }

        $modal.modal("show");
    }

    deleteSelected() {
        if (this.selected.length === 0) {
            return;
        }

        const confirmationModal = $("#confirmation-modal");

        confirmationModal
            .modal("show")
            .find("form")
            .off("submit")
            .on("submit", async (e) => {
                e.preventDefault();

                confirmationModal.modal("hide");

                const ids = this.selected.slice();

                await this.deleteSelectedInBatches(ids);
            });
    }

    setDeleteProgress(current, total) {
        const percent = total > 0 ? Math.round((current / total) * 100) : 0;

        this.$el
            .find(".media-grid-delete-progress-fill")
            .css("width", `${percent}%`);

        this.$el.find(".media-grid-delete-progress-text").text(
            trans("media::media.grid.deleting", {
                current,
                total,
            })
        );
    }

    showDeleteProgress() {
        this.$el.find(".media-grid-delete-progress").removeClass("hide");
        this.$el.find(".btn-delete").prop("disabled", true);
        this.$el.find(".btn-filter-unlinked").prop("disabled", true);
    }

    hideDeleteProgress() {
        this.$el.find(".media-grid-delete-progress").addClass("hide");
        this.$el.find(".btn-delete").prop("disabled", false);
        this.$el.find(".btn-filter-unlinked").prop("disabled", false);
        this.setDeleteProgress(0, 0);
    }

    async deleteSelectedInBatches(ids) {
        const chunkSize = 40;
        const total = ids.length;
        let processed = 0;
        let deletedTotal = 0;
        let failedTotal = 0;

        this.showDeleteProgress();
        this.setDeleteProgress(0, total);

        for (let offset = 0; offset < ids.length; offset += chunkSize) {
            const chunk = ids.slice(offset, offset + chunkSize);

            try {
                const { data } = await axios.post(this.route("bulkDestroy"), {
                    ids: chunk,
                });

                deletedTotal += data?.deleted ?? chunk.length;
            } catch (err) {
                failedTotal += chunk.length;

                if (typeof window.error === "function") {
                    window.error(
                        err.response?.data?.message ||
                            trans("media::media.grid.delete_failed")
                    );
                } else if (typeof notify === "function") {
                    notify(
                        "error",
                        err.response?.data?.message ||
                            trans("media::media.grid.delete_failed")
                    );
                }
            }

            processed += chunk.length;
            this.setDeleteProgress(processed, total);
        }

        this.hideDeleteProgress();
        this.selected = [];
        this.$el.find("#media-grid-select-all").prop("checked", false);
        this.$el.find(".media-grid-item").removeClass("selected");

        if (deletedTotal > 0 && typeof window.success === "function") {
            window.success(
                trans("media::media.grid.delete_done", { count: deletedTotal })
            );
        } else if (deletedTotal > 0 && typeof notify === "function") {
            notify(
                "success",
                trans("media::media.grid.delete_done", { count: deletedTotal })
            );
        }

        if (failedTotal > 0 && deletedTotal > 0 && typeof window.warning === "function") {
            window.warning(
                trans("media::media.grid.delete_partial", {
                    deleted: deletedTotal,
                    failed: failedTotal,
                })
            );
        }

        await this.load();
    }

    updateFilterMeta(meta = {}) {
        const $btn = this.$el.find(".btn-filter-unlinked");
        const $badge = $btn.find(".badge-unlinked-count");
        const count = meta.unlinked_products_count ?? 0;

        $badge.text(count).toggleClass("hide", count === 0);

        this.filterUnlinkedProducts = Boolean(meta.filter_unlinked_products);

        $btn.toggleClass("active", this.filterUnlinkedProducts);

        $btn.find(".btn-filter-unlinked-label").text(
            this.filterUnlinkedProducts
                ? trans("media::media.grid.unlinked_products_active")
                : trans("media::media.grid.unlinked_products")
        );
    }

    static reload(selector) {
        const instance = MediaGrid.instances[selector];

        if (instance) {
            instance.load();
        }
    }
}
