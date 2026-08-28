import flatpickr from "flatpickr";
import { buildStandardDatepickerOptions } from "./flatpickrLocale.js";

/**
 * Shared Flatpickr options for AestheticCart (storefront + vendor bundles).
 */
export function buildDatepickerOptions(el, overrides = {}) {
    return buildStandardDatepickerOptions(el, overrides);
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
