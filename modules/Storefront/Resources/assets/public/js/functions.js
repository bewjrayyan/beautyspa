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
