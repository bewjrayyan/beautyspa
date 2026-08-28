function formatPreviewDuration(totalSeconds) {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    if (hours > 0) {
        return `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
    }

    return `${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
}

export function initAdminPreviewTimer() {
    const root = document.querySelector("[data-tr-admin-preview-timer]");

    if (!root) {
        return;
    }

    const display = root.querySelector("[data-tr-admin-preview-timer-value]");
    const startedAt = Number(root.dataset.startedAt) * 1000;

    if (!display || !Number.isFinite(startedAt) || startedAt <= 0) {
        return;
    }

    const tick = () => {
        const elapsedSeconds = Math.max(0, Math.floor((Date.now() - startedAt) / 1000));
        display.textContent = formatPreviewDuration(elapsedSeconds);
    };

    tick();
    window.setInterval(tick, 1000);
}
