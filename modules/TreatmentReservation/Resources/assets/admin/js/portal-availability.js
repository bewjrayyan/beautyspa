import flatpickr from "flatpickr";

document.addEventListener("DOMContentLoaded", () => {
    const page = document.querySelector("[data-availability-settings], .tr-portal-profile-page");

    if (!page) {
        return;
    }

    const dayAvailableLabel = page.dataset.dayAvailable || "Available";
    const dayOffLabel = page.dataset.dayOff || "Day off";

    const initModernTimeInput = (input) => {
        if (!input || input._flatpickr) {
            return;
        }

        flatpickr(input, {
            allowInput: false,
            animate: true,
            appendTo: document.body,
            altFormat: "h:i K",
            altInput: true,
            dateFormat: "H:i",
            defaultDate: input.value || null,
            disableMobile: true,
            enableTime: true,
            minuteIncrement: 15,
            noCalendar: true,
            time_24hr: false,
            onReady(_selectedDates, _dateStr, instance) {
                instance.calendarContainer.classList.add(
                    "tr-avail-timepicker",
                    "tr-calendar-event-preview__picker-calendar--time"
                );

                if (instance.altInput) {
                    instance.altInput.className = [
                        "form-control",
                        "bp-input",
                        "bp-availability-day__time",
                        "tr-day-time-input",
                        "flatpickr-input",
                    ].join(" ");
                    instance.altInput.disabled = input.disabled;
                    instance.altInput.setAttribute(
                        "aria-label",
                        input.getAttribute("aria-label") || input.getAttribute("placeholder") || "Select time"
                    );
                }
            },
        });
    };

    const setTimeInputEnabled = (input, enabled) => {
        input.disabled = !enabled;

        if (input._flatpickr?.altInput) {
            input._flatpickr.altInput.disabled = !enabled;
        }
    };

    page.querySelectorAll("[data-modern-time]").forEach((input) => initModernTimeInput(input));

    page.querySelectorAll("[data-availability-day]").forEach((row) => {
        const toggle = row.querySelector(".bp-availability-day__toggle");
        const status = row.querySelector(".bp-availability-day__status");

        if (!toggle) {
            return;
        }

        const syncRow = () => {
            const enabled = toggle.checked;

            row.classList.toggle("is-enabled", enabled);

            row.querySelectorAll("[data-modern-time]").forEach((input) => {
                setTimeInputEnabled(input, enabled);
            });

            if (status) {
                status.textContent = enabled ? dayAvailableLabel : dayOffLabel;
            }
        };

        toggle.addEventListener("change", syncRow);
        syncRow();
    });
});
