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
            $(".store-state.input")
                .removeClass("hide")
                .find("input")
                .val(oldState);
        } else {
            let options = "";

            for (let code in data) {
                options += `<option value="${code}">${data[code]}</option>`;
            }

            $(".store-state.select")
                .removeClass("hide")
                .find("select")
                .html(options)
                .val(oldState);
        }

        if (typeof window.scheduleSettingsFormBaseline === "function") {
            window.scheduleSettingsFormBaseline(300);
        }
    });
});

$(function () {
    if ($("#store_country").length) {
        $("#store_country").trigger("change");
    }
});

(function initSettingsPageUx() {
    const form = document.querySelector("form.admin-settings-page");

    if (!form) {
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
    let baselineSnapshot = "";
    let baselineTimer = null;

    const getFormSnapshot = () => {
        const parts = [];

        form.querySelectorAll("input, select, textarea").forEach((field) => {
            const name = field.getAttribute("name");

            if (!name || name === "_token" || name === "_method") {
                return;
            }

            if (field.disabled) {
                return;
            }

            const type = (field.type || "").toLowerCase();

            if (type === "submit" || type === "button" || type === "file") {
                return;
            }

            if (type === "checkbox") {
                parts.push(`${name}=${field.checked ? "1" : "0"}`);

                return;
            }

            if (type === "radio") {
                if (field.checked) {
                    parts.push(`${name}=${field.value}`);
                }

                return;
            }

            if (field.tagName === "SELECT" && field.multiple) {
                const selected = Array.from(field.selectedOptions);

                if (selected.length === 0) {
                    parts.push(`${name}[]=`);
                } else {
                    selected.forEach((option) => {
                        parts.push(`${name}[]=${option.value}`);
                    });
                }

                return;
            }

            parts.push(`${name}=${field.value}`);
        });

        return parts.sort().join("\n");
    };

    const updateDirtyState = () => {
        if (!dirtyTrackingEnabled) {
            return;
        }

        const dirty = getFormSnapshot() !== baselineSnapshot;

        formDirty = dirty;
        unsavedBadge?.classList.toggle("is-hidden", !dirty);
    };

    const establishBaseline = () => {
        baselineSnapshot = getFormSnapshot();
        dirtyTrackingEnabled = true;
        updateDirtyState();
    };

    const scheduleBaseline = (delay = 500) => {
        if (baselineTimer) {
            clearTimeout(baselineTimer);
        }

        baselineTimer = setTimeout(() => {
            baselineTimer = null;
            establishBaseline();
        }, delay);
    };

    const markDirty = () => {
        if (!dirtyTrackingEnabled) {
            scheduleBaseline(500);

            return;
        }

        updateDirtyState();
    };

    form.addEventListener("input", markDirty, true);
    form.addEventListener("change", markDirty, true);

    // Defer baseline until programmatic init settles (#store_country axios, toggles, etc.).
    window.scheduleSettingsFormBaseline = scheduleBaseline;

    if (typeof $ !== "undefined") {
        $(() => scheduleBaseline(500));
    } else {
        document.addEventListener("DOMContentLoaded", () => scheduleBaseline(500));
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

(function initMaintenanceSettingsPanel() {
    const root = document.querySelector('.admin-settings[data-active-tab="maintenance"]');

    if (!root) {
        return;
    }

    const presetSelect = document.getElementById("maintenance_page_effect_preset");
    const colorSourceSelect = document.getElementById("maintenance_page_color_source");
    const accentColorInput = document.getElementById("maintenance_page_accent_color");
    const customColorField = document.getElementById("maintenance-custom-color-field");
    const customEffectsPanel = document.getElementById("maintenance-custom-effects-panel");
    const presetNote = document.getElementById("maintenance-preset-note");
    const bokehToggle = document.getElementById("maintenance_page_bokeh_enabled");
    const bokehCountField = document.getElementById("maintenance-bokeh-count-field");
    const gradientHelp = document.getElementById("maintenance-gradient-help");
    const previewCanvas = document.getElementById("maintenance-page-preview-canvas");
    const previewCard = previewCanvas?.querySelector(".maintenance-page-preview__card");

    const toggles = {
        gradient: document.getElementById("maintenance_page_gradient_enabled"),
        bokeh: bokehToggle,
        shimmer: document.getElementById("maintenance_page_shimmer_enabled"),
        grain: document.getElementById("maintenance_page_grain_drift_enabled"),
        frosted: document.getElementById("maintenance_page_frosted_card_enabled"),
    };

    const bokehCountInput = document.getElementById("maintenance_page_bokeh_count");

    const presets = {
        aesthetic: { gradient: true, bokeh: true, bokeh_count: 12, shimmer: true, grain: true, frosted: true },
        minimal: { gradient: true, bokeh: false, bokeh_count: 6, shimmer: false, grain: false, frosted: true },
        classic: { gradient: true, bokeh: true, bokeh_count: 8, shimmer: false, grain: true, frosted: true },
    };

    const isCustomPreset = () => presetSelect?.value === "custom";

    const setCustomPanelVisible = (custom) => {
        customEffectsPanel?.classList.toggle("hide", !custom);
        presetNote?.classList.toggle("hide", custom);
    };

    const setToggleDisabled = (enabled) => {
        Object.values(toggles).forEach((toggle) => {
            if (!toggle) {
                return;
            }

            toggle.disabled = !enabled;
        });

        if (bokehCountInput) {
            bokehCountInput.disabled = !enabled;
        }
    };

    const applyPresetToToggles = (presetKey) => {
        const preset = presets[presetKey];

        if (!preset) {
            return;
        }

        if (toggles.gradient) toggles.gradient.checked = preset.gradient;
        if (toggles.bokeh) toggles.bokeh.checked = preset.bokeh;
        if (toggles.shimmer) toggles.shimmer.checked = preset.shimmer;
        if (toggles.grain) toggles.grain.checked = preset.grain;
        if (toggles.frosted) toggles.frosted.checked = preset.frosted;
        if (bokehCountInput) bokehCountInput.value = String(preset.bokeh_count);
    };

    const currentAccentColor = () => {
        if (colorSourceSelect?.value === "custom" && accentColorInput?.value) {
            return accentColorInput.value;
        }

        return previewCanvas?.dataset.storeColor || "#ff749f";
    };

    const syncBokehCountVisibility = () => {
        if (!bokehCountField || !bokehToggle) {
            return;
        }

        bokehCountField.classList.toggle("hide", !bokehToggle.checked);
    };

    const syncCustomColorVisibility = () => {
        customColorField?.classList.toggle("hide", colorSourceSelect?.value !== "custom");
    };

    const formatGradientHelp = (template, color) => {
        if (!template) {
            return "";
        }

        return template.replace(":color", color);
    };

    const syncGradientHelp = () => {
        if (!gradientHelp || !previewCanvas) {
            return;
        }

        const color = currentAccentColor();
        const template =
            colorSourceSelect?.value === "custom"
                ? previewCanvas.dataset.gradientHelpCustom
                : previewCanvas.dataset.gradientHelpStore;

        gradientHelp.textContent = formatGradientHelp(template, color);
    };

    const updatePreview = () => {
        if (!previewCanvas) {
            return;
        }

        const color = currentAccentColor();
        const state = {
            gradient: toggles.gradient?.checked ?? true,
            bokeh: toggles.bokeh?.checked ?? true,
            shimmer: toggles.shimmer?.checked ?? true,
            grain: toggles.grain?.checked ?? true,
            frosted: toggles.frosted?.checked ?? true,
        };

        previewCanvas.style.setProperty("--preview-brand", color);
        previewCanvas.classList.toggle("is-gradient-off", !state.gradient);
        previewCanvas.classList.toggle("is-bokeh-off", !state.bokeh);
        previewCanvas.classList.toggle("is-shimmer-off", !state.shimmer);
        previewCanvas.classList.toggle("is-grain-off", !state.grain);

        if (previewCard) {
            previewCard.classList.toggle("is-solid", !state.frosted);
        }
    };

    const syncPresetUi = () => {
        const custom = isCustomPreset();

        setCustomPanelVisible(custom);
        setToggleDisabled(custom);

        if (!custom && presetSelect?.value) {
            applyPresetToToggles(presetSelect.value);
        }

        syncBokehCountVisibility();
        syncCustomColorVisibility();
        syncGradientHelp();
        updatePreview();
    };

    presetSelect?.addEventListener("change", syncPresetUi);
    colorSourceSelect?.addEventListener("change", () => {
        syncCustomColorVisibility();
        syncGradientHelp();
        updatePreview();
    });
    accentColorInput?.addEventListener("input", () => {
        syncGradientHelp();
        updatePreview();
    });

    Object.values(toggles).forEach((toggle) => {
        toggle?.addEventListener("change", () => {
            syncBokehCountVisibility();
            updatePreview();
        });
    });

    bokehCountInput?.addEventListener("input", updatePreview);

    syncPresetUi();
})();
