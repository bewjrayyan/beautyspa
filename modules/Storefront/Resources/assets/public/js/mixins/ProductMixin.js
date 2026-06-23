import {
    formatCurrency,
    hasBaseImageMedia,
    placeholderImageUrl,
    resolveBaseImagePath,
    trans,
} from "../functions";

export default function (product) {
    return {
        product: product,
        item: product.variant || product,
        addingToCart: false,

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

        get hasPriceRange() {
            return Boolean(this.product.formatted_price_range);
        },

        get priceLabel() {
            return this.hasPriceRange
                ? trans("storefront::product_card.price_range")
                : trans("storefront::product_card.normal_price");
        },

        get productPrice() {
            const price = this.hasPriceRange
                ? `<span class="previous-price">${this.product.formatted_price_range}</span>`
                : this.item.formatted_price;

            return `<span class="product-price-label">${this.priceLabel}</span>${price}`;
        },

        get displayPrice() {
            if (this.hasPriceRange) {
                return this.product.formatted_price_range;
            }

            return formatCurrency(this.regularPrice);
        },

        get regularPrice() {
            return this.item.price.inCurrentCurrency.amount;
        },

        get hasSpecialPrice() {
            return this.item.special_price !== null;
        },

        get hasPercentageSpecialPrice() {
            return this.item.has_percentage_special_price;
        },

        get specialPrice() {
            return this.item.selling_price.inCurrentCurrency.amount;
        },

        get specialPricePercent() {
            return Math.round(
                ((this.regularPrice - this.specialPrice) / this.regularPrice) *
                    100
            );
        },

        get hasAnyVariant() {
            return this.product.variant !== null;
        },

        get hasAnyOption() {
            return this.product.options_count > 0;
        },

        get hasNoOption() {
            return !this.hasAnyOption;
        },

        get hasAnyMedia() {
            return this.item.media.length !== 0;
        },

        get hasBaseImage() {
            return (
                hasBaseImageMedia(this.item?.base_image) ||
                (this.hasAnyVariant &&
                    hasBaseImageMedia(this.product?.base_image))
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

        get baseImageSrcset() {
            if (!this.hasBaseImage) {
                return "";
            }

            return (
                this.item.base_image.srcset ||
                this.product.base_image.srcset ||
                ""
            );
        },

        get isInStock() {
            return this.item.is_in_stock;
        },

        get isOutOfStock() {
            return this.item.is_out_of_stock;
        },

        get isVirtualTreatment() {
            return Boolean(this.product.is_virtual);
        },

        get showTreatmentBadge() {
            return (
                this.isOutOfStock &&
                this.isVirtualTreatment &&
                this.hasPriceRange
            );
        },

        get showOutOfStockBadge() {
            return this.isOutOfStock && !this.showTreatmentBadge;
        },

        get doesManageStock() {
            return this.item.does_manage_stock;
        },

        get isNew() {
            return !this.isOutOfStock && this.product.is_new;
        },

        syncWishlist() {
            this.$store.wishlist.syncWishlist(this.product.id);
        },

        syncCompareList() {
            this.$store.compare.syncCompareList(this.product.id);
        },

        addToCart() {
            if (this.addingToCart) {
                return;
            }

            this.addingToCart = true;

            let url = `/cart/items?product_id=${this.product.id}&qty=${1}`;

            if (this.hasAnyVariant) {
                url += `&variant_id=${this.item.id}`;
            }

            axios
                .post(url)
                .then((response) => {
                    this.$store.cart.updateCart(response.data);
                    this.$store.layout.openSidebarCart();
                })
                .catch((error) => {
                    notify(error.response.data.message);
                })
                .finally(() => {
                    this.addingToCart = false;
                });
        },
    };
}
