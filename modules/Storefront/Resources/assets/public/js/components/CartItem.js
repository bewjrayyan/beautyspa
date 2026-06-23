import {
    formatCurrency,
    hasBaseImageMedia,
    placeholderImageUrl,
    resolveBaseImagePath,
    trans,
} from "../functions";

Alpine.data("CartItem", (cartItem) => ({
    controller: null,
    product: cartItem.product,
    item: cartItem.item || cartItem.variant || cartItem.product,
    qty: cartItem.qty,

    get productName() {
        return this.product.name;
    },

    get productUrl() {
        let url = AestheticCart.url(`/products/${this.product.slug}`);

        if (this.hasAnyVariant) {
            url += `?variant=${this.item.uid}`;
        }

        return url;
    },

    get unitPrice() {
        return cartItem.unitPrice.inCurrentCurrency.amount;
    },

    get sellingUnitPrice() {
        return (
            this.item.selling_price?.inCurrentCurrency?.amount ?? this.unitPrice
        );
    },

    get optionsUnitPrice() {
        return this.unitPrice - this.sellingUnitPrice;
    },

    get regularUnitPrice() {
        const basePrice =
            this.item.price?.inCurrentCurrency?.amount ?? this.unitPrice;

        return basePrice + this.optionsUnitPrice;
    },

    get hasSpecialPrice() {
        return (
            this.product.is_in_flash_sale ||
            (this.item.special_price != null &&
                this.regularUnitPrice > this.unitPrice)
        );
    },

    get unitSavings() {
        if (!this.hasSpecialPrice) {
            return 0;
        }

        return Math.max(0, this.regularUnitPrice - this.unitPrice);
    },

    get savingsPercent() {
        if (!this.hasSpecialPrice || this.regularUnitPrice <= 0) {
            return 0;
        }

        return Math.round((this.unitSavings / this.regularUnitPrice) * 100);
    },

    lineSavings(qty) {
        return this.unitSavings * qty;
    },

    savingsLabel(qty) {
        const amount = formatCurrency(this.lineSavings(qty));

        if (this.savingsPercent > 0) {
            return trans("storefront::cart.save_amount_with_percent", {
                amount,
                percent: this.savingsPercent,
            });
        }

        return trans("storefront::cart.save_amount", { amount });
    },

    get hasAnyVariation() {
        return Object.keys(cartItem.variations).length !== 0;
    },

    get variationsLength() {
        return Object.keys(cartItem.variations).length;
    },

    get hasAnyOption() {
        return Object.keys(cartItem.options).length !== 0;
    },

    get optionsLength() {
        return Object.keys(cartItem.options).length;
    },

    get hasAnyVariant() {
        return cartItem.variant != null;
    },

    get hasAnyMedia() {
        return (this.item.media?.length ?? 0) !== 0;
    },

    get hasBaseImage() {
        return (
            hasBaseImageMedia(this.item?.base_image) ||
            (this.hasAnyVariant && hasBaseImageMedia(this.product?.base_image))
        );
    },

    get baseImage() {
        return (
            resolveBaseImagePath(
                this.item,
                this.product,
                this.hasAnyVariant
            ) || placeholderImageUrl()
        );
    },

    isQtyIncreaseDisabled(cartItem) {
        return (
            this.maxQuantity(cartItem) !== null &&
            cartItem.qty >= cartItem.item.qty
        );
    },

    lineTotal(qty) {
        return qty * cartItem.unitPrice.inCurrentCurrency.amount;
    },

    lineRegularTotal(qty) {
        return qty * this.regularUnitPrice;
    },

    get summaryTreatmentLines() {
        return Object.values(cartItem.variations || {}).map((variation) => ({
            name: variation.name,
            value: variation.values?.[0]?.label ?? "",
        }));
    },

    optionValues(option) {
        let values = [];

        for (let value of option.values) {
            values.push(value.label);
        }

        return values.join(", ");
    },

    maxQuantity({ item }) {
        return item.is_in_stock && item.does_manage_stock ? item.qty : null;
    },

    exceedsMaxStock({ item, qty }) {
        return item.does_manage_stock && item.qty < qty;
    },

    changeQuantity(cartItem, qty) {
        if (isNaN(qty) || qty < 1) {
            qty = 1;

            this.updateCart(cartItem, qty);

            return;
        }

        cartItem.qty = qty;

        if (this.exceedsMaxStock(cartItem)) {
            qty = cartItem.item.qty;

            this.updateCart(cartItem, qty);

            return;
        }

        this.updateCart(cartItem, qty);
    },

    updateQuantity(cartItem, qty) {
        if (isNaN(qty) || qty < 1) {
            qty = 1;
            cartItem.qty = 1;

            return;
        }

        cartItem.qty = qty;

        if (this.exceedsMaxStock(cartItem)) {
            cartItem.qty = cartItem.item.qty;

            this.updateCart(cartItem, cartItem.qty);

            return;
        }

        this.updateCart(cartItem, qty);
    },

    async updateCart(cartItem, qty) {
        if (this.controller) {
            this.controller.abort();
        }

        this.controller = new AbortController();

        try {
            const { data } = await axios.put(
                `/cart/items/${cartItem.id}`,
                {
                    qty: qty || 1,
                },
                {
                    signal: this.controller.signal,
                }
            );

            this.qty = data.items[cartItem.id].qty;
            this.$store.cart.updateCart(data);
        } catch (error) {
            if (error.code !== "ERR_CANCELED") {
                // revert cart item quantity on error
                this.$store.cart.updateCartItemQty({
                    id: cartItem.id,
                    qty: this.qty,
                });

                notify(trans("storefront::storefront.something_went_wrong"));
            }
        }
    },

    removeCartItem() {
        this.$store.cart.removeCartItem(cartItem.id);

        axios.delete(`/cart/items/${cartItem.id}`).then((response) => {
            this.$store.cart.updateCart(response.data);
        });
    },
}));
