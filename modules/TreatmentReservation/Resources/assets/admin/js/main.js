import {
    buildCalendarEventHtml,
    buildCalendarLegendHtml,
    collectBeauticiansFromBookings,
    initBeauticianAvatarLightbox,
    initCalendarEventPreview,
    renderKanbanBeautician,
    resolveBooking,
    setCalendarBookings,
    setKanbanBookings,
    upsertBooking,
} from "./kanban-helpers.js";
import { initTreatmentAnalytics } from "./analytics.js";
import { initCrmDashboard } from "./dashboard.js";
import "./portal-account.js";
import "./portal-availability.js";
import "./manual-booking.js";

const TR_KANBAN_STATUS_ACCENT = {
    pending: "#ea580c",
    in_progress: "#4338ca",
    completed: "#047857",
};

class TreatmentReservationsApp {
    static statusAccentColor(status) {
        return TR_KANBAN_STATUS_ACCENT[status] || "#94a3b8";
    }

    constructor(root) {
        this.root = root;
        this.activeView = root.dataset.activeView;
        this.calendarUrl = root.dataset.calendarUrl;
        this.kanbanUrl = root.dataset.kanbanUrl;
        this.statusUrlTemplate = root.dataset.statusUrl;
        this.month = root.dataset.initialMonth || new Date().toISOString().slice(0, 7);
        this.beauticianId = root.dataset.initialBeautician || "";
        this.categoryId = root.dataset.initialCategory || "";
        this.calendarInitialized = false;
        this.kanbanInitialized = false;
        this.lastCalendarBookings = [];

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

        return params;
    }

    initCalendar() {
        this.grid = document.getElementById("tr-calendar-grid");
        this.monthLabel = document.getElementById("tr-cal-month-label");
        this.monthInput = document.getElementById("tr-month");
        this.emptyCalendarLabel = this.root.dataset.calEmptyLabel || "";
        this.compactCalendar = !!document.querySelector("[data-crm-compact-calendar]");

        document.getElementById("tr-cal-prev")?.addEventListener("click", () => this.shiftMonth(-1));
        document.getElementById("tr-cal-next")?.addEventListener("click", () => this.shiftMonth(1));
        document.getElementById("tr-cal-today")?.addEventListener("click", () => {
            this.month = new Date().toISOString().slice(0, 7);
            this.syncMonthInput();
            this.loadCalendar();
        });

        this.loadCalendar();
    }

    syncMonthInput() {
        if (this.monthInput) {
            this.monthInput.value = this.month;
        }
    }

    shiftMonth(delta) {
        const [year, month] = this.month.split("-").map(Number);
        const date = new Date(year, month - 1 + delta, 1);
        this.month = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}`;
        this.syncMonthInput();
        this.loadCalendar();
    }

    async loadCalendar() {
        const params = this.getFilterParams();
        params.set("month", this.month);

        this.grid.classList.add("tr-calendar-grid--loading");
        this.grid.innerHTML = `
            <div class="tr-calendar-loading">
                <i class="fa fa-spinner fa-spin"></i>
                <span>Loading calendar…</span>
            </div>
        `;

        const response = await axios.get(`${this.calendarUrl}?${params.toString()}`);
        const bookings = response.data.bookings || [];

        this.lastCalendarBookings = bookings;
        setCalendarBookings(bookings);
        this.renderCalendar(bookings);
        this.renderCalendarLegend(bookings);
        this.grid.classList.remove("tr-calendar-grid--loading");
        this.refreshAgendaPanel?.();
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
        const [year, month] = this.month.split("-").map(Number);
        const firstDay = new Date(year, month - 1, 1);
        const lastDay = new Date(year, month, 0);
        const startOffset = (firstDay.getDay() + 6) % 7;
        const daysInMonth = lastDay.getDate();

        this.monthLabel.textContent = firstDay.toLocaleDateString(undefined, {
            month: "long",
            year: "numeric",
        });

        const byDate = bookings.reduce((acc, booking) => {
            if (!acc[booking.date]) {
                acc[booking.date] = [];
            }

            acc[booking.date].push(booking);

            return acc;
        }, {});

        const cells = [];
        const todayStr = new Date().toISOString().slice(0, 10);

        for (let i = 0; i < startOffset; i++) {
            cells.push('<div class="tr-cal-day tr-cal-day--muted"></div>');
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
            const dayBookings = byDate[dateStr] || [];
            const isToday = dateStr === todayStr;
            const dayIndex = (startOffset + day - 1) % 7;
            const isWeekend = dayIndex >= 5;
            const dayClasses = [
                "tr-cal-day",
                isToday ? "tr-cal-day--today" : "",
                isWeekend ? "tr-cal-day--weekend" : "",
                dayBookings.length ? "tr-cal-day--has-events" : "",
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
                    <div class="tr-cal-day-events">${events || (this.compactCalendar ? "" : `<span class="tr-cal-empty">${this.emptyLabel()}</span>`)}</div>
                </div>
            `);
        }

        this.grid.innerHTML = cells.join("");
    }

    renderCompactDayContent(dayBookings) {
        const counts = {
            pending: 0,
            in_progress: 0,
            completed: 0,
            canceled: 0,
        };

        dayBookings.forEach((booking) => {
            const status = booking.status || "pending";

            if (counts[status] !== undefined) {
                counts[status] += 1;
            }
        });

        return Object.entries(counts)
            .filter(([, count]) => count > 0)
            .map(([status, count]) => `<span class="tr-cal-dot tr-cal-dot--${status}" title="${status}">${count}</span>`)
            .join("");
    }

    renderCalendarEvent(booking) {
        return buildCalendarEventHtml(booking, {
            showBeautician: !this.beauticianId,
        });
    }

    emptyLabel() {
        return this.emptyCalendarLabel;
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
        el.classList.add("tr-kanban-card--clickable");
        el.setAttribute("role", "button");
        el.setAttribute("tabindex", "0");
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
    new TreatmentReservationsApp(portalRoot);
}

function buildCalendarPreviewLabels(root) {
    return {
        previewTitle: root.dataset.calPreviewTitle || "Appointment details",
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
        whatsappSending: root.dataset.calPreviewWhatsappSending || "Sending…",
        whatsappSent: root.dataset.calPreviewWhatsappSent || "WhatsApp message sent",
        whatsappFailed: root.dataset.calPreviewWhatsappFailed || "Failed to send WhatsApp message",
        whatsappNotConfigured: root.dataset.calPreviewWhatsappNotConfigured || "OneSender WhatsApp API is not configured.",
        activityTitle: root.dataset.calPreviewActivityTitle || "Activity log",
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
        payment: root.dataset.calPreviewPayment || "Payment",
        paymentReceipt: root.dataset.calPreviewPaymentReceipt || "Payment receipt",
        viewReceipt: root.dataset.calPreviewViewReceipt || "View receipt",
        total: root.dataset.calPreviewTotal || "Total",
        source: root.dataset.calPreviewSource || "Source",
        branch: root.dataset.calPreviewBranch || "Branch",
        bookingId: root.dataset.calPreviewBookingId || "Ref",
        session: root.dataset.calPreviewSession || "Session",
        status: root.dataset.calPreviewStatus || "Status",
        reschedule: root.dataset.calPreviewReschedule || "Reschedule",
        statusUpdateFailed: root.dataset.calPreviewStatusUpdateFailed || "Failed to update status",
        sectionSchedule: root.dataset.calPreviewSectionSchedule || "Schedule",
        sectionCustomer: root.dataset.calPreviewSectionCustomer || "Customer",
        sectionTreatment: root.dataset.calPreviewSectionTreatment || "Treatment & payment",
        sectionNotes: root.dataset.calPreviewSectionNotes || "Notes",
        sectionStaff: root.dataset.calPreviewSectionStaff || "Specialist",
    };
}

function buildCalendarPreviewOptions(root) {
    const manualBookingOptions = root.dataset.manualBookingEdit === "1"
        ? {
              manualBookingEditEnabled: true,
              manualBookingCancelUrlTemplate: root.dataset.manualBookingCancelUrl || "",
              manualBookingModalSelector:
                  root.id === "tr-portal-app" ? "#tr-portal-manual-booking-modal" : "#tr-manual-booking-modal",
          }
        : {};

    if (root.id === "tr-portal-app") {
        return {
            hideOrderLink: true,
            hideBeautician: true,
            showWhatsApp: true,
            portalGenericWhatsApp: true,
            canSendNotifications: root.dataset.crmCanEdit === "1",
            allowBeauticianNotes: true,
            notesUrlTemplate: root.dataset.notesUrl || "",
            whatsappUrlTemplate: root.dataset.whatsappUrl || "",
            reminderUrlTemplate: root.dataset.reminderUrl || "",
            beauticianReminderUrlTemplate: root.dataset.beauticianReminderUrl || "",
            ...manualBookingOptions,
        };
    }

    if (root.id === "tr-reservations-app") {
        const canEdit = root.dataset.crmCanEdit === "1";

        return {
            showActivityLog: true,
            showWhatsApp: true,
            canSendNotifications: canEdit,
            whatsappConfigured: root.dataset.whatsappConfigured === "1",
            whatsappUrlTemplate: root.dataset.whatsappUrl || "",
            reminderUrlTemplate: root.dataset.reminderUrl || "",
            beauticianReminderUrlTemplate: root.dataset.beauticianReminderUrl || "",
            statusUrlTemplate: root.dataset.statusUrl || "",
            crmCanEdit: canEdit,
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
