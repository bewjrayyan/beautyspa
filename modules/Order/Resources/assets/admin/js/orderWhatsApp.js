import axios from "axios";

/**
 * OneSender order document send (invoice / receipt PDF).
 */
export function bindOrderWhatsAppSend() {
    const $ = window.jQuery || window.$;
    const root = document.getElementById("order-whatsapp-actions");

    if (!root || !$) {
        return;
    }

    const fleetCart = window.AestheticCart || {};
    const client = axios.create({
        baseURL: fleetCart.baseUrl ? `${fleetCart.baseUrl}/admin` : undefined,
        timeout: 120000,
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json",
            ...(fleetCart.csrfToken
                ? { "X-CSRF-TOKEN": fleetCart.csrfToken }
                : {}),
        },
    });

    let sending = false;

    const buttons = root.querySelectorAll(".js-order-whatsapp-send");

    buttons.forEach((button) => {
        button.addEventListener("click", () => {
            if (sending) {
                return;
            }

            const sendUrl = button.getAttribute("data-send-url");
            const type = button.getAttribute("data-whatsapp-type");
            const sendingLabel =
                button.getAttribute("data-sending-label") || "Sending…";
            const originalHtml = button.innerHTML;

            if (!sendUrl) {
                window.error?.(
                    window.AestheticCart?.langs?.["order::whatsapp.not_configured"] ||
                        "WhatsApp send URL is missing."
                );

                return;
            }

            sending = true;
            root.classList.add("order-show__whatsapp--sending");
            buttons.forEach((btn) => {
                btn.setAttribute("aria-disabled", "true");
            });
            button.innerHTML =
                `<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> ${sendingLabel}`;

            client
                .post(sendUrl)
                .then((response) => {
                    const message =
                        response.data?.message ||
                        (type === "receipt"
                            ? "Receipt sent via WhatsApp."
                            : "Invoice sent via WhatsApp.");

                    window.success?.(message);
                })
                .catch((error) => {
                    const data = error.response?.data;
                    const message =
                        (typeof data === "object" && data?.message) ||
                        (typeof data === "string" && data) ||
                        error.message ||
                        "Failed to send WhatsApp message.";

                    window.error?.(message);
                })
                .finally(() => {
                    sending = false;
                    root.classList.remove("order-show__whatsapp--sending");
                    buttons.forEach((btn) => {
                        btn.removeAttribute("aria-disabled");
                        if (btn === button) {
                            btn.innerHTML = originalHtml;
                        }
                    });
                });
        });
    });
}
