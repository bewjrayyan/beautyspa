let Swal = null;
let Toast = null;
let Modal = null;
let swalReady = null;

async function ensureSwal() {
    if (Swal) {
        return Swal;
    }

    if (!swalReady) {
        swalReady = Promise.all([
            import("sweetalert2"),
            import("sweetalert2/dist/sweetalert2.min.css"),
        ]).then(([mod]) => {
            Swal = mod.default;

            Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    showCloseButton: true,
    timer: 3500,
    timerProgressBar: true,
    backdrop: false,
    target: "body",
    customClass: {
        popup: "ac-swal-toast",
        title: "ac-swal-toast__title",
        htmlContainer: "ac-swal-toast__text",
        closeButton: "ac-swal-toast__close",
        timerProgressBar: "ac-swal-toast__timer",
    },
    didOpen: (el) => {
        el.addEventListener("mouseenter", Swal.stopTimer);
        el.addEventListener("mouseleave", Swal.resumeTimer);
    },
            });

            Modal = Swal.mixin({
    toast: false,
    position: "center",
    target: "body",
    heightAuto: false,
    backdrop: true,
    width: 720,
    padding: "3.5rem 2.75rem 2.5rem",
    customClass: {
        popup: "ac-swal-modal",
        title: "ac-swal-modal__title",
        htmlContainer: "ac-swal-modal__text",
        confirmButton: "ac-swal-modal__confirm",
        cancelButton: "ac-swal-modal__cancel",
        actions: "ac-swal-modal__actions",
        icon: "ac-swal-modal__icon",
    },
            });

            return Swal;
        });
    }

    return swalReady;
}

/**
 * AestheticCart SweetNotification (SweetAlert2)
 *
 * Default feedback (success/error/warning/info):
 *   → centered modal like classic SweetAlert (icon + title + text + backdrop)
 *
 * Explicit corner toast:
 *   → SweetNotification.toast(...)
 *
 * Delete confirmation:
 *   → confirm / confirmDelete (Yes / Cancel)
 */

const TITLES = {
    success: ["admin::admin.notifications.success", "Success"],
    error: ["admin::admin.notifications.error", "Something went wrong"],
    warning: ["admin::admin.notifications.warning", "Please check"],
    info: ["admin::admin.notifications.info", "Notice"],
};

function t(key, fallback) {
    try {
        if (typeof window.trans === "function") {
            const value = window.trans(key);

            if (value && value !== key) {
                return value;
            }
        }

        const mapped = window.AestheticCart?.langs?.[key];

        if (mapped) {
            return mapped;
        }
    } catch (e) {
        // ignore
    }

    return fallback;
}

function normalizeType(type) {
    const value = String(type || "info").toLowerCase();

    if (["danger", "fail", "failed"].includes(value)) {
        return "error";
    }

    if (value === "default") {
        return "info";
    }

    if (["success", "error", "warning", "info", "question"].includes(value)) {
        return value;
    }

    return "info";
}

function headlineFor(type, options = {}) {
    if (options.title) {
        return String(options.title);
    }

    const [key, fallback] = TITLES[type] || TITLES.info;

    return t(key, fallback);
}

/** Optional corner toast */
async function toast(type, message, options = {}) {
    await ensureSwal();
    const icon = normalizeType(type);
    const text = message == null ? "" : String(message).trim();

    if (!text) {
        return Promise.resolve();
    }

    const opts = typeof options === "number" ? { duration: options } : { ...(options || {}) };
    const duration = opts.duration ?? (icon === "error" ? 5000 : 3500);
    const title = headlineFor(icon, opts);

    delete opts.duration;
    delete opts.title;

    return Toast.fire({
        icon,
        title,
        text,
        timer: duration,
        position: opts.position || "top-end",
        ...opts,
        toast: true,
        backdrop: false,
        target: "body",
    });
}

/**
 * Centered SweetAlert (default for success/error/warning/info)
 * Matches classic SweetAlert layout: icon on top, title, message, dimmed backdrop.
 */
async function centered(type, message, options = {}) {
    await ensureSwal();
    const icon = normalizeType(type);
    const text = message == null ? "" : String(message).trim();

    if (!text) {
        return Promise.resolve();
    }

    const opts = typeof options === "number" ? { duration: options } : { ...(options || {}) };
    const duration = opts.duration ?? (icon === "error" ? 3200 : 2200);
    const title = headlineFor(icon, opts);
    const showConfirmButton = opts.showConfirmButton === true;

    delete opts.duration;
    delete opts.title;

    return Modal.fire({
        icon,
        title,
        text,
        showConfirmButton,
        showCancelButton: false,
        confirmButtonText: opts.confirmButtonText || "OK",
        confirmButtonColor: opts.confirmButtonColor || "#28c76f",
        timer: showConfirmButton ? undefined : duration,
        timerProgressBar: !showConfirmButton,
        allowOutsideClick: true,
        allowEscapeKey: true,
        ...opts,
        icon,
        toast: false,
        position: "center",
        backdrop: true,
        target: "body",
    });
}

function success(message, options = {}) {
    return centered("success", message, options);
}

function error(message, options = {}) {
    const opts = typeof options === "number" ? { duration: options } : { ...(options || {}) };

    return centered("error", message, { duration: 3200, ...opts });
}

function warning(message, options = {}) {
    return centered("warning", message, options);
}

function info(message, options = {}) {
    return centered("info", message, options);
}

function notify(typeOrMessage, messageOrOptions, options = {}) {
    const known = ["success", "error", "warning", "info", "danger", "default", "question"];

    if (
        typeof typeOrMessage === "string" &&
        known.includes(String(typeOrMessage).toLowerCase()) &&
        (typeof messageOrOptions === "string" || messageOrOptions == null)
    ) {
        return centered(typeOrMessage, messageOrOptions, options || {});
    }

    return centered("info", typeOrMessage, messageOrOptions || {});
}

async function alert(message, options = {}) {
    await ensureSwal();
    const opts = { ...(options || {}) };
    const icon = normalizeType(opts.type || opts.icon || "info");
    const text = message == null ? "" : String(message);

    return Modal.fire({
        icon,
        title: opts.title || headlineFor(icon, opts),
        text,
        showConfirmButton: true,
        confirmButtonText: opts.confirmButtonText || "OK",
        confirmButtonColor: opts.confirmButtonColor || "#28c76f",
        ...opts,
        icon,
        toast: false,
        position: "center",
        backdrop: true,
        target: "body",
    });
}

async function confirm(message, options = {}) {
    await ensureSwal();
    const opts = { ...(options || {}) };

    return Modal.fire({
        icon: normalizeType(opts.icon || "warning"),
        title: opts.title || t("admin::admin.delete.are_you_sure", "Are you sure?"),
        text:
            message == null || message === ""
                ? t("admin::admin.delete.confirmation_message", "Are you sure you want to delete?")
                : String(message),
        showCancelButton: true,
        showConfirmButton: true,
        focusCancel: true,
        reverseButtons: false,
        confirmButtonText: opts.confirmButtonText || t("admin::admin.buttons.yes", "Yes"),
        cancelButtonText: opts.cancelButtonText || t("admin::admin.buttons.cancel", "Cancel"),
        confirmButtonColor: opts.confirmButtonColor || "#28c76f",
        cancelButtonColor: opts.cancelButtonColor || "#82868b",
        ...opts,
        icon: normalizeType(opts.icon || "warning"),
        toast: false,
        position: "center",
        backdrop: true,
        target: "body",
        timer: undefined,
        timerProgressBar: false,
    }).then((result) => result.isConfirmed);
}

function confirmDelete(message, options = {}) {
    return confirm(message, options);
}

async function bootFlashes(root = document) {
    const el = root.querySelector("#sweet-notification-flashes");

    if (!el) {
        return;
    }

    let flashes = {};

    try {
        flashes = JSON.parse(el.textContent || "{}") || {};
    } catch (e) {
        return;
    }

    if (!Object.values(flashes).some(Boolean)) {
        return;
    }

    await ensureSwal();

    Object.entries(flashes).forEach(([type, message]) => {
        if (message) {
            centered(type, message);
        }
    });
}

export const SweetNotification = {
    toast,
    centered,
    notify,
    success,
    error,
    warning,
    info,
    alert,
    confirm,
    confirmDelete,
    bootFlashes,
    ensureSwal,
    get Swal() {
        return Swal;
    },
};

export {
    toast,
    centered,
    notify,
    success,
    error,
    warning,
    info,
    alert,
    confirm,
    confirmDelete,
    bootFlashes,
};

export default SweetNotification;
