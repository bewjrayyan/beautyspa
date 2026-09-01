import {
    bookingAllowsDetail,
    bookingIsOwnForPortal,
    buildCalendarEventHtml,
    buildCalendarLegendHtml,
    collectBeauticiansFromBookings,
    initBeauticianAvatarLightbox,
    initCalendarEventPreview,
    openBookingPreviewById,
    openCalendarEventPreview,
    renderKanbanBeautician,
    resolveBooking,
    setCalendarBookings,
    setKanbanBookings,
    upsertBooking,
} from "./kanban-helpers.js";
import { initTreatmentAnalytics } from "./analytics.js";
import { initCrmDashboard, initTbaScheduleActions, initCalendarBookingDrop } from "./dashboard.js";
import { initCustomerProfileDrawer } from "./customer-profile.js";
import "./portal-account.js";
import "./portal-availability.js";
import "./manual-booking.js";
import { initAdminPreviewTimer } from "./admin-preview-timer.js";

const TR_KANBAN_STATUS_ACCENT = {
    pending: "#ea580c",
    in_progress: "#4338ca",
    completed: "#047857",
};

class TreatmentReservationsApp {
    static statusAccentColor(status) {
        return TR_KANBAN_STATUS_ACCENT[status] || "#94a3b8";
    }

    static escapeHtml(value) {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    static safeCssColor(value) {
        const raw = String(value || "").trim();

        return /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(raw) ? raw : "";
    }


    static localDateKey(date = new Date()) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");

        return `${year}-${month}-${day}`;
    }

    static parseLocalDate(value) {
        const match = String(value || "").match(/^(\d{4})-(\d{2})-(\d{2})$/);

        if (!match) {
            return null;
        }

        return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]), 12, 0, 0);
    }

    constructor(root) {
        this.root = root;
        this.activeView = root.dataset.activeView;
        this.calendarUrl = root.dataset.calendarUrl;
        this.kanbanUrl = root.dataset.kanbanUrl;
        this.statusUrlTemplate = root.dataset.statusUrl;
        this.month = root.dataset.initialMonth || TreatmentReservationsApp.localDateKey().slice(0, 7);
        this.beauticianId = root.dataset.initialBeautician || "";
        this.spaBranchId = root.dataset.initialSpaBranch || "";
        this.portalBeauticianId = root.dataset.portalBeauticianId || "";
        this.calendarFocusBookingId = root.dataset.calendarFocusBookingId || "";
        this.calendarFocusHandled = false;
        this.categoryId = root.dataset.initialCategory || "";
        this.calendarInitialized = false;
        this.kanbanInitialized = false;
        this.lastCalendarBookings = [];
        this.holidaysRangeUrl = root.dataset.holidaysRangeUrl || "";
        this.holidaysByDate = {};
        this.holidaysRangeKey = "";
        this.calendarDataCache = new Map();
        this.holidayDataCache = new Map();
        this.calendarLoadSequence = 0;

        if (this.root.querySelector("[data-schedule-panel]")) {
            this.initScheduleTabs();
            this.activateView(this.activeView);

            return;
        }

        if (this.activeView === "calendar" || this.activeView === "dashboard") {
            this.initCalendar();
            this.calendarInitialized = true;
        }

        if (this.activeView === "kanban" && ! document.getElementById("tr-crm-dashboard")) {
            this.initKanban();
            this.kanbanInitialized = true;
        }
    }

    initScheduleTabs() {
        this.root.querySelectorAll("[data-schedule-view]").forEach((button) => {
            button.addEventListener("click", (event) => {
                event.preventDefault();

                const view = button.dataset.scheduleView;

                this.root.querySelectorAll("[data-schedule-view]").forEach((tab) => {
                    tab.closest("li")?.classList.toggle("active", tab === button);
                });

                this.activateView(view);

                if (button.hasAttribute("data-scroll-schedule")) {
                    this.root.querySelector("#tr-portal-schedule")?.scrollIntoView({
                        behavior: "smooth",
                        block: "start",
                    });
                }
            });
        });
    }

    activateView(view) {
        this.activeView = view;

        this.root.querySelectorAll("[data-schedule-panel]").forEach((panel) => {
            panel.hidden = panel.dataset.schedulePanel !== view;
        });

        if (view === "calendar" || view === "dashboard") {
            if (!this.calendarInitialized) {
                this.initCalendar();
                this.calendarInitialized = true;
            } else {
                this.loadCalendar();
            }
        }

        if (view === "kanban" && ! document.getElementById("tr-crm-dashboard")) {
            if (!this.kanbanInitialized) {
                this.initKanban();
                this.kanbanInitialized = true;
            } else {
                this.loadKanban();
            }
        }
    }

    getFilterParams() {
        const params = new URLSearchParams();

        if (this.beauticianId) {
            params.set("beautician_id", this.beauticianId);
        }

        if (this.categoryId) {
            params.set("treatment_category_id", this.categoryId);
        }

        if (this.spaBranchId) {
            params.set("spa_branch_id", this.spaBranchId);
        }

        return params;
    }

    initCalendar() {
        // Prefer elements inside this app root (admin, portal job sheet, beautician schedule).
        const scope = this.root || document;
        const q = (sel) => scope.querySelector(sel) || document.querySelector(sel);

        this.grid = q("#tr-calendar-grid");
        this.gridViewport = q("#tr-calendar-grid-viewport");
        this.gridTrack = q("#tr-calendar-grid-track");
        this.monthLabel = q("#tr-cal-month-label");
        this.monthPrevLabel = q("#tr-cal-month-prev");
        this.monthNextLabel = q("#tr-cal-month-next");
        this.monthPrev2Label = q("#tr-cal-month-prev2");
        this.monthNext2Label = q("#tr-cal-month-next2");
        this.monthInput = q("#tr-month");

        // Clickable sibling month buttons
        scope.querySelectorAll("[data-month-offset]").forEach((btn) => {
            btn.addEventListener("click", () => {
                const offset = parseInt(btn.dataset.monthOffset, 10);
                this.shiftMonth(offset);
            });
        });
        this.emptyCalendarLabel = this.root.dataset.calEmptyLabel || "";
        this.compactCalendar =
            !!scope.querySelector("[data-crm-compact-calendar]")
            || !!document.querySelector("[data-crm-compact-calendar]")
            || (
                this.root.classList.contains("tr-reservations--view-calendar")
                && window.matchMedia("(max-width: 991px)").matches
            );
        this.pendingSlideDirection = 0;
        this.calendarAnimating = false;

        q("#tr-cal-prev")?.addEventListener("click", () => this.shiftMonth(-1));
        q("#tr-cal-next")?.addEventListener("click", () => this.shiftMonth(1));
        q("#tr-cal-today")?.addEventListener("click", () => {
            const todayKey = TreatmentReservationsApp.localDateKey();
            const todayMonth = todayKey.slice(0, 7);

            if (todayMonth !== this.month) {
                this.pendingSlideDirection = todayMonth > this.month ? 1 : -1;
            }

            this.month = todayMonth;
            this.selectedDate = todayKey;
            this.weekStart = this.getWeekStart(this.selectedDate);
            this.syncMonthInput();
            this.loadCalendar();
        });

        document.addEventListener("tr-crm-booking-updated", () => {
            this.calendarDataCache.clear();
        });

        this.loadCalendar();

        // Day click: mobile day-info modal; desktop select / day-view sync
        const gridVp = this.gridViewport || this.grid;
        if (gridVp) {
            gridVp.addEventListener("click", (e) => {
                if (e.target.closest(".tr-cal-event--clickable")) {
                    return;
                }

                const dayEl = e.target.closest(".tr-cal-day[data-date]");
                if (!dayEl || dayEl.classList.contains("tr-cal-day--muted")) {
                    return;
                }

                this.handleCalendarDayActivate(dayEl);
            });

            gridVp.addEventListener("keydown", (e) => {
                if (e.key !== "Enter" && e.key !== " ") {
                    return;
                }

                const dayEl = e.target.closest(".tr-cal-day[data-date]");
                if (!dayEl || dayEl.classList.contains("tr-cal-day--muted")) {
                    return;
                }

                e.preventDefault();
                this.handleCalendarDayActivate(dayEl);
            });

            gridVp.addEventListener("dblclick", (e) => {
                if (this.isMobileCalendarView()) {
                    return;
                }

                const dayEl = e.target.closest(".tr-cal-day[data-date]");
                if (!dayEl) return;
                this.selectedDate = dayEl.dataset.date;
                (this.calendarRoot?.querySelector('[data-cal-view="week"]')
                    || this.calendarRoot?.querySelector('[data-cal-view="day"]')
                    || document.querySelector('[data-cal-view="week"]')
                    || document.querySelector('[data-cal-view="day"]'))?.click();
            });
        }

        // Month / week view toggle (admin calendar, CRM agenda, portal — same controls).
        this.calendarRoot = this.grid?.closest(".tr-calendar")
            || scope.querySelector(".tr-calendar")
            || document.querySelector(".tr-calendar");
        this.calendarBoard = this.calendarRoot?.querySelector(".tr-calendar-board") || null;
        this.calendarMeta = this.calendarRoot?.querySelector(".tr-calendar-meta") || null;
        this.dayView = this.calendarRoot?.querySelector("#tr-cal-day-view") || q("#tr-cal-day-view");
        this.dayTitle = this.calendarRoot?.querySelector("#tr-cal-day-title") || q("#tr-cal-day-title");
        this.weekGrid = this.calendarRoot?.querySelector("#tr-cal-week-grid") || q("#tr-cal-week-grid");
        this.currentCalView = "month";
        this.selectedDate = TreatmentReservationsApp.localDateKey();
        this.weekStart = this.getWeekStart(this.selectedDate);

        q("#tr-cal-day-prev")?.addEventListener("click", () => this.shiftWeek(-1));
        q("#tr-cal-day-next")?.addEventListener("click", () => this.shiftWeek(1));

        this.calendarRoot?.querySelectorAll("[data-cal-view]").forEach((btn) => {
            btn.addEventListener("click", () => {
                const rawView = btn.dataset.calView;
                const view = rawView === "day" ? "week" : rawView;
                if (view === this.currentCalView) return;

                this.calendarRoot.querySelectorAll("[data-cal-view]").forEach((b) => b.classList.remove("is-active"));
                btn.classList.add("is-active");
                this.currentCalView = view;
                this.syncCalendarIntroCopy(view);

                if (view === "week") {
                    this.showDayView();
                } else {
                    this.showMonthView();
                }
            });
        });

        this.syncCalendarIntroCopy(this.currentCalView);
    }

    syncCalendarIntroCopy(view = this.currentCalView) {
        const intro = this.calendarRoot?.querySelector(".tr-calendar-intro");
        if (!intro) {
            return;
        }

        const isWeek = view === "week";
        const title = intro.querySelector("#tr-calendar-intro-title");
        const subtitle = intro.querySelector("#tr-calendar-intro-subtitle");

        if (title) {
            title.textContent = isWeek
                ? (intro.dataset.titleWeek || title.textContent)
                : (intro.dataset.titleMonth || title.textContent);
        }

        if (subtitle) {
            subtitle.textContent = isWeek
                ? (intro.dataset.subtitleWeek || subtitle.textContent)
                : (intro.dataset.subtitleMonth || subtitle.textContent);
        }
    }

    showDayView() {
        if (this.calendarBoard) this.calendarBoard.hidden = true;
        if (this.calendarMeta) this.calendarMeta.hidden = true;
        if (this.dayView) {
            this.dayView.hidden = false;
            this.dayView.style.display = "";
        }
        this.calendarRoot?.classList.add("tr-calendar--week-view");
        this.syncCalendarIntroCopy("week");
        this.weekStart = this.getWeekStart(this.selectedDate);
        // Ensure bookings are loaded for the currently visible week range.
        this.loadCalendar();
    }

    showMonthView() {
        if (this.calendarBoard) this.calendarBoard.hidden = false;
        if (this.calendarMeta) this.calendarMeta.hidden = false;
        if (this.dayView) {
            this.dayView.hidden = true;
            this.dayView.style.display = "none";
        }
        this.calendarRoot?.classList.remove("tr-calendar--week-view");
        this.syncCalendarIntroCopy("month");
    }

    isWeekCalView() {
        return this.currentCalView === "week" || this.currentCalView === "day";
    }

    isMobileCalendarView() {
        return (
            this.root.classList.contains("tr-reservations--view-calendar")
            && window.matchMedia("(max-width: 991px)").matches
        );
    }

    handleCalendarDayActivate(dayEl) {
        const dateStr = dayEl.dataset.date;
        this.selectedDate = dateStr;

        this.grid?.querySelectorAll(".tr-cal-day[data-date]").forEach((node) => {
            const selected = node.dataset.date === dateStr;
            node.classList.toggle("tr-cal-day--selected", selected);
            node.setAttribute("aria-selected", selected ? "true" : "false");
        });

        if (this.isWeekCalView()) {
            this.renderDayView();
        }

        if (!this.isMobileCalendarView() || this.currentCalView !== "month") {
            return;
        }

        const bookings = (this.lastCalendarBookings || this.lastBookings || []).filter(
            (booking) => booking.date === dateStr
        );
        const holiday = (this.holidaysByDate || {})[dateStr] || null;

        if (!bookings.length && !holiday) {
            return;
        }

        this.openDayEventsModal(dateStr, bookings, holiday);
    }

    ensureDayEventsModal() {
        let modal = document.getElementById("tr-cal-day-events-modal");

        if (modal) {
            return modal;
        }

        modal = document.createElement("div");
        modal.id = "tr-cal-day-events-modal";
        modal.className = "tr-cal-day-events-modal";
        modal.hidden = true;
        modal.setAttribute("aria-hidden", "true");
        modal.innerHTML = `
            <div class="tr-cal-day-events-modal__backdrop" data-day-modal-dismiss></div>
            <div class="tr-cal-day-events-modal__sheet" role="dialog" aria-modal="true" aria-labelledby="tr-cal-day-events-title">
                <div class="tr-cal-day-events-modal__handle" aria-hidden="true"></div>
                <header class="tr-cal-day-events-modal__head">
                    <div class="tr-cal-day-events-modal__head-text">
                        <p class="tr-cal-day-events-modal__eyebrow" id="tr-cal-day-events-count"></p>
                        <h3 class="tr-cal-day-events-modal__title" id="tr-cal-day-events-title"></h3>
                    </div>
                    <button type="button" class="tr-cal-day-events-modal__close" data-day-modal-dismiss aria-label="${TreatmentReservationsApp.escapeHtml(this.root.dataset.calDayModalClose || "Close")}">
                        <i class="fa fa-times" aria-hidden="true"></i>
                    </button>
                </header>
                <div class="tr-cal-day-events-modal__body" id="tr-cal-day-events-body"></div>
            </div>
        `;

        document.body.appendChild(modal);

        modal.addEventListener("click", async (event) => {
            if (event.target.closest("[data-day-modal-dismiss]")) {
                this.closeDayEventsModal();
                return;
            }

            const item = event.target.closest("[data-day-modal-booking-id]");
            if (!item) {
                return;
            }

            const bookingId = item.dataset.dayModalBookingId;
            const booking = resolveBooking(bookingId)
                || (this.lastCalendarBookings || []).find((row) => String(row.id) === String(bookingId));

            if (!booking) {
                return;
            }

            if (!bookingAllowsDetail(booking, this.portalBeauticianId || null)) {
                return;
            }

            this.closeDayEventsModal();
            await openBookingPreviewById(bookingId);
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && modal && !modal.hidden) {
                this.closeDayEventsModal();
            }
        });

        return modal;
    }

    openDayEventsModal(dateStr, bookings, holiday) {
        const modal = this.ensureDayEventsModal();
        const titleEl = modal.querySelector("#tr-cal-day-events-title");
        const countEl = modal.querySelector("#tr-cal-day-events-count");
        const bodyEl = modal.querySelector("#tr-cal-day-events-body");
        const labels = {
            title: this.root.dataset.calDayModalTitle || "Appointments",
            empty: this.root.dataset.calDayModalEmpty || "No appointments on this day",
            holiday: this.root.dataset.calDayModalHoliday || "Public holiday",
            count: this.root.dataset.calDayModalCount || ":count appointments",
            view: this.root.dataset.calDayModalView || "View details",
            pending: this.root.dataset.calStatusPending || "Pending",
            inProgress: this.root.dataset.calStatusInProgress || "In progress",
            completed: this.root.dataset.calStatusCompleted || "Completed",
        };

        const dateLabel = new Date(`${dateStr}T12:00:00`).toLocaleDateString(
            this.root.dataset.agendaLocale || undefined,
            { weekday: "short", day: "numeric", month: "short", year: "numeric" }
        );

        titleEl.textContent = dateLabel;
        countEl.textContent = bookings.length
            ? labels.count.replace(":count", String(bookings.length))
            : labels.title;

        const statusLabel = (status) => {
            if (status === "in_progress") return labels.inProgress;
            if (status === "completed") return labels.completed;
            if (status === "canceled") return status;
            return labels.pending;
        };

        const holidayHtml = holiday
            ? `<div class="tr-cal-day-events-modal__holiday">
                    <span class="tr-cal-day-events-modal__holiday-label">${TreatmentReservationsApp.escapeHtml(labels.holiday)}</span>
                    <strong>${TreatmentReservationsApp.escapeHtml(holiday.label || "")}</strong>
                    ${Array.isArray(holiday.states) && holiday.states.length
                        ? `<span class="tr-cal-day-events-modal__holiday-states">${TreatmentReservationsApp.escapeHtml(holiday.states.join(", "))}</span>`
                        : ""}
               </div>`
            : "";

        const sorted = [...bookings].sort((a, b) =>
            String(a.time || a.appointment_time || "").localeCompare(String(b.time || b.appointment_time || ""))
        );

        const listHtml = sorted.length
            ? `<ul class="tr-cal-day-events-modal__list">
                ${sorted.map((booking) => {
                    const time = booking.appointment_time_range || booking.time || booking.appointment_time || "—";
                    const status = booking.status || "pending";
                    const canOpen = bookingAllowsDetail(booking, this.portalBeauticianId || null);
                    const tag = canOpen ? "button" : "div";
                    const attrs = canOpen
                        ? `type="button" data-day-modal-booking-id="${TreatmentReservationsApp.escapeHtml(String(booking.id))}"`
                        : "";

                    return `<li>
                        <${tag} class="tr-cal-day-events-modal__item tr-cal-day-events-modal__item--${TreatmentReservationsApp.escapeHtml(status)}${canOpen ? " is-clickable" : ""}" ${attrs}>
                            <div class="tr-cal-day-events-modal__item-time">
                                <strong>${TreatmentReservationsApp.escapeHtml(time)}</strong>
                                <span class="tr-cal-day-events-modal__status tr-cal-day-events-modal__status--${TreatmentReservationsApp.escapeHtml(status)}">${TreatmentReservationsApp.escapeHtml(statusLabel(status))}</span>
                            </div>
                            <div class="tr-cal-day-events-modal__item-main">
                                <strong>${TreatmentReservationsApp.escapeHtml(booking.customer_name || "—")}</strong>
                                <span>${TreatmentReservationsApp.escapeHtml(booking.treatment_name || booking.product_name || "—")}</span>
                                ${booking.beautician_name ? `<span class="tr-cal-day-events-modal__beautician">${TreatmentReservationsApp.escapeHtml(booking.beautician_name)}</span>` : ""}
                            </div>
                            ${canOpen ? `<span class="tr-cal-day-events-modal__chevron" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>` : ""}
                        </${tag}>
                    </li>`;
                }).join("")}
               </ul>`
            : `<p class="tr-cal-day-events-modal__empty">${TreatmentReservationsApp.escapeHtml(labels.empty)}</p>`;

        bodyEl.innerHTML = `${holidayHtml}${listHtml}`;

        modal.hidden = false;
        modal.setAttribute("aria-hidden", "false");
        document.body.classList.add("tr-cal-day-events-modal-open");
        modal.querySelector(".tr-cal-day-events-modal__close")?.focus();
    }

    closeDayEventsModal() {
        const modal = document.getElementById("tr-cal-day-events-modal");
        if (!modal) {
            return;
        }

        modal.hidden = true;
        modal.setAttribute("aria-hidden", "true");
        document.body.classList.remove("tr-cal-day-events-modal-open");
    }

    getWeekStart(dateStr) {
        const d = TreatmentReservationsApp.parseLocalDate(dateStr) || new Date();
        const day = d.getDay();
        const diff = day === 0 ? 6 : day - 1;
        d.setDate(d.getDate() - diff);

        return TreatmentReservationsApp.localDateKey(d);
    }

    getWeekDays(startStr) {
        const days = [];
        const start = TreatmentReservationsApp.parseLocalDate(startStr) || new Date();

        for (let i = 0; i < 7; i++) {
            const d = new Date(start.getFullYear(), start.getMonth(), start.getDate() + i, 12, 0, 0);
            days.push(TreatmentReservationsApp.localDateKey(d));
        }

        return days;
    }

    shiftWeek(delta) {
        const d = TreatmentReservationsApp.parseLocalDate(this.weekStart) || new Date();
        d.setDate(d.getDate() + delta * 7);
        this.weekStart = TreatmentReservationsApp.localDateKey(d);
        this.selectedDate = this.weekStart;

        const newMonth = this.weekStart.slice(0, 7);
        if (newMonth !== this.month) {
            this.month = newMonth;
            this.pendingSlideDirection = delta;
            this.syncMonthInput();
            this.updateMonthLabel();
        }

        // Always reload for the newly visible 7-day range.
        // This guarantees bookings in adjacent months (when the week spans them)
        // appear immediately without needing to move back.
        this.loadCalendar();
    }

    renderDayView() {
        if (!this.weekGrid || !this.dayTitle) return;

        const days = this.getWeekDays(this.weekStart);
        const locale = this.calendarLocale();
        const today = TreatmentReservationsApp.localDateKey();
        const weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

        const startDate = TreatmentReservationsApp.parseLocalDate(days[0]) || new Date(days[0] + "T12:00:00");
        const endDate = TreatmentReservationsApp.parseLocalDate(days[6]) || new Date(days[6] + "T12:00:00");
        this.dayTitle.textContent =
            startDate.toLocaleDateString(locale, { day: "numeric", month: "short" })
            + " — "
            + endDate.toLocaleDateString(locale, { day: "numeric", month: "short", year: "numeric" });

        const bookings = this.lastBookings || [];
        const byDate = {};
        bookings.forEach((b) => {
            if (!byDate[b.date]) byDate[b.date] = [];
            byDate[b.date].push(b);
        });

        const malaysiaPublicHolidays = this.holidaysByDate || {};

        // Header row
        let headerHtml = '<div class="tr-week-header"><div class="tr-week-header__time"></div>';
        days.forEach((ds) => {
            const d = TreatmentReservationsApp.parseLocalDate(ds) || new Date(ds + "T12:00:00");
            const dayNum = d.getDate();
            const dow = weekdays[d.getDay()];
            const isToday = ds === today;
            const count = (byDate[ds] || []).length;
            const holiday = malaysiaPublicHolidays[ds];
            const holidayColor = holiday ? TreatmentReservationsApp.safeCssColor(holiday.color) : "";
            headerHtml += '<div class="tr-week-header__day'
                + (isToday ? ' tr-week-header__day--today' : '')
                + (count ? ' tr-week-header__day--has-booking' : '')
                + (holiday ? ' tr-week-header__day--holiday' : '') + '"'
                + (holidayColor ? ' style="--holiday-color:' + holidayColor + '"' : '') + '>'
                + '<span class="tr-week-header__dow">' + dow + '</span>'
                + '<span class="tr-week-header__num">' + dayNum + '</span>'
                + (holiday ? '<span class="tr-week-header__holiday-name">' + TreatmentReservationsApp.escapeHtml(holiday.label || "") + '</span>' : '')
                + (count ? '<span class="tr-week-header__dot"></span>' : '')
                + (count ? '<span class="tr-week-header__count">' + count + '</span>' : '')
                + '</div>';
        });
        headerHtml += '</div>';

        const ROW_HEIGHT = 100;
        // Full-day 24h grid (00:00–23:00). Still expands if bookings need it (clamped 0–23).
        const DEFAULT_START_HOUR = 0;
        const DEFAULT_END_HOUR = 23;

        const parseTime = (booking) => {
            const value = booking?.appointment_time_value || booking?.time || "";
            if (!value) return null;

            const twentyFour = String(value).match(/^(\d{1,2}):(\d{2})$/);
            if (twentyFour && !/am|pm/i.test(value)) {
                const h = parseInt(twentyFour[1], 10);
                const min = parseInt(twentyFour[2], 10);
                if (h >= 0 && h <= 23 && min >= 0 && min <= 59) {
                    return { h, min };
                }
            }

            const m = String(value).match(/(\d{1,2}):(\d{2})\s*(AM|PM)?/i);
            if (!m) return null;
            let h = parseInt(m[1], 10);
            const min = parseInt(m[2], 10);
            if (m[3]) {
                const ampm = m[3].toUpperCase();
                if (ampm === "PM" && h !== 12) h += 12;
                if (ampm === "AM" && h === 12) h = 0;
            }
            if (h < 0 || h > 23 || min < 0 || min > 59) return null;
            return { h, min };
        };

        // Expand the hour window so early/late appointments never fall outside the grid.
        let START_HOUR = DEFAULT_START_HOUR;
        let END_HOUR = DEFAULT_END_HOUR;
        days.forEach((ds) => {
            (byDate[ds] || []).forEach((booking) => {
                const start = parseTime(booking);
                if (!start) return;
                const durationMin = Number(booking.slot_duration_minutes) || 60;
                const endMinutes = start.h * 60 + start.min + durationMin;
                START_HOUR = Math.min(START_HOUR, start.h);
                END_HOUR = Math.max(END_HOUR, Math.ceil(endMinutes / 60));
            });
        });
        START_HOUR = Math.max(0, START_HOUR);
        END_HOUR = Math.min(23, Math.max(START_HOUR, END_HOUR));

        // Time rows (empty grid lines)
        let rowsHtml = '';
        for (let hour = START_HOUR; hour <= END_HOUR; hour++) {
            const timeLabel = String(hour).padStart(2, "0") + ":00";

            rowsHtml += '<div class="tr-week-row">';
            rowsHtml += '<div class="tr-week-row__time">' + timeLabel + '</div>';
            for (let c = 0; c < 7; c++) {
                rowsHtml += '<div class="tr-week-row__cell"></div>';
            }
            rowsHtml += '</div>';
        }

        // Overlay columns with positioned booking cards
        let overlayHtml = '<div class="tr-week-overlay">';
        overlayHtml += '<div class="tr-week-overlay__gutter"></div>';

        days.forEach((ds) => {
            let colHtml = '<div class="tr-week-overlay__col">';
            const dayB = byDate[ds] || [];
            const holiday = malaysiaPublicHolidays[ds];

            if (holiday) {
                const kindClass = String(holiday.kind || "other").replace(/[^a-z_]/gi, "");
                const holidayColor = TreatmentReservationsApp.safeCssColor(holiday.color);
                const statesSummary = Array.isArray(holiday.states) && holiday.states.length
                    ? TreatmentReservationsApp.escapeHtml(holiday.states.join(", "))
                    : "";
                colHtml += '<div class="tr-week-holiday-watermark tr-week-holiday-watermark--' + kindClass + '"'
                    + (holidayColor ? ' style="--holiday-color:' + holidayColor + '"' : '') + '>'
                    + '<div class="tr-week-holiday-watermark__stack">'
                    + '<span class="tr-week-holiday-watermark__title">' + TreatmentReservationsApp.escapeHtml(holiday.label || "") + '</span>'
                    + (statesSummary ? '<span class="tr-week-holiday-watermark__subtitle">' + statesSummary + '</span>' : '')
                    + '</div>'
                    + '</div>';
            }

            dayB.forEach((b) => {
                const start = parseTime(b);
                if (!start) return;

                const durationMin = Number(b.slot_duration_minutes) || 60;
                const topMin = (start.h - START_HOUR) * 60 + start.min;
                const topPx = Math.max(0, (topMin / 60) * ROW_HEIGHT);
                const heightPx = Math.max((durationMin / 60) * ROW_HEIGHT, 28);

                const endTime = b.appointment_end_time || "";
                const timeLabel = b.time || b.appointment_time_value || "";
                const timeRange = timeLabel + (endTime ? " – " + endTime : "");
                const statusClass = String(b.status || "pending").replace("_", "-");

                const canOpen = bookingAllowsDetail(b, this.portalBeauticianId || null);
                const isOwn = bookingIsOwnForPortal(b, this.portalBeauticianId || null);
                const clickClass = [
                    canOpen ? "tr-cal-event--clickable tr-crm-drawer-booking tr-crm-drawer-booking--clickable" : "",
                    !isOwn ? "tr-week-card--others" : "",
                ].filter(Boolean).join(" ");
                const clickAttrs = canOpen ? ' role="button" tabindex="0"' : '';

                const weekColor = TreatmentReservationsApp.safeCssColor(b.beautician_color) || "#6366f1";
                colHtml += '<div class="tr-week-card tr-week-card--' + TreatmentReservationsApp.escapeHtml(statusClass)
                    + (clickClass ? ' ' + clickClass : '') + '"'
                    + ' data-booking-id="' + TreatmentReservationsApp.escapeHtml(String(b.id ?? '')) + '"'
                    + clickAttrs
                    + ' style="--tr-beautician-color:' + weekColor + ';top:' + topPx + 'px;height:' + heightPx + 'px;border-left-color:' + weekColor + ';background:color-mix(in srgb, ' + weekColor + ' 14%, #fff);border-color:color-mix(in srgb, ' + weekColor + ' 28%, #e2e8f0)">'
                    + '<strong>' + TreatmentReservationsApp.escapeHtml(b.customer_name || "—") + '</strong>'
                    + '<span class="tr-week-card__treatment">' + TreatmentReservationsApp.escapeHtml(b.treatment_name || "") + '</span>'
                    + '<span class="tr-week-card__time">' + TreatmentReservationsApp.escapeHtml(timeRange) + '</span>'
                    + (b.beautician_name ? '<span class="tr-week-card__beautician">' + TreatmentReservationsApp.escapeHtml(b.beautician_name) + '</span>' : '')
                    + '</div>';
            });

            colHtml += '</div>';
            overlayHtml += colHtml;
        });

        overlayHtml += '</div>';

        this.weekGrid.innerHTML = headerHtml
            + '<div class="tr-week-body">'
            + '<div class="tr-week-body__inner">'
            + rowsHtml + overlayHtml
            + '</div></div>';
    }

    syncMonthInput() {
        if (this.monthInput) {
            this.monthInput.value = this.month;
        }
    }

    calendarLocale() {
        return (
            document.getElementById("tr-crm-dashboard")?.dataset.agendaLocale
            || this.root.dataset.agendaLocale
            || document.documentElement.lang
            || undefined
        );
    }

    shiftMonth(delta) {
        if (this.calendarAnimating) {
            return;
        }

        const [year, month] = this.month.split("-").map(Number);
        const date = new Date(year, month - 1 + delta, 1);
        this.month = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}`;
        this.selectedDate = this.month + "-01";
        this.weekStart = this.getWeekStart(this.selectedDate);
        this.pendingSlideDirection = delta;
        this.syncMonthInput();
        this.updateMonthLabel();
        this.loadCalendar();
    }

    setMonthNavDisabled(disabled) {
        ["tr-cal-prev", "tr-cal-next", "tr-cal-today"].forEach((id) => {
            const button = document.getElementById(id);

            if (button) {
                button.disabled = disabled;
            }
        });
    }

    async loadCalendar() {
        const loadSequence = ++this.calendarLoadSequence;
        const requestedMonth = this.month;
        const direction = this.pendingSlideDirection || 0;
        this.pendingSlideDirection = 0;

        const canSlide =
            direction !== 0
            && this.gridTrack
            && this.grid?.querySelector(".tr-cal-day:not(.tr-cal-day--muted)");

        if (!canSlide) {
            this.grid.classList.add("tr-calendar-grid--loading");
            this.grid.innerHTML = `
                <div class="tr-calendar-loading">
                    <i class="fa fa-spinner fa-spin"></i>
                    <span>Loading calendar…</span>
                </div>
            `;
        } else {
            this.gridViewport?.classList.add("tr-calendar-grid-viewport--loading");
        }

        try {
            const holidayFromTo = this.calendarRangeForMonth(requestedMonth);
            const [response, holidays] = await Promise.all([
                this.fetchCalendarMonth(requestedMonth),
                this.fetchHolidaysForRange(holidayFromTo.from, holidayFromTo.to),
            ]);

            if (loadSequence !== this.calendarLoadSequence || requestedMonth !== this.month) {
                return;
            }

            let bookings = response.data.bookings || [];

            // Week view can span adjacent months (e.g. Jul 27 - Aug 2).
            // Fetch those months too so bookings appear immediately without
            // requiring extra arrow navigation.
            if (this.isWeekCalView() && this.weekStart) {
                const visibleMonths = new Set(this.getWeekDays(this.weekStart).map((date) => date.slice(0, 7)));
                visibleMonths.delete(this.month);

                if (visibleMonths.size > 0) {
                    const extraResponses = await Promise.all(
                        Array.from(visibleMonths).map(async (month) => {
                            const extraResponse = await this.fetchCalendarMonth(month);
                            return extraResponse.data.bookings || [];
                        })
                    );

                    const merged = [...bookings, ...extraResponses.flat()];
                    const seen = new Set();
                    bookings = merged.filter((booking) => {
                        const key = booking.id ?? `${booking.date}-${booking.time}-${booking.customer_name}`;
                        if (seen.has(key)) {
                            return false;
                        }

                        seen.add(key);
                        return true;
                    });
                }
            }

            this.holidaysByDate = holidays;
            this.holidaysRangeKey = `${holidayFromTo.from}_${holidayFromTo.to}`;

            this.lastCalendarBookings = bookings;
            setCalendarBookings(bookings);

            const html = this.buildCalendarHtml(bookings);

            if (!canSlide) {
                this.updateMonthLabel();
            }

            if (canSlide) {
                await this.slideCalendarTo(html, direction);
            } else {
                this.grid.classList.remove("tr-calendar-grid--loading");
                this.grid.innerHTML = html;
            }

            this.lastBookings = bookings;
            this.renderCalendarLegend(bookings);
            this.refreshAgendaPanel?.();
            this.applyCrmSearch?.();
            if (this.isWeekCalView()) {
                this.renderDayView();
            }

            if (this.calendarFocusBookingId && !this.calendarFocusHandled) {
                const focused = this.grid?.querySelector(
                    `.tr-cal-event[data-booking-id="${CSS.escape(String(this.calendarFocusBookingId))}"]`
                );

                this.calendarFocusHandled = true;

                if (focused) {
                    focused.classList.add("tr-cal-event--focused");
                    focused.scrollIntoView({ block: "nearest", behavior: "smooth" });
                    window.setTimeout(() => focused.click(), 0);
                } else {
                    // Booking may be TBA / filtered out of the month grid — still open details.
                    openBookingPreviewById(this.calendarFocusBookingId);
                }
            }

            this.prefetchAdjacentMonths(requestedMonth);
        } finally {
            if (loadSequence === this.calendarLoadSequence) {
                this.gridViewport?.classList.remove("tr-calendar-grid-viewport--loading");
                this.grid?.classList.remove("tr-calendar-grid--loading");
            }
        }
    }

    calendarCacheKey(month) {
        return `${month}?${this.getFilterParams().toString()}`;
    }

    fetchCalendarMonth(month) {
        const key = this.calendarCacheKey(month);

        if (!this.calendarDataCache.has(key)) {
            const params = this.getFilterParams();
            params.set("month", month);
            const request = axios
                .get(`${this.calendarUrl}?${params.toString()}`)
                .catch((error) => {
                    this.calendarDataCache.delete(key);
                    throw error;
                });

            this.calendarDataCache.set(key, request);
        }

        return this.calendarDataCache.get(key);
    }

    calendarRangeForMonth(monthValue) {
        if (this.isWeekCalView() && this.weekStart) {
            return { from: this.weekStart, to: this.addDays(this.weekStart, 6) };
        }

        const [year, month] = monthValue.split("-").map(Number);
        const lastDay = new Date(year, month, 0).getDate();

        return {
            from: `${monthValue}-01`,
            to: `${monthValue}-${String(lastDay).padStart(2, "0")}`,
        };
    }

    prefetchAdjacentMonths(monthValue) {
        const run = () => {
            [-1, 1].forEach((offset) => {
                const [year, month] = monthValue.split("-").map(Number);
                const date = new Date(year, month - 1 + offset, 1);
                const adjacentMonth = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}`;
                const range = this.calendarRangeForMonth(adjacentMonth);

                this.fetchCalendarMonth(adjacentMonth).catch(() => {});
                this.fetchHolidaysForRange(range.from, range.to).catch(() => {});
            });
        };

        if ("requestIdleCallback" in window) {
            window.requestIdleCallback(run, { timeout: 1200 });
        } else {
            window.setTimeout(run, 0);
        }
    }

    async slideCalendarTo(html, direction) {
        const track = this.gridTrack;
        const outgoing = this.grid;

        if (!track || !outgoing) {
            outgoing.innerHTML = html;

            return;
        }

        if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
            outgoing.innerHTML = html;

            return;
        }

        const incoming = document.createElement("div");
        incoming.className = "tr-calendar-grid";
        incoming.innerHTML = html;
        incoming.setAttribute("aria-hidden", "true");

        this.calendarAnimating = true;
        this.setMonthNavDisabled(true);

        try {
            track.classList.add("tr-calendar-grid-track--no-transition");
            track.classList.remove("tr-calendar-grid-track--offset-half", "tr-calendar-grid-track--duo");

            if (direction > 0) {
                track.appendChild(incoming);
            } else {
                track.insertBefore(incoming, outgoing);
            }

            track.classList.add("tr-calendar-grid-track--duo");

            if (direction < 0) {
                track.classList.add("tr-calendar-grid-track--offset-half");
            }

            await this.nextAnimationFrame();

            track.classList.remove("tr-calendar-grid-track--no-transition");

            if (direction > 0) {
                track.classList.add("tr-calendar-grid-track--offset-half");
            } else {
                track.classList.remove("tr-calendar-grid-track--offset-half");
            }

            await this.waitForTransition(track);

            track.classList.add("tr-calendar-grid-track--no-transition");
            track.classList.remove("tr-calendar-grid-track--duo", "tr-calendar-grid-track--offset-half");
            incoming.id = "tr-calendar-grid";
            incoming.removeAttribute("aria-hidden");
            outgoing.remove();
            track.appendChild(incoming);
            this.grid = incoming;

            await this.nextAnimationFrame();
            track.classList.remove("tr-calendar-grid-track--no-transition");
        } finally {
            this.calendarAnimating = false;
            this.setMonthNavDisabled(false);
        }
    }

    nextAnimationFrame() {
        return new Promise((resolve) => {
            requestAnimationFrame(() => requestAnimationFrame(resolve));
        });
    }

    waitForTransition(element) {
        return new Promise((resolve) => {
            const finish = () => resolve();

            element.addEventListener("transitionend", finish, { once: true });
            window.setTimeout(finish, 220);
        });
    }

    updateMonthLabel() {
        if (!this.monthLabel) {
            return;
        }

        const [year, month] = this.month.split("-").map(Number);
        const locale = this.calendarLocale();
        const fmt = (d) => d.toLocaleDateString(locale, { month: "short", year: "numeric" });

        const current = new Date(year, month - 1, 1);

        this.monthLabel.textContent = fmt(current);

        const siblings = [
            { el: this.monthPrev2Label, offset: -2 },
            { el: this.monthPrevLabel, offset: -1 },
            { el: this.monthNextLabel, offset: 1 },
            { el: this.monthNext2Label, offset: 2 },
        ];

        for (const { el, offset } of siblings) {
            if (el) {
                const d = new Date(year, month - 1 + offset, 1);
                el.textContent = fmt(d);
            }
        }
    }

    renderCalendarLegend(bookings) {
        const legend = document.getElementById("tr-calendar-legend");

        if (!legend) {
            return;
        }

        if (this.beauticianId) {
            legend.hidden = true;
            legend.innerHTML = "";

            return;
        }

        const beauticians = collectBeauticiansFromBookings(bookings);
        const label = this.root.dataset.calendarLegendLabel || "Beauticians";

        if (!beauticians.length) {
            legend.hidden = true;
            legend.innerHTML = "";

            return;
        }

        legend.hidden = false;
        legend.innerHTML = buildCalendarLegendHtml(beauticians, label);
    }

    renderCalendar(bookings) {
        if (this.grid) {
            this.grid.innerHTML = this.buildCalendarHtml(bookings);
        }

        this.updateMonthLabel();
    }

    buildCalendarHtml(bookings) {
        const [year, month] = this.month.split("-").map(Number);
        const firstDay = new Date(year, month - 1, 1);
        const lastDay = new Date(year, month, 0);
        const startOffset = (firstDay.getDay() + 6) % 7;
        const daysInMonth = lastDay.getDate();

        const byDate = bookings.reduce((acc, booking) => {
            if (!acc[booking.date]) {
                acc[booking.date] = [];
            }

            acc[booking.date].push(booking);

            return acc;
        }, {});

        const cells = [];
        const todayStr = TreatmentReservationsApp.localDateKey();

                const malaysiaPublicHolidays = this.holidaysByDate || {};

        for (let i = 0; i < startOffset; i++) {
            cells.push('<div class="tr-cal-day tr-cal-day--muted"></div>');
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
            const dayBookings = byDate[dateStr] || [];
            const isToday = dateStr === todayStr;
            const holiday = malaysiaPublicHolidays[dateStr];
            const dayIndex = (startOffset + day - 1) % 7;
            const isWeekend = dayIndex >= 5;
            const dayClasses = [
                "tr-cal-day",
                isToday ? "tr-cal-day--today" : "",
                isWeekend ? "tr-cal-day--weekend" : "",
                dayBookings.length ? "tr-cal-day--has-events" : "",
                holiday ? "tr-cal-day--holiday" : "",
            ].filter(Boolean).join(" ");

            const events = this.compactCalendar
                ? this.renderCompactDayContent(dayBookings)
                : dayBookings.map((booking) => this.renderCalendarEvent(booking)).join("");

            const countBadge = !this.compactCalendar && dayBookings.length
                ? `<span class="tr-cal-day-count">${dayBookings.length}</span>`
                : "";

            cells.push(`
                <div class="${dayClasses}" data-date="${dateStr}" role="button" tabindex="0" aria-label="${dayBookings.length} bookings">
                    <div class="tr-cal-day-head">
                        <div class="tr-cal-day-num ${isToday ? "tr-cal-day-num--today" : ""}">${day}</div>
                        ${countBadge}
                    </div>
                    ${holiday ? (() => {
                        const safeLabel = TreatmentReservationsApp.escapeHtml(holiday.label);
                        const kindClass = String(holiday.kind || "other").replace(/[^a-z_]/gi, "");
                        const statesSummary = Array.isArray(holiday.states) && holiday.states.length
                            ? TreatmentReservationsApp.escapeHtml(holiday.states.join(", "))
                            : "";
                        const holidayColor = TreatmentReservationsApp.safeCssColor(holiday.color);
                        const colorAttr = holidayColor ? ` style="--holiday-color:${holidayColor}"` : "";
                        return `<div class="tr-cal-holiday-badge tr-cal-holiday-badge--${kindClass}"${colorAttr} title="${safeLabel}${statesSummary ? " · " + statesSummary : ""}"><span class="tr-cal-holiday-badge__title">${safeLabel}</span>${statesSummary ? `<span class="tr-cal-holiday-badge__states">${statesSummary}</span>` : ""}</div>`;
                    })() : ""}
                    <div class="tr-cal-day-events">${events || (this.compactCalendar ? "" : `<span class="tr-cal-empty">${this.emptyLabel()}</span>`)}</div>
                </div>
            `);
        }

        return cells.join("");
    }

    renderCompactDayContent(dayBookings) {
        const byBeautician = new Map();

        dayBookings.forEach((booking) => {
            const key = String(booking.beautician_id || booking.beautician_name || "unknown");
            const existing = byBeautician.get(key) || {
                count: 0,
                color: "#6366f1",
                name: "—",
            };

            existing.count += 1;

            if (booking.beautician_color) {
                existing.color = booking.beautician_color;
            }

            if (booking.beautician_name) {
                existing.name = booking.beautician_name;
            }

            byBeautician.set(key, existing);
        });

        return Array.from(byBeautician.values())
            .map(({ count, color, name }) => {
                const safeColor = TreatmentReservationsApp.safeCssColor(color) || "#6366f1";
                const title = TreatmentReservationsApp.escapeHtml(name);

                return `<span class="tr-cal-dot" title="${title}" style="background:${safeColor}">${count}</span>`;
            })
            .join("");
    }

    renderCalendarEvent(booking) {
        const showBeautician = !this.beauticianId || !!this.portalBeauticianId;
        const portalBeauticianId = this.portalBeauticianId || null;

        return buildCalendarEventHtml(booking, {
            showBeautician,
            clickable: bookingAllowsDetail(booking, portalBeauticianId),
            isOwn: bookingIsOwnForPortal(booking, portalBeauticianId),
        });
    }

    emptyLabel() {
        return this.emptyCalendarLabel;
    }

    addDays(dateStr, days) {
        const d = TreatmentReservationsApp.parseLocalDate(dateStr) || new Date();
        d.setDate(d.getDate() + days);

        return TreatmentReservationsApp.localDateKey(d);
    }

    async ensureHolidaysForRange(from, to) {
        const key = `${from}_${to}`;

        if (this.holidaysRangeKey === key) {
            return;
        }

        this.holidaysByDate = await this.fetchHolidaysForRange(from, to);
        this.holidaysRangeKey = key;
    }

    fetchHolidaysForRange(from, to) {
        if (!this.holidaysRangeUrl) {
            return Promise.resolve({});
        }

        const key = `${from}_${to}`;

        if (this.holidayDataCache.has(key)) {
            return this.holidayDataCache.get(key);
        }

        const url = `${this.holidaysRangeUrl}?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`;
        const request = axios
            .get(url)
            .then((response) => response.data?.holidays || {})
            .catch(() => ({}));

        this.holidayDataCache.set(key, request);

        return request;
    }

    initKanban() {
        this.loadKanban();
    }

    getKanbanFilterParams() {
        const params = new URLSearchParams();

        if (this.beauticianId) {
            params.set("beautician_id", this.beauticianId);
        }

        return params;
    }

    async loadKanban() {
        const params = this.getKanbanFilterParams();
        const response = await axios.get(`${this.kanbanUrl}?${params.toString()}`);
        const columns = response.data.columns || {};
        const allBookings = Object.values(columns).flat();

        setKanbanBookings(allBookings);

        Object.keys(columns).forEach((status) => {
            const container = document.getElementById(`tr-kanban-${status}`);
            const cards = columns[status] || [];

            container.innerHTML = cards.length
                ? cards.map((card) => this.renderKanbanCard(card)).join("")
                : `<p class="tr-kanban-empty">${this.kanbanEmptyLabel()}</p>`;

            const countEl = document.querySelector(`[data-count="${status}"]`);

            if (countEl) {
                countEl.textContent = cards.length;
            }

            this.makeSortable(container, status);
        });
    }

    kanbanEmptyLabel() {
        return document.querySelector(".tr-kanban-column")?.dataset?.empty || "No tasks";
    }

    renderKanbanCard(card) {
        const template = document.getElementById("tr-kanban-card-template");
        const node = template.content.cloneNode(true);
        const el = node.querySelector(".tr-kanban-card");

        el.dataset.id = card.id;
        el.dataset.bookingId = card.id;
        const portalBeauticianId = this.portalBeauticianId || null;
        const canOpen = bookingAllowsDetail(card, portalBeauticianId);
        const isOwn = bookingIsOwnForPortal(card, portalBeauticianId);

        if (canOpen) {
            el.classList.add("tr-kanban-card--clickable");
            el.setAttribute("role", "button");
            el.setAttribute("tabindex", "0");
        }

        if (!isOwn) {
            el.classList.add("tr-kanban-card--others");
            el.removeAttribute("draggable");
        }
        el.querySelector(".tr-kanban-card-accent").removeAttribute("style");
        el.querySelector(".tr-kanban-card-customer").textContent = card.customer_name;

        const productName = el.querySelector(".tr-kanban-card-product__name");
        const treatmentLine = el.querySelector(".tr-kanban-card-treatment-line");
        const treatmentValue = el.querySelector(".tr-kanban-card-treatment-line__value");
        const displayName = card.product_name || card.treatment_name || "—";

        if (productName) {
            productName.textContent = displayName;
        } else {
            el.querySelector(".tr-kanban-card-treatment").textContent = displayName;
        }

        if (treatmentLine && treatmentValue) {
            const selection = (card.treatment_selection || "").trim();

            if (selection !== "") {
                treatmentValue.textContent = selection;
                treatmentLine.hidden = false;
            } else {
                treatmentValue.textContent = "";
                treatmentLine.hidden = true;
            }
        }
        el.querySelector(".tr-kanban-card-date span").textContent = card.appointment_date || "";
        el.querySelector(".tr-kanban-card-time-slot span").textContent = card.appointment_time || "";
        renderKanbanBeautician(el, card);

        if (card.order_url && this.root.id !== "tr-portal-app") {
            const link = el.querySelector(".tr-kanban-card-link");

            link.href = card.order_url;
            link.hidden = false;
        }

        const wrapper = document.createElement("div");

        wrapper.appendChild(node);

        return wrapper.innerHTML;
    }

    makeSortable(container, status) {
        if (!window.Sortable) {
            return;
        }

        if (container.dataset.sortableInit) {
            return;
        }

        container.dataset.sortableInit = "1";

        Sortable.create(container, {
            group: "tr-kanban",
            animation: 150,
            draggable: ".tr-kanban-card",
            filter: ".tr-kanban-card--others",
            onMove: (evt) => !evt.dragged?.classList.contains("tr-kanban-card--others"),
            onEnd: (evt) => this.handleKanbanMove(evt),
        });
    }

    async handleKanbanMove(evt) {
        const card = evt.item;
        const bookingId = card.dataset.id;
        const newStatus = evt.to.closest(".tr-kanban-column")?.dataset?.status;
        const oldStatus = evt.from.closest(".tr-kanban-column")?.dataset?.status;

        if (!bookingId || !newStatus || newStatus === oldStatus) {
            return;
        }

        const url = this.statusUrlTemplate.replace("__ID__", bookingId);

        try {
            const response = await axios.patch(url, { status: newStatus });
            card.querySelector(".tr-kanban-card-accent")?.removeAttribute("style");

            if (response.data?.booking) {
                upsertBooking(response.data.booking);
            }

            this.updateKanbanCounts();
        } catch (error) {
            evt.from.appendChild(card);
            window.notify?.error?.("Failed to update status") || alert("Failed to update status");
        }
    }

    updateKanbanCounts() {
        ["pending", "in_progress", "completed"].forEach((status) => {
            const container = document.getElementById(`tr-kanban-${status}`);
            const countEl = document.querySelector(`[data-count="${status}"]`);

            if (container && countEl) {
                countEl.textContent = container.querySelectorAll(".tr-kanban-card").length;
            }
        });
    }
}

const root = document.getElementById("tr-reservations-app");

if (root) {
    const reservationsApp = new TreatmentReservationsApp(root);
    window.TRResolveBooking = resolveBooking;
    initCrmDashboard(reservationsApp);
    initCustomerProfileDrawer();
    initTbaScheduleActions();
    initCalendarBookingDrop(reservationsApp);
}

const beauticianScheduleRoot = document.getElementById("tr-beautician-schedule-app");

if (beauticianScheduleRoot) {
    new TreatmentReservationsApp(beauticianScheduleRoot);
}

const portalRoot = document.getElementById("tr-portal-app");

if (portalRoot?.dataset.initialBookings) {
    try {
        setKanbanBookings(JSON.parse(portalRoot.dataset.initialBookings));
    } catch (error) {
        // ignore invalid seed payload
    }
}

if (portalRoot) {
    const portalApp = new TreatmentReservationsApp(portalRoot);
    initCrmDashboard(portalApp);
    initCustomerProfileDrawer();
    initCalendarBookingDrop(portalApp);
    initTbaScheduleActions();
}

function buildCalendarPreviewLabels(root) {
    let workLog = {};
    let scheduling = {};

    try {
        workLog = JSON.parse(root.dataset.calWorkLogLabels || "{}");
    } catch (error) {
        workLog = {};
    }

    try {
        scheduling = JSON.parse(root.dataset.rescheduleLabels || "{}");
    } catch (error) {
        scheduling = {};
    }

    return {
        previewTitle: root.dataset.calPreviewTitle || "Appointment details",
        detailsLoadFailed: root.dataset.calPreviewDetailsLoadFailed || "Failed to load appointment details",
        orderEyebrow: scheduling.order_eyebrow || "Order #:order",
        date: root.dataset.calPreviewDate || "Date",
        time: root.dataset.calPreviewTime || "Time",
        customer: root.dataset.calPreviewCustomer || "Customer",
        treatment: root.dataset.calPreviewTreatment || "Treatment",
        category: root.dataset.calPreviewCategory || "Category",
        viewOrder: root.dataset.calPreviewViewOrder || "View order",
        phone: root.dataset.calPreviewPhone || "Phone",
        email: root.dataset.calPreviewEmail || "Email",
        orderNotes: root.dataset.calPreviewOrderNotes || "Order notes",
        beauticianNotes: root.dataset.calPreviewBeauticianNotes || "Beautician notes",
        saveNotes: root.dataset.calPreviewSaveNotes || "Save notes",
        savingNotes: root.dataset.calPreviewSavingNotes || "Saving…",
        notesSaved: root.dataset.calPreviewNotesSaved || "Notes saved",
        notesSaveFailed: root.dataset.calPreviewNotesSaveFailed || "Failed to save notes",
        whatsappCustomer: root.dataset.calPreviewWhatsappCustomer || "WhatsApp customer",
        consultation: root.dataset.calPreviewConsultation || "Send consultation form",
        consultationPreparing: root.dataset.calPreviewConsultationPreparing || "Preparing…",
        consultationReady: root.dataset.calPreviewConsultationReady || "Consultation link is ready",
        consultationFailed: root.dataset.calPreviewConsultationFailed || "Failed to prepare consultation form",
        whatsappSending: root.dataset.calPreviewWhatsappSending || "Sending…",
        whatsappSent: root.dataset.calPreviewWhatsappSent || "WhatsApp message sent",
        whatsappFailed: root.dataset.calPreviewWhatsappFailed || "Failed to send WhatsApp message",
        whatsappNotConfigured: root.dataset.calPreviewWhatsappNotConfigured || "OneSender WhatsApp API is not configured.",
        activityTitle: root.dataset.calPreviewActivityTitle || "Activity log",
        activityShow: root.dataset.calPreviewActivityShow || "Show",
        activityHide: root.dataset.calPreviewActivityHide || "Hide",
        statusPending: root.dataset.calStatusPending || "Pending",
        statusInProgress: root.dataset.calStatusInProgress || "In Progress",
        statusCompleted: root.dataset.calStatusCompleted || "Completed",
        editManual: root.dataset.calPreviewEditManual || "Edit appointment",
        cancelManual: root.dataset.calPreviewCancelManual || "Cancel appointment",
        cancelManualConfirm: root.dataset.calPreviewCancelManualConfirm || "Cancel this manual appointment?",
        cancelManualSuccess: root.dataset.calPreviewCancelManualSuccess || "Appointment canceled",
        cancelManualFailed: root.dataset.calPreviewCancelManualFailed || "Failed to cancel appointment",
        viewProfile: root.dataset.calPreviewViewProfile || "View profile",
        sendReminder: root.dataset.calPreviewSendReminder || "Send reminder",
        resendReminder: root.dataset.calPreviewResendReminder || "Resend reminder",
        reminderSent: root.dataset.calPreviewReminderSent || "Reminder sent",
        reminderDue: root.dataset.calPreviewReminderDue || "Due for reminder",
        reminderSending: root.dataset.calPreviewReminderSending || "Sending reminder…",
        reminderFailed: root.dataset.calPreviewReminderFailed || "Failed to send reminder",
        whatsappReminderCustomer: root.dataset.calPreviewWhatsappReminderCustomer || "WhatsApp reminder · Customer",
        whatsappReminderBeautician: root.dataset.calPreviewWhatsappReminderBeautician || "WhatsApp reminder · Beautician",
        resendBeauticianReminder: root.dataset.calPreviewResendBeauticianReminder || "Resend beautician reminder",
        beauticianReminderSent: root.dataset.calPreviewBeauticianReminderSent || "Beautician reminder sent",
        beauticianReminderFailed: root.dataset.calPreviewBeauticianReminderFailed || "Failed to send beautician reminder",
        duration: root.dataset.calPreviewDuration || "Duration",
        durationMinutes: root.dataset.calPreviewDurationMinutes || ":count min",
        durationHour: root.dataset.calPreviewDurationHour || ":count hour",
        durationHours: root.dataset.calPreviewDurationHours || ":count hours",
        durationSession: root.dataset.calPreviewDurationSession || ":duration session",
        durationBadgeMinutes: root.dataset.calPreviewDurationBadgeMinutes || ":countMin Session",
        durationBadgeHour: root.dataset.calPreviewDurationBadgeHour || ":countHr Session",
        durationBadgeHours: root.dataset.calPreviewDurationBadgeHours || ":countHrs Session",
        durationBadgeHoursMinutes: root.dataset.calPreviewDurationBadgeHoursMinutes || ":hoursHrs :minutesMin Session",
        payment: root.dataset.calPreviewPayment || "Payment",
        paymentReceipt: root.dataset.calPreviewPaymentReceipt || "Payment receipt",
        viewReceipt: root.dataset.calPreviewViewReceipt || "View receipt",
        total: root.dataset.calPreviewTotal || "Total",
        source: root.dataset.calPreviewSource || "Source",
        branch: root.dataset.calPreviewBranch || "Branch",
        bookingId: root.dataset.calPreviewBookingId || "Ref",
        bookingIdTitle: root.dataset.calPreviewBookingIdTitle || "Treatment reference — quote this when contacting the clinic",
        session: root.dataset.calPreviewSession || "Session",
        status: root.dataset.calPreviewStatus || "Job sheet status",
        statusTitle: root.dataset.calPreviewStatusTitle || root.dataset.calPreviewStatus || "Job sheet status",
        reschedule: root.dataset.calPreviewReschedule || "Reschedule",
        actionProfileShort: root.dataset.calPreviewActionProfileShort || "Profile",
        actionCustomerShort: root.dataset.calPreviewActionCustomerShort || "Remind customer",
        actionConsultationShort: root.dataset.calPreviewActionConsultationShort || "Send Consult Form",
        actionBeauticianShort: root.dataset.calPreviewActionBeauticianShort || "Remind beautician",
        actionRescheduleShort: root.dataset.calPreviewActionRescheduleShort || "Reschedule",
        scheduleTba: root.dataset.calPreviewScheduleTba || "Schedule slot",
        statusUpdateFailed: root.dataset.calPreviewStatusUpdateFailed || "Failed to update status",
        sectionSchedule: root.dataset.calPreviewSectionSchedule || "Schedule",
        sectionCustomer: root.dataset.calPreviewSectionCustomer || "Customer",
        sectionTreatment: root.dataset.calPreviewSectionTreatment || "Treatment & payment",
        sectionNotes: root.dataset.calPreviewSectionNotes || "Notes",
        sectionStaff: root.dataset.calPreviewSectionStaff || "Specialist",
        workLog,
    };
}

function buildCalendarPreviewOptions(root) {
    const manualBookingOptions = root.dataset.manualBookingEdit === "1"
        ? {
              tbaScheduleEnabled: true,
            manualBookingEditEnabled: true,
              manualBookingCancelUrlTemplate: root.dataset.manualBookingCancelUrl || "",
              manualBookingModalSelector:
                  root.id === "tr-portal-app" ? "#tr-portal-manual-booking-modal" : "#tr-manual-booking-modal",
          }
        : {};

    if (root.id === "tr-portal-app") {
        const canEdit = root.dataset.crmCanEdit !== "0";

        return {
            hideOrderLink: true,
            hideBeautician: false,
            showWhatsApp: true,
            portalGenericWhatsApp: false,
            portalBeauticianId: root.dataset.portalBeauticianId || "",
            canSendNotifications: canEdit,
            crmCanEdit: canEdit,
            statusUrlTemplate: root.dataset.statusUrl || "",
            allowBeauticianNotes: true,
            notesUrlTemplate: root.dataset.notesUrl || "",
            whatsappConfigured: root.dataset.whatsappConfigured === "1",
            whatsappUrlTemplate: root.dataset.whatsappUrl || "",
            consultationUrlTemplate: root.dataset.consultationUrl || "",
            rescheduleUrlTemplate: root.dataset.rescheduleUrl || "",
            reminderUrlTemplate: root.dataset.reminderUrl || "",
            beauticianReminderUrlTemplate: root.dataset.beauticianReminderUrl || "",
            detailsUrlTemplate: root.dataset.calendarDetailsUrl || "",
            ...manualBookingOptions,
        };
    }

    if (root.id === "tr-reservations-app") {
        const canEdit = root.dataset.crmCanEdit === "1";
        const portalBeauticianId = root.dataset.portalBeauticianId || "";

        return {
            showActivityLog: true,
            showWhatsApp: true,
            canSendNotifications: canEdit,
            whatsappConfigured: root.dataset.whatsappConfigured === "1",
            whatsappUrlTemplate: root.dataset.whatsappUrl || "",
            consultationUrlTemplate: root.dataset.consultationUrl || "",
            rescheduleUrlTemplate: root.dataset.rescheduleUrl || "",
            reminderUrlTemplate: root.dataset.reminderUrl || "",
            beauticianReminderUrlTemplate: root.dataset.beauticianReminderUrl || "",
            statusUrlTemplate: root.dataset.statusUrl || "",
            detailsUrlTemplate: root.dataset.calendarDetailsUrl || "",
            crmCanEdit: canEdit,
            allowBeauticianNotes: canEdit && Boolean(root.dataset.notesUrl),
            notesUrlTemplate: root.dataset.notesUrl || "",
            portalBeauticianId,
            ...manualBookingOptions,
        };
    }

    return manualBookingOptions;
}

const calendarPreviewRoot =
    document.getElementById("tr-reservations-app") ||
    document.getElementById("tr-beautician-schedule-app") ||
    document.getElementById("tr-portal-app");

if (calendarPreviewRoot) {
    initCalendarEventPreview(
        resolveBooking,
        buildCalendarPreviewLabels(calendarPreviewRoot),
        buildCalendarPreviewOptions(calendarPreviewRoot)
    );
}

initBeauticianAvatarLightbox();
initTreatmentAnalytics();
initAdminPreviewTimer();
