import flatpickr from "flatpickr";
import {
    coerceFlatpickrDate,
    mergeFlatpickrLocale,
    parseLocalYmdDate,
} from "./flatpickrLocale.js";

function resolveFlatpickrYear(dateValue) {
    if (!dateValue) {
        return null;
    }

    if (dateValue instanceof Date) {
        return dateValue.getFullYear();
    }

    const parsed = parseLocalYmdDate(dateValue) ?? new Date(dateValue);

    return Number.isNaN(parsed.getTime()) ? null : parsed.getFullYear();
}

function yearBounds(instance) {
    const maxYear = resolveFlatpickrYear(instance.config.maxDate) ?? new Date().getFullYear();
    const minYear = resolveFlatpickrYear(instance.config.minDate) ?? (maxYear - 120);

    return {
        minYear: Math.min(minYear, maxYear),
        maxYear,
    };
}

function syncYearSelect(instance) {
    const select = instance.calendarContainer?.querySelector(".account-dob-year-select");

    if (!select) {
        return;
    }

    const year = String(instance.currentYear);

    if (select.value !== year) {
        select.value = year;
    }
}

function applyYearChange(instance, year) {
    if (Number.isNaN(year)) {
        return;
    }

    const selected = instance.selectedDates[0];
    const month = selected ? selected.getMonth() : instance.currentMonth;
    const day = selected
        ? Math.min(selected.getDate(), new Date(year, month + 1, 0).getDate())
        : 1;

    instance.changeYear(year);

    if (selected) {
        instance.setDate(new Date(year, month, day, 12, 0, 0), false);
    }

    syncYearSelect(instance);
}

function installYearSelect(instance) {
    const monthNav = instance.calendarContainer?.querySelector(".flatpickr-current-month");

    if (!monthNav) {
        return;
    }

    const yearWrapper = monthNav.querySelector(".numInputWrapper");
    const yearInput = instance.currentYearElement || yearWrapper?.querySelector("input.cur-year");

    if (!yearWrapper || !yearInput) {
        return;
    }

    let select = yearWrapper.querySelector(".account-dob-year-select");
    const { minYear, maxYear } = yearBounds(instance);

    if (!select) {
        yearWrapper.classList.add("account-dob-year-wrap", "bp-portal-year-wrap");
        yearInput.classList.add("account-dob-year-input");
        yearInput.setAttribute("aria-hidden", "true");
        yearInput.tabIndex = -1;

        select = document.createElement("select");
        select.className = "account-dob-year-select bp-portal-year-select";
        select.setAttribute("aria-label", "Year");
        select.addEventListener("change", () => {
            applyYearChange(instance, parseInt(select.value, 10));
        });
        yearWrapper.appendChild(select);
    }

    const expectedOptions = maxYear - minYear + 1;

    if (select.options.length !== expectedOptions) {
        select.replaceChildren();

        for (let year = maxYear; year >= minYear; year -= 1) {
            const option = document.createElement("option");
            option.value = String(year);
            option.textContent = String(year);
            select.appendChild(option);
        }
    }

    syncYearSelect(instance);
}

export function buildDateOfBirthPickerOptions(input, { calendarClass = "account-dob-datepicker-calendar" } = {}) {
    const defaultDate = parseLocalYmdDate(input.dataset.defaultDate || input.value);

    const options = mergeFlatpickrLocale({
        mode: "single",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d M Y",
        disableMobile: true,
        animate: true,
        monthSelectorType: "dropdown",
        defaultDate: defaultDate || undefined,
        appendTo: document.body,
        parseDate: (dateStr) => parseLocalYmdDate(dateStr) ?? undefined,
        onReady: (_selectedDates, _dateStr, instance) => {
            instance.calendarContainer.classList.add(calendarClass);
            installYearSelect(instance);

            if (defaultDate) {
                instance.jumpToDate(defaultDate, false);
            }
        },
        onOpen: (_selectedDates, _dateStr, instance) => {
            instance.config.positionElement = instance.altInput || instance.input;
            installYearSelect(instance);

            const selected = instance.selectedDates[0];

            if (selected) {
                instance.jumpToDate(selected, false);
            }
        },
        onMonthChange: (_selectedDates, _dateStr, instance) => {
            syncYearSelect(instance);
        },
        onYearChange: (_selectedDates, _dateStr, instance) => {
            syncYearSelect(instance);
        },
    });

    if (input.dataset.maxDate) {
        options.maxDate = coerceFlatpickrDate(input.dataset.maxDate);
    }

    if (input.dataset.minDate) {
        options.minDate = coerceFlatpickrDate(input.dataset.minDate);
    }

    return options;
}

export function initDateOfBirthPickers(
    root = document,
    { selector = ".account-dob-picker", calendarClass = "account-dob-datepicker-calendar" } = {}
) {
    root.querySelectorAll(selector).forEach((input) => {
        if (input._flatpickr) {
            return;
        }

        flatpickr(input, buildDateOfBirthPickerOptions(input, { calendarClass }));
    });
}

export function syncDateOfBirthPickersOnSubmit(form, selector = ".account-dob-picker") {
    if (!form || form.dataset.dobSync === "1") {
        return;
    }

    form.dataset.dobSync = "1";
    form.addEventListener("submit", () => {
        form.querySelectorAll(selector).forEach((input) => {
            const picker = input._flatpickr;

            if (!picker) {
                return;
            }

            input.value = picker.selectedDates.length > 0
                ? picker.formatDate(picker.selectedDates[0], "Y-m-d")
                : "";
        });
    });
}
