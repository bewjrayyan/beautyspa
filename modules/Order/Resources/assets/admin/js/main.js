import axios from "axios";
import { bindOrderWhatsAppSend } from "./orderWhatsApp";

(function () {
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

        if (value === "paid" && $orderStatus.val() === "pending_payment") {
            nextStatus = "completed";
        } else if (
            value === "canceled" &&
            !["canceled", "refunded"].includes($orderStatus.val())
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

    function bindStatusSelect(selector, suffix, $badge, bodyKey) {
        const $select = $(selector);

        if (!$select.length) {
            return;
        }

        $select.on("change", (e) => {
            const orderId = e.currentTarget.dataset.id;
            const value = e.currentTarget.value;
            const label = $(e.currentTarget).find("option:selected").text();
            const payload = { [bodyKey]: value };

            $select.prop("disabled", true);

            http
                .put(adminOrderUrl(orderId, suffix), payload)
                .then((response) => {
                    updateBadge($badge, label, value);

                    if (bodyKey === "payment_status") {
                        syncOrderStatusAfterPayment(value);
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
