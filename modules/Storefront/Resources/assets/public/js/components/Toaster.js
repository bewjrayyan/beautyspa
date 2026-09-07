import SweetNotification, {
    notify as sweetNotify,
    success,
    error,
    warning,
    info,
} from "./SweetNotification";

/**
 * Storefront-compatible notify(message, options)
 * Also supports notify(type, message) and notify.success/error helpers.
 */
function coerceNotifyMessage(message) {
    if (message == null || message === "") {
        return "";
    }

    if (typeof message === "string") {
        return message.trim();
    }

    if (typeof message === "number" || typeof message === "boolean") {
        return String(message);
    }

    if (typeof message === "object") {
        if (typeof message.message === "string" && message.message.trim()) {
            return message.message.trim();
        }

        if (message.errors && typeof message.errors === "object") {
            for (const value of Object.values(message.errors)) {
                if (typeof value === "string" && value.trim()) {
                    return value.trim();
                }

                if (Array.isArray(value) && typeof value[0] === "string" && value[0].trim()) {
                    return value[0].trim();
                }
            }
        }
    }

    return "";
}

export function notify(messageOrType, optionsOrMessage = {}, maybeOptions = {}) {
    const knownTypes = ["success", "error", "warning", "info", "danger", "default"];

    if (
        typeof messageOrType === "string" &&
        knownTypes.includes(String(messageOrType).toLowerCase()) &&
        typeof optionsOrMessage === "string"
    ) {
        return sweetNotify(messageOrType, optionsOrMessage, maybeOptions || {});
    }

    const coerced = coerceNotifyMessage(messageOrType);
    const message =
        coerced ||
        (typeof window.trans === "function"
            ? window.trans("storefront::storefront.something_went_wrong")
            : "Something went wrong");

    const options =
        typeof optionsOrMessage === "object" && optionsOrMessage && !Array.isArray(optionsOrMessage)
            ? optionsOrMessage
            : {};
    const type = options.type || options.icon || "info";

    return sweetNotify(type, message, {
        duration: options.duration ?? 3000,
        ...options,
    });
}

Object.assign(notify, {
    success,
    error,
    warning,
    info,
    alert: SweetNotification.alert,
    confirm: SweetNotification.confirm,
});

export { success, error, warning, info, SweetNotification };
export default notify;
