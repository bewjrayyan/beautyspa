import { runDeferred, runAfterPaint } from "../support/scheduleInit";

Alpine.store("wishlist", {
    wishlist: [],
    fetching: true,

    get count() {
        return this.fetching ? AestheticCart.wishlistCount : this.wishlist.length;
    },

    init() {
        runDeferred(() => this.fetchWishlist(), 800);
    },

    async fetchWishlist() {
        if (AestheticCart.loggedIn) {
            try {
                this.fetching = true;

                const { data } = await axios.get(
                    "/account/wishlist/products/list"
                );

                runAfterPaint(() => {
                    this.wishlist = data;
                });
            } catch (error) {
                // Handle error
            } finally {
                this.fetching = false;
            }

            return;
        }

        this.fetching = false;
    },

    inWishlist(id) {
        return this.wishlist.includes(id);
    },

    syncWishlist(id) {
        if (this.inWishlist(id)) {
            this.removeFromWishlist(id);

            return;
        }

        this.addToWishlist(id);
    },

    async addToWishlist(id) {
        if (AestheticCart.loggedIn) {
            this.wishlist.push(id);

            await axios.post("/account/wishlist/products", {
                productId: id,
            });

            return;
        }

        window.location.href = "/login";
    },

    removeFromWishlist(id) {
        this.wishlist.splice(this.wishlist.indexOf(id), 1);

        axios.delete(`/account/wishlist/products/${id}`);
    },
});
