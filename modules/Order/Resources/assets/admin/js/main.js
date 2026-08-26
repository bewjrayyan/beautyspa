import axios from "axios";
import { bindOrderWhatsAppSend } from "./orderWhatsApp";

(function () {
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", bindOrderWorkspaceNavigation);
    } else {
        bindOrderWorkspaceNavigation();
    }

    const $ = window.jQuery || window.$;

    if (!$) {
        return;
    }

    function configureAxios() {
        const fleetCart = window.AestheticCart || {};

        if (fleetCart.baseUrl) {
            axios.defaults.baseURL = `${fleetCart.baseUrl}/admin`;
        }

        if (fleetCart.csrfToken) {
            axios.defaults.headers.common["X-CSRF-TOKEN"] =
                fleetCart.csrfToken;
        }

        axios.defaults.headers.common["X-Requested-With"] =
            "XMLHttpRequest";

        return axios;
    }

    const http = configureAxios();

    function adminOrderUrl(orderId, suffix) {
        return `orders/${orderId}/${suffix}`;
    }

    function updateBadge($badge, text, statusValue = null) {
        if (!$badge.length) {
            return;
        }

        $badge.text(text);

        if (statusValue !== null && statusValue !== undefined) {
            $badge.attr("data-status", statusValue);
        }
    }

    function syncOrderStatusAfterPayment(value) {
        const $orderStatus = $("#order-status");
        const $orderStatusBadge = $("#order-status-badge");

        if (!$orderStatus.length) {
            return;
        }

        let nextStatus = null;

        if (value === "paid" && $orderStatus.val() === "pending") {
            nextStatus = "completed";
        } else if (
            ["canceled", "refunded"].includes(value) &&
            $orderStatus.val() !== "canceled"
        ) {
            nextStatus = "canceled";
        }

        if (
            !nextStatus ||
            !$orderStatus.find(`option[value="${nextStatus}"]`).length
        ) {
            return;
        }

        $orderStatus.val(nextStatus);
        updateBadge(
            $orderStatusBadge,
            $orderStatus.find("option:selected").text(),
            nextStatus
        );
    }

    function paymentReferencePayload() {
        const $panel = $("#order-payment-reference");

        if (!$panel.length) {
            return {};
        }

        return {
            transaction_id: $("#order-payment-transaction-id").val() || "",
            admin_note: $("#order-payment-admin-note").val() || "",
        };
    }

    function paymentReferenceRequiredFor(status) {
        const $panel = $("#order-payment-reference");

        if (!$panel.length) {
            return false;
        }

        let required = ["paid", "processing"];

        try {
            const parsed = JSON.parse(
                $panel.attr("data-requires-reference-for") || "[]"
            );

            if (Array.isArray(parsed) && parsed.length) {
                required = parsed;
            }
        } catch (error) {
            // keep defaults
        }

        return required.includes(status);
    }

    function syncPaymentReferenceDisplay(data = {}) {
        const txId = (data.transaction_id || "").trim();
        const note = (data.admin_note || "").trim();
        const $txDisplay = $("#order-transaction-id-display");
        const $noteRow = $("#order-payment-admin-note-row");
        const $noteDisplay = $("#order-payment-admin-note-display");

        if ($txDisplay.length) {
            $txDisplay.html(
                txId
                    ? `<code class="order-show__mono">${$("<div>").text(txId).html()}</code>`
                    : "—"
            );
        }

        if ($noteRow.length && $noteDisplay.length) {
            if (note) {
                $noteDisplay.text(note);
                $noteRow.prop("hidden", false);
            } else {
                $noteDisplay.text("");
                $noteRow.prop("hidden", true);
            }
        }

        if ($("#order-payment-transaction-id").length && txId) {
            $("#order-payment-transaction-id").val(txId);
        }

        if ($("#order-payment-admin-note").length && Object.prototype.hasOwnProperty.call(data, "admin_note")) {
            $("#order-payment-admin-note").val(note);
        }
    }

    function bindStatusSelect(selector, suffix, $badge, bodyKey) {
        const $select = $(selector);

        if (!$select.length) {
            return;
        }

        $select.on("change", (e) => {
            const orderId = e.currentTarget.dataset.id;
            const previousValue = $select.data("previous-value") || $select.find("option").filter(function () {
                return this.defaultSelected;
            }).val() || $select.val();
            const value = e.currentTarget.value;
            const label = $(e.currentTarget).find("option:selected").text();
            let payload = { [bodyKey]: value };

            if (bodyKey === "payment_status") {
                payload = { ...payload, ...paymentReferencePayload() };

                if (
                    paymentReferenceRequiredFor(value)
                    && !(payload.transaction_id || "").trim()
                ) {
                    $select.val(previousValue);

                    if (typeof window.error === "function") {
                        window.error(
                            $("#order-payment-reference").data("requiredMessage")
                                || "Enter a Transaction ID / bank reference before setting Paid or Processing."
                        );
                    }

                    return;
                }
            }

            $select.prop("disabled", true);

            http
                .put(adminOrderUrl(orderId, suffix), payload)
                .then((response) => {
                    $select.data("previous-value", value);
                    updateBadge($badge, label, value);

                    if (bodyKey === "payment_status") {
                        syncOrderStatusAfterPayment(value);

                        if (response?.data && typeof response.data === "object") {
                            syncPaymentReferenceDisplay(response.data);
                        }
                    }

                    if (typeof window.success === "function") {
                        window.success(
                            typeof response.data === "string"
                                ? response.data
                                : response.data.message || "Updated."
                        );
                    }
                })
                .catch(({ response }) => {
                    $select.val($select.data("previous-value") || previousValue);

                    if (typeof window.error === "function") {
                        window.error(
                            response?.data?.message ?? "Failed to update."
                        );
                    }
                })
                .finally(() => {
                    $select.prop("disabled", false);
                });
        });

        $select.data("previous-value", $select.val());
    }

    function bindPaymentReferenceSave() {
        const $button = $("#order-payment-reference-save");
        const $select = $("#order-payment-status");

        if (!$button.length || !$select.length) {
            return;
        }

        $button.on("click", () => {
            const orderId = $select.data("id");
            const payload = {
                payment_status: $select.val(),
                ...paymentReferencePayload(),
            };

            $button.prop("disabled", true);

            http
                .put(adminOrderUrl(orderId, "payment-status"), payload)
                .then((response) => {
                    if (response?.data && typeof response.data === "object") {
                        syncPaymentReferenceDisplay(response.data);
                    }

                    if (typeof window.success === "function") {
                        window.success(
                            typeof response.data === "string"
                                ? response.data
                                : response.data.message || "Saved."
                        );
                    }
                })
                .catch(({ response }) => {
                    if (typeof window.error === "function") {
                        window.error(
                            response?.data?.message ?? "Failed to save."
                        );
                    }
                })
                .finally(() => {
                    $button.prop("disabled", false);
                });
        });
    }

    function closeOrderActionsDropdown($menu) {
        $menu.removeClass("open");
        $menu.find(".dropdown-toggle").attr("aria-expanded", "false");
    }

    function bindOrderActionsDropdown() {
        const $menu = $("#order-actions");

        if (!$menu.length) {
            return;
        }

        $menu.off("click.orderActions", ".js-order-action");
        $menu.on("click.orderActions", ".js-order-action", function (e) {
            const action = $(this).attr("data-action");

            if (!action) {
                return;
            }

            if (action === "email") {
                e.preventDefault();
                e.stopPropagation();
                $("#order-email-form").trigger("submit");
                closeOrderActionsDropdown($menu);
                return;
            }

            // Print, receipt, and back rely on native <a> navigation so popup
            // blockers and embedded browsers (e.g. Cursor preview) do not break.
            closeOrderActionsDropdown($menu);
        });
    }

    function bindOrderWorkspaceNavigation() {
        const nav = document.querySelector(".order-show__workspace-nav");

        if (!nav || nav.dataset.navigationBound === "true") {
            return;
        }

        nav.dataset.navigationBound = "true";

        const links = Array.from(nav.querySelectorAll("a[data-order-section]"));
        function markActive(activeLink) {
            links.forEach((link) => {
                const isActive = link === activeLink;

                link.classList.toggle("is-active", isActive);

                if (isActive) {
                    link.setAttribute("aria-current", "location");
                } else {
                    link.removeAttribute("aria-current");
                }
            });
        }

        links.forEach((link) => {
            link.addEventListener("click", (event) => {
                const sectionId = link.dataset.orderSection;
                const section = document.getElementById(sectionId);

                if (!section) {
                    return;
                }

                event.preventDefault();
                const sectionUrl = `${window.location.pathname}${window.location.search}#${sectionId}`;
                window.history.replaceState(null, "", sectionUrl);
                const sectionTop = Math.max(
                    0,
                    section.getBoundingClientRect().top + window.pageYOffset - 84
                );

                window.scrollTo(0, sectionTop);
                markActive(link);
            });
        });

        const initialSection = window.location.hash.slice(1);
        const initialLink = links.find(
            (link) => link.dataset.orderSection === initialSection
        );

        markActive(initialLink || links[0]);
    }

    function init() {
        bindStatusSelect(
            "#order-status",
            "status",
            $("#order-status-badge"),
            "status"
        );

        bindStatusSelect(
            "#order-payment-status",
            "payment-status",
            $("#order-payment-status-badge"),
            "payment_status"
        );

        bindStatusSelect(
            "#order-treatment-status",
            "treatment-status",
            $("#order-treatment-status-badge"),
            "treatment_status"
        );

        bindPaymentReferenceSave();
        bindOrderActionsDropdown();
        bindOrderWhatsAppSend();
        bindGoogleSheetsSync();
    }

    function bindGoogleSheetsSync() {
        const $button = $("#order-google-sheets-sync-btn");

        if (!$button.length) {
            return;
        }

        $button.on("click", () => {
            const syncUrl = $button.attr("data-sync-url");

            if (!syncUrl) {
                return;
            }

            $button.prop("disabled", true);

            http
                .post(syncUrl)
                .then((response) => {
                    const message =
                        typeof response.data === "string"
                            ? response.data
                            : response.data?.message || "Synced.";

                    if (typeof window.success === "function") {
                        window.success(message);
                    }

                    window.location.reload();
                })
                .catch(({ response }) => {
                    if (typeof window.error === "function") {
                        window.error(
                            response?.data?.message ?? "Google Sheets sync failed."
                        );
                    }
                })
                .finally(() => {
                    $button.prop("disabled", false);
                });
        });
    }

    $(init);
})();
