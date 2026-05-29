function escapeHtml(text) {
    const element = document.createElement("div");

    element.textContent = text;

    return element.innerHTML;
}

function parseJsonDataset(element, key, fallback = []) {
    if (!element) {
        return fallback;
    }

    try {
        return JSON.parse(element.dataset[key] || "[]");
    } catch {
        return fallback;
    }
}

function debounce(fn, delay) {
    let timer;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

function normalizeProductLabel(value) {
    return String(value || "")
        .toUpperCase()
        .replace(/[^A-Z0-9]/g, "");
}

function shouldShowProductSku(name, sku) {
    if (!sku) {
        return false;
    }

    const normalizedName = normalizeProductLabel(name);
    const normalizedSku = normalizeProductLabel(sku);

    if (!normalizedSku) {
        return false;
    }

    // Hide when SKU is just an uppercase/underscore variant of the product name.
    return normalizedName !== normalizedSku;
}

function initReportProductAutocompletes() {
    document.querySelectorAll(".report-product-autocomplete").forEach((root) => {
        initReportProductAutocomplete(root);
    });
}

function initReportProductAutocomplete(root) {
    const requireCategory = root.dataset.requireCategory === "1";
    const categorySelector = root.dataset.categorySelector || "#category_id";
    const categorySelect = requireCategory
        ? document.querySelector(categorySelector)
        : null;
    const productsUrl = root.dataset.url;
    const optionsUrl = root.dataset.optionsUrl;
    const optionsTarget = root.dataset.optionsTarget
        ? document.querySelector(root.dataset.optionsTarget)
        : null;
    const input = root.querySelector(".report-product-autocomplete__input");
    const hiddenInput = root.querySelector(".report-product-autocomplete__value");
    const resultsEl = root.querySelector(".report-product-autocomplete__results");
    const help = root.querySelector(".report-product-category-help");
    const optionsContainer = optionsTarget?.querySelector(".sales-report-option-groups")
        || document.getElementById("sales-report-options");
    const optionsEmpty = optionsTarget?.querySelector(".sales-report-options-empty");
    const selectedOptionValues = parseJsonDataset(optionsTarget, "selectedOptionValues");
    const selectedVariationValues = parseJsonDataset(optionsTarget, "selectedVariationValues");
    const minChars = 1;

    if (!input || !hiddenInput || !resultsEl || (requireCategory && !categorySelect)) {
        return;
    }

    const skuOptionsSwap = root.dataset.skuOptionsSwap === "1";
    const skuFieldSelector = root.dataset.skuField || "#report-sku-field";
    const skuField = skuOptionsSwap ? document.querySelector(skuFieldSelector) : null;
    const skuInput = skuField?.querySelector('input[name="sku"]');

    function resetSkuOptionsSwap() {
        if (!skuOptionsSwap) {
            return;
        }

        skuField?.classList.remove("hide");

        if (optionsTarget) {
            optionsTarget.classList.add("hide");
            optionsTarget.classList.remove("is-visible");
        }
    }

    function updateSkuOptionsSwap(product) {
        if (!skuOptionsSwap) {
            return;
        }

        const isVirtual = Boolean(product?.is_virtual);

        if (isVirtual) {
            skuField?.classList.add("hide");

            if (skuInput) {
                skuInput.value = "";
            }

            optionsTarget?.classList.remove("hide");
        } else {
            skuField?.classList.remove("hide");
            clearOptionsUi();
            optionsTarget?.classList.add("hide");
        }
    }

    function clearProductSelection() {
        hiddenInput.value = "";
        clearOptionsUi();
        resetSkuOptionsSwap();
    }

    function clearOptionsUi() {
        if (!optionsContainer || !optionsTarget) {
            return;
        }

        optionsContainer.innerHTML = "";
        optionsTarget.classList.add("hide");
        optionsTarget.classList.remove("is-visible");
        optionsEmpty?.classList.add("hide");
    }

    function syncHiddenInputs(groupEl) {
        groupEl.querySelectorAll(".sales-report-option-badge").forEach((badge) => {
            const optionInput = groupEl.querySelector(
                `.sales-report-option-input[data-value-id="${badge.dataset.valueId}"]`
            );

            if (optionInput) {
                optionInput.disabled = !badge.classList.contains("is-active");
            }
        });
    }

    function renderOptionGroups(groups) {
        if (!optionsContainer || !optionsTarget) {
            return;
        }

        optionsContainer.innerHTML = "";

        if (!groups.length) {
            optionsTarget.classList.remove("hide");
            optionsTarget.classList.add("is-visible");
            optionsEmpty?.classList.remove("hide");

            return;
        }

        optionsEmpty?.classList.add("hide");
        optionsTarget.classList.remove("hide");
        optionsTarget.classList.add("is-visible");

        for (const group of groups) {
            const groupKey = `${group.type}-${group.id}`;
            const groupEl = document.createElement("div");

            groupEl.className = "sales-report-option-group";
            groupEl.dataset.groupKey = groupKey;

            const label = document.createElement("span");
            label.className = "sales-report-option-group__label";
            label.textContent = group.name;

            const badges = document.createElement("div");
            badges.className = "sales-report-option-badges";

            const preselected =
                group.type === "option"
                    ? selectedOptionValues.map(String)
                    : selectedVariationValues.map(String);

            for (const value of group.values) {
                const badge = document.createElement("button");
                badge.type = "button";
                badge.className = "sales-report-option-badge";
                badge.dataset.valueId = String(value.id);
                badge.textContent = value.label;

                const hiddenOptionInput = document.createElement("input");
                hiddenOptionInput.type = "hidden";
                hiddenOptionInput.className = "sales-report-option-input";
                hiddenOptionInput.dataset.valueId = String(value.id);
                hiddenOptionInput.name =
                    group.type === "option"
                        ? "option_value_ids[]"
                        : "variation_value_ids[]";
                hiddenOptionInput.value = String(value.id);
                hiddenOptionInput.disabled = true;

                if (preselected.includes(String(value.id))) {
                    badge.classList.add("is-active");
                }

                badge.addEventListener("click", () => {
                    badge.classList.toggle("is-active");
                    syncHiddenInputs(groupEl);
                });

                badges.appendChild(badge);
                badges.appendChild(hiddenOptionInput);
            }

            groupEl.appendChild(label);
            groupEl.appendChild(badges);
            optionsContainer.appendChild(groupEl);
            syncHiddenInputs(groupEl);
        }
    }

    async function loadProductOptions(productId) {
        if (!optionsUrl || !productId) {
            clearOptionsUi();
            resetSkuOptionsSwap();

            return;
        }

        try {
            const { data } = await axios.get(optionsUrl, {
                params: { product_id: productId },
            });

            if (skuOptionsSwap) {
                updateSkuOptionsSwap({ is_virtual: data.is_virtual });

                if (!data.is_virtual) {
                    return;
                }
            }

            renderOptionGroups(data.groups || []);
        } catch {
            clearOptionsUi();
        }
    }

    function setResultsOpen(isOpen) {
        root.classList.toggle("is-open", isOpen);
    }

    function hideResults() {
        resultsEl.hidden = true;
        resultsEl.innerHTML = "";
        setResultsOpen(false);
    }

    function showResults(items, total = items.length) {
        resultsEl.innerHTML = "";

        if (!items.length) {
            const empty = document.createElement("div");
            empty.className = "report-product-autocomplete__empty";
            empty.textContent =
                root.dataset.notFound
                || window.ReportProductAutocompleteLang?.notFound
                || "";
            resultsEl.appendChild(empty);
            resultsEl.hidden = false;
            setResultsOpen(true);

            return;
        }

        const meta = document.createElement("div");
        meta.className = "report-product-autocomplete__meta";
        const countTemplate =
            root.dataset.resultsCount
            || window.ReportProductAutocompleteLang?.resultsCount
            || "";
        if (countTemplate) {
            meta.textContent = countTemplate.replace(":count", String(total));
            resultsEl.appendChild(meta);
        }

        for (const product of items) {
            const button = document.createElement("button");
            button.type = "button";
            button.className = "report-product-autocomplete__item";
            button.setAttribute("role", "option");
            button.dataset.productId = String(product.id);

            const name = document.createElement("span");
            name.className = "report-product-autocomplete__item-name";
            name.textContent = product.name;
            button.appendChild(name);

            if (shouldShowProductSku(product.name, product.sku)) {
                const sku = document.createElement("span");
                sku.className = "report-product-autocomplete__item-sku";
                sku.textContent = product.sku;
                button.appendChild(sku);
            }

            button.addEventListener("mousedown", (event) => {
                event.preventDefault();
                selectProduct(product);
            });

            resultsEl.appendChild(button);
        }

        resultsEl.hidden = false;
        setResultsOpen(true);
    }

    function selectProduct(product) {
        input.value = product.name;
        input.dataset.selectedLabel = product.name;
        hiddenInput.value = String(product.id);
        hideResults();

        if (skuOptionsSwap) {
            updateSkuOptionsSwap(product);
        }

        if (optionsUrl) {
            loadProductOptions(product.id);
        }
    }

    function updateInputState() {
        if (requireCategory && !categorySelect.value) {
            input.value = "";
            input.disabled = true;
            help?.classList.remove("hide");
            clearProductSelection();
            hideResults();

            return;
        }

        input.disabled = false;
        help?.classList.add("hide");
    }

    async function searchProducts(term) {
        if (term.length < minChars) {
            hideResults();

            return;
        }

        const params = {
            query: term,
            limit: 1000,
        };

        if (requireCategory && categorySelect.value) {
            params.category_id = categorySelect.value;
        }

        try {
            const { data } = await axios.get(productsUrl, { params });
            const products = Array.isArray(data) ? data : data?.products || [];
            const total = Array.isArray(data) ? products.length : data?.total ?? products.length;

            showResults(products, total);
        } catch {
            hideResults();
        }
    }

    const debouncedSearch = debounce((term) => {
        searchProducts(term);
    }, 280);

    input.addEventListener("input", () => {
        const term = input.value.trim();

        if (term === "") {
            clearProductSelection();
            hideResults();

            return;
        }

        if (hiddenInput.value && term !== input.dataset.selectedLabel) {
            hiddenInput.value = "";
            clearOptionsUi();
            resetSkuOptionsSwap();
        }

        debouncedSearch(term);
    });

    input.addEventListener("focus", () => {
        const term = input.value.trim();

        if (term.length >= minChars) {
            debouncedSearch(term);
        }
    });

    input.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            hideResults();
        }
    });

    document.addEventListener("click", (event) => {
        if (!root.contains(event.target)) {
            hideResults();
        }
    });

    if (requireCategory) {
        categorySelect.addEventListener("change", () => {
            input.value = "";
            clearProductSelection();
            updateInputState();
            hideResults();
        });

        updateInputState();

        if (hiddenInput.value && input.value) {
            input.dataset.selectedLabel = input.value;

            if (optionsUrl) {
                loadProductOptions(hiddenInput.value);
            }
        }
    } else if (hiddenInput.value && input.value) {
        input.dataset.selectedLabel = input.value;

        if (optionsUrl) {
            loadProductOptions(hiddenInput.value);
        }
    }
}

document.addEventListener("DOMContentLoaded", initReportProductAutocompletes);
