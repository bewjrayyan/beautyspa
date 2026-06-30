export function normalizeScheduleDate(value) {
    if (value === null || value === undefined || value === "") {
        return null;
    }

    const trimmed = String(value).trim();

    if (/^\d{4}-\d{2}-\d{2}$/.test(trimmed)) {
        return `${trimmed} 00:00:00`;
    }

    if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/.test(trimmed)) {
        return `${trimmed}:00`;
    }

    if (/^\d{4}-\d{2}-\d{2}/.test(trimmed)) {
        return trimmed;
    }

    const match = trimmed.match(
        /^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{1,2}):(\d{2}))?$/,
    );

    if (match) {
        const [, day, month, year, hour = "00", minute = "00"] = match;

        return `${year}-${month}-${day} ${String(hour).padStart(2, "0")}:${minute}:00`;
    }

    return trimmed;
}
