export function formatAppointmentTimeDisplay(slot) {
    if (!slot) {
        return "";
    }

    const parsed = parseAppointmentTimeParts(slot);

    if (!parsed) {
        return String(slot).trim();
    }

    const date = new Date();

    date.setHours(parsed.hour, parsed.minute, 0, 0);

    return date.toLocaleTimeString([], {
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
    });
}

export function normalizeAppointmentTime24(slot) {
    const parsed = parseAppointmentTimeParts(slot);

    if (!parsed) {
        return null;
    }

    return `${String(parsed.hour).padStart(2, "0")}:${String(parsed.minute).padStart(2, "0")}`;
}

function parseAppointmentTimeParts(slot) {
    const raw = String(slot || "").trim();

    if (!raw) {
        return null;
    }

    const match24 = raw.match(/^(\d{1,2}):(\d{2})$/);

    if (match24) {
        const hour = Number(match24[1]);
        const minute = Number(match24[2]);

        if (hour > 23 || minute > 59) {
            return null;
        }

        return { hour, minute };
    }

    const match12 = raw.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);

    if (match12) {
        let hour = Number(match12[1]) % 12;
        const minute = Number(match12[2]);

        if (minute > 59) {
            return null;
        }

        if (/pm/i.test(match12[3])) {
            hour += 12;
        }

        return { hour, minute };
    }

    const date = new Date(`1970-01-01 ${raw}`);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    return {
        hour: date.getHours(),
        minute: date.getMinutes(),
    };
}
