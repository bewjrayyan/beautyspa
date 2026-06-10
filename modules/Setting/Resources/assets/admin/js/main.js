let currencyRateExchangeService = $("#currency_rate_exchange_service");

$(`#${currencyRateExchangeService.val()}-service`).removeClass("hide");

currencyRateExchangeService.on("change", (e) => {
    $(".currency-rate-exchange-service").addClass("hide");

    $(`#${e.currentTarget.value}-service`).removeClass("hide");
});

$("#auto_refresh_currency_rates").on("change", () => {
    $("#auto-refresh-currency-rates-frequency-field").toggleClass("hide");
});

$("#auto_refresh_currency_rates").on("change", () => {
    $("#auto-refresh-frequency-field").toggleClass("hide");
});

$("#google_recaptcha_enabled").on("change", () => {
    $("#google-recaptcha-fields").toggleClass("hide");
});

$("#facebook_login_enabled").on("change", () => {
    $("#facebook-login-fields").toggleClass("hide");
});

$("#google_login_enabled").on("change", () => {
    $("#google-login-fields").toggleClass("hide");
});

$("#whatsapp_otp_login_enabled").on("change", () => {
    $("#whatsapp-otp-fields").toggleClass("hide");
});

$("#paypal_enabled").on("change", () => {
    $("#paypal-fields").toggleClass("hide");
});

$("#stripe_enabled").on("change", () => {
    $("#stripe-fields").toggleClass("hide");
});

$("#paytm_enabled").on("change", () => {
    $("#paytm-fields").toggleClass("hide");
});

$("#razorpay_enabled").on("change", () => {
    $("#razorpay-fields").toggleClass("hide");
});

$("#instamojo_enabled").on("change", () => {
    $("#instamojo-fields").toggleClass("hide");
});

$("#paystack_enabled").on("change", () => {
    $("#paystack-fields").toggleClass("hide");
});

$("#authorizenet_enabled").on("change", () => {
    $("#authorizenet-fields").toggleClass("hide");
});

$("#mercadopago_enabled").on("change", () => {
    $("#mercadopago-fields").toggleClass("hide");
});

$("#flutterwave_enabled").on("change", () => {
    $("#flutterwave-fields").toggleClass("hide");
});

$("#iyzico_enabled").on("change", () => {
    $("#iyzico-fields").toggleClass("hide");
});

$("#bkash_enabled").on("change", () => {
    $("#bkash-fields").toggleClass("hide");
});

$("#nagad_enabled").on("change", () => {
    $("#nagad-fields").toggleClass("hide");
});

$("#sslcommerz_enabled").on("change", () => {
    $("#sslcommerz-fields").toggleClass("hide");
});

$("#payfast_enabled").on("change", () => {
    $("#payfast-fields").toggleClass("hide");
});

$("#chip_enabled").on("change", () => {
    $("#chip-fields").toggleClass("hide");
});

["chip_fpx", "chip_card", "chip_atome"].forEach((method) => {
    $(`#${method}_enabled`).on("change", () => {
        $(`#${method.replace(/_/g, "-")}-fields`).toggleClass("hide");
    });
});

$("#bank_transfer_enabled").on("change", () => {
    $("#bank-transfer-fields").toggleClass("hide");
});

$("#check_payment_enabled").on("change", () => {
    $("#check-payment-fields").toggleClass("hide");
});

$("#store_country").on("change", (e) => {
    let oldState = $("#store_state").val();

    axios.get(AestheticCart.apiUrl(`/countries/${e.currentTarget.value}/states`)).then(({data}) => {
        $(".store-state").addClass("hide");

        if (_.isEmpty(data)) {
            return $(".store-state.input")
                .removeClass("hide")
                .find("input")
                .val(oldState);
        }

        let options = "";

        for (let code in data) {
            options += `<option value="${code}">${data[code]}</option>`;
        }

        $(".store-state.select")
            .removeClass("hide")
            .find("select")
            .html(options)
            .val(oldState);
    });
});

$(function () {
    $("#store_country").trigger("change");
});

(function initSettingsPageUx() {
    const form = document.getElementById("settings-edit-form");

    if (!form || !form.classList.contains("admin-settings-page")) {
        return;
    }

    const allowAutofillPattern = /store_email|mail_from_address|mail_from_name|mail_username|mail_password/i;

    form.querySelectorAll("input, textarea, select").forEach((field) => {
        const name = field.getAttribute("name") || "";
        const type = (field.getAttribute("type") || "").toLowerCase();

        if (type === "hidden" || type === "checkbox" || type === "radio" || type === "color" || type === "file") {
            return;
        }

        if (!allowAutofillPattern.test(name)) {
            field.setAttribute("autocomplete", "off");
            field.setAttribute("data-lpignore", "true");
            field.setAttribute("data-1p-ignore", "true");
        }
    });

    const searchInput = document.getElementById("settings-nav-search");

    if (searchInput) {
        searchInput.value = "";
    }
    const navGroups = document.getElementById("settings-nav-groups");
    const unsavedBadge = document.getElementById("settings-unsaved-badge");
    let formDirty = false;
    let dirtyTrackingEnabled = false;

    const markDirty = () => {
        if (!dirtyTrackingEnabled || formDirty) {
            return;
        }

        formDirty = true;
        unsavedBadge?.classList.remove("is-hidden");
    };

    form.addEventListener("input", markDirty, true);
    form.addEventListener("change", markDirty, true);

    // jQuery ready handlers (e.g. #store_country trigger) run after this script;
    // enable dirty tracking only once those programmatic updates have finished.
    const enableDirtyTracking = () => {
        dirtyTrackingEnabled = true;
    };

    if (typeof $ !== "undefined") {
        $(enableDirtyTracking);
    } else {
        document.addEventListener("DOMContentLoaded", enableDirtyTracking);
    }

    form.querySelectorAll('input[type="color"][data-color-empty]').forEach((input) => {
        input.addEventListener("input", () => {
            input.removeAttribute("data-color-empty");
        });
    });

    form.addEventListener("submit", () => {
        form.querySelectorAll('input[type="color"][data-color-empty]').forEach((input) => {
            input.disabled = true;
        });

        formDirty = false;
        unsavedBadge?.classList.add("is-hidden");
    });

    window.addEventListener("beforeunload", (event) => {
        if (!formDirty) {
            return;
        }

        event.preventDefault();
        event.returnValue = "";
    });

    document.addEventListener("keydown", (event) => {
        if (!(event.ctrlKey || event.metaKey) || event.key.toLowerCase() !== "s") {
            return;
        }

        if (!form.contains(document.activeElement) && document.activeElement !== document.body) {
            return;
        }

        event.preventDefault();

        const submitButton = form.querySelector('.settings-panel__footer button[type="submit"]');

        if (submitButton && !submitButton.disabled) {
            submitButton.click();
        }
    });

    document.querySelectorAll("[data-settings-group]").forEach((group) => {
        const toggle = group.querySelector(".settings-nav-group__toggle");

        if (!toggle) {
            return;
        }

        toggle.addEventListener("click", () => {
            const expanded = group.classList.toggle("is-expanded");
            toggle.setAttribute("aria-expanded", expanded ? "true" : "false");
        });
    });

    if (searchInput && navGroups) {
        const noResultsText =
            searchInput.dataset.noResults ||
            "No settings match your search.";
        const searchKbd = document.querySelector(".settings-sidebar__search-kbd");

        const emptyState = document.createElement("p");
        emptyState.className = "settings-sidebar__empty is-hidden";
        emptyState.textContent = noResultsText;
        navGroups.appendChild(emptyState);

        const filterNav = () => {
            const query = searchInput.value.trim().toLowerCase();
            let visibleCount = 0;

            if (searchKbd) {
                searchKbd.classList.toggle("is-hidden", query !== "");
            }

            navGroups.querySelectorAll(".settings-nav__item").forEach((item) => {
                const label = item
                    .querySelector(".settings-nav__label")
                    ?.textContent?.toLowerCase() || "";
                const groupTitle =
                    item
                        .closest(".settings-nav-group")
                        ?.querySelector(".settings-nav-group__title")
                        ?.textContent?.toLowerCase() || "";
                const haystack = `${label} ${groupTitle}`;
                const match = query === "" || haystack.includes(query);

                item.classList.toggle("is-filtered-out", !match);

                if (match) {
                    visibleCount++;
                }
            });

            navGroups.querySelectorAll(".settings-nav-group").forEach((group) => {
                const hasVisible = group.querySelector(
                    ".settings-nav__item:not(.is-filtered-out)"
                );
                group.classList.toggle("is-filtered-out", !hasVisible);

                if (query !== "" && hasVisible) {
                    group.classList.add("is-expanded");
                    group
                        .querySelector(".settings-nav-group__toggle")
                        ?.setAttribute("aria-expanded", "true");
                }
            });

            emptyState.classList.toggle("is-hidden", visibleCount > 0);
        };

        searchInput.addEventListener("input", filterNav);

        searchInput.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                searchInput.value = "";
                filterNav();
                searchInput.blur();
            }
        });

        document.addEventListener("keydown", (event) => {
            if (
                event.key === "/"
                && !event.ctrlKey
                && !event.metaKey
                && !["INPUT", "TEXTAREA", "SELECT"].includes(
                    document.activeElement?.tagName || ""
                )
            ) {
                event.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
        });
    }

    const activeItem = document.querySelector(".settings-nav__item.is-active");

    if (activeItem) {
        activeItem.scrollIntoView({ block: "nearest", behavior: "smooth" });
    }
})();
