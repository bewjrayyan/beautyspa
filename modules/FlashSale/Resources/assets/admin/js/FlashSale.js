import FlashSaleProduct from "./FlashSaleProduct";

export default class {
    constructor() {
        this.productCount = 0;

        this.addFlashSaleProducts(FleetCart.data["flash_sale.products"]);

        if (this.productCount === 0) {
            this.addProduct();
        }

        this.addFlashSaleProductsError(FleetCart.errors["flash_sale.products"]);

        this.attachEventListeners();
        this.makeProductPanelsSortable();
        this.refreshUiState();
    }

    addFlashSaleProducts(products) {
        for (let attributes of products) {
            this.addProduct(attributes);
        }
    }

    addProduct(attributes = {}) {
        let productTemplate = new FlashSaleProduct({
            productPanelNumber: this.productCount++,
            product: attributes,
        });

        $("#products-wrapper").append(productTemplate.render());

        window.admin.selectize();
        this.refreshUiState();
    }

    addFlashSaleProductsError(errors) {
        for (let key in errors) {
            let parent = this.getInputFieldForKey(key).closest(".flash-sale-item__field");

            parent.addClass("has-error");
            parent.append(`<span class="help-block">${errors[key][0]}</span>`);
        }
    }

    getInputFieldForKey(key) {
        let keyParts = key.split(".");

        keyParts = keyParts.map((k) => {
            return k.split("_").join("-");
        });

        return $(`#${keyParts.join("-")}`);
    }

    attachEventListeners() {
        $(document).on("click", ".add-product", (event) => {
            event.preventDefault();
            this.addProduct();
        });

        $(document).on("flash-sale:products-changed", () => {
            this.refreshUiState();
        });
    }

    makeProductPanelsSortable() {
        const wrapper = document.getElementById("products-wrapper");

        if (! wrapper) {
            return;
        }

        Sortable.create(wrapper, {
            handle: ".drag-handle",
            animation: 150,
            ghostClass: "sortable-ghost",
            onEnd: () => {
                this.reindexPanels();
                this.refreshUiState();
            },
        });
    }

    reindexPanels() {
        $("#products-wrapper .flash-sale-item").each((index, element) => {
            $(element).find(".flash-sale-item__index-num").text(index + 1);
        });
    }

    refreshUiState() {
        const $items = $("#products-wrapper .flash-sale-item");
        const count = $items.length;

        $("#flash-sale-products").toggleClass(
            "flash-sale-products--has-items",
            count > 0
        );

        $("#flash-sale-stat-treatments").text(count);

        let totalSold = 0;
        let unlimitedCount = 0;

        $items.each((_, element) => {
            const $el = $(element);
            const isVirtual = $el.data("isVirtual") == 1;
            const qty = parseInt($el.find(".flash-sale-qty-input").val(), 10) || 0;
            const soldText = $el.find(".flash-sale-item__sold").text();
            const soldMatch = soldText.match(/(\d+)/);

            if (soldMatch) {
                totalSold += parseInt(soldMatch[1], 10);
            }

            if (isVirtual && qty <= 0) {
                unlimitedCount++;
            }
        });

        $("#flash-sale-stat-sold").text(totalSold);
        $("#flash-sale-stat-unlimited").text(unlimitedCount);
    }
}
