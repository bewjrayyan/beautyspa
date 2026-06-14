let beauticianAvatarLightboxReady = false;
const calendarBookingsById = new Map();
const kanbanBookingsById = new Map();
let previewOptions = {};
let previewLabels = {};

import { openManualBookingEditor } from "./manual-booking.js";

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

export function buildCalendarEventHtml(booking, { showBeautician = true } = {}) {
    const status = calendarStatusClass(booking.status);
    const color = booking.beautician_color || "#6366f1";
    const treatment = escapeHtml(booking.treatment_name || "Treatment");
    const customer = escapeHtml(booking.customer_name || "—");
    const time = escapeHtml(booking.time || "");
    const beauticianRow = showBeautician && booking.beautician_name
        ? `<span class="tr-cal-event-beautician">${beauticianAvatarMarkup(booking, "tr-beautician-avatar--xs")}<span class="tr-cal-event-beautician-name">${escapeHtml(booking.beautician_name)}</span></span>`
        : "";

    return `
        <div class="tr-cal-event tr-cal-event--clickable" data-booking-id="${escapeHtml(booking.id)}" data-status="${status}" role="button" tabindex="0" style="--tr-beautician-color:${escapeHtml(color)};border-left-color:${escapeHtml(color)};background:${hexToRgba(color, 0.12)};border-color:${hexToRgba(color, 0.28)}">
            <div class="tr-cal-event-top">
                <span class="tr-cal-event-time">${time}</span>
                <span class="tr-cal-event-status-dot tr-cal-event-status-dot--${status}" title="${status.replace("_", " ")}"></span>
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
        return overlay;
    }

    overlay = document.createElement("aside");
    overlay.id = "tr-calendar-event-preview";
    overlay.className = "tr-calendar-event-preview";
    overlay.hidden = true;
    overlay.setAttribute("aria-hidden", "true");
    overlay.innerHTML = `
        <div class="tr-calendar-event-preview__backdrop" data-dismiss></div>
        <div class="tr-calendar-event-preview__panel" role="dialog" aria-modal="true" aria-labelledby="tr-calendar-event-preview-title">
            <header class="tr-calendar-event-preview__head">
                <div class="tr-calendar-event-preview__head-text">
                    <p class="tr-calendar-event-preview__eyebrow" id="tr-calendar-event-preview-eyebrow"></p>
                    <h3 class="tr-calendar-event-preview__title" id="tr-calendar-event-preview-title"></h3>
                    <p class="tr-calendar-event-preview__subtitle" id="tr-calendar-event-preview-subtitle"></p>
                </div>
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

function previewField(label, value, { href = "", full = false, muted = false } = {}) {
    if (! value) {
        return "";
    }

    const valueHtml = href
        ? `<a href="${escapeHtml(href)}" class="tr-calendar-event-preview__field-link">${escapeHtml(value)}</a>`
        : escapeHtml(value);

    return `
        <div class="tr-calendar-event-preview__field${full ? " tr-calendar-event-preview__field--full" : ""}${muted ? " tr-calendar-event-preview__field--muted" : ""}">
            <span class="tr-calendar-event-preview__field-label">${escapeHtml(label)}</span>
            <span class="tr-calendar-event-preview__field-value">${valueHtml}</span>
        </div>
    `;
}

function previewSection(title, content) {
    if (! content.trim()) {
        return "";
    }

    return `
        <section class="tr-calendar-event-preview__section">
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

export function buildCalendarEventPreviewHtml(booking, labels, options = {}) {
    const status = calendarStatusClass(booking.status);
    const color = booking.beautician_color || "#6366f1";
    const statusText = statusLabel(status, labels);
    const timeRange = (booking.appointment_time_range || booking.time || booking.appointment_time || "—").trim();
    const durationMinutes = Number(booking.slot_duration_minutes) || 0;
    const durationLabel = durationMinutes > 0
        ? (labels.durationMinutes || ":count min").replace(":count", String(durationMinutes))
        : (booking.duration_session_label || booking.treatment_subtitle || "");
    const paymentLabel = (booking.payment_status_label || "").trim();
    const totalFormatted = (booking.total_formatted || "").trim();
    const treatmentSubtitle = (booking.treatment_subtitle || booking.duration_session_label || "").trim();
    const alerts = Array.isArray(booking.inline_alerts) ? booking.inline_alerts : [];

    const statusControl = status !== "canceled" && options.crmCanEdit && options.statusUrlTemplate
        ? `
            <select
                id="tr-preview-status-${escapeHtml(String(booking.id))}"
                class="tr-calendar-event-preview__status-select tr-calendar-event-preview__status-select--${escapeHtml(status)}"
                data-preview-status
                data-booking-id="${escapeHtml(String(booking.id))}"
                data-current-status="${escapeHtml(status)}"
                aria-label="${escapeHtml(labels.status || "Status")}"
            >
                <option value="pending"${status === "pending" ? " selected" : ""}>${escapeHtml(labels.statusPending || "Pending")}</option>
                <option value="in_progress"${status === "in_progress" ? " selected" : ""}>${escapeHtml(labels.statusInProgress || "In Progress")}</option>
                <option value="completed"${status === "completed" ? " selected" : ""}>${escapeHtml(labels.statusCompleted || "Completed")}</option>
            </select>
        `
        : `<span class="tr-calendar-event-preview__status tr-calendar-event-preview__status--${status}">${escapeHtml(statusText)}</span>`;

    const metaChips = [
        booking.id ? `<span class="tr-calendar-event-preview__meta-chip">${escapeHtml(labels.bookingId || "Ref")} B${escapeHtml(String(booking.id))}</span>` : "",
        booking.source_label ? `<span class="tr-calendar-event-preview__meta-chip tr-calendar-event-preview__meta-chip--source">${escapeHtml(booking.source_label)}</span>` : "",
        booking.spa_branch_name ? `<span class="tr-calendar-event-preview__meta-chip tr-calendar-event-preview__meta-chip--branch">${escapeHtml(booking.spa_branch_name)}</span>` : "",
    ].filter(Boolean).join("");

    const insightChips = [
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
    ].filter(Boolean).join("");

    const scheduleSection = previewSection(labels.sectionSchedule || "Schedule", `
        <div class="tr-calendar-event-preview__grid">
            ${previewField(labels.date, booking.appointment_date || booking.date || "—")}
            ${previewField(labels.time, timeRange)}
            ${previewField(labels.duration, durationLabel)}
        </div>
    `);

    const customerSection = previewSection(labels.sectionCustomer || "Customer", `
        <div class="tr-calendar-event-preview__grid">
            ${previewField(labels.customer, booking.customer_name || "—", { full: true })}
            ${previewField(labels.phone, booking.customer_phone || "", {
                href: booking.customer_phone ? `tel:${booking.customer_phone.replace(/[^\d+]/g, "")}` : "",
            })}
            ${previewField(labels.email, booking.customer_email || "")}
        </div>
        ${insightChips ? `<div class="tr-calendar-event-preview__chip-row">${insightChips}</div>` : ""}
    `);

    const treatmentSection = previewSection(labels.sectionTreatment || "Treatment & payment", `
        <div class="tr-calendar-event-preview__grid">
            ${previewField(labels.treatment, booking.treatment_name || "—", { full: true })}
            ${previewField(labels.session, treatmentSubtitle)}
            ${previewField(labels.category, booking.category_name || "")}
            ${previewField(labels.total, totalFormatted)}
            ${previewField(labels.payment, paymentLabel)}
        </div>
    `);

    const staffSection = booking.beautician_name && !options.hideBeautician
        ? previewSection(labels.sectionStaff || "Specialist", `
            <div class="tr-calendar-event-preview__staff">
                ${beauticianAvatarMarkup(booking, "tr-beautician-avatar--md")}
                <div class="tr-calendar-event-preview__staff-text">
                    <strong>${escapeHtml(booking.beautician_name)}</strong>
                    ${booking.beautician_job_title ? `<span>${escapeHtml(booking.beautician_job_title)}</span>` : ""}
                </div>
            </div>
        `)
        : "";

    const notesBlocks = [];
    if (booking.notes) {
        notesBlocks.push(`
            <div class="tr-calendar-event-preview__note">
                <span class="tr-calendar-event-preview__note-label">${escapeHtml(labels.orderNotes || "Order notes")}</span>
                <p class="tr-calendar-event-preview__note-body">${escapeHtml(booking.notes)}</p>
            </div>
        `);
    }

    if (options.allowBeauticianNotes) {
        notesBlocks.push(`
            <div class="tr-calendar-event-preview__notes-editor">
                <label for="tr-booking-beautician-notes">${escapeHtml(labels.beauticianNotes || "Beautician notes")}</label>
                <textarea id="tr-booking-beautician-notes" class="form-control" rows="3" data-booking-id="${escapeHtml(booking.id)}">${escapeHtml(booking.beautician_notes || "")}</textarea>
                <button type="button" class="tr-calendar-event-preview__action-btn tr-calendar-event-preview__action-btn--ghost tr-calendar-event-preview__save-notes" data-booking-id="${escapeHtml(booking.id)}">
                    ${escapeHtml(labels.saveNotes || "Save notes")}
                </button>
            </div>
        `);
    } else if (booking.beautician_notes) {
        notesBlocks.push(`
            <div class="tr-calendar-event-preview__note">
                <span class="tr-calendar-event-preview__note-label">${escapeHtml(labels.beauticianNotes || "Beautician notes")}</span>
                <p class="tr-calendar-event-preview__note-body">${escapeHtml(booking.beautician_notes)}</p>
            </div>
        `);
    }

    const notesSection = notesBlocks.length
        ? previewSection(labels.sectionNotes || "Notes", notesBlocks.join(""))
        : "";

    const activitySection = options.showActivityLog && Array.isArray(booking.recent_activities) && booking.recent_activities.length
        ? previewSection(labels.activityTitle || "Activity log", `
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
        `)
        : "";

    const actionButtons = [
        (booking.customer_phone || booking.id)
            ? previewActionButton(
                "tr-calendar-event-preview__profile tr-calendar-event-preview__action-btn--ghost",
                `<i class="fa fa-user" aria-hidden="true"></i><span>${escapeHtml(labels.viewProfile || "View profile")}</span>`,
                `data-customer-profile data-booking-id="${escapeHtml(String(booking.id))}"`
            )
            : "",
        booking.can_reschedule_manual && options.manualBookingEditEnabled
            ? previewActionButton(
                "tr-calendar-event-preview__reschedule tr-calendar-event-preview__action-btn--ghost",
                `<i class="fa fa-calendar" aria-hidden="true"></i><span>${escapeHtml(labels.reschedule || "Reschedule")}</span>`,
                `data-preview-reschedule data-booking-id="${escapeHtml(String(booking.id))}"`
            )
            : "",
        booking.can_edit_manual && options.manualBookingEditEnabled
            ? previewActionButton(
                "tr-calendar-event-preview__edit-manual tr-calendar-event-preview__action-btn--ghost",
                `<i class="fa fa-pencil" aria-hidden="true"></i><span>${escapeHtml(labels.editManual || "Edit appointment")}</span>`,
                `data-booking-id="${escapeHtml(String(booking.id))}"`
            )
            : "",
        booking.can_send_reminder && options.manualBookingEditEnabled
            ? previewActionButton(
                "tr-calendar-event-preview__reminder tr-calendar-event-preview__action-btn--warning",
                `<i class="fa fa-bell" aria-hidden="true"></i><span>${escapeHtml(booking.reminder_sent ? (labels.resendReminder || "Resend reminder") : (labels.sendReminder || "Send reminder"))}</span>`,
                `data-send-reminder data-booking-id="${escapeHtml(String(booking.id))}" data-resend="${booking.reminder_sent ? "1" : "0"}"`
            )
            : "",
        booking.can_whatsapp_customer && options.showWhatsApp
            ? previewActionButton(
                "tr-calendar-event-preview__whatsapp tr-calendar-event-preview__action-btn--success",
                `<i class="fa fa-whatsapp" aria-hidden="true"></i><span>${escapeHtml(labels.whatsappCustomer || "WhatsApp customer")}</span>`,
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

    const orderLink = booking.order_url && !options.hideOrderLink
        ? `<a href="${escapeHtml(booking.order_url)}" class="tr-calendar-event-preview__action-btn tr-calendar-event-preview__action-btn--primary tr-calendar-event-preview__order" target="_blank" rel="noopener noreferrer"><i class="fa fa-external-link" aria-hidden="true"></i><span>${escapeHtml(labels.viewOrder)}</span></a>`
        : "";

    return `
        <div class="tr-calendar-event-preview__card" style="--tr-beautician-color:${escapeHtml(color)}">
            <div class="tr-calendar-event-preview__toolbar">
                <div class="tr-calendar-event-preview__toolbar-main">
                    ${statusControl}
                </div>
                ${metaChips ? `<div class="tr-calendar-event-preview__meta-row">${metaChips}</div>` : ""}
            </div>

            <div class="tr-calendar-event-preview__scroll">
                ${scheduleSection}
                ${customerSection}
                ${treatmentSection}
                ${staffSection}
                ${notesSection}
                ${activitySection}
            </div>

            ${actionButtons.length || orderLink
                ? `<div class="tr-calendar-event-preview__footer">
                    <div class="tr-calendar-event-preview__actions">
                        ${actionButtons.join("")}
                        ${orderLink}
                    </div>
                </div>`
                : ""}
        </div>
    `;
}

export function openCalendarEventPreview(booking, labels, options = {}) {
    previewLabels = labels;
    previewOptions = options;

    const overlay = getCalendarEventPreviewOverlay();
    const eyebrow = overlay.querySelector("#tr-calendar-event-preview-eyebrow");
    const title = overlay.querySelector("#tr-calendar-event-preview-title");
    const subtitle = overlay.querySelector("#tr-calendar-event-preview-subtitle");
    const timeRange = (booking.appointment_time_range || booking.time || booking.appointment_time || "").trim();

    if (eyebrow) {
        eyebrow.textContent = labels.previewTitle || "Appointment details";
    }

    if (title) {
        title.textContent = booking.customer_name || booking.treatment_name || "—";
    }

    if (subtitle) {
        subtitle.textContent = [booking.treatment_name, timeRange].filter(Boolean).join(" · ");
    }

    overlay.querySelector(".tr-calendar-event-preview__body").innerHTML = buildCalendarEventPreviewHtml(
        booking,
        labels,
        options
    );
    overlay.hidden = false;
    overlay.setAttribute("aria-hidden", "false");
    document.body.classList.add("tr-calendar-event-preview-open");
    overlay.querySelector(".tr-calendar-event-preview__close")?.focus();
}

export function closeCalendarEventPreview() {
    const overlay = document.getElementById("tr-calendar-event-preview");

    if (!overlay) {
        return;
    }

    overlay.hidden = true;
    overlay.setAttribute("aria-hidden", "true");
    document.body.classList.remove("tr-calendar-event-preview-open");
}

export function upsertBooking(booking) {
    const id = String(booking.id);

    kanbanBookingsById.set(id, { ...kanbanBookingsById.get(id), ...booking });
    calendarBookingsById.set(id, { ...calendarBookingsById.get(id), ...booking });
}

let calendarEventPreviewReady = false;
let previewResolveBooking = null;

function findClickableBookingTarget(target) {
    return target.closest(
        ".tr-cal-event--clickable, .tr-kanban-card--clickable, .tr-portal-today__item--clickable, .tr-crm-appointment, .tr-crm-ledger__row--clickable, .tr-crm-drawer-booking, .tr-crm-agenda-card__compact"
    );
}

function getBookingIdFromElement(element) {
    return element?.dataset?.bookingId || element?.dataset?.id || null;
}

function openBookingPreviewFromElement(element) {
    const bookingId = getBookingIdFromElement(element);

    if (!bookingId || !previewResolveBooking) {
        return;
    }

    const booking = previewResolveBooking(bookingId);

    if (!booking) {
        return;
    }

    openCalendarEventPreview(booking, previewLabels, previewOptions);
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

async function saveBeauticianNotes(button) {
    const bookingId = button.dataset.bookingId;
    const textarea = document.getElementById("tr-booking-beautician-notes");
    const notesUrlTemplate = previewOptions.notesUrlTemplate;

    if (!bookingId || !textarea || !notesUrlTemplate || !window.axios) {
        return;
    }

    const originalText = button.textContent;

    button.disabled = true;
    button.textContent = previewLabels.savingNotes || "Saving…";

    try {
        const url = notesUrlTemplate.replace("__ID__", bookingId);
        const response = await window.axios.patch(url, {
            beautician_notes: textarea.value,
        });
        const booking = response.data?.booking;

        if (booking) {
            upsertBooking(booking);
            openCalendarEventPreview(booking, previewLabels, previewOptions);
            window.notify?.success?.(previewLabels.notesSaved || "Notes saved") ||
                alert(previewLabels.notesSaved || "Notes saved");
        }
    } catch (error) {
        window.notify?.error?.(previewLabels.notesSaveFailed || "Failed to save notes") ||
            alert(previewLabels.notesSaveFailed || "Failed to save notes");
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

export function initCalendarEventPreview(resolveBooking, labels, options = {}) {
    previewResolveBooking = resolveBooking;
    previewLabels = labels;
    previewOptions = options;

    if (calendarEventPreviewReady) {
        return;
    }

    calendarEventPreviewReady = true;

    document.addEventListener("click", (event) => {
        if (event.target.closest(".tr-beautician-avatar--zoomable")) {
            return;
        }

        if (event.target.closest("[data-agenda-status], [data-agenda-status-wrap], [data-preview-status], .tr-calendar-event-preview__status-control")) {
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

        const editManualButton = event.target.closest(".tr-calendar-event-preview__edit-manual");

        if (editManualButton) {
            event.preventDefault();
            openManualBookingEditorFromPreview(editManualButton);

            return;
        }

        const rescheduleButton = event.target.closest("[data-preview-reschedule]");

        if (rescheduleButton) {
            event.preventDefault();
            openManualBookingEditorFromPreview(rescheduleButton);

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
