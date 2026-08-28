/** Shared Flatpickr locale + local-date parsing for AestheticCart. */

export const FLATPICKR_MONDAY_LOCALE = {
    firstDayOfWeek: 1,
};

/**
 * Parse Y-m-d as local calendar date (noon) to avoid UTC timezone shifts.
 */
export function parseLocalYmdDate(value) {
    const match = String(value || "").match(/^(\d{4})-(\d{2})-(\d{2})$/);

    if (!match) {
        return null;
    }

    return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]), 12, 0, 0);
}

/**
 * Parse Y-m-d or Y-m-d H:i as local date/time.
 */
export function parseLocalDateTime(value) {
    const match = String(value || "").match(
        /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?$/
    );

    if (!match) {
        return null;
    }

    return new Date(
        Number(match[1]),
        Number(match[2]) - 1,
        Number(match[3]),
        Number(match[4] ?? 12),
        Number(match[5] ?? 0),
        0
    );
}

export function coerceFlatpickrDate(value) {
    if (!value) {
        return undefined;
    }

    if (value instanceof Date) {
        return value;
    }

    if (value === "today") {
        return "today";
    }

    const ymd = parseLocalYmdDate(value);

    if (ymd) {
        return ymd;
    }

    const dateTime = parseLocalDateTime(value);

    if (dateTime) {
        return dateTime;
    }

    return value;
}

export function defaultFlatpickrParseDate(dateStr) {
    const local = parseLocalYmdDate(dateStr) ?? parseLocalDateTime(dateStr);

    return local ?? undefined;
}

export function mergeFlatpickrLocale(options = {}) {
    const locale = {
        ...FLATPICKR_MONDAY_LOCALE,
        ...(options.locale || {}),
        firstDayOfWeek: options.locale?.firstDayOfWeek ?? FLATPICKR_MONDAY_LOCALE.firstDayOfWeek,
    };

    return {
        ...options,
        locale,
        parseDate: options.parseDate || defaultFlatpickrParseDate,
    };
}

export function applyFlatpickrDateAttrs(options, el) {
    const next = { ...options };

    if (el.dataset.maxDate) {
        next.maxDate = coerceFlatpickrDate(el.dataset.maxDate);
    }

    if (el.dataset.minDate) {
        next.minDate = coerceFlatpickrDate(el.dataset.minDate);
    }

    const defaultRaw = el.dataset.defaultDate || el.value;

    if (defaultRaw && next.defaultDate == null) {
        next.defaultDate = coerceFlatpickrDate(defaultRaw);
    }

    return next;
}

export function buildStandardDatepickerOptions(el, overrides = {}) {
    const enableTime = el.hasAttribute("data-time");
    const noCalendar = el.hasAttribute("data-no-calender");

    let options = {
        mode: el.hasAttribute("data-range") ? "range" : "single",
        enableTime,
        noCalendar,
        dateFormat: enableTime ? "Y-m-d H:i" : "Y-m-d",
        altInput: true,
        altFormat: enableTime ? "d/m/Y H:i" : "d/m/Y",
        time_24hr: false,
        disableMobile: true,
    };

    options = applyFlatpickrDateAttrs(options, el);

    if (el.hasAttribute("data-enable-dates")) {
        const enabledDates = (el.dataset.enableDates || "")
            .split(",")
            .map((date) => date.trim())
            .filter(Boolean);

        options.enable = enabledDates.length ? enabledDates : [() => false];
    }

    return mergeFlatpickrLocale({ ...options, ...overrides });
}
