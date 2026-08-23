import { formatAppointmentTimeDisplay } from "./time-format.js";
import axios from "axios";
import flatpickr from "flatpickr";
import {
    bookingAllowsDetail,
    closeCalendarEventPreview,
    getCalendarBooking,
    getCalendarBookingsForOrder,
    setCalendarBookings,
    upsertBooking,
} from "./kanban-helpers.js";
import { initCustomerProfileDrawer } from "./customer-profile.js";
import { openManualBookingEditor } from "./manual-booking.js";

function escapeHtml(value = "") {
    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

function normalizePhoneSearchDigits(value = "") {
    let digits = String(value).replace(/\D+/g, "");

    if (!digits) {
        return "";
    }

    if (digits.startsWith("00")) {
        digits = digits.slice(2);
    } else if (digits.startsWith("0")) {
        digits = `60${digits.slice(1)}`;
    }

    return digits;
}

function matchesCrmSearchQuery(haystack = "", query = "") {
    const trimmedQuery = query.trim();

    if (!trimmedQuery) {
        return true;
    }

    const haystackText = String(haystack || "");
    const haystackLower = haystackText.toLowerCase();
    const queryLower = trimmedQuery.toLowerCase();

    if (haystackLower.includes(queryLower)) {
        return true;
    }

    const queryDigits = normalizePhoneSearchDigits(trimmedQuery);

    if (queryDigits.length < 3) {
        return false;
    }

    const haystackDigits = normalizePhoneSearchDigits(haystackText);

    return haystackDigits.includes(queryDigits);
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
        holidayEyebrow: root?.dataset.agendaHolidayEyebrow || "Public holiday",
        holidayStates: root?.dataset.agendaHolidayStates || "Applies to",
        holidayType: root?.dataset.agendaHolidayType || "Type",
        holidaySubjectToChange: root?.dataset.agendaHolidaySubjectToChange || "Date may be subject to change",
        holidayNationwide: root?.dataset.agendaHolidayNationwide || "Nationwide",
        holidayKinds: {
            national: root?.dataset.agendaHolidayKindNational || "National holiday",
            labour: root?.dataset.agendaHolidayKindLabour || "Labour day",
            religious: root?.dataset.agendaHolidayKindReligious || "Religious holiday",
            festival: root?.dataset.agendaHolidayKindFestival || "Festival",
            other: root?.dataset.agendaHolidayKindOther || "Public holiday",
        },
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

function bookingSearchHaystack(booking = {}) {
    return [
        booking.customer_name,
        booking.customer_phone,
        booking.customer_email,
        booking.treatment_name,
        booking.product_name,
        booking.treatment_subtitle,
        booking.treatment_selection,
        booking.category_name,
        booking.beautician_name,
        booking.beautician_job_title,
        booking.source_label,
        booking.spa_branch_name,
        booking.appointment_date,
        booking.appointment_date_short,
        booking.appointment_time_range,
        booking.appointment_time,
        booking.time,
        booking.date,
        booking.payment_status_label,
        booking.total_formatted,
        booking.id,
    ].filter(Boolean).join(" ");
}

function initDashboardSearch(app = null) {
    const input = document.getElementById("tr-crm-search");
    const dashboard = document.getElementById("tr-crm-dashboard");

    if (!input || !dashboard) {
        return;
    }

    const itemSelectors = [
        "[data-crm-list] .tr-crm-appointment",
        "[data-crm-list] .tr-crm-alert",
        "[data-crm-list] .tr-crm-activity__item",
        "[data-crm-list] .tr-crm-audit__item",
        "[data-crm-list] .tr-crm-specialist",
        "[data-crm-list] .tr-crm-pipeline-card",
        "[data-crm-list] .tr-crm-ledger__row",
        "[data-crm-list] .tr-crm-agenda-card",
        "[data-crm-list] .tr-crm-tba-item",
        ".tr-crm-tba-item",
    ].join(", ");

    const emptySelectors = [
        "[data-crm-list] .tr-crm-empty",
        "[data-crm-list] .tr-crm-ledger__empty",
    ].join(", ");

    const noResultsMessage = dashboard.dataset.searchNoResults || "No matches for your search";
    const labels = getAgendaLabels();
    const agendaList = document.getElementById("tr-crm-agenda-list");
    const agendaEmpty = document.getElementById("tr-crm-agenda-empty");
    const agendaTitle = document.getElementById("tr-crm-agenda-title");
    const agendaHoliday = document.getElementById("tr-crm-agenda-holiday");
    const searchWrap = input.closest(".tr-crm-toolbar__search") || input.parentElement;

    let searchNotice = document.getElementById("tr-crm-search-empty");

    if (!searchNotice) {
        searchNotice = document.createElement("p");
        searchNotice.id = "tr-crm-search-empty";
        searchNotice.className = "tr-crm-search-empty";
        searchNotice.hidden = true;
        searchWrap?.insertAdjacentElement("afterend", searchNotice);
    }

    const clearCalendarSearchMarks = () => {
        dashboard.querySelectorAll(".tr-cal-day--search-hit, .tr-cal-day--search-dim").forEach((day) => {
            day.classList.remove("tr-cal-day--search-hit", "tr-cal-day--search-dim");
        });
    };

    const matchingCalendarBookings = (query) => {
        const bookings = Array.isArray(app?.lastCalendarBookings) ? app.lastCalendarBookings : [];

        return bookings.filter((booking) => matchesCrmSearchQuery(bookingSearchHaystack(booking), query));
    };

    const markCalendarSearchHits = (query, matches) => {
        clearCalendarSearchMarks();

        if (!query) {
            return;
        }

        const hitDates = new Set(matches.map((booking) => booking.date).filter(Boolean));

        dashboard.querySelectorAll(".tr-cal-day[data-date]").forEach((day) => {
            const dateStr = day.dataset.date || "";
            const isHit = hitDates.has(dateStr);

            day.classList.toggle("tr-cal-day--search-hit", isHit);
            day.classList.toggle("tr-cal-day--search-dim", !isHit && !day.classList.contains("tr-cal-day--muted"));
        });
    };

    const renderAgendaSearchResults = (query, matches, focusDate = "") => {
        if (!agendaList) {
            return matches.length;
        }

        const focused = focusDate
            ? matches.filter((booking) => booking.date === focusDate)
            : matches;
        const rows = focused.length ? focused : matches;

        if (agendaHoliday) {
            agendaHoliday.hidden = true;
            agendaHoliday.innerHTML = "";
        }

        if (agendaTitle) {
            agendaTitle.textContent = query
                ? (dashboard.dataset.searchResultsTitle || "Search results")
                : agendaTitle.textContent;
        }

        if (!rows.length) {
            agendaList.innerHTML = "";

            if (agendaEmpty) {
                agendaEmpty.hidden = false;
                agendaEmpty.textContent = noResultsMessage;
            }

            return 0;
        }

        const byDate = rows.reduce((map, booking) => {
            const key = booking.date || "";
            if (!map.has(key)) {
                map.set(key, []);
            }
            map.get(key).push(booking);
            return map;
        }, new Map());

        const html = [];

        [...byDate.entries()]
            .sort(([a], [b]) => String(a).localeCompare(String(b)))
            .forEach(([dateStr, dayBookings]) => {
                if (dateStr) {
                    const date = new Date(`${dateStr}T12:00:00`);
                    const dateLabel = Number.isNaN(date.getTime())
                        ? dateStr
                        : date.toLocaleDateString(labels.locale, {
                            weekday: "short",
                            day: "numeric",
                            month: "short",
                            year: "numeric",
                        });
                    html.push(`<li class="tr-crm-agenda-search-date" aria-hidden="true">${escapeHtml(dateLabel)}</li>`);
                }

                dayBookings
                    .sort((a, b) => String(a.time || a.appointment_time || "").localeCompare(String(b.time || b.appointment_time || "")))
                    .forEach((booking) => {
                        html.push(renderAgendaBooking(booking, labels));
                    });
            });

        agendaList.innerHTML = html.join("");

        if (agendaEmpty) {
            agendaEmpty.hidden = true;
            agendaEmpty.textContent = dashboard.dataset.agendaEmptyDefault
                || agendaEmpty.dataset.defaultText
                || agendaEmpty.textContent;
        }

        return rows.length;
    };

    const applySearch = (options = {}) => {
        const query = input.value.trim();
        const focusDate = options.focusDate || "";
        let visibleCount = 0;

        dashboard.classList.toggle("tr-crm-dashboard--searching", query !== "");

        dashboard.querySelectorAll(itemSelectors).forEach((row) => {
            const haystack = row.dataset.search || row.textContent || "";
            const matches = matchesCrmSearchQuery(haystack, query);

            row.classList.toggle("is-search-hidden", !matches);

            if (matches) {
                visibleCount += 1;
            }
        });

        dashboard.querySelectorAll(emptySelectors).forEach((row) => {
            row.classList.toggle("is-search-hidden", query !== "");
        });

        const calendarMatches = matchingCalendarBookings(query);
        markCalendarSearchHits(query, calendarMatches);

        if (query) {
            const agendaMatches = renderAgendaSearchResults(query, calendarMatches, focusDate);
            visibleCount += agendaMatches;

            if (agendaTitle) {
                const countLabel = dashboard.dataset.searchResultsCount || ":count found";
                agendaTitle.textContent = countLabel.replace(":count", String(calendarMatches.length));
            }
        } else {
            clearCalendarSearchMarks();
            app?.refreshAgendaPanel?.();
        }

        searchNotice.textContent = noResultsMessage;
        searchNotice.hidden = query === "" || visibleCount > 0;
    };

    input.addEventListener("input", () => applySearch());
    input.addEventListener("search", () => applySearch());
    input.addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
            event.preventDefault();
            applySearch();
        }
    });

    if (app) {
        app.applyCrmSearch = applySearch;
        app.isCrmSearching = () => input.value.trim() !== "";
    }
}

function presetDateForFilter(filter) {
    const today = new Date();

    if (filter === "tomorrow") {
        today.setDate(today.getDate() + 1);
    } else if (filter === "yesterday") {
        today.setDate(today.getDate() - 1);
    } else if (filter !== "today") {
        return "";
    }

    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, "0");
    const day = String(today.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
}

function setCrmDatePickerValue(input, dateStr = "") {
    if (!input) {
        return;
    }

    const picker = input._flatpickr;

    if (picker) {
        if (dateStr) {
            picker.setDate(dateStr, false);
        } else {
            picker.clear();
        }

        return;
    }

    input.value = dateStr;
}

function initCrmDatePicker() {
    const form = document.getElementById("tr-crm-header-form");
    const pickerInput = document.getElementById("tr-crm-date-picker");
    const dateFilterInput = document.getElementById("tr-crm-date-filter");
    const filterDateInput = document.getElementById("tr-crm-filter-date");

    if (!form || !pickerInput || pickerInput._flatpickr) {
        return;
    }

    const pickerWrap = pickerInput.closest(".tr-crm-toolbar__date-picker");

    flatpickr(pickerInput, {
        mode: "single",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "j M Y",
        altInputClass: "tr-crm-toolbar__date-input",
        disableMobile: true,
        animate: true,
        appendTo: document.body,
        defaultDate: pickerInput.value || null,
        onReady: (_selectedDates, _dateStr, instance) => {
            instance.calendarContainer.classList.add("tr-crm-toolbar-datepicker-calendar");
        },
        onOpen: (_selectedDates, _dateStr, instance) => {
            instance.config.positionElement = instance.altInput || instance.input;
        },
        onChange: (_selectedDates, dateStr) => {
            if (!dateStr) {
                return;
            }

            dateFilterInput.value = "custom";
            filterDateInput.value = dateStr;
            form.querySelectorAll("[data-date-filter]").forEach((pill) => pill.classList.remove("is-active"));
            pickerWrap?.classList.add("is-active");
            form.requestSubmit();
        },
    });
}

function initDateFilterPills() {
    const form = document.getElementById("tr-crm-header-form");
    const hiddenInput = document.getElementById("tr-crm-date-filter");
    const filterDateInput = document.getElementById("tr-crm-filter-date");
    const pickerInput = document.getElementById("tr-crm-date-picker");
    const pickerWrap = pickerInput?.closest(".tr-crm-toolbar__date-picker");

    if (!form || !hiddenInput) {
        return;
    }

    form.querySelectorAll("[data-date-filter]").forEach((button) => {
        button.addEventListener("click", () => {
            const filter = button.dataset.dateFilter || "today";

            hiddenInput.value = filter;
            if (filterDateInput) {
                filterDateInput.value = "";
            }

            form.querySelectorAll("[data-date-filter]").forEach((pill) => {
                pill.classList.toggle("is-active", pill === button);
            });

            pickerWrap?.classList.remove("is-active");

            if (filter === "all") {
                setCrmDatePickerValue(pickerInput, "");
            } else {
                setCrmDatePickerValue(pickerInput, presetDateForFilter(filter));
            }

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

    const portalBeauticianId = document.getElementById("tr-reservations-app")?.dataset.portalBeauticianId || "";
    const canOpen = bookingAllowsDetail(booking, portalBeauticianId || null);
    const compactClass = canOpen
        ? "tr-crm-agenda-card__compact tr-crm-agenda-card__compact--clickable"
        : "tr-crm-agenda-card__compact tr-crm-agenda-card__compact--readonly";
    const compactAttrs = canOpen
        ? `data-agenda-open data-booking-id="${escapeHtml(booking.id)}" role="button" tabindex="0" aria-label="${escapeHtml((booking.customer_name || "—") + ", " + (booking.treatment_name || booking.product_name || "—"))}"`
        : `data-booking-id="${escapeHtml(booking.id)}"`;

    return `
        <li
            class="tr-crm-agenda-card tr-crm-agenda-card--compact${canOpen ? "" : " tr-crm-agenda-card--others"}"
            data-booking-id="${escapeHtml(booking.id)}"
            data-search="${escapeHtml(searchHaystack)}"
        >
            <div
                class="${compactClass}"
                ${compactAttrs}
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


function holidayItemsForDate(app, dateStr) {
    const holiday = app?.holidaysByDate?.[dateStr];

    if (!holiday) {
        return [];
    }

    if (Array.isArray(holiday.items) && holiday.items.length) {
        return holiday.items;
    }

    return [holiday];
}

function renderAgendaHolidayCard(holiday, labels) {
    const kind = String(holiday.kind || "other");
    const kindLabel = labels.holidayKinds?.[kind] || labels.holidayKinds?.other || labels.holidayEyebrow;
    const states = Array.isArray(holiday.states) ? holiday.states.filter(Boolean) : [];
    const statesText = states.length ? states.join(", ") : labels.holidayNationwide;
    const color = holiday.color || "#2563eb";
    const subjectNote = holiday.is_subject_to_change
        ? `<p class="tr-crm-agenda-holiday-card__note">${escapeHtml(labels.holidaySubjectToChange)}</p>`
        : "";

    return `
        <article class="tr-crm-agenda-holiday-card" style="--holiday-color:${escapeHtml(color)}">
            <p class="tr-crm-agenda-holiday-card__eyebrow">${escapeHtml(labels.holidayEyebrow)}</p>
            <h5 class="tr-crm-agenda-holiday-card__title">${escapeHtml(holiday.label || "—")}</h5>
            <dl class="tr-crm-agenda-holiday-card__meta">
                <div>
                    <dt>${escapeHtml(labels.holidayType)}</dt>
                    <dd>${escapeHtml(kindLabel)}</dd>
                </div>
                <div>
                    <dt>${escapeHtml(labels.holidayStates)}</dt>
                    <dd>${escapeHtml(statesText)}</dd>
                </div>
            </dl>
            ${subjectNote}
        </article>
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

    const holidayMount = document.getElementById("tr-crm-agenda-holiday");

    const updateAgenda = (dateStr, bookings = null) => {
        if (!dateStr) {
            return;
        }

        selectedDate = dateStr;
        const dayBookings = bookings ?? bookingsForDate(dateStr);
        const dayHolidays = holidayItemsForDate(app, dateStr);
        const date = new Date(`${dateStr}T12:00:00`);

        if (title) {
            title.textContent = date.toLocaleDateString(labels.locale, {
                weekday: "short",
                day: "numeric",
                month: "short",
                year: "numeric",
            });
        }

        if (holidayMount) {
            if (dayHolidays.length) {
                holidayMount.hidden = false;
                holidayMount.innerHTML = dayHolidays
                    .map((holiday) => renderAgendaHolidayCard(holiday, labels))
                    .join("");
            } else {
                holidayMount.hidden = true;
                holidayMount.innerHTML = "";
            }
        }

        if (list) {
            list.innerHTML = dayBookings
                .sort((a, b) => String(a.time || a.appointment_time || "").localeCompare(String(b.time || b.appointment_time || "")))
                .map((booking) => renderAgendaBooking(booking, labels))
                .join("");
        }

        if (empty) {
            if (empty.dataset.defaultText) {
                empty.textContent = empty.dataset.defaultText;
            }
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

        const dateStr = day.dataset.date || "";
        const searchInput = document.getElementById("tr-crm-search");
        const query = searchInput?.value.trim() || "";

        if (query && typeof app.applyCrmSearch === "function") {
            selectedDate = dateStr;
            app.grid.querySelectorAll(".tr-cal-day[data-date]").forEach((node) => {
                node.classList.toggle("tr-cal-day--selected", node.dataset.date === dateStr);
                node.setAttribute("aria-selected", node.dataset.date === dateStr ? "true" : "false");
            });
            app.applyCrmSearch({ focusDate: dateStr });
            return;
        }

        updateAgenda(dateStr);
    };

    const calendarInteractionRoot = app.gridViewport || app.grid;

    calendarInteractionRoot?.addEventListener("click", (event) => {
        const day = event.target.closest(".tr-cal-day[data-date]");

        if (!day || !app.grid?.contains(day)) {
            return;
        }

        openAgendaForDay(day);
    });

    calendarInteractionRoot?.addEventListener("keydown", (event) => {
        if (event.key !== "Enter" && event.key !== " ") {
            return;
        }

        const day = event.target.closest(".tr-cal-day[data-date]");

        if (!day || !app.grid?.contains(day)) {
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

function formatPipelineElapsed(seconds) {
    const total = Math.max(0, seconds);
    const hours = Math.floor(total / 3600);
    const minutes = Math.floor((total % 3600) / 60);
    const secs = total % 60;

    if (hours > 0) {
        return `${hours}:${String(minutes).padStart(2, "0")}:${String(secs).padStart(2, "0")}`;
    }

    return `${String(minutes).padStart(2, "0")}:${String(secs).padStart(2, "0")}`;
}

function tickPipelineTimers() {
    const now = Date.now();

    document.querySelectorAll("[data-pipeline-timer]").forEach((timer) => {
        const startedAt = Date.parse(timer.dataset.startedAt || "");

        if (!startedAt) {
            return;
        }

        const elapsedSeconds = Math.floor((now - startedAt) / 1000);
        const durationSeconds = Math.max(60, Number(timer.dataset.durationMinutes || 60) * 60);
        const valueEl = timer.querySelector(".tr-crm-pipeline-card__timer-value");

        if (valueEl) {
            valueEl.textContent = formatPipelineElapsed(elapsedSeconds);
        }

        timer.classList.toggle("is-overtime", elapsedSeconds > durationSeconds);
    });
}

function initPipelineTimers() {
    tickPipelineTimers();

    if (window._trPipelineTimerInterval) {
        return;
    }

    window._trPipelineTimerInterval = window.setInterval(tickPipelineTimers, 1000);
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
            filter: ".tr-crm-pipeline-card--readonly, .tr-crm-pipeline-card__cta, .tr-crm-pipeline-card__footer, .tr-crm-pipeline-card__links, .tr-crm-pipeline-card__link, a, button",
            preventOnFilter: true,
            onMove: (evt) => evt.dragged?.dataset?.ownBooking !== "0",
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


let rescheduleWorkspaceState = null;

function getRescheduleWorkspace() {
    let workspace = document.getElementById("tr-reschedule-workspace");

    if (workspace) {
        return workspace;
    }

    workspace = document.createElement("div");
    workspace.id = "tr-reschedule-workspace";
    workspace.className = "tr-reschedule-workspace";
    workspace.hidden = true;
    workspace.setAttribute("aria-hidden", "true");
    workspace.innerHTML = `
        <button class="tr-reschedule-workspace__backdrop" type="button" data-reschedule-close tabindex="-1" aria-label="Close"></button>
        <section class="tr-reschedule-workspace__panel" role="dialog" aria-modal="true" aria-labelledby="tr-reschedule-workspace-title">
            <header class="tr-reschedule-workspace__head">
                <div>
                    <span class="tr-reschedule-workspace__eyebrow" data-reschedule-eyebrow></span>
                    <h2 id="tr-reschedule-workspace-title" data-reschedule-title></h2>
                    <p data-reschedule-subtitle></p>
                </div>
                <button class="tr-reschedule-workspace__close" type="button" data-reschedule-close aria-label="Close">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>
            </header>
            <div class="tr-reschedule-workspace__body">
                <aside class="tr-reschedule-workspace__treatments" aria-labelledby="tr-reschedule-treatments-title">
                    <div class="tr-reschedule-workspace__section-head">
                        <h3 id="tr-reschedule-treatments-title" data-reschedule-list-title></h3>
                        <span class="tr-reschedule-workspace__count" data-reschedule-count></span>
                    </div>
                    <p class="tr-reschedule-workspace__help" data-reschedule-list-help></p>
                    <div data-reschedule-order-summary></div>
                    <div class="tr-reschedule-workspace__treatment-list" data-reschedule-treatment-list></div>
                </aside>
                <main class="tr-reschedule-workspace__editor">
                    <div class="tr-reschedule-workspace__selection" data-reschedule-selection></div>
                    <div class="tr-reschedule-workspace__field">
                        <span class="tr-reschedule-workspace__field-label" id="tr-reschedule-date-label" data-reschedule-date-label></span>
                        <div class="tr-reschedule-calendar" data-reschedule-calendar aria-labelledby="tr-reschedule-date-label">
                            <div class="tr-reschedule-calendar__head">
                                <button type="button" data-reschedule-calendar-shift="-1" data-reschedule-calendar-prev>
                                    <i class="fa fa-chevron-left" aria-hidden="true"></i>
                                </button>
                                <strong data-reschedule-calendar-title></strong>
                                <button type="button" data-reschedule-calendar-shift="1" data-reschedule-calendar-next>
                                    <i class="fa fa-chevron-right" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="tr-reschedule-calendar__weekdays" data-reschedule-calendar-weekdays aria-hidden="true"></div>
                            <div class="tr-reschedule-calendar__grid" data-reschedule-calendar-grid role="grid"></div>
                            <div class="tr-reschedule-calendar__status" data-reschedule-calendar-status aria-live="polite"></div>
                        </div>
                        <input id="tr-reschedule-date" type="hidden" data-reschedule-date>
                        <small data-reschedule-date-help></small>
                    </div>
                    <fieldset class="tr-reschedule-workspace__slots">
                        <legend data-reschedule-slots-label></legend>
                        <div class="tr-reschedule-workspace__slot-status" data-reschedule-slot-status aria-live="polite"></div>
                        <div class="tr-reschedule-workspace__slot-grid" data-reschedule-slot-grid></div>
                    </fieldset>
                    <div class="tr-reschedule-workspace__notifications">
                        <label><input type="checkbox" data-reschedule-notify-customer checked> <span data-reschedule-notify-customer-label></span></label>
                        <label data-reschedule-beautician-option><input type="checkbox" data-reschedule-notify-beautician checked> <span data-reschedule-notify-beautician-label></span></label>
                        <small data-reschedule-notifications-help></small>
                    </div>
                    <div class="tr-reschedule-workspace__error" data-reschedule-error role="alert" hidden></div>
                </main>
            </div>
            <footer class="tr-reschedule-workspace__footer">
                <button class="btn btn-default" type="button" data-reschedule-close data-reschedule-cancel></button>
                <button class="btn btn-primary" type="button" data-reschedule-save disabled>
                    <i class="fa fa-check" aria-hidden="true"></i>
                    <span data-reschedule-save-label></span>
                </button>
            </footer>
        </section>`;
    document.body.appendChild(workspace);

    workspace.addEventListener("click", (event) => {
        if (event.target.closest("[data-reschedule-close]")) {
            closeRescheduleWorkspace();
            return;
        }

        const treatment = event.target.closest("[data-reschedule-treatment]");
        if (treatment && !treatment.disabled) {
            selectRescheduleTreatment(treatment.dataset.rescheduleTreatment);
            return;
        }

        const slot = event.target.closest("[data-reschedule-slot]");
        if (slot && !slot.disabled) {
            rescheduleWorkspaceState.selectedSlot = slot.dataset.rescheduleSlot;
            workspace.querySelectorAll("[data-reschedule-slot]").forEach((button) => {
                button.classList.toggle("is-selected", button === slot);
                button.setAttribute("aria-pressed", button === slot ? "true" : "false");
            });
            updateRescheduleSaveState();
            return;
        }

        const calendarShift = event.target.closest("[data-reschedule-calendar-shift]");
        if (calendarShift && !calendarShift.disabled) {
            changeRescheduleCalendarMonth(Number(calendarShift.dataset.rescheduleCalendarShift || 0));
            return;
        }

        const calendarDate = event.target.closest("[data-reschedule-calendar-date]");
        if (calendarDate && !calendarDate.disabled) {
            selectRescheduleDate(calendarDate.dataset.rescheduleCalendarDate);
            return;
        }

        if (event.target.closest("[data-reschedule-save]")) {
            saveRescheduledAppointment();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !workspace.hidden) {
            closeRescheduleWorkspace();
        }
    });

    return workspace;
}

function closeRescheduleWorkspace() {
    const workspace = document.getElementById("tr-reschedule-workspace");
    const returnFocus = rescheduleWorkspaceState?.trigger;

    if (!workspace) {
        return;
    }

    workspace.hidden = true;
    workspace.setAttribute("aria-hidden", "true");
    document.body.classList.remove("tr-reschedule-workspace-open");
    rescheduleWorkspaceState = null;
    returnFocus?.focus?.();
}

function rescheduleTreatmentCard(booking, index, total, selectedId, labels) {
    const selected = String(booking.id) === String(selectedId);
    const disabled = booking.can_reschedule === false;
    const schedule = [booking.appointment_date, booking.appointment_time].filter(Boolean).join(" · ") || labels.notScheduled;
    const status = labels.statuses?.[booking.status] || booking.status || "";

    return `
        <button type="button" class="tr-reschedule-treatment${selected ? " is-selected" : ""}" data-reschedule-treatment="${escapeHtml(booking.id)}" aria-pressed="${selected ? "true" : "false"}" ${disabled ? "disabled" : ""}>
            <span class="tr-reschedule-treatment__topline">
                <span>${escapeHtml((labels.treatmentSequence || "Treatment :current of :total").replace(":current", index + 1).replace(":total", total))}</span>
                <span class="tr-reschedule-treatment__status tr-reschedule-treatment__status--${escapeHtml(booking.status || "pending")}">${escapeHtml(status)}</span>
            </span>
            <strong>${escapeHtml(booking.treatment_name || booking.product_name || labels.treatmentFallback)}</strong>
            ${booking.treatment_selection ? `<span class="tr-reschedule-treatment__selection">${escapeHtml(booking.treatment_selection)}</span>` : ""}
            <span class="tr-reschedule-treatment__meta"><i class="fa fa-calendar" aria-hidden="true"></i>${escapeHtml(schedule)}</span>
            <span class="tr-reschedule-treatment__meta"><i class="fa fa-user" aria-hidden="true"></i>${escapeHtml(booking.beautician_name || "—")}</span>
        </button>`;
}

function rescheduleOrderSummaryHtml(bookings, labels) {
    const booking = bookings.find((item) => item.order_id) || bookings[0] || {};
    const orderNumber = booking.order_id ? `#${booking.order_id}` : labels.notAvailable;
    const rows = [
        [labels.orderNumber, orderNumber],
        [labels.customer, booking.customer_name || labels.notAvailable],
        [labels.contact, booking.customer_phone || labels.notAvailable],
        [labels.branch, booking.spa_branch_name || labels.notAvailable],
        [labels.payment, booking.payment_status_label || labels.notAvailable],
        [labels.paymentMethod, booking.payment_method_label || labels.notAvailable],
        [labels.orderTotal, booking.order_total_formatted || labels.notAvailable],
    ];

    return `
        <section class="tr-reschedule-order-summary" aria-labelledby="tr-reschedule-order-summary-title">
            <div class="tr-reschedule-order-summary__head">
                <span aria-hidden="true"><i class="fa fa-file-text-o"></i></span>
                <h4 id="tr-reschedule-order-summary-title">${escapeHtml(labels.bookingInformation)}</h4>
            </div>
            <dl>
                ${rows.map(([label, value]) => `
                    <div>
                        <dt>${escapeHtml(label)}</dt>
                        <dd>${escapeHtml(value)}</dd>
                    </div>`).join("")}
            </dl>
        </section>`;
}

function selectedTreatmentHtml(booking, labels) {
    const treatmentName = booking.treatment_name || booking.product_name || labels.treatmentFallback;
    const thumbnail = booking.product_image
        ? `<img class="tr-reschedule-workspace__selection-thumb" src="${escapeHtml(booking.product_image)}" alt="${escapeHtml(treatmentName)}" loading="lazy" decoding="async">`
        : `<span class="tr-reschedule-workspace__selection-thumb tr-reschedule-workspace__selection-thumb--fallback" aria-hidden="true"><i class="fa fa-image"></i></span>`;

    return `
        ${thumbnail}
        <div class="tr-reschedule-workspace__selection-copy">
            <span>${escapeHtml(labels.selectedTreatment)}</span>
            <strong>${escapeHtml(treatmentName)}</strong>
            ${booking.treatment_selection ? `<small>${escapeHtml(booking.treatment_selection)}</small>` : ""}
            <div><i class="fa fa-user" aria-hidden="true"></i> ${escapeHtml(booking.beautician_name || "—")}</div>
        </div>`;
}

function localDateKey(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
}

function parseLocalDate(value) {
    const match = String(value || "").match(/^(\d{4})-(\d{2})-(\d{2})$/);

    return match
        ? new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]))
        : null;
}

function currentOrFutureDate(value) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const candidate = parseLocalDate(value);

    return candidate && candidate >= today ? candidate : today;
}

function calendarLocale() {
    return document.documentElement.lang || undefined;
}

function renderRescheduleCalendar() {
    const state = rescheduleWorkspaceState;
    if (!state?.calendarMonth) {
        return;
    }

    const workspace = getRescheduleWorkspace();
    const grid = workspace.querySelector("[data-reschedule-calendar-grid]");
    const title = workspace.querySelector("[data-reschedule-calendar-title]");
    const weekdays = workspace.querySelector("[data-reschedule-calendar-weekdays]");
    const previous = workspace.querySelector("[data-reschedule-calendar-prev]");
    const first = new Date(state.calendarMonth.getFullYear(), state.calendarMonth.getMonth(), 1);
    const startOffset = (first.getDay() + 6) % 7;
    const gridStart = new Date(first);
    gridStart.setDate(first.getDate() - startOffset);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const currentMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const formatter = new Intl.DateTimeFormat(calendarLocale(), { month: "long", year: "numeric" });
    const dayFormatter = new Intl.DateTimeFormat(calendarLocale(), { weekday: "short" });
    const fullDateFormatter = new Intl.DateTimeFormat(calendarLocale(), {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    });

    title.textContent = formatter.format(first);
    weekdays.innerHTML = Array.from({ length: 7 }, (_, index) => {
        const weekday = new Date(2024, 0, 1 + index);
        return `<span>${escapeHtml(dayFormatter.format(weekday).replace(/\.$/, ""))}</span>`;
    }).join("");
    previous.disabled = first <= currentMonth;

    grid.innerHTML = Array.from({ length: 42 }, (_, index) => {
        const date = new Date(gridStart);
        date.setDate(gridStart.getDate() + index);
        const dateKey = localDateKey(date);
        const inMonth = date.getMonth() === first.getMonth();
        const isPast = date < today;
        const isAvailable = inMonth && !isPast && state.availableDates.has(dateKey);
        const selected = dateKey === state.selectedDate;
        const isToday = dateKey === localDateKey(today);
        const classes = [
            "tr-reschedule-calendar__day",
            !inMonth ? "is-outside" : "",
            isAvailable ? "is-available" : "is-unavailable",
            selected ? "is-selected" : "",
            isToday ? "is-today" : "",
        ].filter(Boolean).join(" ");

        return `<button type="button" class="${classes}" data-reschedule-calendar-date="${dateKey}"
            aria-label="${escapeHtml(fullDateFormatter.format(date))}"
            aria-pressed="${selected ? "true" : "false"}"
            ${isAvailable ? "" : "disabled"}>
            <span>${date.getDate()}</span>
            ${isAvailable ? `<i aria-hidden="true"></i>` : ""}
        </button>`;
    }).join("");
}

function selectRescheduleDate(date) {
    const state = rescheduleWorkspaceState;

    if (!state?.availableDates.has(date)) {
        return;
    }

    state.selectedDate = date;
    state.preferredDate = date;
    state.selectedSlot = "";
    getRescheduleWorkspace().querySelector("[data-reschedule-date]").value = date;
    renderRescheduleCalendar();
    loadRescheduleSlots();
}

function changeRescheduleCalendarMonth(offset) {
    const state = rescheduleWorkspaceState;
    if (!state?.calendarMonth || !offset) {
        return;
    }

    state.calendarMonth = new Date(
        state.calendarMonth.getFullYear(),
        state.calendarMonth.getMonth() + offset,
        1,
    );
    state.selectedDate = "";
    state.selectedSlot = "";
    getRescheduleWorkspace().querySelector("[data-reschedule-date]").value = "";
    loadRescheduleDates();
}

async function loadRescheduleDates() {
    const state = rescheduleWorkspaceState;
    if (!state?.booking || !state.calendarMonth) {
        return;
    }

    const workspace = getRescheduleWorkspace();
    const calendar = workspace.querySelector("[data-reschedule-calendar]");
    const status = workspace.querySelector("[data-reschedule-calendar-status]");
    const first = new Date(state.calendarMonth.getFullYear(), state.calendarMonth.getMonth(), 1);
    const last = new Date(state.calendarMonth.getFullYear(), state.calendarMonth.getMonth() + 1, 0);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const fromDate = first < today ? today : first;
    const from = localDateKey(fromDate);
    const to = localDateKey(last);
    const requestToken = `${state.booking.id}:${from}:${to}:${Date.now()}`;
    state.datesRequestToken = requestToken;
    state.availableDates = new Set();
    state.selectedSlot = "";
    calendar.setAttribute("aria-busy", "true");
    status.innerHTML = `<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> ${escapeHtml(state.labels.loadingDates)}`;
    renderRescheduleCalendar();
    updateRescheduleSaveState();

    if (!state.datesUrl && !state.reschedule) {
        for (let cursor = new Date(fromDate); cursor <= last; cursor.setDate(cursor.getDate() + 1)) {
            state.availableDates.add(localDateKey(cursor));
        }
        calendar.setAttribute("aria-busy", "false");
        status.textContent = state.labels.availableDatesHint;
        renderRescheduleCalendar();
        return;
    }

    if (!state.datesUrl) {
        state.selectedDate = "";
        workspace.querySelector("[data-reschedule-date]").value = "";
        workspace.querySelector("[data-reschedule-slot-grid]").innerHTML = "";
        workspace.querySelector("[data-reschedule-slot-status]").textContent = state.labels.chooseDate;
        calendar.setAttribute("aria-busy", "false");
        status.textContent = state.labels.noAvailableDates;
        showRescheduleError(state.labels.datesLoadFailed);
        renderRescheduleCalendar();
        updateRescheduleSaveState();
        return;
    }

    try {
        const url = state.datesUrl.replace("__ID__", String(state.booking.id));
        const { data } = await axios.get(url, {
            params: { from, to, _: Date.now() },
        });

        if (rescheduleWorkspaceState?.datesRequestToken !== requestToken) {
            return;
        }

        state.availableDates = new Set(data.dates || []);
        calendar.setAttribute("aria-busy", "false");
        status.textContent = state.availableDates.size
            ? state.labels.availableDatesHint
            : state.labels.noAvailableDates;
        renderRescheduleCalendar();

        const preferred = state.preferredDate;
        const preferredDate = parseLocalDate(preferred);
        if (preferredDate
            && preferredDate.getFullYear() === first.getFullYear()
            && preferredDate.getMonth() === first.getMonth()
            && state.availableDates.has(preferred)) {
            selectRescheduleDate(preferred);
        } else {
            state.selectedDate = "";
            workspace.querySelector("[data-reschedule-date]").value = "";
            workspace.querySelector("[data-reschedule-slot-grid]").innerHTML = "";
            workspace.querySelector("[data-reschedule-slot-status]").textContent = state.labels.chooseDate;
            updateRescheduleSaveState();
        }
    } catch (error) {
        if (rescheduleWorkspaceState?.datesRequestToken !== requestToken) {
            return;
        }
        console.error("Unable to load reschedule dates", error);
        calendar.setAttribute("aria-busy", "false");
        status.textContent = "";
        showRescheduleError(error?.response?.data?.message || state.labels.datesLoadFailed);
        renderRescheduleCalendar();
    }
}

function updateRescheduleSaveState() {
    const workspace = getRescheduleWorkspace();
    const save = workspace.querySelector("[data-reschedule-save]");
    save.disabled = !rescheduleWorkspaceState?.selectedDate || !rescheduleWorkspaceState?.selectedSlot || rescheduleWorkspaceState?.saving;
}

function showRescheduleError(message = "") {
    const error = getRescheduleWorkspace().querySelector("[data-reschedule-error]");
    error.textContent = message;
    error.hidden = !message;
}

async function loadRescheduleSlots() {
    const state = rescheduleWorkspaceState;
    if (!state?.selectedDate || !state.booking) {
        return;
    }

    const workspace = getRescheduleWorkspace();
    const status = workspace.querySelector("[data-reschedule-slot-status]");
    const grid = workspace.querySelector("[data-reschedule-slot-grid]");
    const requestToken = `${state.booking.id}:${state.selectedDate}:${Date.now()}`;
    state.requestToken = requestToken;
    showRescheduleError();
    grid.innerHTML = "";
    status.innerHTML = `<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> ${escapeHtml(state.labels.loadingSlots)}`;
    updateRescheduleSaveState();

    try {
        const resolvedSlotsUrl = state.slotsUrl.replace("__ID__", String(state.booking.id));
        const { data } = await axios.get(resolvedSlotsUrl, {
            params: {
                beautician_id: state.booking.beautician_id,
                date: state.selectedDate,
                booking_id: state.booking.id,
                product_id: state.booking.product_id || undefined,
                spa_branch_id: state.booking.spa_branch_id || undefined,
                _: Date.now(),
            },
        });

        if (rescheduleWorkspaceState?.requestToken !== requestToken) {
            return;
        }

        const slots = data.slots || [];
        status.textContent = slots.length ? state.labels.chooseSlot : state.labels.noSlots;
        grid.innerHTML = slots.map((slot) => `
            <button type="button" data-reschedule-slot="${escapeHtml(slot)}" aria-pressed="false">
                ${escapeHtml(formatAppointmentTimeDisplay(slot))}
            </button>`).join("");
    } catch (error) {
        if (rescheduleWorkspaceState?.requestToken !== requestToken) {
            return;
        }
        status.textContent = "";
        showRescheduleError(error?.response?.data?.message || state.labels.loadFailed);
    }
}

function selectRescheduleTreatment(bookingId) {
    const state = rescheduleWorkspaceState;
    const booking = state?.bookings.find((item) => String(item.id) === String(bookingId));

    if (!booking) {
        return;
    }

    state.booking = booking;
    state.preferredDate = booking.appointment_date_value || booking.date || "";
    state.selectedDate = "";
    state.selectedSlot = "";
    const preferredDate = currentOrFutureDate(state.preferredDate);
    state.calendarMonth = new Date(preferredDate.getFullYear(), preferredDate.getMonth(), 1);
    const workspace = getRescheduleWorkspace();
    workspace.querySelector("[data-reschedule-treatment-list]").innerHTML = state.bookings
        .map((item, index) => rescheduleTreatmentCard(item, index, state.bookings.length, booking.id, state.labels))
        .join("");
    workspace.querySelector("[data-reschedule-selection]").innerHTML = selectedTreatmentHtml(booking, state.labels);
    workspace.querySelector("[data-reschedule-date]").value = "";
    workspace.querySelector("[data-reschedule-beautician-option]").hidden = !state.reschedule;
    workspace.querySelector("[data-reschedule-slot-grid]").innerHTML = "";
    workspace.querySelector("[data-reschedule-slot-status]").textContent = state.labels.chooseDate;
    loadRescheduleDates();
}

async function saveRescheduledAppointment() {
    const state = rescheduleWorkspaceState;
    if (!state?.booking || !state.selectedDate || !state.selectedSlot || state.saving) {
        return;
    }

    const workspace = getRescheduleWorkspace();
    const save = workspace.querySelector("[data-reschedule-save]");
    const label = save.querySelector("[data-reschedule-save-label]");
    state.saving = true;
    label.textContent = state.labels.saving;
    save.querySelector("i").className = "fa fa-spinner fa-spin";
    updateRescheduleSaveState();
    showRescheduleError();

    try {
        const payload = {
            beautician_id: state.booking.beautician_id,
            appointment_date: state.selectedDate,
            appointment_time: state.selectedSlot,
            notify_customer: workspace.querySelector("[data-reschedule-notify-customer]").checked,
            notify_beautician: state.reschedule && workspace.querySelector("[data-reschedule-notify-beautician]").checked,
        };

        if (state.booking.spa_branch_id) {
            payload.spa_branch_id = state.booking.spa_branch_id;
        }

        const url = state.urlTemplate.replace("__ID__", String(state.booking.id));
        const { data } = await axios.patch(url, payload);
        window.notify?.success?.(data.message || state.labels.saved);
        window.location.reload();
    } catch (error) {
        state.saving = false;
        label.textContent = state.labels.save;
        save.querySelector("i").className = "fa fa-check";
        updateRescheduleSaveState();
        showRescheduleError(error?.response?.data?.message || state.labels.saveFailed);
    }
}

function openAppointmentSchedulingWorkspace({
    bookingId,
    beauticianId,
    productId,
    spaBranchId,
    urlTemplate,
    slotsUrl,
    datesUrl = "",
    appointmentDate = null,
    reschedule = false,
    labels = {},
    trigger = null,
}) {
    const current = getCalendarBooking(bookingId) || {
        id: bookingId,
        beautician_id: beauticianId,
        product_id: productId,
        spa_branch_id: spaBranchId,
        can_reschedule: true,
    };
    const siblings = current.order_id ? getCalendarBookingsForOrder(current.order_id) : [];
    const bookings = siblings.some((item) => String(item.id) === String(current.id)) ? siblings : [current, ...siblings];
    const workspace = getRescheduleWorkspace();
    const preferredDateValue = appointmentDate || current.appointment_date_value || current.date || "";
    const preferredDate = currentOrFutureDate(preferredDateValue);

    closeCalendarEventPreview();
    rescheduleWorkspaceState = {
        booking: current,
        bookings,
        urlTemplate,
        slotsUrl,
        datesUrl,
        preferredDate: preferredDateValue,
        selectedDate: "",
        selectedSlot: "",
        calendarMonth: new Date(preferredDate.getFullYear(), preferredDate.getMonth(), 1),
        availableDates: new Set(),
        reschedule,
        labels,
        trigger,
        saving: false,
    };

    workspace.querySelector("[data-reschedule-eyebrow]").textContent = current.order_id
        ? labels.orderEyebrow.replace(":order", current.order_id)
        : labels.scheduleEyebrow;
    workspace.querySelector("[data-reschedule-title]").textContent = reschedule ? labels.title : labels.scheduleTitle;
    workspace.querySelector("[data-reschedule-subtitle]").textContent = labels.subtitle;
    workspace.querySelector("[data-reschedule-list-title]").textContent = labels.orderTreatments;
    workspace.querySelector("[data-reschedule-list-help]").textContent = labels.orderTreatmentsHelp;
    workspace.querySelector("[data-reschedule-count]").textContent = labels.treatmentCount.replace(":count", bookings.length);
    workspace.querySelector("[data-reschedule-date-label]").textContent = labels.newDate;
    workspace.querySelector("[data-reschedule-date-help]").textContent = labels.newDateHelp;
    workspace.querySelector("[data-reschedule-calendar-prev]").setAttribute("aria-label", labels.previousMonth);
    workspace.querySelector("[data-reschedule-calendar-next]").setAttribute("aria-label", labels.nextMonth);
    workspace.querySelector("[data-reschedule-slots-label]").textContent = labels.availableSlots;
    workspace.querySelector("[data-reschedule-notify-customer-label]").textContent = labels.notifyCustomer;
    workspace.querySelector("[data-reschedule-notify-beautician-label]").textContent = labels.notifyBeautician;
    workspace.querySelector("[data-reschedule-notifications-help]").textContent = labels.notificationsHelp;
    workspace.querySelector("[data-reschedule-cancel]").textContent = labels.cancel;
    workspace.querySelector("[data-reschedule-save-label]").textContent = labels.save;
    workspace.querySelector("[data-reschedule-date]").value = "";
    workspace.querySelector("[data-reschedule-order-summary]").innerHTML = rescheduleOrderSummaryHtml(bookings, labels);
    workspace.querySelector("[data-reschedule-treatment-list]").innerHTML = bookings
        .map((booking, index) => rescheduleTreatmentCard(booking, index, bookings.length, current.id, labels))
        .join("");
    workspace.querySelector("[data-reschedule-selection]").innerHTML = selectedTreatmentHtml(current, labels);
    workspace.querySelector("[data-reschedule-beautician-option]").hidden = !reschedule;
    workspace.querySelector("[data-reschedule-slot-grid]").innerHTML = "";
    workspace.querySelector("[data-reschedule-slot-status]").textContent = labels.chooseDate;
    showRescheduleError();
    updateRescheduleSaveState();
    workspace.hidden = false;
    workspace.setAttribute("aria-hidden", "false");
    document.body.classList.add("tr-reschedule-workspace-open");
    workspace.querySelector("[data-reschedule-close]")?.focus();

    loadRescheduleDates();
}

function schedulingLabels(root) {
    let translated = {};

    try {
        translated = JSON.parse(root?.dataset?.rescheduleLabels || "{}");
    } catch (error) {
        translated = {};
    }

    return {
        ...translated,
        datePrompt: root?.dataset?.rescheduleDatePrompt || "Appointment date (YYYY-MM-DD)",
        timesPrompt: root?.dataset?.rescheduleTimesPrompt || "Available times:\n__TIMES__\n\nEnter time",
        loadFailed: root?.dataset?.rescheduleLoadFailed || "Failed to load slots",
        saveFailed: root?.dataset?.rescheduleSaveFailed || "Failed to reschedule",
        noSlots: root?.dataset?.rescheduleNoSlots || "No available times on this date.",
        orderEyebrow: translated.order_eyebrow || "Order #:order",
        scheduleEyebrow: translated.schedule_eyebrow || "Appointment scheduling",
        title: translated.workspace_title || "Reschedule appointments",
        scheduleTitle: translated.schedule_title || "Schedule appointment",
        subtitle: translated.workspace_subtitle || "Choose a treatment, date, and available time before saving.",
        orderTreatments: translated.order_treatments || "Treatments in this order",
        orderTreatmentsHelp: translated.order_treatments_help || "Select one treatment to reschedule. Other treatments remain unchanged.",
        bookingInformation: translated.booking_information || "Booking information",
        orderNumber: translated.order_number || "Order",
        customer: translated.customer || "Customer",
        contact: translated.contact || "Contact",
        branch: translated.branch || "Branch",
        payment: translated.payment || "Payment",
        paymentMethod: translated.payment_method || "Payment method",
        orderTotal: translated.order_total || "Order total",
        notAvailable: translated.not_available || "—",
        treatmentCount: translated.treatment_count || ":count treatments",
        treatmentSequence: translated.treatment_sequence || "Treatment :current of :total",
        selectedTreatment: translated.selected_treatment || "Selected treatment",
        treatmentFallback: translated.treatment_fallback || "Treatment",
        notScheduled: translated.not_scheduled || "Not scheduled",
        newDate: translated.new_date || "New appointment date",
        newDateHelp: translated.new_date_help || "Available times are checked against this beautician, branch, and treatment.",
        availableSlots: translated.available_slots || "Available times",
        chooseDate: translated.choose_date || "Choose a date to see available times.",
        chooseSlot: translated.choose_slot || "Choose one available time.",
        loadingSlots: translated.loading_slots || "Checking available times…",
        loadingDates: translated.loading_dates || "Checking available dates…",
        availableDatesHint: translated.available_dates_hint || "Only dates with an available time can be selected.",
        noAvailableDates: translated.no_available_dates || "No available dates in this month.",
        datesLoadFailed: translated.dates_load_failed || "Unable to load available dates.",
        previousMonth: translated.previous_month || "Previous month",
        nextMonth: translated.next_month || "Next month",
        notifyCustomer: translated.notify_customer || "Notify customer on WhatsApp",
        notifyBeautician: translated.notify_beautician || "Notify beautician on WhatsApp",
        notificationsHelp: translated.notifications_help || "Notifications are sent only after the new schedule is saved.",
        cancel: translated.cancel || "Cancel",
        save: translated.save || "Save new schedule",
        saving: translated.saving || "Saving…",
        saved: translated.saved || "Appointment saved.",
        statuses: translated.statuses || {},
    };
}

export function initTbaScheduleActions() {
    const root =
        document.getElementById("tr-crm-dashboard") ||
        document.getElementById("tr-reservations-app") ||
        document.getElementById("tr-portal-app") ||
        document.body;
    const routeRoot = document.querySelector("[data-reschedule-url][data-reschedule-slots-url]") || root;
    const scheduleUrlTemplate = root.dataset?.tbaScheduleUrl || "";
    const rescheduleUrlTemplate = routeRoot.dataset?.rescheduleUrl || "";
    const slotsUrl = root.dataset?.tbaSlotsUrl || "";
    const rescheduleSlotsUrl = routeRoot.dataset?.rescheduleSlotsUrl || slotsUrl;
    const rescheduleDatesUrl = routeRoot.dataset?.rescheduleDatesUrl || "";

    if ((!scheduleUrlTemplate && !rescheduleUrlTemplate)
        || (!slotsUrl && !rescheduleSlotsUrl)
        || root.dataset?.scheduleActionsReady === "1") {
        return;
    }

    root.dataset.scheduleActionsReady = "1";

    document.addEventListener("click", (event) => {
        const button = event.target.closest("[data-tba-schedule], [data-reschedule-booking]");

        if (!button) {
            return;
        }

        event.preventDefault();

        const isReschedule = button.hasAttribute("data-reschedule-booking");

        openAppointmentSchedulingWorkspace({
            bookingId: button.dataset.bookingId,
            beauticianId: button.dataset.beauticianId,
            productId: button.dataset.productId,
            spaBranchId: button.dataset.spaBranchId,
            urlTemplate: isReschedule ? rescheduleUrlTemplate : scheduleUrlTemplate,
            slotsUrl: isReschedule ? rescheduleSlotsUrl : slotsUrl,
            datesUrl: isReschedule ? rescheduleDatesUrl : "",
            reschedule: isReschedule,
            labels: schedulingLabels(routeRoot),
            trigger: button,
        });
    });
}


export function initCalendarBookingDrop(app) {
    const root =
        document.getElementById("tr-crm-dashboard") ||
        document.getElementById("tr-reservations-app") ||
        document.getElementById("tr-portal-app");
    const routeRoot = document.querySelector("[data-reschedule-url][data-reschedule-slots-url]") || root;
    const scheduleUrlTemplate = root?.dataset?.tbaScheduleUrl || "";
    const rescheduleUrlTemplate = routeRoot?.dataset?.rescheduleUrl || "";
    const slotsUrl = root?.dataset?.tbaSlotsUrl || "";
    const rescheduleSlotsUrl = routeRoot?.dataset?.rescheduleSlotsUrl || slotsUrl;
    const rescheduleDatesUrl = routeRoot?.dataset?.rescheduleDatesUrl || "";
    const grid = app?.gridViewport || app?.grid || document.getElementById("tr-calendar-grid-viewport") || document.getElementById("tr-calendar-grid");

    if (!grid) {
        return;
    }

    let dragPayload = null;

    document.addEventListener("dragstart", (event) => {
        const tbaItem = event.target.closest(".tr-crm-tba-item[draggable='true']");
        const calEvent = event.target.closest(".tr-cal-event[data-cal-draggable='1']");
        const source = tbaItem || calEvent;

        if (!source || !event.dataTransfer) {
            return;
        }

        const bookingId = source.dataset.bookingId || source.dataset.tbaBookingId;
        if (!bookingId) {
            return;
        }

        dragPayload = {
            bookingId,
            beauticianId: source.dataset.beauticianId || source.dataset.tbaBeauticianId || "",
            productId: source.dataset.productId || "",
            spaBranchId: source.dataset.spaBranchId || "",
            isTba: tbaItem ? true : source.dataset.isTba === "1",
        };

        event.dataTransfer.setData("text/plain", bookingId);
        event.dataTransfer.effectAllowed = "move";
        source.classList.add("is-dragging");
    });

    document.addEventListener("dragend", (event) => {
        event.target.closest(".is-dragging")?.classList.remove("is-dragging");
        grid.querySelectorAll(".tr-cal-day--drop-target").forEach((el) => el.classList.remove("tr-cal-day--drop-target"));
        dragPayload = null;
    });

    grid.addEventListener("dragover", (event) => {
        const day = event.target.closest("[data-date]");
        if (!day || !dragPayload) {
            return;
        }
        event.preventDefault();
        grid.querySelectorAll(".tr-cal-day--drop-target").forEach((el) => {
            if (el !== day) el.classList.remove("tr-cal-day--drop-target");
        });
        day.classList.add("tr-cal-day--drop-target");
        if (event.dataTransfer) {
            event.dataTransfer.dropEffect = "move";
        }
    });

    grid.addEventListener("dragleave", (event) => {
        const day = event.target.closest("[data-date]");
        if (day && !day.contains(event.relatedTarget)) {
            day.classList.remove("tr-cal-day--drop-target");
        }
    });

    grid.addEventListener("drop", async (event) => {
        const day = event.target.closest("[data-date]");
        if (!day || !dragPayload) {
            return;
        }
        event.preventDefault();
        day.classList.remove("tr-cal-day--drop-target");

        const date = day.dataset.date;
        const payload = { ...dragPayload };
        dragPayload = null;

        if (payload.isTba) {
            if (!scheduleUrlTemplate || !slotsUrl) {
                window.notify?.error?.("Schedule URL missing") || alert("Schedule URL missing");
                return;
            }
            openAppointmentSchedulingWorkspace({
                bookingId: payload.bookingId,
                beauticianId: payload.beauticianId,
                productId: payload.productId,
                spaBranchId: payload.spaBranchId,
                urlTemplate: scheduleUrlTemplate,
                slotsUrl,
                appointmentDate: date,
                labels: schedulingLabels(root),
            });
            return;
        }

        if (!rescheduleUrlTemplate || !rescheduleSlotsUrl) {
            const message = schedulingLabels(routeRoot).saveFailed;
            window.notify?.error?.(message) || alert(message);
            return;
        }

        openAppointmentSchedulingWorkspace({
            bookingId: payload.bookingId,
            beauticianId: payload.beauticianId,
            productId: payload.productId,
            spaBranchId: payload.spaBranchId,
            urlTemplate: rescheduleUrlTemplate,
            slotsUrl: rescheduleSlotsUrl,
            datesUrl: rescheduleDatesUrl,
            appointmentDate: date,
            reschedule: true,
            labels: schedulingLabels(routeRoot),
        });
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

    initDashboardSearch(app);
    initDateFilterPills();
    initCrmDatePicker();
    initAgendaPanel(app);
    initPipelineTimers();
    initPipelineSortable(app);
    initPipelineActions(app);
    initSpecialistToggles();
    initCustomerProfileDrawer();
    initTbaScheduleActions();

    document.addEventListener("tr-crm-booking-updated", () => {
        app.refreshAgendaPanel?.();
    });
}
