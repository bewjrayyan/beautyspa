import axios from "axios";
import { getCalendarBooking, sendBookingWhatsApp, setCalendarBookings, upsertBooking } from "./kanban-helpers.js";
import { initCustomerProfileDrawer } from "./customer-profile.js";
import { openManualBookingEditor } from "./manual-booking.js";

function escapeHtml(value = "") {
    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

function getAgendaLabels() {
    const root = document.getElementById("tr-crm-dashboard");

    return {
        pending: root?.dataset.agendaStatusPending || "Pending",
        in_progress: root?.dataset.agendaStatusInProgress || "In Progress",
        completed: root?.dataset.agendaStatusCompleted || "Completed",
        canceled: root?.dataset.agendaStatusCanceled || "Canceled",
        idLabel: root?.dataset.agendaIdLabel || "ID",
        durationMinutes: root?.dataset.agendaDurationMinutes || "(:count min)",
        updateStatusAria: root?.dataset.agendaUpdateStatusAria || "Update booking status",
        statusUpdateFailed: root?.dataset.agendaStatusUpdateFailed || "Failed to update status",
        locale: root?.dataset.agendaLocale || undefined,
        orderNotesLabel: root?.dataset.agendaOrderNotesLabel || "Order notes",
        beauticianNotesLabel: root?.dataset.agendaBeauticianNotesLabel || "Beautician notes",
        viewOrder: root?.dataset.agendaViewOrder || "View order",
        reschedule: root?.dataset.agendaReschedule || "Reschedule",
        editManual: root?.dataset.agendaEditManual || "Edit appointment",
        whatsapp: root?.dataset.agendaWhatsapp || "WhatsApp",
        whatsappSending: root?.dataset.agendaWhatsappSending || "Sending…",
        whatsappSent: root?.dataset.agendaWhatsappSent || "WhatsApp message sent",
        whatsappFailed: root?.dataset.agendaWhatsappFailed || "Failed to send WhatsApp message",
        viewProfile: root?.dataset.agendaViewProfile || "View profile",
        sendReminder: root?.dataset.agendaSendReminder || "Send reminder",
        resendReminder: root?.dataset.agendaResendReminder || "Resend reminder",
        reminderSent: root?.dataset.agendaReminderSent || "Reminder sent",
    };
}

function getCrmDashboardLabels() {
    const root = document.getElementById("tr-crm-dashboard");

    return {
        specialistUnavailable: root?.dataset.specialistUnavailable || "Day off",
        specialistAvailable: root?.dataset.specialistAvailable || "Available",
        specialistToggleFailed: root?.dataset.specialistToggleFailed || "Failed to update specialist availability",
        pipelineStatusFailed: root?.dataset.pipelineStatusFailed || "Failed to update status",
    };
}

function manualBookingEditEnabled() {
    return document.getElementById("tr-reservations-app")?.dataset.manualBookingEdit === "1";
}

function crmEditEnabled() {
    const root = document.getElementById("tr-crm-dashboard");

    return root?.dataset.crmCanEdit === "1" || manualBookingEditEnabled();
}

function initDashboardSearch() {
    const input = document.getElementById("tr-crm-search");
    const root = document.getElementById("tr-crm-dashboard");

    if (!input) {
        return;
    }

    const selectors = [
        "[data-crm-list] .tr-crm-appointment",
        "[data-crm-list] .tr-crm-alert",
        "[data-crm-list] .tr-crm-activity__item",
        "[data-crm-list] .tr-crm-audit__item",
        "[data-crm-list] .tr-crm-specialist",
        "[data-crm-list] .tr-crm-pipeline-card",
        "[data-crm-list] .tr-crm-ledger__row",
        "[data-crm-list] .tr-crm-agenda-card",
    ].join(", ");

    const emptySelectors = [
        "[data-crm-list] .tr-crm-empty",
        "[data-crm-list] .tr-crm-ledger__empty",
    ].join(", ");

    const noResultsMessage = root?.dataset.searchNoResults || "No matches for your search";
    let searchNotice = document.getElementById("tr-crm-search-empty");

    if (!searchNotice && root) {
        searchNotice = document.createElement("p");
        searchNotice.id = "tr-crm-search-empty";
        searchNotice.className = "tr-crm-search-empty";
        searchNotice.hidden = true;
        root.prepend(searchNotice);
    }

    input.addEventListener("input", () => {
        const query = input.value.trim().toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll(selectors).forEach((row) => {
            const haystack = (row.dataset.search || row.textContent || "").toLowerCase();
            const matches = query === "" || haystack.includes(query);

            row.hidden = !matches;

            if (matches) {
                visibleCount += 1;
            }
        });

        document.querySelectorAll(emptySelectors).forEach((row) => {
            row.hidden = query !== "";
        });

        if (searchNotice) {
            searchNotice.textContent = noResultsMessage;
            searchNotice.hidden = query === "" || visibleCount > 0;
        }
    });
}

function initDateFilterPills() {
    const form = document.getElementById("tr-crm-header-form");
    const hiddenInput = document.getElementById("tr-crm-date-filter");

    if (!form || !hiddenInput) {
        return;
    }

    form.querySelectorAll("[data-date-filter]").forEach((button) => {
        button.addEventListener("click", () => {
            hiddenInput.value = button.dataset.dateFilter || "today";

            form.querySelectorAll("[data-date-filter]").forEach((pill) => {
                pill.classList.toggle("is-active", pill === button);
            });

            form.requestSubmit();
        });
    });
}

function formatAgendaTime(booking) {
    return (booking.appointment_time_range || booking.time || booking.appointment_time || "—").trim();
}

function renderAgendaCustomerInsight(booking) {
    const history = (booking.customer_history_label || "").trim();
    const tier = (booking.loyalty_tier_name || "").trim();
    const parts = [];

    if (history) {
        parts.push(`<span class="tr-crm-agenda-card__insight">${escapeHtml(history)}</span>`);
    }

    if (tier) {
        parts.push(`<span class="tr-crm-agenda-card__insight tr-crm-agenda-card__insight--loyalty"><i class="fa fa-star" aria-hidden="true"></i> ${escapeHtml(tier)}</span>`);
    }

    if (parts.length === 0) {
        return "";
    }

    return `<div class="tr-crm-agenda-card__customer-insight">${parts.join("")}</div>`;
}

function renderAgendaAlerts(booking) {
    const alerts = Array.isArray(booking.inline_alerts) ? booking.inline_alerts : [];

    if (alerts.length === 0) {
        return "";
    }

    return `
        <div class="tr-crm-agenda-card__alerts">
            ${alerts.map((alert) => `
                <span class="tr-crm-agenda-card__alert tr-crm-agenda-card__alert--${escapeHtml(alert.level || "info")}">
                    ${escapeHtml(alert.label || "")}
                </span>
            `).join("")}
        </div>
    `;
}

function renderAgendaQuickActions(booking, labels) {
    const actions = [];

    if (booking.order_url) {
        actions.push(`
            <a
                href="${escapeHtml(booking.order_url)}"
                class="tr-crm-agenda-card__action"
                target="_blank"
                rel="noopener noreferrer"
            >
                <i class="fa fa-external-link" aria-hidden="true"></i>
                ${escapeHtml(labels.viewOrder)}
            </a>
        `);
    }

    if (manualBookingEditEnabled() && booking.can_reschedule_manual) {
        actions.push(`
            <button
                type="button"
                class="tr-crm-agenda-card__action"
                data-agenda-reschedule
                data-booking-id="${escapeHtml(booking.id)}"
            >
                <i class="fa fa-calendar" aria-hidden="true"></i>
                ${escapeHtml(labels.reschedule)}
            </button>
        `);
    }

    if (manualBookingEditEnabled() && booking.can_edit_manual) {
        actions.push(`
            <button
                type="button"
                class="tr-crm-agenda-card__action"
                data-agenda-edit-manual
                data-booking-id="${escapeHtml(booking.id)}"
            >
                <i class="fa fa-pencil" aria-hidden="true"></i>
                ${escapeHtml(labels.editManual)}
            </button>
        `);
    }

    if (manualBookingEditEnabled() && booking.can_whatsapp_customer) {
        actions.push(`
            <button
                type="button"
                class="tr-crm-agenda-card__action tr-crm-agenda-card__action--whatsapp"
                data-agenda-whatsapp
                data-booking-id="${escapeHtml(booking.id)}"
            >
                <i class="fa fa-whatsapp" aria-hidden="true"></i>
                ${escapeHtml(labels.whatsapp)}
            </button>
        `);
    }

    if (booking.customer_phone || booking.id) {
        actions.push(`
            <button
                type="button"
                class="tr-crm-agenda-card__action"
                data-customer-profile
                data-booking-id="${escapeHtml(booking.id)}"
            >
                <i class="fa fa-user" aria-hidden="true"></i>
                ${escapeHtml(labels.viewProfile)}
            </button>
        `);
    }

    if (manualBookingEditEnabled() && booking.can_send_reminder) {
        actions.push(`
            <button
                type="button"
                class="tr-crm-agenda-card__action tr-crm-agenda-card__action--reminder"
                data-send-reminder
                data-booking-id="${escapeHtml(booking.id)}"
                data-resend="${booking.reminder_sent ? "1" : "0"}"
            >
                <i class="fa fa-bell" aria-hidden="true"></i>
                ${escapeHtml(booking.reminder_sent ? labels.resendReminder : labels.sendReminder)}
            </button>
        `);
    }

    if (actions.length === 0) {
        return "";
    }

    return `<div class="tr-crm-agenda-card__actions">${actions.join("")}</div>`;
}

function renderAgendaMeta(booking) {
    const chips = [];
    const source = (booking.source_label || "").trim();
    const branch = (booking.spa_branch_name || "").trim();
    const category = (booking.category_name || "").trim();

    if (source) {
        const sourceKey = booking.source || "checkout";
        chips.push(`<span class="tr-crm-agenda-card__chip tr-crm-agenda-card__chip--source-${escapeHtml(sourceKey)}">${escapeHtml(source)}</span>`);
    }

    if (branch) {
        chips.push(`<span class="tr-crm-agenda-card__chip tr-crm-agenda-card__chip--branch">${escapeHtml(branch)}</span>`);
    }

    if (category) {
        chips.push(`<span class="tr-crm-agenda-card__chip tr-crm-agenda-card__chip--category">${escapeHtml(category)}</span>`);
    }

    if (chips.length === 0) {
        return "";
    }

    return `<div class="tr-crm-agenda-card__meta">${chips.join("")}</div>`;
}

function renderAgendaNotes(booking, labels) {
    const orderNotes = (booking.notes || "").trim();
    const beauticianNotes = (booking.beautician_notes || "").trim();
    const blocks = [];

    if (orderNotes) {
        blocks.push(`
            <div class="tr-crm-agenda-card__notes-block">
                <span class="tr-crm-agenda-card__notes-label">${escapeHtml(labels.orderNotesLabel)}</span>
                <blockquote class="tr-crm-agenda-card__notes">"${escapeHtml(orderNotes)}"</blockquote>
            </div>
        `);
    }

    if (beauticianNotes) {
        blocks.push(`
            <div class="tr-crm-agenda-card__notes-block">
                <span class="tr-crm-agenda-card__notes-label">${escapeHtml(labels.beauticianNotesLabel)}</span>
                <blockquote class="tr-crm-agenda-card__notes tr-crm-agenda-card__notes--clinical">"${escapeHtml(beauticianNotes)}"</blockquote>
            </div>
        `);
    }

    return blocks.join("");
}

function renderAgendaSpecialistSubtitle(booking) {
    const jobTitle = (booking.beautician_job_title || "").trim();

    if (jobTitle) {
        return `<span>${escapeHtml(jobTitle)}</span>`;
    }

    if (booking.category_name) {
        return `<span>${escapeHtml(booking.category_name)}</span>`;
    }

    return "";
}

function formatAgendaDuration(booking, labels) {
    const minutes = Number(booking.slot_duration_minutes) || 60;

    return labels.durationMinutes.replace(":count", String(minutes));
}

function renderAgendaAvatar(booking) {
    const color = booking.beautician_color || "#6d2847";
    const name = booking.beautician_name || "—";

    if (booking.beautician_avatar) {
        return `<span class="tr-crm-agenda-card__avatar tr-crm-agenda-card__avatar--photo" style="background-color:${escapeHtml(color)}"><img src="${escapeHtml(booking.beautician_avatar)}" alt="${escapeHtml(name)}" draggable="false"></span>`;
    }

    return `<span class="tr-crm-agenda-card__avatar" style="background-color:${escapeHtml(color)}">${escapeHtml(booking.beautician_initial || "?")}</span>`;
}

function renderAgendaStatusControl(status, labels, bookingId) {
    if (status === "canceled" || !crmEditEnabled()) {
        const label = status === "canceled"
            ? labels.canceled
            : (labels[status] || labels.pending);

        return `<span class="tr-crm-agenda-card__status-badge tr-crm-agenda-card__status-badge--${escapeHtml(status)}">${escapeHtml(label)}</span>`;
    }

    const statuses = ["pending", "in_progress", "completed"];
    const options = statuses.map((key) => `
        <option value="${key}"${status === key ? " selected" : ""}>${escapeHtml(labels[key] || key)}</option>
    `).join("");

    return `
        <div class="tr-crm-agenda-card__status-wrap tr-crm-agenda-card__status-wrap--${escapeHtml(status)}" data-agenda-status-wrap>
            <select
                class="tr-crm-agenda-card__status-select"
                data-agenda-status
                data-booking-id="${escapeHtml(bookingId)}"
                data-current-status="${escapeHtml(status)}"
                aria-label="${escapeHtml(labels.updateStatusAria)}"
            >
                ${options}
            </select>
            <i class="fa fa-chevron-down tr-crm-agenda-card__status-chevron" aria-hidden="true"></i>
        </div>
    `;
}

function renderAgendaPhone(booking) {
    const phone = (booking.customer_phone || "").trim();

    if (! phone) {
        return "";
    }

    const telHref = `tel:${phone.replace(/[^\d+]/g, "")}`;

    return `
        <div class="tr-crm-agenda-card__contact">
            <a href="${escapeHtml(telHref)}" class="tr-crm-agenda-card__phone">${escapeHtml(phone)}</a>
        </div>
    `;
}

function renderAgendaBooking(booking, labels) {
    const status = booking.status || "pending";
    const price = booking.total_formatted || "";
    const treatmentLine = [booking.treatment_name || booking.product_name || "—", price].filter(Boolean).join(" • ");
    const subtitle = booking.treatment_subtitle || booking.duration_session_label || "";
    const paymentLabel = (booking.payment_status_label || "").trim();
    const time = formatAgendaTime(booking);
    const searchHaystack = [
        booking.customer_name,
        booking.customer_phone,
        booking.treatment_name,
        booking.product_name,
        booking.treatment_subtitle,
        booking.treatment_selection,
        booking.category_name,
        booking.beautician_name,
        booking.beautician_job_title,
        booking.source_label,
        booking.spa_branch_name,
        booking.appointment_time_range,
        paymentLabel,
        time,
        price,
        booking.customer_history_label,
        booking.loyalty_tier_name,
        ...(Array.isArray(booking.inline_alerts) ? booking.inline_alerts.map((alert) => alert.label) : []),
        booking.id,
    ].filter(Boolean).join(" ");

    return `
        <li
            class="tr-crm-agenda-card"
            data-booking-id="${escapeHtml(booking.id)}"
            data-search="${escapeHtml(searchHaystack)}"
        >
            <div class="tr-crm-agenda-card__top">
                <div class="tr-crm-agenda-card__time-wrap">
                    <i class="fa fa-clock-o tr-crm-agenda-card__time-icon" aria-hidden="true"></i>
                    <span class="tr-crm-agenda-card__time">${escapeHtml(time)}</span>
                    <span class="tr-crm-agenda-card__duration">${escapeHtml(formatAgendaDuration(booking, labels))}</span>
                </div>
                ${renderAgendaStatusControl(status, labels, booking.id)}
                ${booking.reminder_sent
                    ? `<span class="tr-crm-agenda-card__reminder tr-crm-agenda-card__reminder--sent">${escapeHtml(labels.reminderSent)}</span>`
                    : (booking.reminder_due
                        ? `<span class="tr-crm-agenda-card__reminder tr-crm-agenda-card__reminder--due">${escapeHtml(labels.sendReminder)}</span>`
                        : "")}
            </div>
            <div class="tr-crm-agenda-card__body" data-agenda-open data-booking-id="${escapeHtml(booking.id)}" role="button" tabindex="0">
                <strong
                    class="tr-crm-agenda-card__customer tr-crm-agenda-card__customer--link"
                    data-customer-profile
                    data-booking-id="${escapeHtml(booking.id)}"
                >${escapeHtml(booking.customer_name || "—")}</strong>
                ${renderAgendaCustomerInsight(booking)}
                ${renderAgendaAlerts(booking)}
                ${renderAgendaPhone(booking)}
                <p class="tr-crm-agenda-card__treatment">${escapeHtml(treatmentLine)}</p>
                ${subtitle ? `<p class="tr-crm-agenda-card__subtitle">${escapeHtml(subtitle)}</p>` : ""}
                ${paymentLabel ? `<span class="tr-crm-agenda-card__payment">${escapeHtml(paymentLabel)}</span>` : ""}
                ${renderAgendaMeta(booking)}
                <div class="tr-crm-agenda-card__footer">
                    <div class="tr-crm-agenda-card__specialist">
                        ${renderAgendaAvatar(booking)}
                        <div class="tr-crm-agenda-card__specialist-text">
                            <strong>${escapeHtml(booking.beautician_name || "—")}</strong>
                            ${renderAgendaSpecialistSubtitle(booking)}
                        </div>
                    </div>
                    <span class="tr-crm-agenda-card__id">${escapeHtml(labels.idLabel)}: B${escapeHtml(booking.id)}</span>
                </div>
                ${renderAgendaNotes(booking, labels)}
            </div>
            ${renderAgendaQuickActions(booking, labels)}
        </li>
    `;
}

async function handleAgendaStatusChange(select, app, labels) {
    const bookingId = select.dataset.bookingId;
    const nextStatus = select.value;
    const previousStatus = select.dataset.currentStatus;

    if (!bookingId || !nextStatus || nextStatus === previousStatus || !app?.statusUrlTemplate) {
        return;
    }

    const url = app.statusUrlTemplate.replace("__ID__", bookingId);
    const wrap = select.closest("[data-agenda-status-wrap]");

    select.disabled = true;

    try {
        const response = await axios.patch(url, { status: nextStatus });

        if (response.data?.booking) {
            upsertBooking(response.data.booking);

            const index = (app.lastCalendarBookings || []).findIndex(
                (booking) => String(booking.id) === String(bookingId)
            );

            if (index >= 0) {
                app.lastCalendarBookings[index] = {
                    ...app.lastCalendarBookings[index],
                    ...response.data.booking,
                };
            }

            select.dataset.currentStatus = nextStatus;

            if (wrap) {
                wrap.className = `tr-crm-agenda-card__status-wrap tr-crm-agenda-card__status-wrap--${nextStatus}`;
            }

            app.refreshAgendaPanel?.();
        }
    } catch (error) {
        select.value = previousStatus;
        window.notify?.error?.(labels.statusUpdateFailed) || alert(labels.statusUpdateFailed);
    } finally {
        select.disabled = false;
    }
}

function initAgendaPanel(app) {
    const panel = document.getElementById("tr-crm-agenda-panel");

    if (!panel || !app?.grid) {
        return;
    }

    const title = document.getElementById("tr-crm-agenda-title");
    const list = document.getElementById("tr-crm-agenda-list");
    const empty = document.getElementById("tr-crm-agenda-empty");
    const labels = getAgendaLabels();
    const crmRoot = document.getElementById("tr-crm-dashboard");
    let selectedDate = crmRoot?.dataset.agendaInitialDate || new Date().toISOString().slice(0, 10);

    const bookingsForDate = (dateStr) => (app.lastCalendarBookings || []).filter((booking) => booking.date === dateStr);

    const updateAgenda = (dateStr, bookings = null) => {
        if (!dateStr) {
            return;
        }

        selectedDate = dateStr;
        const dayBookings = bookings ?? bookingsForDate(dateStr);
        const date = new Date(`${dateStr}T12:00:00`);

        if (title) {
            title.textContent = date.toLocaleDateString(labels.locale, {
                weekday: "short",
                day: "numeric",
                month: "short",
                year: "numeric",
            });
        }

        if (list) {
            list.innerHTML = dayBookings
                .sort((a, b) => String(a.time || a.appointment_time || "").localeCompare(String(b.time || b.appointment_time || "")))
                .map((booking) => renderAgendaBooking(booking, labels))
                .join("");
        }

        if (empty) {
            empty.hidden = dayBookings.length > 0;
        }

        app.grid.querySelectorAll(".tr-cal-day[data-date]").forEach((day) => {
            day.classList.toggle("tr-cal-day--selected", day.dataset.date === dateStr);
            day.setAttribute("aria-selected", day.dataset.date === dateStr ? "true" : "false");
        });
    };

    const openAgendaForDay = (day) => {
        if (!day || day.classList.contains("tr-cal-day--muted")) {
            return;
        }

        updateAgenda(day.dataset.date || "");
    };

    app.grid.addEventListener("click", (event) => {
        const day = event.target.closest(".tr-cal-day[data-date]");

        if (!day) {
            return;
        }

        openAgendaForDay(day);
    });

    app.grid.addEventListener("keydown", (event) => {
        if (event.key !== "Enter" && event.key !== " ") {
            return;
        }

        const day = event.target.closest(".tr-cal-day[data-date]");

        if (!day) {
            return;
        }

        event.preventDefault();
        openAgendaForDay(day);
    });

    list?.addEventListener("change", (event) => {
        const select = event.target.closest("[data-agenda-status]");

        if (!select) {
            return;
        }

        event.stopPropagation();
        handleAgendaStatusChange(select, app, labels);
    });

    list?.addEventListener("click", async (event) => {
        const whatsappButton = event.target.closest("[data-agenda-whatsapp]");

        if (whatsappButton) {
            event.preventDefault();
            event.stopPropagation();

            const appRoot = document.getElementById("tr-reservations-app");
            const whatsappUrlTemplate = appRoot?.dataset.whatsappUrl || "";
            const bookingId = whatsappButton.dataset.bookingId;
            const originalHtml = whatsappButton.innerHTML;

            whatsappButton.disabled = true;
            whatsappButton.innerHTML = `<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> ${escapeHtml(labels.whatsappSending)}`;

            const result = await sendBookingWhatsApp(bookingId, {
                whatsappUrlTemplate,
                labels,
            });

            if (result.ok) {
                window.notify?.success?.(result.message) || alert(result.message);
                whatsappButton.disabled = false;
                whatsappButton.innerHTML = originalHtml;
                app.refreshAgendaPanel?.();
            } else {
                window.notify?.error?.(result.message) || alert(result.message);
                whatsappButton.disabled = false;
                whatsappButton.innerHTML = originalHtml;
            }

            return;
        }

        const rescheduleButton = event.target.closest("[data-agenda-reschedule], [data-agenda-edit-manual]");

        if (rescheduleButton) {
            event.preventDefault();
            event.stopPropagation();

            const booking = getCalendarBooking(rescheduleButton.dataset.bookingId);

            if (booking) {
                openManualBookingEditor(booking);
            }

            return;
        }

        if (event.target.closest("[data-agenda-status], [data-agenda-status-wrap], .tr-crm-agenda-card__phone, .tr-crm-agenda-card__actions, .tr-crm-agenda-card__action, [data-customer-profile], [data-send-reminder], .tr-crm-agenda-card__customer--link")) {
            event.stopPropagation();
        }
    });

    app.refreshAgendaPanel = () => {
        updateAgenda(selectedDate);
    };

    app.updateAgenda = updateAgenda;

    if (crmRoot?.dataset.agendaInitialDate) {
        updateAgenda(selectedDate);
    }
}

function getPipelineBooking(id) {
    const root = document.getElementById("tr-crm-dashboard");

    if (!root?.dataset.initialBookings) {
        return null;
    }

    try {
        const bookings = JSON.parse(root.dataset.initialBookings);

        return bookings.find((booking) => String(booking.id) === String(id)) || null;
    } catch {
        return null;
    }
}

function updatePipelineCounts() {
    document.querySelectorAll("[data-pipeline-count]").forEach((countEl) => {
        const status = countEl.dataset.pipelineCount;
        const list = document.querySelector(`[data-pipeline-list="${status}"]`);

        if (!list) {
            return;
        }

        countEl.textContent = String(list.querySelectorAll(".tr-crm-pipeline-card").length);
    });
}

function initPipelineSortable(app) {
    const root = document.querySelector("[data-crm-pipeline]");

    if (!root || !app?.statusUrlTemplate || !window.Sortable) {
        return;
    }

    const labels = getCrmDashboardLabels();

    root.querySelectorAll("[data-pipeline-list]").forEach((container) => {
        if (container.dataset.sortableInit) {
            return;
        }

        container.dataset.sortableInit = "1";

        Sortable.create(container, {
            group: "tr-crm-pipeline",
            animation: 150,
            draggable: ".tr-crm-pipeline-card",
            ghostClass: "tr-crm-pipeline-card--ghost",
            chosenClass: "tr-crm-pipeline-card--chosen",
            filter: ".tr-crm-pipeline-card__action, .tr-crm-pipeline-card__quick-action, .tr-crm-pipeline-card__quick-actions, a, button",
            preventOnFilter: true,
            onEnd: async (evt) => {
                const card = evt.item;
                const bookingId = card.dataset.bookingId;
                const newStatus = evt.to.dataset.pipelineList;
                const oldStatus = evt.from.dataset.pipelineList;

                if (!bookingId || !newStatus || newStatus === oldStatus) {
                    return;
                }

                const url = app.statusUrlTemplate.replace("__ID__", bookingId);

                try {
                    const response = await axios.patch(url, { status: newStatus });

                    if (response.data?.booking) {
                        upsertBooking(response.data.booking);
                    }

                    updatePipelineCounts();
                    window.location.reload();
                } catch (error) {
                    evt.from.insertBefore(card, evt.from.children[evt.oldIndex] || null);
                    updatePipelineCounts();
                    window.notify?.error?.(labels.pipelineStatusFailed) || alert(labels.pipelineStatusFailed);
                }
            },
        });
    });
}

function updateSpecialistRow(row, available, labels) {
    const badge = row.querySelector("[data-specialist-badge]");

    if (!badge) {
        return;
    }

    if (available) {
        badge.className = "tr-crm-specialist__badge tr-crm-specialist__badge--available";
        badge.textContent = labels.specialistAvailable;
        row.dataset.specialistStatus = "available";
    } else {
        badge.className = "tr-crm-specialist__badge tr-crm-specialist__badge--unavailable";
        badge.textContent = labels.specialistUnavailable;
        row.dataset.specialistStatus = "unavailable";
    }
}

function initSpecialistToggles() {
    const root = document.getElementById("tr-crm-dashboard");

    if (!root || root.dataset.specialistToggleEnabled !== "1" || !root.dataset.specialistToggleUrl) {
        return;
    }

    const labels = getCrmDashboardLabels();
    const toggleUrlTemplate = root.dataset.specialistToggleUrl;
    const toggleDate = root.dataset.specialistToggleDate || new Date().toISOString().slice(0, 10);

    document.querySelectorAll("[data-specialist-toggle]").forEach((input) => {
        input.addEventListener("change", async () => {
            const beauticianId = input.dataset.beauticianId;
            const row = input.closest(".tr-crm-specialist");
            const available = input.checked;
            const previous = !available;

            if (!beauticianId || !row) {
                return;
            }

            input.disabled = true;

            try {
                const url = toggleUrlTemplate.replace("__ID__", beauticianId);

                await axios.patch(url, {
                    available,
                    date: input.dataset.toggleDate || toggleDate,
                });

                updateSpecialistRow(row, available, labels);
            } catch (error) {
                input.checked = previous;
                window.notify?.error?.(labels.specialistToggleFailed) || alert(labels.specialistToggleFailed);
            } finally {
                input.disabled = false;
            }
        });
    });
}

function initLedgerActions() {
    const ledger = document.querySelector(".tr-crm-ledger");

    if (!ledger) {
        return;
    }

    ledger.addEventListener("click", (event) => {
        const rescheduleButton = event.target.closest("[data-ledger-reschedule]");

        if (!rescheduleButton) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const booking = getPipelineBooking(rescheduleButton.dataset.bookingId)
            || getCalendarBooking(rescheduleButton.dataset.bookingId);

        if (booking) {
            openManualBookingEditor(booking);
        }
    });
}

function initPipelineActions(app) {
    const root = document.querySelector("[data-crm-pipeline]");

    if (!root || !app?.statusUrlTemplate) {
        return;
    }

    const labels = getCrmDashboardLabels();

    root.addEventListener("click", async (event) => {
        const rescheduleButton = event.target.closest("[data-pipeline-reschedule]");

        if (rescheduleButton) {
            event.preventDefault();
            event.stopPropagation();

            const booking = getPipelineBooking(rescheduleButton.dataset.bookingId)
                || getCalendarBooking(rescheduleButton.dataset.bookingId);

            if (booking) {
                openManualBookingEditor(booking);
            }

            return;
        }

        const button = event.target.closest("[data-pipeline-action]");

        if (!button) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const bookingId = button.dataset.bookingId;
        const nextStatus = button.dataset.nextStatus;

        if (!bookingId || !nextStatus || button.disabled) {
            return;
        }

        const url = app.statusUrlTemplate.replace("__ID__", bookingId);
        const originalText = button.textContent;

        button.disabled = true;
        button.innerHTML = `<i class="fa fa-spinner fa-spin"></i>`;

        try {
            const response = await axios.patch(url, { status: nextStatus });

            if (response.data?.booking) {
                upsertBooking(response.data.booking);
            }

            window.location.reload();
        } catch (error) {
            button.disabled = false;
            button.textContent = originalText;
            window.notify?.error?.(labels.pipelineStatusFailed) || alert(labels.pipelineStatusFailed);
        }
    });
}

export function initCrmDashboard(app) {
    const root = document.getElementById("tr-crm-dashboard");

    if (!root) {
        return;
    }

    try {
        const seed = JSON.parse(root.dataset.initialBookings || "[]");

        if (Array.isArray(seed) && seed.length) {
            setCalendarBookings(seed.map((booking) => ({
                ...booking,
                date: booking.date || booking.appointment_date_value || "",
                time: booking.appointment_time || booking.time || "",
            })));
        }
    } catch (error) {
        // ignore invalid seed payload
    }

    initDashboardSearch();
    initDateFilterPills();
    initAgendaPanel(app);
    initPipelineSortable(app);
    initPipelineActions(app);
    initLedgerActions();
    initSpecialistToggles();
    initCustomerProfileDrawer();

    document.addEventListener("tr-crm-booking-updated", () => {
        app.refreshAgendaPanel?.();
    });
}
