function initStampProgramProductPicker() {
    const root = document.getElementById("stamp-program-products");

    if (!root || root.dataset.bound === "1") {
        return;
    }

    root.dataset.bound = "1";

    const searchUrl = root.dataset.searchUrl;
    const categoryProductsUrl = root.dataset.categoryProductsUrl;
    const filterSelect = document.getElementById("product_filter_category_id");
    const addCategoryBtn = document.getElementById("stamp-program-add-category-products");
    const $productsSelect = $("#eligible_product_ids");
    const $categoriesSelect = $("#eligible_category_ids");

    if (!$productsSelect.length) {
        return;
    }

    if ($categoriesSelect.length && !$categoriesSelect[0].selectize) {
        $categoriesSelect.removeClass("form-control custom-select-black");
        $categoriesSelect.selectize({
            plugins: ["remove_button"],
            persist: false,
            create: false,
        });
    }

    const baseOptions = _.merge(
        {
            valueField: "id",
            labelField: "name",
            searchField: "name",
            plugins: ["remove_button"],
            persist: false,
            create: false,
            hideSelected: true,
            onItemAdd(value) {
                this.getItem(value)[0].innerHTML = this.getItem(value)[0].innerHTML.replace(/¦––\s/g, "");
            },
        },
        ...(AestheticCart.selectize ?? [])
    );

    if ($productsSelect[0].selectize) {
        $productsSelect[0].selectize.destroy();
    }

    $productsSelect.removeClass("form-control custom-select-black");

    const productSelectize = $productsSelect.selectize(
        _.merge(baseOptions, {
            load(query, callback) {
                const categoryId = filterSelect?.value || "";

                if (!query.length && !categoryId) {
                    callback();

                    return;
                }

                axios
                    .get(searchUrl, {
                        params: {
                            query,
                            category_id: categoryId || undefined,
                            limit: 50,
                        },
                    })
                    .then((response) => callback(response.data))
                    .catch(() => callback());
            },
        })
    )[0].selectize;

    filterSelect?.addEventListener("change", () => {
        productSelectize.clearOptions();
        productSelectize.refreshOptions(false);
    });

    addCategoryBtn?.addEventListener("click", async () => {
        const categoryId = filterSelect?.value || "";

        if (!categoryId) {
            window.alert(addCategoryBtn.dataset.needCategory);

            return;
        }

        addCategoryBtn.disabled = true;

        try {
            const response = await axios.get(`${categoryProductsUrl}/${categoryId}/products`);
            const products = response.data || [];

            products.forEach((product) => {
                if (!product?.id) {
                    return;
                }

                productSelectize.addOption(product);
                productSelectize.addItem(String(product.id), true);
            });
        } catch {
            window.alert(addCategoryBtn.dataset.loadFailed);
        } finally {
            addCategoryBtn.disabled = false;
        }
    });
}

document.addEventListener("DOMContentLoaded", initStampProgramProductPicker);

export {};
