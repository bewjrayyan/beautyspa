import axios from "axios";
import { getCalendarBooking, setCalendarBookings, upsertBooking } from "./kanban-helpers.js";
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

function renderAgendaStatusPill(status, labels) {
    const label = status === "canceled"
        ? labels.canceled
        : (labels[status] || labels.pending);

    return `<span class="tr-crm-agenda-card__status-pill tr-crm-agenda-card__status-pill--${escapeHtml(status)}">${escapeHtml(label)}</span>`;
}

function renderAgendaCompactAlertDot(booking) {
    const alerts = Array.isArray(booking.inline_alerts) ? booking.inline_alerts : [];

    if (alerts.length === 0) {
        return "";
    }

    const level = alerts.some((alert) => alert.level === "critical")
        ? "critical"
        : (alerts.some((alert) => alert.level === "warning") ? "warning" : "info");
    const title = alerts.map((alert) => alert.label).filter(Boolean).join(" · ");

    return `<span class="tr-crm-agenda-card__alert-dot tr-crm-agenda-card__alert-dot--${level}" title="${escapeHtml(title)}" aria-label="${escapeHtml(title)}">${alerts.length}</span>`;
}

function renderAgendaCompactReminderDot(booking, labels) {
    if (booking.reminder_sent) {
        return `<span class="tr-crm-agenda-card__reminder-dot tr-crm-agenda-card__reminder-dot--sent" title="${escapeHtml(labels.reminderSent)}"><i class="fa fa-bell" aria-hidden="true"></i></span>`;
    }

    if (booking.reminder_due) {
        return `<span class="tr-crm-agenda-card__reminder-dot tr-crm-agenda-card__reminder-dot--due" title="${escapeHtml(labels.sendReminder)}"><i class="fa fa-bell-o" aria-hidden="true"></i></span>`;
    }

    return "";
}

function formatAgendaShortTime(booking) {
    const time = formatAgendaTime(booking);
    const match = time.match(/^(\d{1,2}:\d{2}\s*(?:AM|PM)?)/i);

    return match ? match[1].trim() : time.split("–")[0].trim();
}

function renderAgendaBooking(booking, labels) {
    const status = booking.status || "pending";
    const price = booking.total_formatted || "";
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
            class="tr-crm-agenda-card tr-crm-agenda-card--compact"
            data-booking-id="${escapeHtml(booking.id)}"
            data-search="${escapeHtml(searchHaystack)}"
        >
            <div
                class="tr-crm-agenda-card__compact"
                data-agenda-open
                data-booking-id="${escapeHtml(booking.id)}"
                role="button"
                tabindex="0"
                aria-label="${escapeHtml((booking.customer_name || "—") + ", " + (booking.treatment_name || booking.product_name || "—"))}"
            >
                <div class="tr-crm-agenda-card__compact-row">
                    <div class="tr-crm-agenda-card__compact-time">
                        <span class="tr-crm-agenda-card__time-short">${escapeHtml(formatAgendaShortTime(booking))}</span>
                        <span class="tr-crm-agenda-card__duration-short">${escapeHtml(formatAgendaDuration(booking, labels))}</span>
                    </div>
                    <div class="tr-crm-agenda-card__compact-info">
                        <strong class="tr-crm-agenda-card__customer-compact">${escapeHtml(booking.customer_name || "—")}</strong>
                        <span class="tr-crm-agenda-card__treatment-compact">${escapeHtml(booking.treatment_name || booking.product_name || "—")}</span>
                    </div>
                    <div class="tr-crm-agenda-card__compact-badges">
                        ${renderAgendaStatusPill(status, labels)}
                        ${renderAgendaCompactAlertDot(booking)}
                    </div>
                </div>
                <div class="tr-crm-agenda-card__compact-footer">
                    <div class="tr-crm-agenda-card__compact-specialist">
                        ${renderAgendaAvatar(booking)}
                        <span class="tr-crm-agenda-card__specialist-name">${escapeHtml(booking.beautician_name || "—")}</span>
                    </div>
                    <div class="tr-crm-agenda-card__compact-meta">
                        ${renderAgendaCompactReminderDot(booking, labels)}
                        <span class="tr-crm-agenda-card__open-hint"><i class="fa fa-chevron-right" aria-hidden="true"></i></span>
                    </div>
                </div>
            </div>
        </li>
    `;
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
    initSpecialistToggles();
    initCustomerProfileDrawer();

    document.addEventListener("tr-crm-booking-updated", () => {
        app.refreshAgendaPanel?.();
    });
}
