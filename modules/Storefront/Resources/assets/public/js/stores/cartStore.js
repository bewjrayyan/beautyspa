import { runWhenIdle, runAfterPaint } from "../support/scheduleInit";

Alpine.store("cart", {
    cart: {
        items: {},
        availableShippingMethods: {},
        coupon: {},
        loyalty: {},
        quantity: 0,
        shippingCost: {},
        shippingMethodName: null,
        subTotal: {},
        taxes: [],
        total: [],
    },
    loading: false,
    fetching: false,
    fetched: false,

    get items() {
        return this.normalizeItems(this.cart?.items);
    },

    get quantity() {
        if (!this.fetched) {
            return AestheticCart?.cartQuantity ?? 0;
        }

        return Object.values(this.items).length;
    },

    get isEmpty() {
        return Object.keys(this.items).length === 0;
    },

    get shippingCost() {
        return this.cart.shippingCost?.inCurrentCurrency?.amount || 0;
    },

    get taxTotal() {
        const taxes = this.cart.taxes ?? [];

        return Object.values(taxes).reduce((accumulator, tax) => {
            return accumulator + tax.amount.inCurrentCurrency.amount;
        }, 0);
    },

    get subTotal() {
        return Object.values(this.items).reduce((accumulator, cartItem) => {
            return (
                accumulator +
                cartItem.qty * cartItem.unitPrice.inCurrentCurrency.amount
            );
        }, 0);
    },

    get total() {
        return (
            this.subTotal -
            this.couponValue -
            this.loyaltyValue +
            this.taxTotal +
            this.shippingCost
        );
    },

    get hasCoupon() {
        return Boolean(this.cart.coupon?.code);
    },

    get couponValue() {
        return this.cart.coupon?.value?.inCurrentCurrency?.amount ?? 0;
    },

    get hasLoyalty() {
        return Boolean(this.cart.loyalty?.points);
    },

    get loyaltyValue() {
        return this.cart.loyalty?.value?.inCurrentCurrency?.amount ?? 0;
    },

    init() {
        runWhenIdle(() => this.fetchingCart());
    },

    async fetchingCart() {
        try {
            this.fetching = true;

            const { data } = await axios.get(AestheticCart.url("/cart/get"));

            runAfterPaint(() => {
                this.cart = this.normalizeCart(data);
            });
        } catch (error) {
            // Handle error
        } finally {
            this.fetching = false;
            this.fetched = true;
        }
    },

    updateCart(cart) {
        this.cart = this.normalizeCart(cart);

        this.setCoupon(this.cart);
        this.setLoyalty(this.cart);
    },

    updateCartItemQty({ id, qty }) {
        this.cart.items[id].qty = qty;
    },

    removeCartItem(id) {
        delete this.cart.items[id];
    },

    clearCart() {
        this.cart = this.normalizeCart({ items: {} });
    },

    normalizeCart(cart) {
        if (!cart || typeof cart !== "object") {
            return this.cart;
        }

        return {
            ...this.cart,
            ...cart,
            items: this.normalizeItems(cart.items),
            taxes: cart.taxes ?? this.cart.taxes ?? [],
            coupon: cart.coupon ?? {},
            loyalty: cart.loyalty ?? {},
        };
    },

    normalizeItems(items) {
        if (!items || typeof items !== "object") {
            return {};
        }

        if (Array.isArray(items)) {
            return items.reduce((accumulator, item) => {
                if (item?.id != null) {
                    accumulator[item.id] = item;
                }

                return accumulator;
            }, {});
        }

        return items;
    },

    setCoupon(cart) {
        if (cart.coupon?.code) {
            this.cart.coupon = cart.coupon;
        } else {
            this.cart.coupon = {};
        }
    },

    setLoyalty(cart) {
        if (cart.loyalty?.points) {
            this.cart.loyalty = cart.loyalty;
        } else {
            this.cart.loyalty = {};
        }
    },
});
