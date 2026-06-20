import { Manipulation, Pagination, Navigation, Thumbs } from "swiper/modules";
import md5 from "blueimp-md5";
import Swiper from "swiper";
import Drift from "drift-zoom";
import GLightbox from "glightbox";
import Errors from "../../../components/Errors";
import { formatCurrency } from "../../../functions";
import {
    productSliderNavigation,
    resolveProductSliderControls,
} from "../../../support/productSliderPagination";
import { wrapProductSliderOptions } from "../../../support/productSliderControlActions";
import { productSliderStateMixin } from "../../../support/productSliderStateMixin";
import "../../../components/ProductRating";
import "../../../components/Pagination";
import "../../../components/ProductCard";

let galleryPreviewSlider;
let galleryPreviewLightbox;
let galleryPreviewZoomInstances = [];

Alpine.data(
    "ProductShow",
    ({ product, variant, reviewCount, avgRating, flashSalePrice, reviewerName = "", whatsAppShareMessage = "" }) => ({
        product: product,
        item: variant || product,
        whatsAppShareMessage,
        optionPrices: {},
        addingToCart: false,
        oldMediaLength: null,
        activeVariationValues: {},
        variationImagePath: null,
        showDescriptionContent: false,
        showMore: false,
        fetchingReviews: false,
        reviews: {},
        reviewCount,
        avgRating,
        addingNewReview: false,
        reviewerName,
        reviewForm: {
            reviewer_name: reviewerName,
        },
        currentPage: 1,
        cartItemForm: {
            product_id: product.id,
            qty: 1,
            variations: {},
            options: {},
        },
        errors: new Errors(),
        relatedProductsSwiper: null,
        loading: false,
        gallerySlideIndex: 1,
        gallerySlideTotal: 1,
        openVariationUid: null,

        ...productSliderStateMixin(function () {
            return this.relatedProductsSwiper;
        }),

        get productName() {
            return this.product.name;
        },

        get isActiveItem() {
            return this.item.is_active === true;
        },

        get productUrl() {
            let url = AestheticCart.url(`/products/${this.product.slug}`);

            if (this.isVariantSelectionComplete && this.item?.uid) {
                url += `?variant=${this.item.uid}`;
            }

            return url;
        },

        get whatsAppShareUrl() {
            return `https://api.whatsapp.com/send?text=${encodeURIComponent(
                this.buildWhatsAppShareMessage()
            )}`;
        },

        buildWhatsAppShareMessage() {
            const template = this.whatsAppShareMessage?.trim();
            const productName = this.productName;
            const productUrl = this.productUrl;
            const description = this.stripHtml(
                this.product.meta?.meta_description ||
                    this.product.short_description ||
                    ""
            ).trim();

            if (template) {
                return template
                    .replace(/\{product_name\}/g, productName)
                    .replace(/\{product_url\}/g, productUrl)
                    .replace(/\{product_description\}/g, description)
                    .replace(/\{product_id\}/g, String(this.product.id))
                    .replace(/\{product_slug\}/g, this.product.slug);
            }

            const parts = [productName];

            if (description) {
                parts.push(description.substring(0, 200));
            }

            parts.push(productUrl);

            return parts.join("\n\n");
        },

        stripHtml(html) {
            if (!html) {
                return "";
            }

            const element = document.createElement("div");
            element.innerHTML = html;

            return (element.textContent || element.innerText || "").replace(
                /\s+/g,
                " "
            );
        },

        get hasAnyMedia() {
            return (
                (this.item.media?.length ?? 0) > 0 ||
                Boolean(this.item.base_image?.path)
            );
        },

        get productPrice() {
            return this.hasSpecialPrice
                ? this.item.selling_price.inCurrentCurrency.amount
                : this.item.price.inCurrentCurrency.amount;
        },

        get regularPrice() {
            let productPrice = this.item.price.inCurrentCurrency.amount;

            if (
                this.hasAnyOption &&
                !this.hasSpecialPrice &&
                this.hasAnyOptionPrice
            ) {
                return productPrice + this.optionsPrice;
            }

            return productPrice;
        },

        get hasSpecialPrice() {
            return (
                this.product.is_in_flash_sale ||
                this.item.special_price !== null
            );
        },

        get hasPercentageSpecialPrice() {
            return this.item.has_percentage_special_price;
        },

        get specialPrice() {
            let productPrice = this.item.selling_price.inCurrentCurrency.amount;

            if (flashSalePrice && !this.hasAnyVariant) {
                productPrice = flashSalePrice;
            }

            if (
                this.hasAnyOption &&
                this.hasSpecialPrice &&
                this.hasAnyOptionPrice
            ) {
                return productPrice + this.optionsPrice;
            }

            return productPrice;
        },

        get isInStock() {
            return this.item.is_in_stock;
        },

        get isOutOfStock() {
            return this.item.is_out_of_stock;
        },

        get doesManageStock() {
            return this.item.does_manage_stock;
        },

        get hasAnyVariationImage() {
            return this.variationImagePath !== null;
        },

        get inWishlist() {
            return this.$store.wishlist.inWishlist(this.product.id);
        },

        get inCompareList() {
            return this.$store.compare.inCompareList(this.product.id);
        },

        get hasVariants() {
            return (
                Array.isArray(this.product.variants) &&
                this.product.variants.length > 0
            );
        },

        get hasPreselectedVariant() {
            return this.product.variant !== null;
        },

        get hasAnyVariant() {
            return this.hasVariants;
        },

        get isVariantSelectionComplete() {
            if (!this.hasVariants) {
                return true;
            }

            if (this.product.variations.length === 0) {
                return Boolean(this.item.id);
            }

            const selectedCount = Object.keys(
                this.cartItemForm.variations
            ).length;

            if (selectedCount !== this.product.variations.length) {
                return false;
            }

            const selectedUids = Object.values(this.cartItemForm.variations)
                .sort()
                .join(".");

            return this.product.variants.some(
                (variant) => variant.uids === selectedUids
            );
        },

        get hasAnyOption() {
            return this.product.options.length > 0;
        },

        get hasAnyOptionPrice() {
            return Object.keys(this.optionPrices).length !== 0;
        },

        get optionsPrice() {
            return Object.values(this.optionPrices).reduce(
                (total, value) => total + value,
                0
            );
        },

        get isAddToCartDisabled() {
            if (this.hasVariants && !this.isVariantSelectionComplete) {
                return true;
            }

            return this.isActiveItem ? this.isOutOfStock : true;
        },

        get maxQuantity() {
            return this.isInStock && this.doesManageStock
                ? this.item.qty
                : null;
        },

        get isQtyIncreaseDisabled() {
            return (
                this.isOutOfStock ||
                (this.maxQuantity !== null &&
                    this.cartItemForm.qty >= this.item.qty) ||
                !this.isActiveItem
            );
        },

        get isQtyDecreaseDisabled() {
            return (
                this.isOutOfStock ||
                this.cartItemForm.qty <= 1 ||
                !this.isActiveItem
            );
        },

        get totalReviews() {
            if (!this.reviews.total) {
                return this.reviewCount;
            }

            return this.reviews.total;
        },

        get ratingPercent() {
            return (this.avgRating / 5) * 100;
        },

        get emptyReviews() {
            return this.totalReviews === 0;
        },

        get totalPage() {
            return Math.ceil(this.reviews.total / 5);
        },

        init() {
            this.$watch("cartItemForm.options", () => {
                this.productPriceWithOptionsPrice();
            });

            if (this.hasVariants && !this.hasPreselectedVariant) {
                this.item = this.product;
            }

            galleryPreviewSlider = this.initGalleryPreviewSlider();
            galleryPreviewLightbox = this.initGalleryPreviewLightbox();

            this.fetchReviews();
            this.setOldMediaLength();
            this.initGalleryPreviewZoom();
            this.setActiveVariationsValue();
            this.setDescriptionContentHeight();
            this.initUpSellProductsSlider();
            this.initRelatedProductsSlider();
        },

        openVariationSheet(uid) {
            if (!this.isMobileDevice()) {
                return;
            }

            this.openVariationUid = uid;
            document.body.classList.add("variant-sheet-open");
        },

        closeVariationSheet() {
            this.openVariationUid = null;
            document.body.classList.remove("variant-sheet-open");
        },

        formatVariationValuePrice(variationUid, valueUid) {
            const amount = this.getVariationValuePrice(variationUid, valueUid);

            if (amount === null) {
                return "";
            }

            return formatCurrency(amount);
        },

        getVariationValuePrice(variationUid, valueUid) {
            if (this.product.variations.length === 1) {
                const variant = this.product.variants.find(
                    (entry) => entry.uids === valueUid
                );

                return variant?.selling_price?.inCurrentCurrency?.amount ?? null;
            }

            const testVariations = {
                ...this.cartItemForm.variations,
                [variationUid]: valueUid,
            };

            const selectedUids = Object.values(testVariations)
                .filter(Boolean)
                .sort()
                .join(".");

            if (
                selectedUids.split(".").length !== this.product.variations.length
            ) {
                return null;
            }

            const variant = this.product.variants.find(
                (entry) => entry.uids === selectedUids
            );

            return variant?.selling_price?.inCurrentCurrency?.amount ?? null;
        },

        goBack(fallbackUrl) {
            if (window.history.length > 1) {
                window.history.back();

                return;
            }

            window.location.href = fallbackUrl;
        },

        syncWishlist() {
            this.$store.wishlist.syncWishlist(this.product.id);
        },

        syncCompareList() {
            this.$store.compare.syncCompareList(this.product.id);
        },

        setOldMediaLength() {
            if (this.hasAnyVariant) {
                this.oldMediaLength = this.item.media?.length ?? 0;
            }
        },

        initGalleryPreviewSlider() {
            const isMobile = this.isMobileDevice();
            const slider = new Swiper(".product-gallery-preview", {
                modules: isMobile
                    ? [Manipulation, Navigation, Thumbs, Pagination]
                    : [Manipulation, Navigation, Thumbs],
                slidesPerView: 1,
                allowTouchMove: isMobile,
                navigation: {
                    nextEl: ".product-gallery-preview .swiper-button-next",
                    prevEl: ".product-gallery-preview .swiper-button-prev",
                },
                pagination: isMobile
                    ? {
                          el: ".product-gallery-pagination",
                          clickable: true,
                          dynamicBullets: true,
                      }
                    : undefined,
                thumbs: {
                    swiper: this.initGalleryThumbnailSlider(),
                },
            });

            this.syncGallerySlideState(slider);

            slider.on("slideChange", () => {
                this.syncGallerySlideState(slider);
            });

            return slider;
        },

        syncGallerySlideState(slider) {
            this.gallerySlideIndex = slider.activeIndex + 1;
            this.gallerySlideTotal = Math.max(slider.slides.length, 1);
        },

        initGalleryThumbnailSlider() {
            return new Swiper(".product-gallery-thumbnail", {
                modules: [Manipulation, Navigation],
                slidesPerView: "auto",
                spaceBetween: 8,
                watchSlidesProgress: true,
                touchEventsTarget: "container",
                freeMode: {
                    enabled: true,
                    sticky: true,
                },
                navigation: {
                    nextEl: ".product-gallery-thumbnail .swiper-button-next",
                    prevEl: ".product-gallery-thumbnail .swiper-button-prev",
                },
                breakpoints: {
                    992: {
                        slidesPerView: 6,
                        spaceBetween: 10,
                        freeMode: false,
                    },
                    1600: {
                        slidesPerView: 7,
                    },
                },
            });
        },

        updateGallerySlider() {
            if (!galleryPreviewSlider) {
                return;
            }

            const mediaPaths = this.collectGalleryMediaPaths();
            const resolvedPaths =
                mediaPaths.length > 0
                    ? mediaPaths
                    : [this.getGalleryPlaceholderPath()];

            this.renderGallerySlides(resolvedPaths);
            this.addGalleryEventListeners();
        },

        getGalleryPlaceholderPath() {
            return `${AestheticCart.baseUrl}/build/assets/image-placeholder.png`;
        },

        collectGalleryMediaPaths() {
            const paths = [];
            const seen = new Set();

            const pushPath = (path) => {
                if (!path || seen.has(path)) {
                    return;
                }

                seen.add(path);
                paths.push(path);
            };

            const pushMedia = (mediaList = []) => {
                mediaList.forEach(({ path }) => pushPath(path));
            };

            const pushBaseImage = (item) => {
                pushPath(item?.base_image?.path);
            };

            if (this.isVariantSelectionComplete) {
                pushMedia(this.item?.media ?? []);
                pushBaseImage(this.item);
            }

            if (paths.length === 0) {
                pushMedia(this.product?.media ?? []);
                pushBaseImage(this.product);
            }

            return paths;
        },

        renderGallerySlides(mediaPaths) {
            const previewWrapper = document.querySelector(
                ".product-gallery-preview .swiper-wrapper"
            );
            const thumbnailWrapper = document.querySelector(
                ".product-gallery-thumbnail .swiper-wrapper"
            );

            if (previewWrapper) {
                previewWrapper.innerHTML = mediaPaths
                    .map((path) => this.galleryPreviewSlide(path))
                    .join("");
            }

            if (thumbnailWrapper) {
                thumbnailWrapper.innerHTML = mediaPaths
                    .map((path) => this.galleryThumbnailSlide(path))
                    .join("");
            }

            if (galleryPreviewSlider.thumbs?.swiper) {
                galleryPreviewSlider.thumbs.swiper.update();
                galleryPreviewSlider.thumbs.swiper.slideTo(0, 0, false);
            }

            galleryPreviewSlider.update();
            galleryPreviewSlider.slideTo(0, 0, false);
            this.syncGallerySlideState(galleryPreviewSlider);
        },

        refreshGallerySliderLayout() {
            if (!galleryPreviewSlider) {
                return;
            }

            galleryPreviewSlider.update();
            galleryPreviewSlider.thumbs?.swiper?.update();
            this.syncGallerySlideState(galleryPreviewSlider);
        },

        addGalleryEventListeners() {
            this.$nextTick(() => {
                this.initGalleryPreviewZoom();
                galleryPreviewLightbox.reload();
                this.refreshGallerySliderLayout();
            });
        },

        initGalleryPreviewZoom() {
            if (this.isMobileDevice()) {
                this.destroyGalleryPreviewZoomInstances();

                return;
            }

            this.initGalleryPreviewDesktopZoom();
        },

        initGalleryPreviewMobileZoom() {
            this.destroyGalleryPreviewZoomInstances();

            [
                ...document.querySelectorAll(".gallery-preview-item > img"),
            ].forEach((el) => {
                galleryPreviewZoomInstances.push(
                    new Drift(el, {
                        namespace: "mobile-drift",
                        inlinePane: true,
                        inlineOffsetY: -50,
                        passive: true,
                    })
                );
            });
        },

        initGalleryPreviewDesktopZoom() {
            this.destroyGalleryPreviewZoomInstances();

            [
                ...document.querySelectorAll(".gallery-preview-item > img"),
            ].forEach((el) => {
                galleryPreviewZoomInstances.push(
                    new Drift(el, {
                        inlinePane: false,
                        hoverBoundingBox: true,
                        boundingBoxContainer: document.body,
                        paneContainer:
                            document.querySelector(".product-gallery"),
                    })
                );
            });
        },

        destroyGalleryPreviewZoomInstances() {
            if (galleryPreviewZoomInstances.length !== 0) {
                galleryPreviewZoomInstances.forEach((instance) => {
                    instance.destroy();
                });
            }
        },

        initGalleryPreviewLightbox() {
            return GLightbox({
                zoomable: true,
                preload: false,
            });
        },

        triggerGalleryPreviewLightbox(event) {
            if (window.innerWidth > 990) {
                event.currentTarget.nextElementSibling.click();
            }
        },

        galleryPreviewSlide(filePath) {
            return `
                <div class="swiper-slide">
                    <div class="gallery-preview-slide">
                        <div class="gallery-preview-item" @click="triggerGalleryPreviewLightbox(event)">
                            <img src="${filePath}" data-zoom="${filePath}" alt="${this.productName}">
                        </div>

                        <a href="${filePath}" data-gallery="product-gallery-preview" class="gallery-view-icon glightbox">
                            <i class="las la-search-plus"></i>
                        </a>
                    </div>
                </div>
            `;
        },

        galleryThumbnailSlide(filePath) {
            return `
                <div class="swiper-slide">
                    <div class="gallery-thumbnail-slide">
                        <div class="gallery-thumbnail-item">
                            <img src="${filePath}" alt="${this.productName}">
                        </div>
                    </div>
                </div>
            `;
        },

        galleryPreviewEmptySlide(filePath) {
            return `
                <div class="swiper-slide">
                    <div class="gallery-preview-slide">
                        <div class="gallery-preview-item" @click="triggerGalleryPreviewLightbox(event)">
                            <img src="${filePath}" data-zoom="${filePath}" alt="${this.productName}" class="image-placeholder">
                        </div>

                        <a href="${filePath}" data-gallery="product-gallery-preview" class="gallery-view-icon glightbox">
                            <i class="las la-search-plus"></i>
                        </a>
                    </div>
                </div>
            `;
        },

        galleryThumbnailEmptySlide(filePath) {
            return `
                <div class="swiper-slide">
                    <div class="gallery-thumbnail-slide">
                        <div class="gallery-thumbnail-item">
                            <img src="${filePath}" alt="${this.productName}" class="image-placeholder">
                        </div>
                    </div>
                </div>
            `;
        },

        productPriceWithOptionsPrice() {
            const cartItemoptions = Object.entries(this.cartItemForm.options);

            cartItemoptions.forEach(([key, value]) => {
                const option = this.product.options.find(
                    ({ id }) => id === Number(key)
                );

                // Single select with single value
                if (
                    ["field", "textarea", "date", "date_time", "time"].includes(
                        option.type
                    )
                ) {
                    if (!Boolean(this.cartItemForm.options[option.id])) {
                        delete this.optionPrices[option.id];

                        return;
                    }

                    const optionValue = option.values[0];
                    const price =
                        optionValue.price?.inCurrentCurrency?.amount ??
                        (+optionValue.price / 100) * this.productPrice;

                    this.optionPrices[key] = price;

                    return;
                }

                // Single select with multiple values
                if (
                    ["dropdown", "radio", "radio_custom"].includes(option.type)
                ) {
                    const optionValue = option.values.find(
                        ({ id }) => id === Number(value)
                    );

                    const price =
                        optionValue.price?.inCurrentCurrency?.amount ??
                        (+optionValue.price / 100) * this.productPrice;

                    this.optionPrices[key] = price;

                    return;
                }

                // Multiple select with multiple values
                if (
                    ["checkbox", "checkbox_custom", "multiple_select"].includes(
                        option.type
                    ) &&
                    value.length !== 0
                ) {
                    const values = this.product.options
                        .find(({ id }) => id === Number(key))
                        .values.filter((data) => value.includes(data.id));

                    const price = values.reduce(
                        (accumulator, value) =>
                            accumulator +
                            (value.price?.inCurrentCurrency?.amount ??
                                (+value.price / 100) * this.productPrice),
                        0
                    );

                    this.optionPrices[key] = price;
                }
            });
        },

        isVariationValueEnabled(variationUid, variationIndex, valueUid) {
            // Check if enabled first variation values
            if (variationIndex === 0) {
                return this.doesVariantExist(valueUid);
            }

            // Check if enabled variation values between first and last variation
            if (
                variationIndex > 0 &&
                variationIndex < this.product.variations.length - 1
            ) {
                return this.doesVariantExist(valueUid);
            }

            // Check if enabled last variation values
            if (variationIndex === this.product.variations.length - 1) {
                const variations = this.cartItemForm.variations;
                const valueUids = Object.values(variations).filter(
                    (uid) => uid !== variations[variationUid]
                );

                valueUids.push(valueUid);

                return this.doesVariantExist(valueUids.sort().join("."));
            }
        },

        setActiveVariationsValue() {
            if (!this.hasPreselectedVariant || !this.item.uids) return;

            const variations = { ...this.cartItemForm.variations };

            this.item.uids.split(".").forEach((uid) => {
                this.product.variations.some((variation) => {
                    const value = variation.values.find(
                        (value) => value.uid === uid
                    );

                    if (value !== undefined) {
                        this.activeVariationValues[variation.uid] =
                            value.label;
                        variations[variation.uid] = uid;

                        return true;
                    }
                });
            });

            this.cartItemForm = {
                ...this.cartItemForm,
                variations,
            };
        },

        setActiveVariationValueLabel(variationIndex) {
            this.variationImagePath = null;

            const variation = this.product.variations[variationIndex];
            const selectedUid =
                this.cartItemForm.variations?.[variation.uid];

            if (!selectedUid) {
                delete this.activeVariationValues[variation.uid];

                return;
            }

            const value = variation.values.find(
                (value) => value.uid === selectedUid
            );

            if (value) {
                this.activeVariationValues[variation.uid] = value.label;
            }
        },

        previewVariationValue(variationIndex, valueIndex) {
            const variation = this.product.variations[variationIndex];
            const value = variation.values[valueIndex];

            if (!this.isMobileDevice() && variation.type === "image") {
                this.variationImagePath = value.image.path;
            }
        },

        setVariationValueLabel(variationIndex, valueIndex) {
            const variation = this.product.variations[variationIndex];
            const value = variation.values[valueIndex];

            if (!this.isMobileDevice() && variation.type === "image") {
                this.variationImagePath = value.image.path;
            }

            this.activeVariationValues[variation.uid] = value.label;
        },

        isActiveVariationValue(variationUid, valueUid) {
            return this.cartItemForm.variations?.[variationUid] === valueUid;
        },

        syncVariationValue(variationUid, variationIndex, valueUid, valueIndex) {
            if (this.isActiveVariationValue(variationUid, valueUid)) {
                return;
            }

            this.cartItemForm = {
                ...this.cartItemForm,
                variations: {
                    ...this.cartItemForm.variations,
                    [variationUid]: valueUid,
                },
            };

            this.setVariationValueLabel(variationIndex, valueIndex);
            this.updateVariantDetails();

            if (this.isMobileDevice()) {
                this.closeVariationSheet();
            }
        },

        doesVariantExist(uid) {
            return this.product.variants.some((variant) => {
                if (!variant.uids) {
                    return false;
                }

                if (variant.uids === uid) {
                    return true;
                }

                return variant.uids.split(".").includes(uid);
            });
        },

        setVariant() {
            const selectedUids = Object.values(this.cartItemForm.variations)
                .sort()
                .join(".");

            const variant = this.product.variants.find(
                (variant) => variant.uids === selectedUids
            );

            if (variant !== undefined) {
                this.item = { ...variant };

                this.reduceToMaxQuantity();

                return;
            }

            // Set empty variant data if variant does not exist
            const uid = md5(
                Object.values(this.cartItemForm.variations).sort().join(".")
            );

            this.item = {
                uid,
                media: [],
                base_image: [],
            };

            this.cartItemForm.qty = 1;
        },

        setVariantSlug() {
            const url = `${AestheticCart.url(`/products/${this.product.slug}`)}?variant=${this.item.uid}`;

            window.history.replaceState({}, "", url);
        },

        updateVariantDetails() {
            this.setOldMediaLength();
            this.setVariant();
            this.setVariantSlug();
            this.updateGallerySlider();
        },

        updateSelectTypeOptionValue(optionId, event) {
            this.cartItemForm.options = Object.assign(
                {},
                this.cartItemForm.options,
                {
                    [optionId]: event.target.value,
                }
            );

            this.errors.clear(`options.${optionId}`);
        },

        updateCheckboxTypeOptionValue(optionId, event) {
            let values = $(event.target)
                .parents(".variant-check")
                .find('input[type="checkbox"]:checked')
                .map((_, el) => {
                    return el.value;
                });

            this.cartItemForm.options = Object.assign(
                {},
                this.cartItemForm.options,
                {
                    [optionId]: values.get(),
                }
            );
        },

        customRadioTypeOptionValueIsActive(optionId, valueId) {
            if (!this.cartItemForm.options.hasOwnProperty(optionId)) {
                return false;
            }

            return this.cartItemForm.options[optionId] === valueId;
        },

        syncCustomRadioTypeOptionValue(optionId, valueId) {
            if (this.customRadioTypeOptionValueIsActive(optionId, valueId)) {
                delete this.cartItemForm.options[optionId];
            } else {
                this.cartItemForm.options = Object.assign(
                    {},
                    this.cartItemForm.options,
                    {
                        [optionId]: valueId,
                    }
                );

                this.errors.clear(`options.${optionId}`);
            }
        },

        customCheckboxTypeOptionValueIsActive(optionId, valueId) {
            if (!this.cartItemForm.options.hasOwnProperty(optionId)) {
                this.cartItemForm.options = Object.assign(
                    {},
                    this.cartItemForm.options,
                    {
                        [optionId]: [],
                    }
                );

                return false;
            }

            return this.cartItemForm.options[optionId].includes(valueId);
        },

        syncCustomCheckboxTypeOptionValue(optionId, valueId) {
            if (this.customCheckboxTypeOptionValueIsActive(optionId, valueId)) {
                this.cartItemForm.options[optionId].splice(
                    this.cartItemForm.options[optionId].indexOf(valueId),
                    1
                );
            } else {
                this.cartItemForm.options[optionId].push(valueId);

                // Reassign the existing data due to reactivity issue
                this.cartItemForm = Object.assign(
                    {},
                    this.cartItemForm,
                    this.cartItemForm.options
                );

                this.errors.clear(`options.${optionId}`);
            }
        },

        setDescriptionContentHeight() {
            this.$nextTick(() => {
                this.showMore =
                    this.$refs.descriptionContent.clientHeight >= 400
                        ? true
                        : false;
            });
        },

        setInactiveItemData() {
            this.item = {
                uid: this.item.uid,
                media: [],
                base_image: [],
            };
        },

        isMobileDevice() {
            return window.matchMedia("only screen and (max-width: 992px)")
                .matches;
        },

        updateQuantity(qty) {
            if (isNaN(qty) || qty < 1) {
                this.cartItemForm.qty = 1;

                return;
            }

            this.cartItemForm.qty = qty;

            if (this.exceedsMaxStock(qty)) {
                this.cartItemForm.qty = this.item.qty;

                return;
            }
        },

        exceedsMaxStock(qty) {
            return this.doesManageStock && this.item.qty < qty;
        },

        reduceToMaxQuantity() {
            if (this.doesManageStock && this.cartItemForm.qty > this.item.qty) {
                this.cartItemForm.qty = this.item.qty || 1;
            }
        },

        addToCart() {
            if (this.isAddToCartDisabled) return;

            this.addingToCart = true;

            const payload = {
                ...this.cartItemForm,
            };

            if (this.hasAnyVariant) {
                payload.variant_id = this.item.id;
            }

            axios
                .post(AestheticCart.url("/cart/items"), payload)
                .then((response) => {
                    this.$store.cart.updateCart(response.data);
                    this.$store.layout.openSidebarCart();
                })
                .catch((error) => {
                    const response = error.response;

                    if (!response) {
                        notify(
                            trans(
                                "storefront::storefront.something_went_wrong"
                            )
                        );

                        return;
                    }

                    if (response.status === 422) {
                        this.errors.record(response.data.errors);
                    }

                    notify(
                        response.data?.message ||
                            trans(
                                "storefront::storefront.something_went_wrong"
                            )
                    );
                })
                .finally(() => {
                    this.addingToCart = false;
                });
        },

        toggleDescriptionContent() {
            this.showDescriptionContent = !this.showDescriptionContent;
        },

        async fetchReviews() {
            this.fetchingReviews = true;

            try {
                const response = await axios.get(
                    `/products/${this.product.id}/reviews?page=${this.currentPage}`
                );

                this.reviews = response.data;
            } catch (error) {
                notify(error.response.data.message);
            } finally {
                this.fetchingReviews = false;
            }
        },

        buildReviewPayload() {
            const form = this.$refs.reviewForm;
            const payload = {};

            if (form) {
                new FormData(form).forEach((value, key) => {
                    payload[key] = value;
                });

                if (!payload.rating) {
                    const selectedRating = form.querySelector(
                        'input[name="rating"]:checked'
                    );

                    if (selectedRating) {
                        payload.rating = selectedRating.value;
                    }
                }
            } else {
                Object.assign(payload, this.reviewForm);
            }

            if (!payload.rating && this.reviewForm.rating) {
                payload.rating = this.reviewForm.rating;
            }

            const captchaResponse = window.grecaptcha?.getResponse?.();

            if (captchaResponse) {
                payload["g-recaptcha-response"] = captchaResponse;
            }

            return payload;
        },

        addNewReview() {
            const payload = this.buildReviewPayload();

            this.addingNewReview = true;

            axios
                .post(`/products/${this.product.id}/reviews`, payload)
                .then((response) => {
                    this.reviewForm = {
                        reviewer_name: this.reviewerName,
                    };
                    this.reviews.total++;
                    this.reviews.data.unshift(response.data);

                    notify(trans("storefront::product.review_submitted"));

                    this.errors.reset();
                })
                .catch(({ response }) => {
                    if (response.status === 422) {
                        this.errors.record(response.data.errors);

                        const firstError = Object.values(
                            response.data.errors || {}
                        )[0]?.[0];

                        if (firstError) {
                            notify(firstError);
                        }

                        return;
                    }

                    notify(response.data.message);
                })
                .finally(() => {
                    this.addingNewReview = false;

                    if (window.grecaptcha) {
                        grecaptcha.reset();
                    }
                });
        },

        changePage(page) {
            this.currentPage = page;

            this.fetchReviews();
        },

        hideRelatedProductsSkeleton() {
            const skeletons = document.querySelectorAll(
                ".landscape-products .swiper-slide-skeleton"
            );

            skeletons.forEach((skeleton) => skeleton.remove());
        },

        initUpSellProductsSlider() {
            const swiperEl = this.$refs.upSellProducts;

            if (!swiperEl) {
                return;
            }

            new Swiper(swiperEl, {
                modules: [Navigation],
                slidesPerView: 1,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
            });
        },

        initRelatedProductsSlider() {
            const swiperEl = this.$refs.landscapeProducts;

            if (!swiperEl) {
                return;
            }

            this.hideRelatedProductsSkeleton();

            const options = {
                modules: [Navigation, Pagination],
                slidesPerView: 2,
                watchOverflow: true,
                ...productSliderNavigation(
                    swiperEl,
                    swiperEl.closest(".landscape-products-wrap")
                ),
                breakpoints: {
                    640: {
                        slidesPerView: 3,
                    },
                    880: {
                        slidesPerView: 4,
                    },
                    992: {
                        slidesPerView: 3,
                    },
                    1100: {
                        slidesPerView: 4,
                    },
                    1300: {
                        slidesPerView: 5,
                    },
                    1600: {
                        slidesPerView: 6,
                    },
                },
            };

            const scopeEl = swiperEl.closest(".landscape-products-inner");
            const { paginationEl } = resolveProductSliderControls(
                swiperEl,
                scopeEl
            );

            if (options.pagination && paginationEl) {
                options.pagination.el = paginationEl;
            }

            this.relatedProductsSwiper = new Swiper(
                swiperEl,
                wrapProductSliderOptions(
                    options,
                    swiperEl,
                    scopeEl,
                    (swiper) => this.updateSliderState(swiper)
                )
            );
        },
    })
);
