/**
 * Above-the-fold: load immediately so hero/features can paint and bind fast.
 * Below-the-fold: dynamic import in parallel to shrink the critical JS path.
 */
import "./sections/Hero";
import "./sections/HomeFeatures";
import "./sections/MobileHomePromo";

window.__storefrontPageReady = Promise.all([
    import("./sections/FeaturedCateogires"),
    import("./sections/ProductTabsOne"),
    import("./sections/TopBrands"),
    import("./sections/FlashSale"),
    import("./sections/GridProducts"),
    import("./sections/ProductTabsTwo"),
    import("./sections/GoogleReviews"),
    import("./sections/Blog"),
]).catch((error) => {
    console.error("Failed to load homepage sections", error);
});
