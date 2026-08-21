import axios from "axios";
import flatpickr from "flatpickr";
import { bookingAllowsDetail, getCalendarBooking, setCalendarBookings, upsertBooking } from "./kanban-helpers.js";
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


async function scheduleTbaBooking({
    bookingId,
    beauticianId,
    productId,
    spaBranchId,
    scheduleUrlTemplate,
    slotsUrl,
    appointmentDate = null,
}) {
    const date = appointmentDate || window.prompt("Appointment date (YYYY-MM-DD)");

    if (!date) {
        return;
    }

    let slots = [];

    try {
        const { data } = await axios.get(slotsUrl, {
            params: {
                beautician_id: beauticianId,
                date,
                booking_id: bookingId,
                product_id: productId || undefined,
                spa_branch_id: spaBranchId || undefined,
            },
        });
        slots = data.slots || [];
    } catch (error) {
        window.notify?.error?.(error?.response?.data?.message || "Failed to load slots")
            || alert(error?.response?.data?.message || "Failed to load slots");
        return;
    }

    if (!slots.length) {
        window.notify?.error?.("No available times on this date.") || alert("No available times on this date.");
        return;
    }

    const time = window.prompt(`Available times:\n${slots.join(", ")}\n\nEnter time (HH:MM)`, slots[0]);

    if (!time) {
        return;
    }

    const url = scheduleUrlTemplate.replace("__ID__", String(bookingId));

    try {
        const payload = {
            beautician_id: beauticianId,
            appointment_date: date,
            appointment_time: time,
            notify_customer: true,
        };

        if (spaBranchId) {
            payload.spa_branch_id = spaBranchId;
        }

        const { data } = await axios.patch(url, payload);
        window.notify?.success?.(data.message || "Scheduled") || alert(data.message || "Scheduled");
        window.location.reload();
    } catch (error) {
        window.notify?.error?.(error?.response?.data?.message || "Failed to schedule")
            || alert(error?.response?.data?.message || "Failed to schedule");
    }
}

export function initTbaScheduleActions() {
    const root =
        document.getElementById("tr-crm-dashboard") ||
        document.getElementById("tr-reservations-app") ||
        document.getElementById("tr-portal-app") ||
        document.body;
    const scheduleUrlTemplate = root.dataset?.tbaScheduleUrl || "";
    const slotsUrl = root.dataset?.tbaSlotsUrl || "";

    if (!scheduleUrlTemplate || !slotsUrl) {
        return;
    }

    document.addEventListener("click", (event) => {
        const button = event.target.closest("[data-tba-schedule]");

        if (!button) {
            return;
        }

        event.preventDefault();

        scheduleTbaBooking({
            bookingId: button.dataset.bookingId,
            beauticianId: button.dataset.beauticianId,
            productId: button.dataset.productId,
            spaBranchId: button.dataset.spaBranchId,
            scheduleUrlTemplate,
            slotsUrl,
        });
    });
}


export function initCalendarBookingDrop(app) {
    const root =
        document.getElementById("tr-crm-dashboard") ||
        document.getElementById("tr-reservations-app") ||
        document.getElementById("tr-portal-app");
    const scheduleUrlTemplate = root?.dataset?.tbaScheduleUrl || "";
    const slotsUrl = root?.dataset?.tbaSlotsUrl || "";
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
            await scheduleTbaBooking({
                bookingId: payload.bookingId,
                beauticianId: payload.beauticianId,
                productId: payload.productId,
                spaBranchId: payload.spaBranchId,
                scheduleUrlTemplate,
                slotsUrl,
                appointmentDate: date,
            });
            return;
        }

        const booking = typeof getCalendarBooking === "function"
            ? getCalendarBooking(payload.bookingId)
            : null;

        if (!booking) {
            window.notify?.error?.("Booking not found") || alert("Booking not found");
            return;
        }

        // Open manual editor on the dropped date; slots API enforces capacity via engine.
        if (typeof openManualBookingEditor === "function") {
            openManualBookingEditor({
                ...booking,
                appointment_date_value: date,
                date,
                appointment_date: date,
            });
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
