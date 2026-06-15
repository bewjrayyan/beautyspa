function escapeHtml(value) {
    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

function initManualBookingProducts(form, catalog = []) {
    const root = form.querySelector(".tr-manual-booking-treatment");

    if (!root || root.dataset.productsBound === "1") {
        return root?._manualBookingProducts || null;
    }

    root.dataset.productsBound = "1";

    const state = {
        catalog: Array.isArray(catalog) ? catalog : [],
        selectedProductId: "",
        options: {},
        variations: {},
        receiptFile: null,
        existingReceiptUrl: "",
    };

    const productIdInput = root.querySelector('[name="product_id"]');
    const searchInput = root.querySelector(".tr-manual-booking-products__search");
    const listRoot = root.querySelector(".tr-manual-booking-products__list");
    const configRoot = root.querySelector(".tr-manual-booking-products__config");
    const priceRoot = root.querySelector(".tr-manual-booking-products__price");
    const paymentStatusInput = root.querySelector('[name="payment_status"]');
    const receiptInput = root.querySelector('[name="payment_receipt"]');
    const receiptPreview = root.querySelector(".tr-manual-booking-receipt__preview");
    const defaultPaymentStatus = "deposit";

    const getSelectedProduct = () =>
        state.catalog.find((product) => String(product.id) === String(state.selectedProductId)) || null;

    const renderProductList = (query = "") => {
        if (!listRoot) {
            return;
        }

        const needle = query.trim().toLowerCase();
        const items = state.catalog.filter((product) => {
            if (!needle) {
                return true;
            }

            return String(product.name || "").toLowerCase().includes(needle);
        });

        if (items.length === 0) {
            listRoot.innerHTML = `<p class="tr-manual-booking-products__empty">${root.dataset.emptyProducts || "No treatments found."}</p>`;

            return;
        }

        listRoot.innerHTML = items
            .map((product) => {
                const isSelected = String(product.id) === String(state.selectedProductId);
                const meta = [];

                if (product.has_variations) {
                    meta.push(root.dataset.hasVariationsLabel || "Variants");
                }

                if (product.has_options) {
                    meta.push(root.dataset.hasOptionsLabel || "Options");
                }

                return `
                    <button
                        type="button"
                        class="tr-manual-booking-product-card${isSelected ? " is-selected" : ""}"
                        data-product-id="${product.id}"
                    >
                        <span class="tr-manual-booking-product-card__name">${escapeHtml(product.name)}</span>
                        <span class="tr-manual-booking-product-card__price">${product.formatted_price || ""}</span>
                        ${meta.length ? `<span class="tr-manual-booking-product-card__meta">${escapeHtml(meta.join(" · "))}</span>` : ""}
                    </button>
                `;
            })
            .join("");

        listRoot.querySelectorAll(".tr-manual-booking-product-card").forEach((button) => {
            button.addEventListener("click", () => {
                selectProduct(button.dataset.productId || "");
            });
        });
    };

    const renderOptionField = (option) => {
        const requiredMark = option.is_required ? '<span class="tr-manual-booking-products__required">*</span>' : "";
        const fieldName = `options[${option.id}]`;
        const currentValue = state.options[option.id] ?? "";

        if (["dropdown", "radio", "radio_custom"].includes(option.type)) {
            if (option.type === "radio" || option.type === "radio_custom") {
                return `
                    <div class="tr-manual-booking-products__field">
                        <label>${escapeHtml(option.name)}${requiredMark}</label>
                        <div class="tr-manual-booking-products__choices">
                            ${option.values
                                .map(
                                    (value) => `
                                        <label class="tr-manual-booking-products__choice">
                                            <input
                                                type="radio"
                                                name="${fieldName}"
                                                value="${value.id}"
                                                ${String(currentValue) === String(value.id) ? "checked" : ""}
                                            >
                                            <span>${escapeHtml(value.label)}</span>
                                        </label>
                                    `
                                )
                                .join("")}
                        </div>
                    </div>
                `;
            }

            return `
                <div class="tr-manual-booking-products__field">
                    <label for="tr-manual-booking-option-${option.id}">${escapeHtml(option.name)}${requiredMark}</label>
                    <select class="form-control" id="tr-manual-booking-option-${option.id}" name="${fieldName}">
                        <option value="">${root.dataset.chooseOption || "Choose an option"}</option>
                        ${option.values
                            .map(
                                (value) => `
                                    <option value="${value.id}" ${String(currentValue) === String(value.id) ? "selected" : ""}>
                                        ${escapeHtml(value.label)}
                                    </option>
                                `
                            )
                            .join("")}
                    </select>
                </div>
            `;
        }

        if (["checkbox", "checkbox_custom", "multiple_select"].includes(option.type)) {
            const selectedValues = Array.isArray(currentValue)
                ? currentValue.map(String)
                : currentValue
                  ? [String(currentValue)]
                  : [];

            return `
                <div class="tr-manual-booking-products__field">
                    <label>${escapeHtml(option.name)}${requiredMark}</label>
                    <div class="tr-manual-booking-products__choices">
                        ${option.values
                            .map(
                                (value) => `
                                    <label class="tr-manual-booking-products__choice">
                                        <input
                                            type="checkbox"
                                            name="${fieldName}${option.type === "multiple_select" ? "[]" : ""}"
                                            value="${value.id}"
                                            ${selectedValues.includes(String(value.id)) ? "checked" : ""}
                                        >
                                        <span>${escapeHtml(value.label)}</span>
                                    </label>
                                `
                            )
                            .join("")}
                    </div>
                </div>
            `;
        }

        if (option.type === "textarea") {
            return `
                <div class="tr-manual-booking-products__field">
                    <label for="tr-manual-booking-option-${option.id}">${escapeHtml(option.name)}${requiredMark}</label>
                    <textarea class="form-control" id="tr-manual-booking-option-${option.id}" name="${fieldName}" rows="3">${escapeHtml(currentValue)}</textarea>
                </div>
            `;
        }

        return `
            <div class="tr-manual-booking-products__field">
                <label for="tr-manual-booking-option-${option.id}">${escapeHtml(option.name)}${requiredMark}</label>
                <input
                    type="text"
                    class="form-control"
                    id="tr-manual-booking-option-${option.id}"
                    name="${fieldName}"
                    value="${escapeHtml(currentValue)}"
                >
            </div>
        `;
    };

    const renderVariationField = (variation) => {
        const currentValue = state.variations[variation.uid] ?? "";

        return `
            <div class="tr-manual-booking-products__field">
                <label>${escapeHtml(variation.name)} <span class="tr-manual-booking-products__required">*</span></label>
                <div class="tr-manual-booking-products__choices tr-manual-booking-products__choices--wrap">
                    ${variation.values
                        .map(
                            (value) => `
                                <button
                                    type="button"
                                    class="tr-manual-booking-products__variation${currentValue === value.uid ? " is-selected" : ""}"
                                    data-variation-uid="${variation.uid}"
                                    data-value-uid="${value.uid}"
                                >
                                    ${escapeHtml(value.label)}
                                </button>
                            `
                        )
                        .join("")}
                </div>
            </div>
        `;
    };

    const syncConfigFromDom = () => {
        if (!configRoot) {
            return;
        }

        const options = {};

        configRoot.querySelectorAll("[name^='options']").forEach((field) => {
            const match = field.name.match(/^options\[(\d+)\](\[\])?$/);

            if (!match) {
                return;
            }

            const optionId = match[1];

            if (field.type === "checkbox") {
                if (!options[optionId]) {
                    options[optionId] = [];
                }

                if (field.checked) {
                    options[optionId].push(field.value);
                }

                return;
            }

            if (field.type === "radio") {
                if (field.checked) {
                    options[optionId] = field.value;
                }

                return;
            }

            options[optionId] = field.value;
        });

        state.options = options;
    };

    const updatePrice = () => {
        if (!priceRoot) {
            return;
        }

        const product = getSelectedProduct();

        if (!product) {
            priceRoot.hidden = true;
            priceRoot.textContent = "";

            return;
        }

        let label = product.formatted_price || "";
        const selectedUids = Object.values(state.variations).filter(Boolean);

        if (product.has_variants && selectedUids.length) {
            const selectedKey = selectedUids.join(".");
            const variant = product.variants.find((item) => item.uids === selectedKey);

            if (variant?.formatted_price) {
                label = variant.formatted_price;
            }
        }

        priceRoot.hidden = false;
        priceRoot.innerHTML = label;
    };

    const renderProductConfig = () => {
        if (!configRoot) {
            return;
        }

        const product = getSelectedProduct();

        if (!product) {
            configRoot.innerHTML = `<p class="tr-manual-booking-products__placeholder">${root.dataset.selectProduct || "Select a treatment to configure options."}</p>`;
            updatePrice();

            return;
        }

        const sections = [];

        if (product.has_variations && product.variations.length) {
            sections.push(`
                <div class="tr-manual-booking-products__group">
                    <h6>${root.dataset.variationsLabel || "Variants"}</h6>
                    ${product.variations.map((variation) => renderVariationField(variation)).join("")}
                </div>
            `);
        }

        if (product.has_options && product.options.length) {
            sections.push(`
                <div class="tr-manual-booking-products__group">
                    <h6>${root.dataset.optionsLabel || "Options"}</h6>
                    ${product.options.map((option) => renderOptionField(option)).join("")}
                </div>
            `);
        }

        if (!sections.length) {
            sections.push(`<p class="tr-manual-booking-products__placeholder">${root.dataset.noConfigNeeded || "No extra options for this treatment."}</p>`);
        }

        configRoot.innerHTML = sections.join("");

        configRoot.querySelectorAll(".tr-manual-booking-products__variation").forEach((button) => {
            button.addEventListener("click", () => {
                state.variations[button.dataset.variationUid || ""] = button.dataset.valueUid || "";
                renderProductConfig();
            });
        });

        configRoot.querySelectorAll("select, input, textarea").forEach((field) => {
            field.addEventListener("change", () => {
                syncConfigFromDom();
                updatePrice();
            });

            field.addEventListener("input", syncConfigFromDom);
        });

        updatePrice();
    };

    const selectProduct = (productId, preserveSelection = false) => {
        state.selectedProductId = String(productId || "");

        if (productIdInput) {
            productIdInput.value = state.selectedProductId;
        }

        if (!preserveSelection) {
            state.options = {};
            state.variations = {};
        }

        renderProductList(searchInput?.value || "");
        renderProductConfig();
    };

    const renderReceiptPreview = () => {
        if (!receiptPreview) {
            return;
        }

        if (state.receiptFile) {
            receiptPreview.hidden = false;
            receiptPreview.innerHTML = `<span><i class="fa fa-paperclip"></i> ${escapeHtml(state.receiptFile.name)}</span>`;

            return;
        }

        if (state.existingReceiptUrl) {
            receiptPreview.hidden = false;
            receiptPreview.innerHTML = `
                <a href="${escapeHtml(state.existingReceiptUrl)}" target="_blank" rel="noopener">
                    <i class="fa fa-file-image-o"></i> ${root.dataset.viewReceipt || "View current receipt"}
                </a>
            `;

            return;
        }

        receiptPreview.hidden = true;
        receiptPreview.innerHTML = "";
    };

    const reset = () => {
        state.selectedProductId = "";
        state.options = {};
        state.variations = {};
        state.receiptFile = null;
        state.existingReceiptUrl = "";

        if (productIdInput) {
            productIdInput.value = "";
        }

        if (paymentStatusInput) {
            paymentStatusInput.value = defaultPaymentStatus;
        }

        if (receiptInput) {
            receiptInput.value = "";
        }

        renderProductList("");
        renderProductConfig();
        renderReceiptPreview();
    };

    const fillFromBooking = (booking = {}) => {
        selectProduct(booking.product_id || "", true);
        state.options = { ...(booking.product_options || {}) };
        state.variations = { ...(booking.product_variations || {}) };

        if (paymentStatusInput) {
            paymentStatusInput.value = booking.payment_status || defaultPaymentStatus;
        }

        state.existingReceiptUrl = booking.payment_receipt_url || "";
        state.receiptFile = null;

        if (receiptInput) {
            receiptInput.value = "";
        }

        renderProductConfig();
        renderReceiptPreview();
    };

    const validate = () => {
        const product = getSelectedProduct();

        if (!product) {
            return root.dataset.productRequired || "Please select a treatment.";
        }

        syncConfigFromDom();

        for (const variation of product.variations || []) {
            if (!state.variations[variation.uid]) {
                return `${variation.name}: ${root.dataset.optionRequired || "This field is required."}`;
            }
        }

        for (const option of product.options || []) {
            const value = state.options[option.id];

            if (!option.is_required) {
                continue;
            }

            if (value === undefined || value === null || value === "" || (Array.isArray(value) && value.length === 0)) {
                return `${option.name}: ${root.dataset.optionRequired || "This field is required."}`;
            }
        }

        return "";
    };

    const appendToFormData = (formData) => {
        syncConfigFromDom();

        if (state.selectedProductId) {
            formData.set("product_id", state.selectedProductId);
        }

        Object.entries(state.options).forEach(([optionId, value]) => {
            if (Array.isArray(value)) {
                value.forEach((entry) => formData.append(`options[${optionId}][]`, entry));

                return;
            }

            formData.append(`options[${optionId}]`, value);
        });

        Object.entries(state.variations).forEach(([variationUid, valueUid]) => {
            formData.append(`variations[${variationUid}]`, valueUid);
        });

        if (paymentStatusInput?.value) {
            formData.set("payment_status", paymentStatusInput.value);
        }

        if (state.receiptFile) {
            formData.set("payment_receipt", state.receiptFile);
        }
    };

    searchInput?.addEventListener("input", (event) => renderProductList(event.target.value));
    receiptInput?.addEventListener("change", (event) => {
        state.receiptFile = event.target.files?.[0] || null;
        renderReceiptPreview();
    });

    renderProductList();
    renderProductConfig();
    renderReceiptPreview();

    root._manualBookingProducts = { reset, fillFromBooking, validate, appendToFormData, selectProduct };

    return root._manualBookingProducts;
}

export { initManualBookingProducts };
