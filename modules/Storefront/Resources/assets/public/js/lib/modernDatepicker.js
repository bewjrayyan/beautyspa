import flatpickr from "flatpickr";

/**
 * Shared Flatpickr options for AestheticCart (storefront + vendor bundles).
 */
export function buildDatepickerOptions(el) {
    const enableTime = el.hasAttribute("data-time");
    const noCalendar = el.hasAttribute("data-no-calender");

    const options = {
        mode: el.hasAttribute("data-range") ? "range" : "single",
        enableTime,
        noCalendar,
        dateFormat: enableTime ? "Y-m-d H:i" : "Y-m-d",
        altInput: true,
        altFormat: enableTime ? "d/m/Y H:i" : "d/m/Y",
        time_24hr: false,
        disableMobile: true,
        defaultDate: el.dataset.defaultDate || el.value || null,
    };

    if (el.dataset.maxDate) {
        options.maxDate = el.dataset.maxDate;
    }

    if (el.dataset.minDate) {
        options.minDate = el.dataset.minDate;
    }

    return options;
}

export function initModernDatepickers(root = document) {
    root.querySelectorAll("input.modern-datepicker").forEach((el) => {
        if (el._flatpickr) {
            return;
        }

        flatpickr(el, buildDatepickerOptions(el));
    });
}

export function initDatetimePickers(root = document) {
    root.querySelectorAll("input.datetime-picker").forEach((el) => {
        if (el._flatpickr) {
            return;
        }

        flatpickr(el, buildDatepickerOptions(el));
    });
}
