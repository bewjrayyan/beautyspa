import { toast } from "./SweetNotification";

export function toaster(message, options = {}) {
    const type = options.type || "default";

    return toast(type, message, {
        duration: options.duration ?? 5000,
        position: options.position === "top-right" ? "top-end" : options.position,
        ...options,
    });
}
