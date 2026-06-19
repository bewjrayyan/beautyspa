/**
 * Run work after the browser has painted (avoids blocking load handlers).
 */
export function runAfterPaint(callback) {
    if (typeof callback !== "function") {
        return;
    }

    requestAnimationFrame(() => {
        requestAnimationFrame(callback);
    });
}

/**
 * Run non-critical work when the main thread is idle.
 */
export function runWhenIdle(callback, timeout = 1500) {
    if (typeof callback !== "function") {
        return;
    }

    if (typeof window.requestIdleCallback === "function") {
        window.requestIdleCallback(() => callback(), { timeout });

        return;
    }

    setTimeout(callback, 16);
}

/**
 * Defer work until idle, optionally with an extra delay to stagger requests.
 */
export function runDeferred(callback, delay = 0, idleTimeout = 1500) {
    runWhenIdle(() => {
        if (delay > 0) {
            setTimeout(callback, delay);

            return;
        }

        callback();
    }, idleTimeout);
}
