export function trans(langKey, replace = {}) {
    let line = window.AestheticCart.langs[langKey];

    if (!line || line === langKey) {
        return line ?? langKey;
    }

    for (let key in replace) {
        line = line.replace(`:${key}`, replace[key]);
    }

    return line;
}

export function formatCurrency(amount) {
    return new Intl.NumberFormat(AestheticCart.locale.replace("_", "-"), {
        ...(AestheticCart.locale === "ar" && {
            numberingSystem: "arab",
        }),
        style: "currency",
        currency: AestheticCart.currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
}

export function generateUid() {
    const timestamp = Math.floor(Math.random() * Date.now()).toString(36);
    const randomPart = Math.random().toString(36).substring(2, 8);

    return (timestamp + randomPart).substring(0, 12);
}

export function assetUrl(path) {
    const normalizedPath = path.startsWith("/") ? path : `/${path}`;
    const appUrl = (AestheticCart?.appUrl || "").replace(/\/$/, "");
    const installPath = (AestheticCart?.installPath || "").replace(/\/$/, "");

    if (appUrl.startsWith("http://") || appUrl.startsWith("https://")) {
        return `${appUrl}${installPath}${normalizedPath}`;
    }

    return `${window.location.origin}${installPath}${normalizedPath}`;
}

export function hasBaseImageMedia(baseImage) {
    if (!baseImage) {
        return false;
    }

    if (Array.isArray(baseImage)) {
        return baseImage.length > 0 && Boolean(baseImage[0]?.path || baseImage[0]?.id);
    }

    return Boolean(baseImage.path || baseImage.id);
}

export function resolveBaseImagePath(item, product = null, hasVariant = false) {
    if (hasBaseImageMedia(item?.base_image)) {
        return item.base_image.path;
    }

    if (hasVariant && hasBaseImageMedia(product?.base_image)) {
        return product.base_image.path;
    }

    return null;
}

export function placeholderImageUrl() {
    return assetUrl("/build/assets/image-placeholder.png");
}

export function resolveCartLineItem(cartItem) {
    return cartItem.item || cartItem.variant || cartItem.product;
}

export function cartLineUnitPrice(cartItem) {
    return cartItem.unitPrice.inCurrentCurrency.amount;
}

export function cartLineSellingUnitPrice(cartItem) {
    const item = resolveCartLineItem(cartItem);

    return item?.selling_price?.inCurrentCurrency?.amount ?? cartLineUnitPrice(cartItem);
}

export function cartLineOptionsUnitPrice(cartItem) {
    return cartLineUnitPrice(cartItem) - cartLineSellingUnitPrice(cartItem);
}

export function cartLineRegularUnitPrice(cartItem) {
    const item = resolveCartLineItem(cartItem);
    const basePrice =
        item?.price?.inCurrentCurrency?.amount ?? cartLineUnitPrice(cartItem);

    return basePrice + cartLineOptionsUnitPrice(cartItem);
}

export function cartLineHasSpecialPrice(cartItem) {
    const item = resolveCartLineItem(cartItem);
    const regular = cartLineRegularUnitPrice(cartItem);
    const unit = cartLineUnitPrice(cartItem);

    return Boolean(
        cartItem.product?.is_in_flash_sale ||
            (item?.special_price != null && regular > unit)
    );
}

export function cartLineUnitSavings(cartItem) {
    if (!cartLineHasSpecialPrice(cartItem)) {
        return 0;
    }

    return Math.max(0, cartLineRegularUnitPrice(cartItem) - cartLineUnitPrice(cartItem));
}
