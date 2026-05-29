document.addEventListener("DOMContentLoaded", () => {
    const page = document.querySelector(".tr-portal-profile-page");

    if (!page) {
        return;
    }

    const dayAvailableLabel = page.dataset.dayAvailable || "Available";
    const dayOffLabel = page.dataset.dayOff || "Day off";

    page.querySelectorAll("[data-availability-day]").forEach((row) => {
        const toggle = row.querySelector(".bp-availability-day__toggle");
        const status = row.querySelector(".bp-availability-day__status");
        const timeInputs = row.querySelectorAll(".bp-availability-day__time");

        if (!toggle) {
            return;
        }

        const syncRow = () => {
            const enabled = toggle.checked;

            row.classList.toggle("is-enabled", enabled);

            timeInputs.forEach((input) => {
                input.disabled = !enabled;
            });

            if (status) {
                status.textContent = enabled ? dayAvailableLabel : dayOffLabel;
            }
        };

        toggle.addEventListener("change", syncRow);
        syncRow();
    });
});
