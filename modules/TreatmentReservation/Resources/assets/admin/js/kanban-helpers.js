let beauticianAvatarLightboxReady = false;
const calendarBookingsById = new Map();
const kanbanBookingsById = new Map();
let previewOptions = {};
let previewLabels = {};
const previewDetailRequests = new Map();
const schedulingDetailRequests = new Map();

import { openManualBookingEditor } from "./manual-booking.js";
import { parseLocalDateTime } from "../../../../../Storefront/Resources/assets/public/js/lib/flatpickrLocale.js";

/** Local calendar date as Y-m-d (work-log “completed at”, not appointment). */
function currentWorkLogDateValue(date = new Date()) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
}

/** Local clock time as H:i for work-log pickers. */
function currentWorkLogTimeValue(date = new Date()) {
    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");

    return `${hours}:${minutes}`;
}

/** Latest completed_at from checklist items (for beautician_notes_at on save). */
function workLogStampFromChecklist(checklist = []) {
    const latest = checklist
        .filter((item) => item.completed && item.completed_at)
        .map((item) => new Date(item.completed_at))
        .filter((date) => !Number.isNaN(date.getTime()))
        .sort((left, right) => right - left)[0];

    if (!latest) {
        return { date: null, time: null };
    }

    return {
        date: currentWorkLogDateValue(latest),
        time: currentWorkLogTimeValue(latest),
    };
}

/** Match PHP `d M Y` + Flatpickr `h:i K` (e.g. "01 Sep 2026, 10:43 AM"). */
function formatWorkLogNoteSchedule(dateValue, timeValue) {
    if (!dateValue || !timeValue) {
        return "";
    }

    const treatmentAt = parseLocalDateTime(`${dateValue} ${timeValue}`);

    if (!treatmentAt) {
        return "";
    }

    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const day = String(treatmentAt.getDate()).padStart(2, "0");
    const month = months[treatmentAt.getMonth()];
    const year = treatmentAt.getFullYear();
    let hours = treatmentAt.getHours();
    const minutes = String(treatmentAt.getMinutes()).padStart(2, "0");
    const meridiem = hours >= 12 ? "PM" : "AM";

    hours = hours % 12 || 12;

    return `${day} ${month} ${year}, ${hours}:${minutes} ${meridiem}`;
}

/** Local ISO-8601 with offset so completed_at survives save/reload. */
function toIsoLocal(date = new Date()) {
    const pad = (value) => String(value).padStart(2, "0");
    const offsetMinutes = -date.getTimezoneOffset();
    const sign = offsetMinutes >= 0 ? "+" : "-";
    const absolute = Math.abs(offsetMinutes);

    return [
        `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`,
        `T${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`,
        `${sign}${pad(Math.floor(absolute / 60))}:${pad(absolute % 60)}`,
    ].join("");
}

function formatCompletedAtDisplay(value) {
    if (!value) {
        return "";
    }

    const date = value instanceof Date ? value : new Date(value);

    if (Number.isNaN(date.getTime())) {
        return "";
    }

    return formatWorkLogNoteSchedule(currentWorkLogDateValue(date), currentWorkLogTimeValue(date));
}

function updateChecklistItemStamp(row, completed, completedAt = null) {
    if (!row) {
        return;
    }

    const stampEl = row.querySelector(".tr-calendar-event-preview__checklist-stamp");

    if (!completed) {
        delete row.dataset.completedAt;

        if (stampEl) {
            stampEl.hidden = true;
            stampEl.textContent = "";
        }

        return;
    }

    const iso = completedAt || row.dataset.completedAt || toIsoLocal();
    row.dataset.completedAt = iso;

    if (stampEl) {
        stampEl.hidden = false;
        stampEl.textContent = formatCompletedAtDisplay(iso);
    }
}


export function setCalendarBookings(bookings) {
    calendarBookingsById.clear();

    bookings.forEach((booking) => {
        calendarBookingsById.set(String(booking.id), booking);
    });
}

export function setKanbanBookings(bookings) {
    kanbanBookingsById.clear();

    bookings.forEach((booking) => {
        kanbanBookingsById.set(String(booking.id), booking);
    });
}

export function getCalendarBooking(id) {
    return calendarBookingsById.get(String(id));
}

export function getCalendarBookingsForOrder(orderId) {
    const normalizedOrderId = String(orderId || "");

    if (!normalizedOrderId) {
        return [];
    }

    return Array.from(calendarBookingsById.values())
        .filter((booking) => String(booking.order_id || "") === normalizedOrderId)
        .sort((left, right) => {
            const leftSchedule = `${left.appointment_date_value || left.date || ""} ${left.appointment_time_value || left.time || ""}`;
            const rightSchedule = `${right.appointment_date_value || right.date || ""} ${right.appointment_time_value || right.time || ""}`;

            return leftSchedule.localeCompare(rightSchedule) || Number(left.id) - Number(right.id);
        });
}

export function getKanbanBooking(id) {
    return kanbanBookingsById.get(String(id));
}

export function resolveBooking(id) {
    return getCalendarBooking(id) || getKanbanBooking(id);
}

const TR_STATUS_ACCENT = {
    pending: "#ea580c",
    in_progress: "#4338ca",
    completed: "#047857",
};

export function escapeHtml(value) {
    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

function workLogChecklistItemMarkup(item, labels) {
    const id = item.id || `work-${Date.now()}-${Math.random().toString(16).slice(2)}`;
    const completedAt = item.completed && item.completed_at ? String(item.completed_at) : "";
    const stampText = formatCompletedAtDisplay(completedAt);
    const completedAtAttr = completedAt ? ` data-completed-at="${escapeHtml(completedAt)}"` : "";
    const stampAttrs = stampText ? "" : " hidden";
    const completedAtLabel = labels.completedAt || "Completed at";

    return `
        <li class="tr-calendar-event-preview__checklist-item" data-checklist-id="${escapeHtml(id)}"${completedAtAttr}>
            <label class="tr-calendar-event-preview__checklist-toggle">
                <input type="checkbox"${item.completed ? " checked" : ""}>
                <span class="sr-only">${escapeHtml(item.label || labels.itemPlaceholder || "Treatment task")}</span>
            </label>
            <div class="tr-calendar-event-preview__checklist-body">
                <input
                    type="text"
                    class="form-control tr-calendar-event-preview__checklist-label"
                    maxlength="160"
                    value="${escapeHtml(item.label || "")}"
                    placeholder="${escapeHtml(labels.itemPlaceholder || "Describe a treatment task")}"
                    aria-label="${escapeHtml(labels.itemPlaceholder || "Describe a treatment task")}"
                >
                <span
                    class="tr-calendar-event-preview__checklist-stamp"${stampAttrs}
                    aria-label="${escapeHtml(completedAtLabel)}"
                >${escapeHtml(stampText)}</span>
            </div>
            <button
                type="button"
                class="tr-calendar-event-preview__checklist-remove"
                aria-label="${escapeHtml(labels.removeItem || "Remove checklist item")}"
                title="${escapeHtml(labels.removeItem || "Remove checklist item")}"
            ><i class="fa fa-times" aria-hidden="true"></i></button>
        </li>
    `;
}

function workLogEditorMarkup(booking, labels) {
    const workLog = labels.workLog || {};
    const checklist = Array.isArray(booking.beautician_checklist) ? booking.beautician_checklist : [];
    const presets = Array.isArray(workLog.presets) ? workLog.presets : [];

    return `
        <div class="tr-calendar-event-preview__notes-editor tr-calendar-event-preview__work-log">
            <div class="tr-calendar-event-preview__work-log-head">
                <p>${escapeHtml(workLog.help || "Track treatment tasks and keep the customer note separate.")}</p>
            </div>

            <section class="tr-calendar-event-preview__work-log-step" aria-labelledby="tr-work-log-step-checklist">
                <div class="tr-calendar-event-preview__work-log-step-head">
                    <span class="tr-calendar-event-preview__work-log-step-num" aria-hidden="true">1</span>
                    <strong id="tr-work-log-step-checklist">${escapeHtml(workLog.checklist || "Treatment checklist")}</strong>
                    <span class="tr-calendar-event-preview__work-log-step-aside">${escapeHtml(workLog.quickAdd || "Quick add")}</span>
                </div>
                <div class="tr-calendar-event-preview__checklist-presets">
                    ${presets.map((preset) => `
                        <button type="button" class="tr-calendar-event-preview__checklist-preset" data-checklist-preset="${escapeHtml(preset)}">
                            <i class="fa fa-plus" aria-hidden="true"></i>${escapeHtml(preset)}
                        </button>
                    `).join("")}
                </div>
                <ul class="tr-calendar-event-preview__checklist">
                    ${checklist.map((item) => workLogChecklistItemMarkup(item, workLog)).join("")}
                </ul>
                <button type="button" class="tr-calendar-event-preview__add-checklist-item">
                    <i class="fa fa-plus-circle" aria-hidden="true"></i>${escapeHtml(workLog.customItem || "Add custom item")}
                </button>
                <button type="button" class="tr-calendar-event-preview__generate-note">
                    <i class="fa fa-magic" aria-hidden="true"></i>${escapeHtml(workLog.generateSummary || "Generate note from checklist")}
                </button>
            </section>

            <section class="tr-calendar-event-preview__work-log-step tr-calendar-event-preview__customer-note" aria-labelledby="tr-work-log-step-note">
                <div class="tr-calendar-event-preview__work-log-step-head">
                    <span class="tr-calendar-event-preview__work-log-step-num" aria-hidden="true">2</span>
                    <strong id="tr-work-log-step-note">${escapeHtml(workLog.customerNote || labels.beauticianNotes || "Customer note")}</strong>
                </div>
                <label class="sr-only" for="tr-booking-beautician-notes">${escapeHtml(workLog.customerNote || labels.beauticianNotes || "Customer note")}</label>
                <p>${escapeHtml(workLog.customerNoteHelp || "This note is visible to the customer.")}</p>
                <textarea id="tr-booking-beautician-notes" class="form-control" rows="4" maxlength="5000" data-booking-id="${escapeHtml(booking.id)}">${escapeHtml(booking.beautician_notes || "")}</textarea>
            </section>

            <button type="button" class="tr-calendar-event-preview__action-btn tr-calendar-event-preview__action-btn--primary tr-calendar-event-preview__save-notes" data-booking-id="${escapeHtml(booking.id)}">
                <i class="fa fa-save" aria-hidden="true"></i>${escapeHtml(labels.saveNotes || "Save work log")}
            </button>
        </div>
    `;
}

export function hexToRgba(hex, alpha) {
    const normalized = String(hex || "#6366f1").replace("#", "");

    if (normalized.length === 3) {
        const r = parseInt(normalized[0] + normalized[0], 16);
        const g = parseInt(normalized[1] + normalized[1], 16);
        const b = parseInt(normalized[2] + normalized[2], 16);

        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    const r = parseInt(normalized.slice(0, 2), 16);
    const g = parseInt(normalized.slice(2, 4), 16);
    const b = parseInt(normalized.slice(4, 6), 16);

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

export function calendarStatusClass(status) {
    const allowed = ["pending", "in_progress", "completed"];

    return allowed.includes(status) ? status : "pending";
}

function beauticianAvatarMarkup(booking, sizeClass) {
    const color = booking.beautician_color || "#6366f1";
    const name = booking.beautician_name || "—";
    const classes = `tr-beautician-avatar ${sizeClass}`;

    if (booking.beautician_avatar) {
        return `<span class="${classes} tr-beautician-avatar--zoomable" role="button" tabindex="0" title="${escapeHtml(name)}" data-preview-src="${escapeHtml(booking.beautician_avatar)}" data-preview-name="${escapeHtml(name)}" style="background-color:${escapeHtml(color)};box-shadow:0 0 0 2px ${hexToRgba(color, 0.35)}"><img src="${escapeHtml(booking.beautician_avatar)}" alt="${escapeHtml(name)}" draggable="false"></span>`;
    }

    const initial = escapeHtml(booking.beautician_initial || name.charAt(0).toUpperCase() || "?");

    return `<span class="${classes}" style="background-color:${escapeHtml(color)};box-shadow:0 0 0 2px ${hexToRgba(color, 0.35)}" title="${escapeHtml(name)}">${initial}</span>`;
}

export function bookingIsOwnForPortal(booking, portalBeauticianId = null) {
    if (!portalBeauticianId) {
        return true;
    }

    if (typeof booking?.is_own_booking === "boolean") {
        return booking.is_own_booking;
    }

    if (booking?.beautician_id == null || booking?.beautician_id === "") {
        return true;
    }

    return String(booking.beautician_id) === String(portalBeauticianId);
}

export function bookingAllowsDetail(booking, portalBeauticianId = null) {
    if (!portalBeauticianId) {
        return true;
    }

    if (typeof booking?.can_open_detail === "boolean") {
        return booking.can_open_detail;
    }

    // Fallback for payloads without can_open_detail: own bookings only.
    return bookingIsOwnForPortal(booking, portalBeauticianId);
}

function previewOptionsForBooking(booking, options = {}) {
    if (bookingIsOwnForPortal(booking, options.portalBeauticianId || null)) {
        return options;
    }

    return {
        ...options,
        allowBeauticianNotes: false,
        canSendNotifications: false,
        crmCanEdit: false,
        portalGenericWhatsApp: false,
        consultationUrlTemplate: "",
        manualBookingEditEnabled: false,
        tbaScheduleEnabled: false,
    };
}

export function buildCalendarEventHtml(booking, { showBeautician = true, clickable = true, isOwn = true } = {}) {
    const status = calendarStatusClass(booking.status);
    const color = booking.beautician_color || "#6366f1";
    const treatment = escapeHtml(booking.treatment_name || "Treatment");
    const customer = escapeHtml(booking.customer_name || "—");
    const time = escapeHtml(booking.time || "");
    const beauticianRow = showBeautician && booking.beautician_name
        ? `<span class="tr-cal-event-beautician">${beauticianAvatarMarkup(booking, "tr-beautician-avatar--xs")}<span class="tr-cal-event-beautician-name">${escapeHtml(booking.beautician_name)}</span></span>`
        : "";
    const classes = [
        "tr-cal-event",
        clickable ? "tr-cal-event--clickable" : "",
        !isOwn ? "tr-cal-event--others" : "",
    ].filter(Boolean).join(" ");
    const canDrag = isOwn && (booking.can_reschedule || booking.can_schedule_tba);
    const dragAttrs = canDrag
        ? ` draggable="true" data-cal-draggable="1" data-product-id="${escapeHtml(String(booking.product_id || ""))}" data-spa-branch-id="${escapeHtml(String(booking.spa_branch_id || ""))}" data-beautician-id="${escapeHtml(String(booking.beautician_id || ""))}" data-is-tba="${booking.can_schedule_tba || booking.is_tba || booking.schedule_status === "tba" ? "1" : "0"}"`
        : "";
    const clickAttrs = clickable
        ? ` class="${classes}" role="button" tabindex="0"`
        : ` class="${classes}"`;

    return `
        <div${clickAttrs}${dragAttrs} data-booking-id="${escapeHtml(booking.id)}" data-status="${status}" style="--tr-beautician-color:${escapeHtml(color)};border-left-color:${escapeHtml(color)};background:${hexToRgba(color, 0.12)};border-color:${hexToRgba(color, 0.28)}">
            <div class="tr-cal-event-top">
                <span class="tr-cal-event-time">${time}</span>
                <span class="tr-cal-event-status-dot" title="${status.replace("_", " ")}" style="background:${escapeHtml(color)};box-shadow:0 0 0 2px ${hexToRgba(color, 0.35)}"></span>
            </div>
            ${beauticianRow}
            <strong class="tr-cal-event-customer">${customer}</strong>
            <span class="tr-cal-event-treatment">${treatment}</span>
        </div>
    `;
}

export function collectBeauticiansFromBookings(bookings) {
    const map = new Map();

    bookings.forEach((booking) => {
        const key = booking.beautician_id || booking.beautician_name;

        if (!key) {
            return;
        }

        if (!map.has(key)) {
            map.set(key, booking);
        }
    });

    return Array.from(map.values()).sort((a, b) =>
        (a.beautician_name || "").localeCompare(b.beautician_name || "")
    );
}

export function buildCalendarLegendHtml(beauticians, label) {
    if (!beauticians.length) {
        return "";
    }

    const items = beauticians
        .map((booking) => {
            const color = booking.beautician_color || "#6366f1";

            return `
                <span class="tr-calendar-legend__item">
                    ${beauticianAvatarMarkup(booking, "tr-beautician-avatar--sm")}
                    <span class="tr-calendar-legend__name">${escapeHtml(booking.beautician_name || "—")}</span>
                    <span class="tr-calendar-legend__swatch" style="background:${escapeHtml(color)}"></span>
                </span>
            `;
        })
        .join("");

    return `
        <div class="tr-calendar-legend__inner">
            <span class="tr-calendar-legend__label">${escapeHtml(label)}</span>
            <div class="tr-calendar-legend__items">${items}</div>
        </div>
    `;
}

function statusLabel(status, labels) {
    const map = {
        pending: labels.statusPending,
        in_progress: labels.statusInProgress,
        completed: labels.statusCompleted,
    };

    return map[status] || status;
}

function getCalendarEventPreviewOverlay() {
    let overlay = document.getElementById("tr-calendar-event-preview");

    if (overlay) {
        const panel = overlay.querySelector(".tr-calendar-event-preview__panel");
        if (panel && !panel.querySelector(".tr-calendar-event-preview__handle")) {
            const handle = document.createElement("div");
            handle.className = "tr-calendar-event-preview__handle";
            handle.setAttribute("aria-hidden", "true");
            panel.insertBefore(handle, panel.firstChild);
        }
        return overlay;
    }

    overlay = document.createElement("aside");
    overlay.id = "tr-calendar-event-preview";
    overlay.className = "tr-calendar-event-preview";
    overlay.hidden = true;
    overlay.setAttribute("aria-hidden", "true");
    overlay.innerHTML = `
        <div class="tr-calendar-event-preview__backdrop" data-dismiss></div>
        <div class="tr-calendar-event-preview__panel" role="dialog" aria-modal="true" aria-labelledby="tr-calendar-event-preview-title" aria-describedby="tr-calendar-event-preview-customer">
            <div class="tr-calendar-event-preview__handle" aria-hidden="true"></div>
            <header class="tr-calendar-event-preview__head">
                <div class="tr-calendar-event-preview__head-col tr-calendar-event-preview__head-col--info">
                    <div class="tr-calendar-event-preview__head-title-row">
                        <h3 class="tr-calendar-event-preview__title" id="tr-calendar-event-preview-title"></h3>
                        <div class="tr-calendar-event-preview__head-chips" id="tr-calendar-event-preview-chips"></div>
                    </div>
                    <div class="tr-calendar-event-preview__head-customer" id="tr-calendar-event-preview-customer"></div>
                </div>
                <div class="tr-calendar-event-preview__head-col tr-calendar-event-preview__head-col--refs" id="tr-calendar-event-preview-refs"></div>
                <button type="button" class="tr-calendar-event-preview__close" data-dismiss aria-label="Close">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>
            </header>
            <div class="tr-calendar-event-preview__body"></div>
        </div>
    `;

    document.body.appendChild(overlay);

    overlay.addEventListener("click", (event) => {
        if (event.target.closest("[data-dismiss]")) {
            closeCalendarEventPreview();
        }
    });

    return overlay;
}


function previewCrmContactRow({ icon, label, value, href = "", blurred = false }) {
    if (!value) {
        return "";
    }

    const valueClass = [
        "tr-calendar-event-preview__crm-value",
        blurred ? "tr-calendar-event-preview__crm-value--blurred" : "",
        href && !blurred ? "tr-calendar-event-preview__crm-value--link" : "",
    ].filter(Boolean).join(" ");

    const inner = `
        <span class="tr-calendar-event-preview__crm-icon" aria-hidden="true"><i class="fa ${escapeHtml(icon)}"></i></span>
        <span class="tr-calendar-event-preview__crm-meta">
            <span class="tr-calendar-event-preview__crm-label">${escapeHtml(label)}</span>
            <span class="${valueClass}">${escapeHtml(value)}</span>
        </span>
    `;

    if (href && !blurred) {
        return `<a href="${escapeHtml(href)}" class="tr-calendar-event-preview__crm-row tr-calendar-event-preview__crm-row--link">${inner}</a>`;
    }

    return `<div class="tr-calendar-event-preview__crm-row">${inner}</div>`;
}

function previewField(label, value, { href = "", full = false, muted = false, blurred = false, icon = "" } = {}) {
    if (! value) {
        return "";
    }

    const valueHtml = href && !blurred
        ? `<a href="${escapeHtml(href)}" class="tr-calendar-event-preview__field-link">${escapeHtml(value)}</a>`
        : escapeHtml(value);
    const valueClass = [
        "tr-calendar-event-preview__field-value",
        blurred ? "tr-calendar-event-preview__field-value--blurred" : "",
    ].filter(Boolean).join(" ");
    const labelHtml = icon
        ? `<span class="tr-calendar-event-preview__field-label"><i class="fa ${escapeHtml(icon)}" aria-hidden="true"></i> ${escapeHtml(label)}</span>`
        : `<span class="tr-calendar-event-preview__field-label">${escapeHtml(label)}</span>`;

    return `
        <div class="tr-calendar-event-preview__field${full ? " tr-calendar-event-preview__field--full" : ""}${muted ? " tr-calendar-event-preview__field--muted" : ""}${blurred ? " tr-calendar-event-preview__field--blurred" : ""}">
            ${labelHtml}
            <span class="${valueClass}"${blurred ? ' aria-hidden="true"' : ""}>${valueHtml}</span>
        </div>
    `;
}

function previewReceiptField(label, receiptUrl, viewLabel) {
    const url = String(receiptUrl || "").trim();

    if (! url) {
        return "";
    }

    const safeUrl = escapeHtml(url);
    const isImage = /\.(jpe?g|png|gif|webp|bmp)(\?.*)?$/i.test(url);
    const valueHtml = isImage
        ? `<a href="${safeUrl}" class="tr-calendar-event-preview__receipt-link" target="_blank" rel="noopener noreferrer">
                <img src="${safeUrl}" alt="" class="tr-calendar-event-preview__receipt-thumb" loading="lazy">
                <span class="tr-calendar-event-preview__receipt-caption">${escapeHtml(viewLabel)}</span>
           </a>`
        : `<a href="${safeUrl}" class="tr-calendar-event-preview__field-link" target="_blank" rel="noopener noreferrer">${escapeHtml(viewLabel)}</a>`;

    return `
        <div class="tr-calendar-event-preview__field tr-calendar-event-preview__field--receipt tr-calendar-event-preview__field--full">
            <span class="tr-calendar-event-preview__field-label">${escapeHtml(label)}</span>
            <span class="tr-calendar-event-preview__field-value">${valueHtml}</span>
        </div>
    `;
}

function previewSection(title, content, modifier = "", id = "") {
    if (! content.trim()) {
        return "";
    }

    const modifierClass = modifier
        ? ` tr-calendar-event-preview__section--${escapeHtml(modifier)}`
        : "";
    const idAttribute = id ? ` id="${escapeHtml(id)}"` : "";

    return `
        <section class="tr-calendar-event-preview__section${modifierClass}"${idAttribute}>
            <h4 class="tr-calendar-event-preview__section-title">${escapeHtml(title)}</h4>
            <div class="tr-calendar-event-preview__section-card">
                ${content}
            </div>
        </section>
    `;
}

function previewActionButton(className, content, attrs = "") {
    return `<button type="button" class="tr-calendar-event-preview__action-btn ${className}" ${attrs}>${content}</button>`;
}

function bookingIsTbaSchedule(booking) {
    return Boolean(booking?.is_tba || booking?.schedule_status === "tba");
}

function formatDurationMinutes(minutes, labels) {
    const totalMinutes = Math.max(0, Number(minutes) || 0);

    if (! totalMinutes) {
        return "";
    }

    const hours = Math.floor(totalMinutes / 60);
    const remainingMinutes = totalMinutes % 60;
    const parts = [];

    if (hours) {
        const hourTemplate = hours === 1
            ? (labels.durationHour || ":count hour")
            : (labels.durationHours || ":count hours");
        parts.push(hourTemplate.replace(":count", String(hours)));
    }

    if (remainingMinutes || ! hours) {
        parts.push((labels.durationMinutes || ":count min").replace(":count", String(remainingMinutes || totalMinutes)));
    }

    return parts.join(" ");
}

function formatDurationBadge(minutes, labels) {
    const totalMinutes = Math.max(0, Number(minutes) || 0);

    if (! totalMinutes) {
        return "";
    }

    const hours = Math.floor(totalMinutes / 60);
    const remainingMinutes = totalMinutes % 60;

    if (hours && remainingMinutes) {
        return (labels.durationBadgeHoursMinutes || ":hoursHrs :minutesMin Session")
            .replace(":hours", String(hours))
            .replace(":minutes", String(remainingMinutes));
    }

    if (hours) {
        return (hours === 1
            ? (labels.durationBadgeHour || ":countHr Session")
            : (labels.durationBadgeHours || ":countHrs Session"))
            .replace(":count", String(hours));
    }

    return (labels.durationBadgeMinutes || ":countMin Session")
        .replace(":count", String(totalMinutes));
}

function treatmentIndexFromHeading(heading) {
    const match = String(heading || "").match(/(\d+)\s*$/);

    return match ? Number(match[1]) : null;
}

function orderBookingRefsForPreview(booking) {
    if (Array.isArray(booking.order_bookings) && booking.order_bookings.length) {
        return booking.order_bookings;
    }

    if (booking.order_id) {
        return getCalendarBookingsForOrder(booking.order_id).map((entry) => ({
            id: entry.id,
            reference_code: entry.reference_code || (entry.id ? `B${entry.id}` : ""),
        }));
    }

    return [{
        id: booking.id,
        reference_code: booking.reference_code || (booking.id ? `B${booking.id}` : ""),
    }];
}

function orderBookingRefForTreatment(booking, heading) {
    const treatmentIndex = treatmentIndexFromHeading(heading);

    if (! treatmentIndex) {
        return booking.reference_code || (booking.id ? `B${booking.id}` : "");
    }

    const refs = orderBookingRefsForPreview(booking);
    const entry = refs[treatmentIndex - 1];

    return entry?.reference_code || "";
}

function orderNotesMarkup(notes, booking = {}, labels = {}) {
    const rawNotes = String(notes || "").trim();

    if (! rawNotes) {
        return "";
    }

    const lines = rawNotes.split(/\r?\n/).map((line) => line.trim()).filter(Boolean);
    const groups = [];
    const unmatched = [];

    lines.forEach((line) => {
        const match = line.match(/^(.+?\s+\d+)\s+([^:]+):\s*(.*)$/u);

        if (! match) {
            unmatched.push(line);
            return;
        }

        const [, heading, label, value] = match;
        let group = groups.find((candidate) => candidate.heading === heading);

        if (! group) {
            group = { heading, rows: [] };
            groups.push(group);
        }

        group.rows.push({ label, value });
    });

    if (! groups.length || unmatched.length) {
        return `<p class="tr-calendar-event-preview__note-body">${escapeHtml(rawNotes)}</p>`;
    }

    const refTitle = labels.bookingIdTitle || "Treatment reference — quote this when contacting the clinic";
    const refLabel = labels.bookingId || "Ref";

    return `
        <div class="tr-calendar-event-preview__order-note-groups">
            ${groups.map((group) => {
                const ref = orderBookingRefForTreatment(booking, group.heading);

                return `
                <section class="tr-calendar-event-preview__order-note-group">
                    <div class="tr-calendar-event-preview__order-note-head">
                        <span class="tr-calendar-event-preview__order-note-badge">${escapeHtml(group.heading)}</span>
                        ${ref ? `<span class="tr-calendar-event-preview__order-note-ref" title="${escapeHtml(refTitle)}">${escapeHtml(refLabel)} ${escapeHtml(ref)}</span>` : ""}
                    </div>
                    <dl>
                        ${group.rows.map((row) => `
                            <div>
                                <dt>${escapeHtml(row.label)}</dt>
                                <dd>${escapeHtml(row.value || "—")}</dd>
                            </div>
                        `).join("")}
                    </dl>
                </section>
            `;
            }).join("")}
        </div>
    `;
}

function previewNotificationVisibility(booking, options = {}) {
    const status = calendarStatusClass(booking.status);
    const active = status === "pending" || status === "in_progress";
    const customerPhone = String(booking.customer_phone || "").trim();
    const beauticianPhone = booking.beautician_phone_available === true
        || booking.beautician_phone_available === 1
        || booking.beautician_phone_available === "1";

    return {
        customerReminder: Boolean(options.canSendNotifications && active && customerPhone),
        beauticianReminder: Boolean(options.canSendNotifications && active && beauticianPhone),
    };
}

function buildPreviewInsightChips(booking, labels) {
    const alerts = Array.isArray(booking.inline_alerts) ? booking.inline_alerts : [];

    return [
        booking.customer_history_label
            ? `<span class="tr-calendar-event-preview__chip">${escapeHtml(booking.customer_history_label)}</span>`
            : "",
        booking.loyalty_tier_name
            ? `<span class="tr-calendar-event-preview__chip tr-calendar-event-preview__chip--loyalty"><i class="fa fa-star" aria-hidden="true"></i> ${escapeHtml(booking.loyalty_tier_name)}</span>`
            : "",
        ...alerts.map((alert) => `
            <span class="tr-calendar-event-preview__chip tr-calendar-event-preview__chip--${escapeHtml(alert.level || "info")}">
                ${escapeHtml(alert.label || "")}
            </span>
        `),
        booking.reminder_sent
            ? `<span class="tr-calendar-event-preview__chip tr-calendar-event-preview__chip--sent">${escapeHtml(labels.reminderSent || "Reminder sent")}</span>`
            : (booking.reminder_due
                ? `<span class="tr-calendar-event-preview__chip tr-calendar-event-preview__chip--due">${escapeHtml(labels.reminderDue || "Due for reminder")}</span>`
                : ""),
        booking.beautician_reminder_sent
            ? `<span class="tr-calendar-event-preview__chip tr-calendar-event-preview__chip--sent">${escapeHtml(labels.beauticianReminderSent || "Beautician reminder sent")}</span>`
            : "",
    ].filter(Boolean).join("");
}

function buildCustomerPreviewMarkup(booking, labels, options = {}) {
    const blurContact = typeof booking.blur_customer_contact === "boolean"
        ? booking.blur_customer_contact
        : (booking.blur_customer_contact === 1 || booking.blur_customer_contact === "1")
            ? true
            : !bookingIsOwnForPortal(booking, options.portalBeauticianId || null);
    const customerName = booking.customer_name || "—";
    const phoneHref = !blurContact && booking.customer_phone
        ? `tel:${String(booking.customer_phone).replace(/[^\d+]/g, "")}`
        : "";

    return `
        <div class="tr-calendar-event-preview__crm">
            <div class="tr-calendar-event-preview__crm-head">
                <div class="tr-calendar-event-preview__crm-identity">
                    <strong class="tr-calendar-event-preview__crm-name">${escapeHtml(customerName)}</strong>
                    <div class="tr-calendar-event-preview__crm-contacts">
                        ${previewCrmContactRow({
                            icon: "fa-phone",
                            label: labels.phone || "Phone",
                            value: booking.customer_phone || "",
                            href: phoneHref,
                            blurred: Boolean(blurContact && booking.customer_phone),
                        })}
                        ${previewCrmContactRow({
                            icon: "fa-envelope",
                            label: labels.email || "Email",
                            value: booking.customer_email || "",
                            blurred: Boolean(blurContact && booking.customer_email),
                        })}
                    </div>
                </div>
            </div>
        </div>
    `;
}

export function buildCalendarEventPreviewHtml(booking, labels, options = {}) {
    const status = calendarStatusClass(booking.status);
    const color = booking.beautician_color || "#6366f1";
    const statusText = statusLabel(status, labels);
    const timeRange = (booking.appointment_time_range || booking.time || booking.appointment_time || "—").trim();
    const durationMinutes = Number(booking.slot_duration_minutes) || 0;
    const durationLabel = durationMinutes > 0
        ? formatDurationMinutes(durationMinutes, labels)
        : (booking.duration_session_label || booking.treatment_subtitle || "");
    const paymentLabel = (booking.payment_status_label || "").trim();
    const totalFormatted = (booking.total_formatted || "").trim();
    const treatmentSelection = String(booking.treatment_selection || "").trim();
    const isTbaSchedule = bookingIsTbaSchedule(booking);
    const tbaLabel = labels.tbaBadge || "TBA";
    const showAppointmentDuration = Boolean(durationLabel) && ! isTbaSchedule;
    const treatmentName = booking.treatment_name || "—";
    const notify = previewNotificationVisibility(booking, options);
    const sectionPrefix = `tr-preview-${escapeHtml(String(booking.id))}`;

    const statusTitle = labels.statusTitle || labels.status || "Job sheet status";
    const statusControl = status !== "canceled" && options.crmCanEdit && options.statusUrlTemplate
        ? `
            <div class="tr-calendar-event-preview__status-control">
                <label
                    class="tr-calendar-event-preview__status-label"
                    for="tr-preview-status-${escapeHtml(String(booking.id))}"
                >${escapeHtml(statusTitle)}</label>
                <select
                    id="tr-preview-status-${escapeHtml(String(booking.id))}"
                    class="tr-calendar-event-preview__status-select tr-calendar-event-preview__status-select--${escapeHtml(status)}"
                    data-preview-status
                    data-booking-id="${escapeHtml(String(booking.id))}"
                    data-current-status="${escapeHtml(status)}"
                    aria-label="${escapeHtml(statusTitle)}"
                    title="${escapeHtml(statusTitle)}"
                >
                    <option value="pending"${status === "pending" ? " selected" : ""}>${escapeHtml(labels.statusPending || "Pending")}</option>
                    <option value="in_progress"${status === "in_progress" ? " selected" : ""}>${escapeHtml(labels.statusInProgress || "In Progress")}</option>
                    <option value="completed"${status === "completed" ? " selected" : ""}>${escapeHtml(labels.statusCompleted || "Completed")}</option>
                </select>
            </div>
        `
        : `
            <div class="tr-calendar-event-preview__status-control">
                <span class="tr-calendar-event-preview__status-label">${escapeHtml(statusTitle)}</span>
                <span class="tr-calendar-event-preview__status tr-calendar-event-preview__status--${status}">${escapeHtml(statusText)}</span>
            </div>
        `;

    const orderMetaChip = booking.order_url && !options.hideOrderLink
        ? `<a href="${escapeHtml(booking.order_url)}" class="tr-calendar-event-preview__meta-chip tr-calendar-event-preview__meta-chip--order" target="_blank" rel="noopener noreferrer"><i class="fa fa-external-link" aria-hidden="true"></i> ${escapeHtml(labels.viewOrder || "View order")}</a>`
        : "";

    const metaChips = [
        booking.source_label ? `<span class="tr-calendar-event-preview__meta-chip tr-calendar-event-preview__meta-chip--source">${escapeHtml(booking.source_label)}</span>` : "",
        booking.spa_branch_name ? `<span class="tr-calendar-event-preview__meta-chip tr-calendar-event-preview__meta-chip--branch">${escapeHtml(booking.spa_branch_name)}</span>` : "",
        orderMetaChip,
    ].filter(Boolean).join("");

    const showStaff = Boolean(booking.beautician_name) && !options.hideBeautician;
    const appointmentDate = isTbaSchedule
        ? tbaLabel
        : (booking.appointment_date || booking.date || "—");
    const appointmentTime = isTbaSchedule
        ? tbaLabel
        : (timeRange || "—");
    const durationBlock = showAppointmentDuration
        ? `
            <div class="tr-calendar-event-preview__appointment-duration">
                <span class="tr-calendar-event-preview__appointment-icon" aria-hidden="true"><i class="fa fa-hourglass-half"></i></span>
                <div class="tr-calendar-event-preview__appointment-slot-copy">
                    <span class="tr-calendar-event-preview__appointment-label">${escapeHtml(labels.duration || "Duration")}</span>
                    <strong>${escapeHtml(durationLabel)}</strong>
                </div>
            </div>
        `
        : "";
    const staffBlock = showStaff
        ? `
            <div class="tr-calendar-event-preview__appointment-specialist">
                ${beauticianAvatarMarkup(booking, "tr-beautician-avatar--md")}
                <div class="tr-calendar-event-preview__appointment-specialist-meta">
                    <strong>${escapeHtml(booking.beautician_name)}</strong>
                    ${booking.beautician_job_title ? `<span>${escapeHtml(booking.beautician_job_title)}</span>` : ""}
                    ${Array.isArray(booking.beautician_branches) && booking.beautician_branches.length
                        ? `<div class="tr-calendar-event-preview__appointment-branches" aria-label="${escapeHtml(labels.branch || "Branch")}">
                            ${booking.beautician_branches.map((branch) => `
                                <span class="tr-calendar-event-preview__appointment-branch${branch.is_current ? " is-current" : ""}">
                                    <i class="fa fa-map-marker" aria-hidden="true"></i>
                                    ${escapeHtml(branch.name || "")}
                                </span>
                            `).join("")}
                           </div>`
                        : ""}
                </div>
            </div>
        `
        : "";
    const appointmentTop = (showStaff || showAppointmentDuration)
        ? `
            <div class="tr-calendar-event-preview__appointment-top">
                ${staffBlock}
                ${durationBlock}
            </div>
        `
        : "";
    const appointmentSection = previewSection(labels.sectionAppointment || "Specialist & schedule", `
        <div class="tr-calendar-event-preview__appointment">
            ${appointmentTop}
            ${(showStaff || showAppointmentDuration) ? '<div class="tr-calendar-event-preview__appointment-divider" aria-hidden="true"></div>' : ""}
            <div class="tr-calendar-event-preview__appointment-schedule">
                <div class="tr-calendar-event-preview__appointment-slot">
                    <span class="tr-calendar-event-preview__appointment-icon" aria-hidden="true"><i class="fa fa-calendar"></i></span>
                    <div class="tr-calendar-event-preview__appointment-slot-copy">
                        <span class="tr-calendar-event-preview__appointment-label">${escapeHtml(labels.date || "Date")}</span>
                        <strong>${escapeHtml(appointmentDate)}</strong>
                    </div>
                </div>
                <div class="tr-calendar-event-preview__appointment-slot">
                    <span class="tr-calendar-event-preview__appointment-icon" aria-hidden="true"><i class="fa fa-clock-o"></i></span>
                    <div class="tr-calendar-event-preview__appointment-slot-copy">
                        <span class="tr-calendar-event-preview__appointment-label">${escapeHtml(labels.time || "Time")}</span>
                        <strong>${escapeHtml(appointmentTime)}</strong>
                    </div>
                </div>
            </div>
        </div>
    `, "appointment", `${sectionPrefix}-appointment`);

    const treatmentMedia = booking.product_image
        ? `<img class="tr-calendar-event-preview__treatment-thumb" src="${escapeHtml(booking.product_image)}" alt="${escapeHtml(treatmentName)}" loading="lazy" decoding="async">`
        : `<span class="tr-calendar-event-preview__treatment-thumb tr-calendar-event-preview__treatment-thumb--fallback" aria-hidden="true"><i class="fa fa-image"></i></span>`;
    const treatmentSection = previewSection(labels.sectionTreatment || "Treatment & payment", `
        <div class="tr-calendar-event-preview__treatment-hero">
            ${treatmentMedia}
            <div class="tr-calendar-event-preview__treatment-copy">
                <strong>${escapeHtml(treatmentName)}</strong>
                ${treatmentSelection ? `
                    <div class="tr-calendar-event-preview__treatment-meta">
                        <div class="tr-calendar-event-preview__treatment-session">
                            <span class="tr-calendar-event-preview__treatment-session-label">${escapeHtml(labels.session || "Session")}</span>
                            <strong class="tr-calendar-event-preview__treatment-session-value">${escapeHtml(treatmentSelection)}</strong>
                        </div>
                    </div>
                ` : ""}
            </div>
        </div>
        <div class="tr-calendar-event-preview__treatment-payment">
            ${previewField(labels.category, booking.category_name || "", { full: true })}
            <div class="tr-calendar-event-preview__treatment-payment-row">
                ${previewField(labels.total, totalFormatted)}
                ${previewField(labels.payment, paymentLabel)}
            </div>
            ${previewReceiptField(
                labels.paymentReceipt || "Payment receipt",
                booking.payment_receipt_url,
                labels.viewReceipt || "View receipt",
            )}
        </div>
    `, "treatment", `${sectionPrefix}-treatment`);

    const orderNotesSection = booking.notes
        ? previewSection(labels.orderNotes || "Order notes", `
            <div class="tr-calendar-event-preview__note">
                ${orderNotesMarkup(booking.notes, booking, labels)}
            </div>
        `, "order-notes", `${sectionPrefix}-order-notes`)
        : "";

    let workLogSection = "";
    let beauticianNotesSection = "";
    if (options.allowBeauticianNotes) {
        workLogSection = previewSection(
            labels.workLog?.title || labels.beauticianNotes || "Treatment work log",
            workLogEditorMarkup(booking, labels),
            "work-log",
            `${sectionPrefix}-work-log`
        );
    } else if (booking.beautician_notes) {
        beauticianNotesSection = previewSection(labels.beauticianNotes || "Beautician notes", `
            <div class="tr-calendar-event-preview__note">
                <p class="tr-calendar-event-preview__note-body">${escapeHtml(booking.beautician_notes)}</p>
            </div>
        `, "notes", `${sectionPrefix}-beautician-notes`);
    }

    const activitySection = options.showActivityLog && Array.isArray(booking.recent_activities) && booking.recent_activities.length
        ? `
            <details class="tr-calendar-event-preview__section tr-calendar-event-preview__section--activity tr-calendar-event-preview__disclosure" id="${sectionPrefix}-activity">
                <summary class="tr-calendar-event-preview__disclosure-summary">
                    <span class="tr-calendar-event-preview__disclosure-title">
                        <i class="fa fa-history" aria-hidden="true"></i>
                        ${escapeHtml(labels.activityTitle || "Activity log")}
                    </span>
                    <span class="tr-calendar-event-preview__disclosure-meta">
                        <span class="tr-calendar-event-preview__disclosure-count">${booking.recent_activities.length}</span>
                        <span class="tr-calendar-event-preview__disclosure-action tr-calendar-event-preview__disclosure-action--show">${escapeHtml(labels.activityShow || "Show")}</span>
                        <span class="tr-calendar-event-preview__disclosure-action tr-calendar-event-preview__disclosure-action--hide">${escapeHtml(labels.activityHide || "Hide")}</span>
                        <i class="fa fa-chevron-down tr-calendar-event-preview__disclosure-chevron" aria-hidden="true"></i>
                    </span>
                </summary>
                <div class="tr-calendar-event-preview__section-card">
                    <ul class="tr-calendar-event-preview__activity-list">
                        ${booking.recent_activities.map((activity) => `
                            <li class="tr-calendar-event-preview__activity-item">
                                <span class="tr-calendar-event-preview__activity-time">${escapeHtml(activity.created_at || "")}</span>
                                <div class="tr-calendar-event-preview__activity-copy">
                                    <strong>${escapeHtml(activity.actor_name || "—")}</strong>
                                    <span>${escapeHtml(activity.summary || "")}</span>
                                </div>
                            </li>
                        `).join("")}
                    </ul>
                </div>
            </details>
        `
        : "";

    const actionButtons = [
        (bookingIsOwnForPortal(booking, options.portalBeauticianId || null) && (booking.customer_phone || booking.id))
            ? previewActionButton(
                "tr-calendar-event-preview__profile tr-calendar-event-preview__action-btn--ghost",
                `<i class="fa fa-user" aria-hidden="true"></i><span>${escapeHtml(labels.actionProfileShort || "Profile")}</span>`,
                `data-customer-profile data-booking-id="${escapeHtml(String(booking.id))}" aria-label="${escapeHtml(labels.viewProfile || "View profile")}" title="${escapeHtml(labels.viewProfile || "View profile")}"`
            )
            : "",
        notify.customerReminder
            ? previewActionButton(
                "tr-calendar-event-preview__whatsapp-reminder-customer tr-calendar-event-preview__action-btn--success",
                `<i class="fa fa-whatsapp" aria-hidden="true"></i><span>${escapeHtml(labels.actionCustomerShort || "Customer")}</span>`,
                `data-send-customer-reminder data-booking-id="${escapeHtml(String(booking.id))}" data-resend="${booking.reminder_sent ? "1" : "0"}" aria-label="${escapeHtml(booking.reminder_sent ? (labels.resendReminder || "Resend reminder") : (labels.whatsappReminderCustomer || "WhatsApp reminder · Customer"))}" title="${escapeHtml(booking.reminder_sent ? (labels.resendReminder || "Resend reminder") : (labels.whatsappReminderCustomer || "WhatsApp reminder · Customer"))}"`
            )
            : "",
        notify.beauticianReminder
            ? previewActionButton(
                "tr-calendar-event-preview__whatsapp-reminder-beautician tr-calendar-event-preview__action-btn--success",
                `<i class="fa fa-whatsapp" aria-hidden="true"></i><span>${escapeHtml(labels.actionBeauticianShort || "Beautician")}</span>`,
                `data-send-beautician-reminder data-booking-id="${escapeHtml(String(booking.id))}" data-resend="${booking.beautician_reminder_sent ? "1" : "0"}" aria-label="${escapeHtml(booking.beautician_reminder_sent ? (labels.resendBeauticianReminder || "Resend beautician reminder") : (labels.whatsappReminderBeautician || "WhatsApp reminder · Beautician"))}" title="${escapeHtml(booking.beautician_reminder_sent ? (labels.resendBeauticianReminder || "Resend beautician reminder") : (labels.whatsappReminderBeautician || "WhatsApp reminder · Beautician"))}"`
            )
            : "",
        options.consultationUrlTemplate && booking.status !== "canceled"
            ? previewActionButton(
                "tr-calendar-event-preview__consultation tr-calendar-event-preview__action-btn--primary",
                `<i class="fa fa-file-text-o" aria-hidden="true"></i><span>${escapeHtml(labels.actionConsultationShort || "Consultation")}</span>`,
                `data-send-consultation data-booking-id="${escapeHtml(String(booking.id))}" aria-label="${escapeHtml(labels.consultation || "Send consultation form")}" title="${escapeHtml(labels.consultation || "Send consultation form")}"`
            )
            : "",
        options.portalGenericWhatsApp && !notify.customerReminder && String(booking.customer_phone || "").trim()
            ? previewActionButton(
                "tr-calendar-event-preview__whatsapp tr-calendar-event-preview__action-btn--success",
                `<i class="fa fa-whatsapp" aria-hidden="true"></i><span>${escapeHtml(labels.actionCustomerShort || "Customer")}</span>`,
                `data-booking-id="${escapeHtml(String(booking.id))}" aria-label="${escapeHtml(labels.whatsappCustomer || "WhatsApp customer")}" title="${escapeHtml(labels.whatsappCustomer || "WhatsApp customer")}"`
            )
            : "",
        booking.can_reschedule && options.rescheduleUrlTemplate
            ? previewActionButton(
                "tr-calendar-event-preview__reschedule tr-calendar-event-preview__action-btn--ghost",
                `<i class="fa fa-calendar" aria-hidden="true"></i><span>${escapeHtml(labels.actionRescheduleShort || labels.reschedule || "Reschedule")}</span>`,
                `data-reschedule-booking data-booking-id="${escapeHtml(String(booking.id))}" data-beautician-id="${escapeHtml(String(booking.beautician_id || ""))}" data-product-id="${escapeHtml(String(booking.product_id || ""))}" data-spa-branch-id="${escapeHtml(String(booking.spa_branch_id || ""))}" aria-label="${escapeHtml(labels.reschedule || "Reschedule")}" title="${escapeHtml(labels.reschedule || "Reschedule")}"`
            )
            : "",
        booking.can_schedule_tba && options.tbaScheduleEnabled !== false
            ? previewActionButton(
                "tr-calendar-event-preview__schedule-tba tr-calendar-event-preview__action-btn--primary",
                `<i class="fa fa-calendar-plus-o" aria-hidden="true"></i><span>${escapeHtml(labels.scheduleTba || "Schedule slot")}</span>`,
                `data-tba-schedule data-booking-id="${escapeHtml(String(booking.id))}" data-beautician-id="${escapeHtml(String(booking.beautician_id || ""))}" data-product-id="${escapeHtml(String(booking.product_id || ""))}" data-spa-branch-id="${escapeHtml(String(booking.spa_branch_id || ""))}"`
            )
            : "",
        booking.can_edit_manual && options.manualBookingEditEnabled
            ? previewActionButton(
                "tr-calendar-event-preview__edit-manual tr-calendar-event-preview__action-btn--ghost",
                `<i class="fa fa-pencil" aria-hidden="true"></i><span>${escapeHtml(labels.editManual || "Edit appointment")}</span>`,
                `data-booking-id="${escapeHtml(String(booking.id))}"`
            )
            : "",
        booking.can_cancel_manual && options.manualBookingEditEnabled
            ? previewActionButton(
                "tr-calendar-event-preview__cancel-manual tr-calendar-event-preview__action-btn--danger",
                `<i class="fa fa-times" aria-hidden="true"></i><span>${escapeHtml(labels.cancelManual || "Cancel appointment")}</span>`,
                `data-booking-id="${escapeHtml(String(booking.id))}"`
            )
            : "",
    ].filter(Boolean);

    const whatsappHint = !options.whatsappConfigured && (notify.customerReminder || notify.beauticianReminder)
        ? `<p class="tr-calendar-event-preview__whatsapp-hint">${escapeHtml(labels.whatsappNotConfigured || "OneSender WhatsApp API is not configured.")}</p>`
        : "";

    return `
        <div class="tr-calendar-event-preview__card" style="--tr-beautician-color:${escapeHtml(color)}">
            <div class="tr-calendar-event-preview__toolbar">
                <div class="tr-calendar-event-preview__toolbar-main">
                    ${statusControl}
                </div>
                ${metaChips ? `<div class="tr-calendar-event-preview__meta-row">${metaChips}</div>` : ""}
            </div>

            <div class="tr-calendar-event-preview__workspace">
                <main class="tr-calendar-event-preview__column tr-calendar-event-preview__column--details">
                    ${workLogSection}
                </main>
                <aside class="tr-calendar-event-preview__column tr-calendar-event-preview__column--summary">
                    ${treatmentSection}
                    ${appointmentSection}
                    ${orderNotesSection}
                    ${beauticianNotesSection}
                    ${activitySection}
                </aside>
            </div>

            ${actionButtons.length
                ? `<div class="tr-calendar-event-preview__footer">
                    ${whatsappHint}
                    <div class="tr-calendar-event-preview__actions tr-calendar-event-preview__actions--${Math.min(actionButtons.length, 4)}">
                        ${actionButtons.join("")}
                    </div>
                </div>`
                : (whatsappHint ? `<div class="tr-calendar-event-preview__footer">${whatsappHint}</div>` : "")}
        </div>
    `;
}

export function openCalendarEventPreview(booking, labels, options = {}) {
    if (labels) {
        previewLabels = labels;
    }

    // Prefer the original init options so a read-only open does not permanently
    // strip actions for subsequent own-booking opens/refreshes.
    const baseOptions = Object.keys(previewOptionsBase || {}).length
        ? previewOptionsBase
        : options;
    previewOptions = previewOptionsForBooking(booking, baseOptions);

    // Avoid stacking under CRM customer profile when reopening a booking.
    document.dispatchEvent(new CustomEvent("tr-crm-close-customer-profile"));

    const overlay = getCalendarEventPreviewOverlay();
    calendarEventPreviewLastFocus = document.activeElement instanceof HTMLElement
        ? document.activeElement
        : null;
    const refs = overlay.querySelector("#tr-calendar-event-preview-refs");
    const title = overlay.querySelector("#tr-calendar-event-preview-title");
    const customerHead = overlay.querySelector("#tr-calendar-event-preview-customer");
    if (refs) {
        const refTitle = previewLabels.bookingIdTitle || "Treatment reference — quote this when contacting the clinic";
        const orderChip = booking.order_id
            ? `<span class="tr-calendar-event-preview__head-ref tr-calendar-event-preview__head-ref--order">${escapeHtml((previewLabels.orderEyebrow || "Order #:order").replace(":order", booking.order_id))}</span>`
            : "";
        const bookingChip = booking.id
            ? `<span class="tr-calendar-event-preview__head-ref tr-calendar-event-preview__head-ref--booking" title="${escapeHtml(refTitle)}">${escapeHtml(previewLabels.bookingId || "Ref")} ${escapeHtml(booking.reference_code || `B${booking.id}`)}</span>`
            : "";

        refs.innerHTML = [orderChip, bookingChip].filter(Boolean).join("");
    }

    if (title) {
        title.textContent = previewLabels.previewTitle || "Appointment details";
    }

    const titleChips = overlay.querySelector("#tr-calendar-event-preview-chips");

    if (titleChips) {
        const insightChips = buildPreviewInsightChips(booking, previewLabels);

        titleChips.innerHTML = insightChips
            ? `<div class="tr-calendar-event-preview__chip-row tr-calendar-event-preview__chip-row--head">${insightChips}</div>`
            : "";
    }

    if (customerHead) {
        customerHead.innerHTML = buildCustomerPreviewMarkup(booking, previewLabels, previewOptions);
    }

    overlay.querySelector(".tr-calendar-event-preview__body").innerHTML = buildCalendarEventPreviewHtml(
        booking,
        previewLabels,
        previewOptions
    );
    overlay.hidden = false;
    overlay.setAttribute("aria-hidden", "false");
    document.body.classList.add("tr-calendar-event-preview-open");
    overlay.querySelector(".tr-calendar-event-preview__close")?.focus();
}

export function closeCalendarEventPreview(options = {}) {
    const overlay = document.getElementById("tr-calendar-event-preview");
    const restoreFocus = options.restoreFocus !== false;

    if (!overlay) {
        return;
    }

    overlay.hidden = true;
    overlay.setAttribute("aria-hidden", "true");
    document.body.classList.remove("tr-calendar-event-preview-open");

    if (restoreFocus && calendarEventPreviewLastFocus?.isConnected) {
        calendarEventPreviewLastFocus.focus();
    }

    calendarEventPreviewLastFocus = null;
}

export function upsertBooking(booking) {
    const id = String(booking.id);

    kanbanBookingsById.set(id, { ...kanbanBookingsById.get(id), ...booking });
    calendarBookingsById.set(id, { ...calendarBookingsById.get(id), ...booking });
}

let calendarEventPreviewReady = false;
let calendarEventPreviewLastFocus = null;
let previewResolveBooking = null;
let previewOptionsBase = {};

function findClickableBookingTarget(target) {
    return target.closest(
        ".tr-cal-event--clickable, .tr-kanban-card--clickable, .tr-portal-today__item--clickable, .tr-crm-appointment--clickable, .tr-crm-ledger__row--clickable, .tr-crm-drawer-booking--clickable, .tr-crm-agenda-card__compact--clickable"
    );
}

function getBookingIdFromElement(element) {
    return element?.dataset?.bookingId || element?.dataset?.id || null;
}

export function schedulingBookingNeedsDetails(booking) {
    if (!booking?.id) {
        return true;
    }

    if (booking.details_loaded === true) {
        return false;
    }

    const hasIdentity = Boolean(
        (booking.customer_name && booking.customer_name !== "—")
        || booking.treatment_name
        || booking.product_name
    );

    if (!hasIdentity) {
        return true;
    }

    if (booking.order_id) {
        return !booking.order_total_formatted && !booking.payment_status_label;
    }

    return false;
}

async function fetchBookingDetails(bookingId, detailsUrlTemplate, cached = null) {
    if (!detailsUrlTemplate || !window.axios) {
        return cached;
    }

    const key = String(bookingId);

    if (!previewDetailRequests.has(key) && !schedulingDetailRequests.has(key)) {
        const request = window.axios
            .get(detailsUrlTemplate.replace("__ID__", key))
            .then((response) => {
                const detailedBooking = response.data?.booking;

                if (detailedBooking) {
                    upsertBooking(detailedBooking);
                }

                return detailedBooking || cached;
            })
            .finally(() => {
                previewDetailRequests.delete(key);
                schedulingDetailRequests.delete(key);
            });

        previewDetailRequests.set(key, request);
        schedulingDetailRequests.set(key, request);
    }

    return previewDetailRequests.get(key) || schedulingDetailRequests.get(key);
}

async function resolvePreviewBooking(bookingId) {
    const cached = previewResolveBooking?.(bookingId) || null;
    // Ledger / older appointments often are not seeded into calendar/kanban maps.
    // Fetch details whenever the booking is missing or marked incomplete.
    const needsFetch = !cached || cached.details_loaded === false;

    if (!needsFetch || !previewOptions.detailsUrlTemplate || !window.axios) {
        return cached;
    }

    return fetchBookingDetails(bookingId, previewOptions.detailsUrlTemplate, cached);
}

export async function resolveSchedulingBooking(bookingId, fallback = null, detailsUrlTemplate = "") {
    let booking = resolveBooking(bookingId) || fallback;

    if (!schedulingBookingNeedsDetails(booking)) {
        return booking;
    }

    const url = detailsUrlTemplate
        || previewOptionsBase.detailsUrlTemplate
        || document.querySelector("[data-calendar-details-url]")?.dataset?.calendarDetailsUrl
        || "";

    return fetchBookingDetails(bookingId, url, booking);
}

export async function openBookingPreviewById(bookingId) {
    if (!bookingId || (!previewResolveBooking && !previewOptions.detailsUrlTemplate)) {
        return;
    }

    try {
        const booking = await resolvePreviewBooking(bookingId);

        if (!booking || !bookingAllowsDetail(booking, previewOptions.portalBeauticianId || null)) {
            return;
        }

        openCalendarEventPreview(booking, previewLabels, previewOptions);
    } catch (error) {
        const message = previewLabels.detailsLoadFailed || "Failed to load appointment details";
        window.notify?.error?.(message) || alert(message);
    }
}

async function openBookingPreviewFromElement(element) {
    const bookingId = getBookingIdFromElement(element);

    if (!bookingId) {
        return;
    }

    element.classList.add("tr-booking-preview--loading");

    try {
        await openBookingPreviewById(bookingId);
    } finally {
        element.classList.remove("tr-booking-preview--loading");
    }
}

export async function sendBookingWhatsApp(bookingId, { whatsappUrlTemplate = "", labels = {} } = {}) {
    if (!bookingId || !whatsappUrlTemplate || !window.axios) {
        return {
            ok: false,
            message: labels.whatsappFailed || "Failed to send WhatsApp message",
        };
    }

    try {
        const url = whatsappUrlTemplate.replace("__ID__", bookingId);
        const response = await window.axios.post(url);
        const booking = response.data?.booking;

        if (booking) {
            upsertBooking(booking);
        }

        return {
            ok: true,
            message: response.data?.message || labels.whatsappSent || "WhatsApp message sent",
            booking,
        };
    } catch (error) {
        return {
            ok: false,
            message:
                error.response?.data?.message ||
                labels.whatsappFailed ||
                "Failed to send WhatsApp message",
        };
    }
}

async function sendCustomerReminder(button) {
    const bookingId = button.dataset.bookingId;
    const resend = button.dataset.resend === "1";
    const reminderUrlTemplate = previewOptions.reminderUrlTemplate;

    if (!bookingId || !reminderUrlTemplate || !window.axios) {
        return;
    }

    const originalHtml = button.innerHTML;

    button.disabled = true;
    button.innerHTML = `<i class="fa fa-spinner fa-spin"></i> ${escapeHtml(previewLabels.reminderSending || "Sending reminder…")}`;

    try {
        const response = await window.axios.post(
            reminderUrlTemplate.replace("__ID__", bookingId),
            { resend }
        );
        const booking = response.data?.booking;

        if (booking) {
            upsertBooking(booking);
            openCalendarEventPreview(booking, previewLabels, previewOptions);
            document.dispatchEvent(new CustomEvent("tr-crm-booking-updated", { detail: booking }));
        }

        window.notify?.success?.(response.data?.message || previewLabels.reminderSent || "Reminder sent") ||
            alert(response.data?.message || previewLabels.reminderSent || "Reminder sent");
        button.disabled = false;
        button.innerHTML = originalHtml;
    } catch (error) {
        const message = error.response?.data?.message || previewLabels.reminderFailed || "Failed to send reminder";

        window.notify?.error?.(message) || alert(message);
        button.disabled = false;
        button.innerHTML = originalHtml;
    }
}

async function sendBeauticianReminder(button) {
    const bookingId = button.dataset.bookingId;
    const resend = button.dataset.resend === "1";
    const reminderUrlTemplate = previewOptions.beauticianReminderUrlTemplate;

    if (!bookingId || !reminderUrlTemplate || !window.axios) {
        return;
    }

    const originalHtml = button.innerHTML;

    button.disabled = true;
    button.innerHTML = `<i class="fa fa-spinner fa-spin"></i> ${escapeHtml(previewLabels.reminderSending || "Sending reminder…")}`;

    try {
        const response = await window.axios.post(
            reminderUrlTemplate.replace("__ID__", bookingId),
            { resend }
        );
        const booking = response.data?.booking;

        if (booking) {
            upsertBooking(booking);
            openCalendarEventPreview(booking, previewLabels, previewOptions);
            document.dispatchEvent(new CustomEvent("tr-crm-booking-updated", { detail: booking }));
        }

        window.notify?.success?.(response.data?.message || previewLabels.beauticianReminderSent || "Beautician reminder sent") ||
            alert(response.data?.message || previewLabels.beauticianReminderSent || "Beautician reminder sent");
        button.disabled = false;
        button.innerHTML = originalHtml;
    } catch (error) {
        const message = error.response?.data?.message || previewLabels.beauticianReminderFailed || "Failed to send beautician reminder";

        window.notify?.error?.(message) || alert(message);
        button.disabled = false;
        button.innerHTML = originalHtml;
    }
}

async function sendCustomerWhatsApp(button) {
    const bookingId = button.dataset.bookingId;
    const whatsappUrlTemplate = previewOptions.whatsappUrlTemplate;

    if (!bookingId || !whatsappUrlTemplate || !window.axios) {
        return;
    }

    const originalHtml = button.innerHTML;

    button.disabled = true;
    button.innerHTML = `<i class="fa fa-spinner fa-spin"></i> ${escapeHtml(previewLabels.whatsappSending || "Sending…")}`;

    const result = await sendBookingWhatsApp(bookingId, {
        whatsappUrlTemplate,
        labels: previewLabels,
    });

    if (result.ok) {
        if (result.booking) {
            openCalendarEventPreview(result.booking, previewLabels, previewOptions);
        }

        window.notify?.success?.(result.message) || alert(result.message);
        button.disabled = false;
        button.innerHTML = originalHtml;
    } else {
        window.notify?.error?.(result.message) || alert(result.message);
        button.disabled = false;
        button.innerHTML = originalHtml;
    }
}

async function prepareConsultation(button) {
    const bookingId = button.dataset.bookingId;
    const urlTemplate = previewOptions.consultationUrlTemplate;

    if (!bookingId || !urlTemplate || !window.axios) {
        return;
    }

    const originalHtml = button.innerHTML;
    button.disabled = true;
    button.innerHTML = `<i class="fa fa-spinner fa-spin"></i> ${escapeHtml(previewLabels.consultationPreparing || "Sending via WhatsApp…")}`;

    try {
        const response = await window.axios.post(urlTemplate.replace("__ID__", bookingId));
        const message =
            response.data?.message
            || previewLabels.consultationReady
            || "Consultation form sent to the customer via WhatsApp.";

        window.notify?.success?.(message) || alert(message);
    } catch (error) {
        const message =
            error.response?.data?.message
            || previewLabels.consultationFailed
            || "Failed to send consultation form";
        window.notify?.error?.(message) || alert(message);
    } finally {
        button.disabled = false;
        button.innerHTML = originalHtml;
    }
}

async function cancelManualBooking(button) {
    const bookingId = button.dataset.bookingId;
    const cancelUrlTemplate = previewOptions.manualBookingCancelUrlTemplate;

    if (!bookingId || !cancelUrlTemplate || !window.axios) {
        return;
    }

    const confirmMessage = previewLabels.cancelManualConfirm || "Cancel this manual appointment?";

    if (!window.confirm(confirmMessage)) {
        return;
    }

    const originalHtml = button.innerHTML;

    button.disabled = true;
    button.innerHTML = `<i class="fa fa-spinner fa-spin"></i>`;

    try {
        const url = cancelUrlTemplate.replace("__ID__", bookingId);
        const response = await window.axios.patch(url);
        const message = response.data?.message || previewLabels.cancelManualSuccess || "Appointment canceled";

        closeCalendarEventPreview();
        window.notify?.success?.(message) || alert(message);
        window.location.reload();
    } catch (error) {
        const message =
            error.response?.data?.message ||
            previewLabels.cancelManualFailed ||
            "Failed to cancel appointment";

        window.notify?.error?.(message) || alert(message);
        button.disabled = false;
        button.innerHTML = originalHtml;
    }
}

function openManualBookingEditorFromPreview(button) {
    const bookingId = button.dataset.bookingId;

    if (!bookingId || !previewResolveBooking) {
        return;
    }

    const booking = previewResolveBooking(bookingId);

    if (!booking) {
        return;
    }

    closeCalendarEventPreview();
    openManualBookingEditor(booking, previewOptions.manualBookingModalSelector || "");
}

function appendChecklistItem(item = {}) {
    const list = document.querySelector(".tr-calendar-event-preview__checklist");

    if (!list) {
        return;
    }

    list.insertAdjacentHTML("beforeend", workLogChecklistItemMarkup(item, previewLabels.workLog || {}));
    list.lastElementChild?.querySelector(".tr-calendar-event-preview__checklist-label")?.focus();
}

function checklistRows() {
    return Array.from(document.querySelectorAll(".tr-calendar-event-preview__checklist-item")).map((row) => ({
        row,
        input: row.querySelector(".tr-calendar-event-preview__checklist-label"),
        id: row.dataset.checklistId || "",
        label: row.querySelector(".tr-calendar-event-preview__checklist-label")?.value.trim() || "",
        completed: Boolean(row.querySelector('input[type="checkbox"]')?.checked),
        completed_at: row.dataset.completedAt || null,
    }));
}

function checklistPayload() {
    return checklistRows()
        .map(({ id, label, completed, completed_at }) => ({
            id,
            label,
            completed,
            completed_at: completed ? completed_at : null,
        }))
        .filter((item) => item.label);
}

/** Full checklist including empty labels — used on save so the server can reject blanks. */
function checklistSavePayload() {
    return checklistRows().map(({ id, label, completed, completed_at }) => ({
        id,
        label,
        completed,
        completed_at: completed ? completed_at : null,
    }));
}

function clearChecklistValidation() {
    document
        .querySelectorAll(".tr-calendar-event-preview__checklist-label.is-invalid")
        .forEach((input) => input.classList.remove("is-invalid"));

    const banner = document.querySelector(".tr-calendar-event-preview__work-log-error");

    if (banner) {
        banner.hidden = true;
        banner.textContent = "";
    }
}

function showChecklistValidationMessage(message) {
    const list = document.querySelector(".tr-calendar-event-preview__checklist");

    if (!list) {
        return;
    }

    let banner = document.querySelector(".tr-calendar-event-preview__work-log-error");

    if (!banner) {
        banner = document.createElement("p");
        banner.className = "tr-calendar-event-preview__work-log-error";
        banner.setAttribute("role", "alert");
        list.insertAdjacentElement("beforebegin", banner);
    }

    banner.hidden = false;
    banner.textContent = message;
}

function validateWorkLogBeforeSave() {
    const workLog = previewLabels.workLog || {};
    clearChecklistValidation();

    const emptyRows = checklistRows().filter((item) => !item.label);

    if (emptyRows.length === 0) {
        return true;
    }

    emptyRows.forEach((item) => item.input?.classList.add("is-invalid"));

    const firstInput = emptyRows[0]?.input;
    firstInput?.scrollIntoView({ block: "center", behavior: "smooth" });
    firstInput?.focus();

    const message =
        workLog.emptyChecklistItem ||
        "Fill in or remove empty checklist items before saving.";

    showChecklistValidationMessage(message);

    // Prefer in-panel alert; toast/modal can sit awkwardly over the preview drawer.
    if (typeof window.notify?.error === "function") {
        window.notify.error(message);
    } else {
        window.alert(message);
    }

    return false;
}

function generateCustomerNote() {
    const workLog = previewLabels.workLog || {};
    const completedItems = checklistPayload().filter((item) => item.completed);
    const textarea = document.getElementById("tr-booking-beautician-notes");

    if (!textarea || completedItems.length === 0) {
        const message = workLog.noCompletedItems || "Complete at least one checklist item before generating a note.";
        window.notify?.info?.(message) || alert(message);

        return;
    }

    const stampedItems = completedItems.map((item) => {
        let completedAt = item.completed_at;
        let stamp = formatCompletedAtDisplay(completedAt);

        if (!stamp) {
            completedAt = toIsoLocal(new Date());
            stamp = formatCompletedAtDisplay(completedAt);

            const row = item.id
                ? document.querySelector(`.tr-calendar-event-preview__checklist-item[data-checklist-id="${CSS.escape(item.id)}"]`)
                : null;
            updateChecklistItemStamp(row, true, completedAt);
        }

        return {
            label: item.label,
            completed_at: completedAt,
            line: `• ${item.label} — ${stamp}`,
        };
    });

    const lines = stampedItems.map((item) => item.line);
    const heading = workLog.summaryPrefix || "Treatment completed";
    textarea.value = [heading, ...lines].join("\n");
    textarea.focus();
}

async function saveBeauticianNotes(button) {
    const bookingId = button.dataset.bookingId;
    const textarea = document.getElementById("tr-booking-beautician-notes");
    const notesUrlTemplate = previewOptions.notesUrlTemplate;

    if (!bookingId || !textarea || !notesUrlTemplate || !window.axios) {
        return;
    }

    if (!validateWorkLogBeforeSave()) {
        return;
    }

    const originalText = button.textContent;

    button.disabled = true;
    button.textContent = previewLabels.savingNotes || "Saving…";

    try {
        const url = notesUrlTemplate.replace("__ID__", bookingId);
        // POST: many hosts/WAFs block PATCH and surface it as a generic 500.
        const checklist = checklistSavePayload();
        const workLogStamp = workLogStampFromChecklist(checklist);
        const response = await window.axios.post(url, {
            beautician_notes: textarea.value,
            beautician_notes_date: workLogStamp.date,
            beautician_notes_time: workLogStamp.time,
            // Include empty labels so the API rejects them if client validation is skipped.
            beautician_checklist: checklist,
        });
        const booking = response.data?.booking;

        if (booking) {
            upsertBooking(booking);
            openCalendarEventPreview(booking, previewLabels, previewOptions);
            window.notify?.success?.(previewLabels.notesSaved || "Notes saved") ||
                alert(previewLabels.notesSaved || "Notes saved");
        }
    } catch (error) {
        const message = error.response?.data?.message || previewLabels.notesSaveFailed || "Failed to save work log";
        window.notify?.error?.(message) || alert(message);
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

export function initCalendarEventPreview(resolveBooking, labels, options = {}) {
    previewResolveBooking = resolveBooking;
    previewLabels = labels;
    previewOptionsBase = options;
    previewOptions = options;

    if (calendarEventPreviewReady) {
        return;
    }

    calendarEventPreviewReady = true;

    document.addEventListener("input", (event) => {
        if (event.target.matches?.(".tr-calendar-event-preview__checklist-label")) {
            event.target.classList.remove("is-invalid");
        }
    });

    document.addEventListener("keydown", (event) => {
        const overlay = document.getElementById("tr-calendar-event-preview");

        if (event.key === "Escape" && overlay && !overlay.hidden) {
            event.preventDefault();
            closeCalendarEventPreview();
        }
    });

    document.addEventListener("click", (event) => {
        if (event.target.closest(".tr-beautician-avatar--zoomable")) {
            return;
        }

        if (event.target.closest("[data-agenda-status], [data-agenda-status-wrap], [data-preview-status], .tr-calendar-event-preview__status-control")) {
            return;
        }

        const sectionJumpButton = event.target.closest("[data-preview-jump]");

        if (sectionJumpButton) {
            event.preventDefault();
            const section = document.getElementById(sectionJumpButton.dataset.previewJump || "");

            if (section) {
                section.scrollIntoView({
                    behavior: window.matchMedia("(prefers-reduced-motion: reduce)").matches ? "auto" : "smooth",
                    block: "start",
                });
            }

            return;
        }

        const presetButton = event.target.closest(".tr-calendar-event-preview__checklist-preset");

        if (presetButton) {
            event.preventDefault();
            const label = presetButton.dataset.checklistPreset || "";
            const alreadyAdded = checklistPayload().some((item) => item.label === label);

            if (!alreadyAdded && label) {
                appendChecklistItem({ label, completed: false });
            }

            return;
        }

        const addChecklistButton = event.target.closest(".tr-calendar-event-preview__add-checklist-item");

        if (addChecklistButton) {
            event.preventDefault();
            appendChecklistItem();

            return;
        }

        const removeChecklistButton = event.target.closest(".tr-calendar-event-preview__checklist-remove");

        if (removeChecklistButton) {
            event.preventDefault();
            removeChecklistButton.closest(".tr-calendar-event-preview__checklist-item")?.remove();

            return;
        }

        const generateNoteButton = event.target.closest(".tr-calendar-event-preview__generate-note");

        if (generateNoteButton) {
            event.preventDefault();
            generateCustomerNote();

            return;
        }

        const saveButton = event.target.closest(".tr-calendar-event-preview__save-notes");

        if (saveButton) {
            event.preventDefault();
            saveBeauticianNotes(saveButton);

            return;
        }

        const whatsappButton = event.target.closest(".tr-calendar-event-preview__whatsapp");

        if (whatsappButton) {
            event.preventDefault();
            sendCustomerWhatsApp(whatsappButton);

            return;
        }

        const consultationButton = event.target.closest("[data-send-consultation]");

        if (consultationButton) {
            event.preventDefault();
            prepareConsultation(consultationButton);

            return;
        }

        const customerReminderButton = event.target.closest("[data-send-customer-reminder]");

        if (customerReminderButton) {
            event.preventDefault();
            sendCustomerReminder(customerReminderButton);

            return;
        }

        const beauticianReminderButton = event.target.closest("[data-send-beautician-reminder]");

        if (beauticianReminderButton) {
            event.preventDefault();
            sendBeauticianReminder(beauticianReminderButton);

            return;
        }

        const legacyReminderButton = event.target.closest("[data-send-reminder]");

        if (legacyReminderButton) {
            event.preventDefault();
            sendCustomerReminder(legacyReminderButton);

            return;
        }

        const editManualButton = event.target.closest(".tr-calendar-event-preview__edit-manual");

        if (editManualButton) {
            event.preventDefault();
            openManualBookingEditorFromPreview(editManualButton);

            return;
        }

        const cancelManualButton = event.target.closest(".tr-calendar-event-preview__cancel-manual");

        if (cancelManualButton) {
            event.preventDefault();
            cancelManualBooking(cancelManualButton);

            return;
        }

        if (event.target.closest(".tr-calendar-event-preview__notes-editor")) {
            return;
        }

        if (event.target.closest(".tr-kanban-card-link")) {
            return;
        }

        if (event.target.closest(".tr-calendar-event-preview__panel")) {
            return;
        }

        const card = findClickableBookingTarget(event.target);

        if (!card) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        openBookingPreviewFromElement(card);
    });

    document.addEventListener("change", async (event) => {
        const checklistCheckbox = event.target.closest(
            ".tr-calendar-event-preview__checklist-toggle input[type=\"checkbox\"]"
        );

        if (checklistCheckbox) {
            const row = checklistCheckbox.closest(".tr-calendar-event-preview__checklist-item");
            updateChecklistItemStamp(row, checklistCheckbox.checked);

            return;
        }

        const select = event.target.closest("[data-preview-status]");

        if (!select || !previewOptions.statusUrlTemplate || !window.axios) {
            return;
        }

        const bookingId = select.dataset.bookingId;
        const nextStatus = select.value;
        const previousStatus = select.dataset.currentStatus;

        if (!bookingId || !nextStatus || nextStatus === previousStatus) {
            return;
        }

        select.disabled = true;

        try {
            const url = previewOptions.statusUrlTemplate.replace("__ID__", bookingId);
            const response = await window.axios.patch(url, { status: nextStatus });
            const booking = response.data?.booking;

            if (booking) {
                upsertBooking(booking);
                openCalendarEventPreview(booking, previewLabels, previewOptions);
                document.dispatchEvent(new CustomEvent("tr-crm-booking-updated", {
                    detail: { booking },
                }));
            }
        } catch (error) {
            select.value = previousStatus;
            const message = previewLabels.statusUpdateFailed || "Failed to update status";
            window.notify?.error?.(message) || alert(message);
        } finally {
            select.disabled = false;
        }
    });

    document.addEventListener("keydown", (event) => {
        const card = event.target.closest(
            ".tr-cal-event--clickable, .tr-kanban-card--clickable, .tr-portal-today__item--clickable, .tr-crm-appointment, .tr-crm-ledger__row--clickable, .tr-crm-drawer-booking, .tr-crm-agenda-card__compact"
        );
        const bookingId = getBookingIdFromElement(card);

        if (!bookingId || (event.key !== "Enter" && event.key !== " ")) {
            return;
        }

        if (event.target.closest(".tr-beautician-avatar--zoomable")) {
            return;
        }

        event.preventDefault();
        openBookingPreviewFromElement(card);
    });
}

export function renderKanbanBeautician(cardEl, card) {
    const avatar = cardEl.querySelector(".tr-kanban-card-beautician-avatar");
    const name = cardEl.querySelector(".tr-kanban-card-beautician-name");

    if (!avatar || !name) {
        return;
    }

    const color = card.beautician_color || "#6366f1";

    name.textContent = card.beautician_name || "—";

    const position = cardEl.querySelector(".tr-kanban-card-position");
    const jobTitle = (card.beautician_job_title || "").trim();

    if (position) {
        if (jobTitle !== "") {
            position.textContent = jobTitle;
            position.hidden = false;
        } else {
            position.textContent = "";
            position.hidden = true;
        }
    }

    avatar.style.backgroundColor = color;
    avatar.style.boxShadow = `0 0 0 2px ${color}33`;
    avatar.replaceChildren();
    avatar.classList.remove("tr-beautician-avatar--zoomable");
    avatar.removeAttribute("role");
    avatar.removeAttribute("tabindex");
    avatar.removeAttribute("title");
    delete avatar.dataset.previewSrc;
    delete avatar.dataset.previewName;

    if (card.beautician_avatar) {
        const img = document.createElement("img");

        img.src = card.beautician_avatar;
        img.alt = card.beautician_name || "";
        img.draggable = false;
        avatar.appendChild(img);
        avatar.classList.add("tr-beautician-avatar--zoomable");
        avatar.setAttribute("role", "button");
        avatar.setAttribute("tabindex", "0");
        avatar.setAttribute("title", card.beautician_name || "");
        avatar.dataset.previewSrc = card.beautician_avatar;
        avatar.dataset.previewName = card.beautician_name || "";
    } else {
        avatar.textContent = card.beautician_initial || "?";
    }
}

function getBeauticianAvatarLightbox() {
    let overlay = document.getElementById("tr-beautician-avatar-preview");

    if (overlay) {
        return overlay;
    }

    overlay = document.createElement("div");
    overlay.id = "tr-beautician-avatar-preview";
    overlay.className = "tr-beautician-avatar-preview";
    overlay.hidden = true;
    overlay.innerHTML = `
        <div class="tr-beautician-avatar-preview__backdrop" data-dismiss></div>
        <div class="tr-beautician-avatar-preview__dialog" role="dialog" aria-modal="true">
            <button type="button" class="tr-beautician-avatar-preview__close" data-dismiss aria-label="Close">&times;</button>
            <div class="tr-beautician-avatar-preview__frame">
                <img src="" alt="" class="tr-beautician-avatar-preview__img">
            </div>
            <p class="tr-beautician-avatar-preview__name"></p>
        </div>
    `;

    document.body.appendChild(overlay);

    overlay.addEventListener("click", (event) => {
        if (event.target.closest("[data-dismiss]")) {
            closeBeauticianAvatarPreview();
        }
    });

    return overlay;
}

function closeAllPreviewsOnEscape(event) {
    if (event.key === "Escape") {
        closeBeauticianAvatarPreview();
        closeCalendarEventPreview();
    }
}

function openBeauticianAvatarPreview(src, name) {
    const overlay = getBeauticianAvatarLightbox();
    const img = overlay.querySelector(".tr-beautician-avatar-preview__img");
    const label = overlay.querySelector(".tr-beautician-avatar-preview__name");

    img.src = src;
    img.alt = name;
    label.textContent = name;
    overlay.hidden = false;
    document.body.classList.add("tr-beautician-avatar-preview-open");
    overlay.querySelector(".tr-beautician-avatar-preview__close")?.focus();
}

function closeBeauticianAvatarPreview() {
    const overlay = document.getElementById("tr-beautician-avatar-preview");

    if (!overlay) {
        return;
    }

    overlay.hidden = true;
    document.body.classList.remove("tr-beautician-avatar-preview-open");
}

function openAvatarFromElement(avatar) {
    if (!avatar?.dataset.previewSrc) {
        return;
    }

    openBeauticianAvatarPreview(avatar.dataset.previewSrc, avatar.dataset.previewName || "");
}

export function initBeauticianAvatarLightbox() {
    if (beauticianAvatarLightboxReady) {
        return;
    }

    beauticianAvatarLightboxReady = true;

    document.addEventListener("keydown", closeAllPreviewsOnEscape);

    document.addEventListener("click", (event) => {
        const avatar = event.target.closest(".tr-beautician-avatar--zoomable");

        if (!avatar?.dataset.previewSrc) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        openAvatarFromElement(avatar);
    });

    document.addEventListener("keydown", (event) => {
        const avatar = event.target.closest(".tr-beautician-avatar--zoomable");

        if (!avatar?.dataset.previewSrc || (event.key !== "Enter" && event.key !== " ")) {
            return;
        }

        event.preventDefault();
        openAvatarFromElement(avatar);
    });

    document.addEventListener("mousedown", (event) => {
        if (event.target.closest(".tr-beautician-avatar--zoomable")) {
            event.stopPropagation();
        }
    });
}

export { TR_STATUS_ACCENT };
