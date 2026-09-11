import { centered } from "./SweetNotification";

export function toaster(message, options = {}) {
    const type = options.type || "default";
    const { type: _type, position: _position, ...modalOptions } = options;

    return centered(type, message, {
        duration: options.duration ?? 5000,
        ...modalOptions,
    });
}
