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

    overlay = document.createElement("div");
    overlay.id = "tr-calendar-event-preview";
    overlay.className = "tr-calendar-event-preview";
    overlay.hidden = true;
    overlay.innerHTML = `
        <div class="tr-calendar-event-preview__backdrop" data-dismiss></div>
        <div class="tr-calendar-event-preview__dialog" role="dialog" aria-modal="true">
            <button type="button" class="tr-calendar-event-preview__close" data-dismiss aria-label="Close">&times;</button>
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

export function buildCalendarEventPreviewHtml(booking, labels, options = {}) {
    const status = calendarStatusClass(booking.status);
    const color = booking.beautician_color || "#6366f1";
    const statusText = statusLabel(status, labels);
    const beauticianBlock = booking.beautician_name && !options.hideBeautician
        ? `<div class="tr-calendar-event-preview__beautician">${beauticianAvatarMarkup(booking, "tr-beautician-avatar--md")}<span>${escapeHtml(booking.beautician_name)}</span></div>`
        : "";
    const categoryRow = booking.category_name
        ? `<div class="tr-calendar-event-preview__row"><dt>${escapeHtml(labels.category)}</dt><dd>${escapeHtml(booking.category_name)}</dd></div>`
        : "";
    const phoneRow = booking.customer_phone
        ? `<div class="tr-calendar-event-preview__row"><dt>${escapeHtml(labels.phone || "Phone")}</dt><dd>${escapeHtml(booking.customer_phone)}</dd></div>`
        : "";
    const emailRow = booking.customer_email
        ? `<div class="tr-calendar-event-preview__row"><dt>${escapeHtml(labels.email || "Email")}</dt><dd>${escapeHtml(booking.customer_email)}</dd></div>`
        : "";
    const orderNotesRow = booking.notes
        ? `<div class="tr-calendar-event-preview__row tr-calendar-event-preview__row--notes"><dt>${escapeHtml(labels.orderNotes || "Order notes")}</dt><dd>${escapeHtml(booking.notes)}</dd></div>`
        : "";
    const whatsappBtn = booking.can_whatsapp_customer && options.showWhatsApp
        ? `<button type="button" class="btn btn-success btn-sm tr-calendar-event-preview__whatsapp" data-booking-id="${escapeHtml(String(booking.id))}"><i class="fa fa-whatsapp"></i> ${escapeHtml(labels.whatsappCustomer || "WhatsApp customer")}</button>`
        : "";
    const orderLink = booking.order_url && !options.hideOrderLink
        ? `<a href="${escapeHtml(booking.order_url)}" class="btn btn-primary btn-sm tr-calendar-event-preview__order" target="_blank"><i class="fa fa-external-link"></i> ${escapeHtml(labels.viewOrder)}</a>`
        : "";
    const manualEditBtn =
        booking.can_edit_manual && options.manualBookingEditEnabled
            ? `<button type="button" class="btn btn-default btn-sm tr-calendar-event-preview__edit-manual" data-booking-id="${escapeHtml(String(booking.id))}"><i class="fa fa-pencil"></i> ${escapeHtml(labels.editManual || "Edit appointment")}</button>`
            : "";
    const manualCancelBtn =
        booking.can_cancel_manual && options.manualBookingEditEnabled
            ? `<button type="button" class="btn btn-danger btn-sm tr-calendar-event-preview__cancel-manual" data-booking-id="${escapeHtml(String(booking.id))}"><i class="fa fa-times"></i> ${escapeHtml(labels.cancelManual || "Cancel appointment")}</button>`
            : "";
    const profileBtn = booking.customer_phone || booking.id
        ? `<button type="button" class="btn btn-default btn-sm tr-calendar-event-preview__profile" data-customer-profile data-booking-id="${escapeHtml(String(booking.id))}"><i class="fa fa-user"></i> ${escapeHtml(labels.viewProfile || "View profile")}</button>`
        : "";
    const reminderBtn = booking.can_send_reminder && options.manualBookingEditEnabled
        ? `<button type="button" class="btn btn-warning btn-sm tr-calendar-event-preview__reminder" data-send-reminder data-booking-id="${escapeHtml(String(booking.id))}" data-resend="${booking.reminder_sent ? "1" : "0"}"><i class="fa fa-bell"></i> ${escapeHtml(booking.reminder_sent ? (labels.resendReminder || "Resend reminder") : (labels.sendReminder || "Send reminder"))}</button>`
        : "";
    const insightBlock = (booking.customer_history_label || booking.loyalty_tier_name)
        ? `<div class="tr-calendar-event-preview__insights">
            ${booking.customer_history_label ? `<span>${escapeHtml(booking.customer_history_label)}</span>` : ""}
            ${booking.loyalty_tier_name ? `<span><i class="fa fa-star"></i> ${escapeHtml(booking.loyalty_tier_name)}</span>` : ""}
        </div>`
        : "";
    const reminderStatus = booking.reminder_sent
        ? `<div class="tr-calendar-event-preview__reminder-status tr-calendar-event-preview__reminder-status--sent">${escapeHtml(labels.reminderSent || "Reminder sent")}${booking.customer_reminder_sent_label ? ` · ${escapeHtml(booking.customer_reminder_sent_label)}` : ""}</div>`
        : (booking.reminder_due
            ? `<div class="tr-calendar-event-preview__reminder-status tr-calendar-event-preview__reminder-status--due">${escapeHtml(labels.reminderDue || "Due for reminder")}</div>`
            : "");
    const notesSection = options.allowBeauticianNotes
        ? `
            <div class="tr-calendar-event-preview__notes">
                <label for="tr-booking-beautician-notes">${escapeHtml(labels.beauticianNotes || "Beautician notes")}</label>
                <textarea id="tr-booking-beautician-notes" class="form-control" rows="3" data-booking-id="${escapeHtml(booking.id)}">${escapeHtml(booking.beautician_notes || "")}</textarea>
                <button type="button" class="btn btn-default btn-sm tr-calendar-event-preview__save-notes" data-booking-id="${escapeHtml(booking.id)}">
                    ${escapeHtml(labels.saveNotes || "Save notes")}
                </button>
            </div>
        `
        : (booking.beautician_notes
            ? `<div class="tr-calendar-event-preview__row tr-calendar-event-preview__row--notes"><dt>${escapeHtml(labels.beauticianNotes || "Beautician notes")}</dt><dd>${escapeHtml(booking.beautician_notes)}</dd></div>`
            : "");
    const activitySection = options.showActivityLog && Array.isArray(booking.recent_activities) && booking.recent_activities.length
        ? `
            <div class="tr-calendar-event-preview__activity">
                <h4 class="tr-calendar-event-preview__activity-title">${escapeHtml(labels.activityTitle || "Activity log")}</h4>
                <ul class="tr-calendar-event-preview__activity-list">
                    ${booking.recent_activities.map((activity) => `
                        <li class="tr-calendar-event-preview__activity-item">
                            <span class="tr-calendar-event-preview__activity-time">${escapeHtml(activity.created_at || "")}</span>
                            <strong>${escapeHtml(activity.actor_name || "—")}</strong>
                            <span>${escapeHtml(activity.summary || "")}</span>
                        </li>
                    `).join("")}
                </ul>
            </div>
        `
        : "";

    return `
        <div class="tr-calendar-event-preview__card" style="--tr-beautician-color:${escapeHtml(color)}">
            <div class="tr-calendar-event-preview__header">
                <span class="tr-calendar-event-preview__status tr-calendar-event-preview__status--${status}">${escapeHtml(statusText)}</span>
            </div>
            ${insightBlock}
            ${reminderStatus}
            ${beauticianBlock}
            <dl class="tr-calendar-event-preview__details">
                <div class="tr-calendar-event-preview__row"><dt>${escapeHtml(labels.date)}</dt><dd>${escapeHtml(booking.appointment_date || booking.date || "—")}</dd></div>
                <div class="tr-calendar-event-preview__row"><dt>${escapeHtml(labels.time)}</dt><dd>${escapeHtml(booking.time || booking.appointment_time || "—")}</dd></div>
                <div class="tr-calendar-event-preview__row"><dt>${escapeHtml(labels.customer)}</dt><dd>${escapeHtml(booking.customer_name || "—")}</dd></div>
                ${phoneRow}
                ${emailRow}
                <div class="tr-calendar-event-preview__row tr-calendar-event-preview__row--treatment"><dt>${escapeHtml(labels.treatment)}</dt><dd>${escapeHtml(booking.treatment_name || "—")}</dd></div>
                ${categoryRow}
                ${orderNotesRow}
            </dl>
            ${notesSection}
            <div class="tr-calendar-event-preview__actions">
                ${profileBtn}
                ${manualEditBtn}
                ${manualCancelBtn}
                ${reminderBtn}
                ${whatsappBtn}
                ${orderLink}
            </div>
            ${activitySection}
        </div>
    `;
}

export function openCalendarEventPreview(booking, labels, options = {}) {
    previewLabels = labels;
    previewOptions = options;

    const overlay = getCalendarEventPreviewOverlay();

    overlay.querySelector(".tr-calendar-event-preview__body").innerHTML = buildCalendarEventPreviewHtml(
        booking,
        labels,
        options
    );
    overlay.hidden = false;
    document.body.classList.add("tr-calendar-event-preview-open");
    overlay.querySelector(".tr-calendar-event-preview__close")?.focus();
}

export function closeCalendarEventPreview() {
    const overlay = document.getElementById("tr-calendar-event-preview");

    if (!overlay) {
        return;
    }

    overlay.hidden = true;
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
        ".tr-cal-event--clickable, .tr-kanban-card--clickable, .tr-portal-today__item--clickable, .tr-crm-appointment, .tr-crm-ledger__row--clickable, .tr-crm-drawer-booking, .tr-crm-agenda-card__body"
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

        if (event.target.closest("[data-agenda-status], [data-agenda-status-wrap]")) {
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

        const cancelManualButton = event.target.closest(".tr-calendar-event-preview__cancel-manual");

        if (cancelManualButton) {
            event.preventDefault();
            cancelManualBooking(cancelManualButton);

            return;
        }

        if (event.target.closest(".tr-calendar-event-preview__notes")) {
            return;
        }

        if (event.target.closest(".tr-kanban-card-link")) {
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

    document.addEventListener("keydown", (event) => {
        const card = event.target.closest(
            ".tr-cal-event--clickable, .tr-kanban-card--clickable, .tr-portal-today__item--clickable, .tr-crm-appointment, .tr-crm-ledger__row--clickable, .tr-crm-drawer-booking, .tr-crm-agenda-card__body"
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
