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
export function notify(messageOrType, optionsOrMessage = {}, maybeOptions = {}) {
    const knownTypes = ["success", "error", "warning", "info", "danger", "default"];

    if (
        typeof messageOrType === "string" &&
        knownTypes.includes(String(messageOrType).toLowerCase()) &&
        typeof optionsOrMessage === "string"
    ) {
        return sweetNotify(messageOrType, optionsOrMessage, maybeOptions || {});
    }

    const message =
        messageOrType ||
        (typeof window.trans === "function"
            ? window.trans("storefront::storefront.something_went_wrong")
            : "Something went wrong");

    const options = typeof optionsOrMessage === "object" && optionsOrMessage ? optionsOrMessage : {};
    const type = options.type || options.icon || "info";

    return sweetNotify(type, message, {
        position: window.innerWidth > 991 ? "bottom-end" : "top-end",
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
