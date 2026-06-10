/**
 * Run callback when element enters (or is near) the viewport.
 */
export function whenVisible(element, callback, rootMargin = "120px") {
    if (!element || typeof callback !== "function") {
        return;
    }

    if (!("IntersectionObserver" in window)) {
        callback();

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            if (!entries[0]?.isIntersecting) {
                return;
            }

            observer.disconnect();
            callback();
        },
        { rootMargin }
    );

    observer.observe(element);
}
