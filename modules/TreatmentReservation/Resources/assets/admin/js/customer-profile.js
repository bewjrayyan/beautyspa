import axios from "axios";
import {
    closeCalendarEventPreview,
    escapeHtml,
    openBookingPreviewById,
    upsertBooking,
} from "./kanban-helpers.js";

let lastProfileQuery = {};

function getProfileRoot() {
    return document.getElementById("tr-crm-customer-profile");
}

function getProfileLabels() {
    const root = getProfileRoot();

    return {
        title: root?.dataset.profileTitle || "Customer profile",
        loading: root?.dataset.profileLoading || "Loading profile…",
        failed: root?.dataset.profileFailed || "Failed to load customer profile",
        visits: root?.dataset.profileVisits || "Visit history",
        upcoming: root?.dataset.profileUpcoming || "Upcoming appointments",
        reminders: root?.dataset.profileReminders || "Reminders",
        noVisits: root?.dataset.profileNoVisits || "No completed visits yet",
        noUpcoming: root?.dataset.profileNoUpcoming || "No upcoming appointments",
        viewUser: root?.dataset.profileViewUser || "View customer account",
        openBooking: root?.dataset.profileOpenBooking || "Open appointment",
        sendReminder: root?.dataset.profileSendReminder || "Send reminder",
        resendReminder: root?.dataset.profileResendReminder || "Resend reminder",
        reminderSent: root?.dataset.profileReminderSent || "Reminder sent",
        reminderDue: root?.dataset.profileReminderDue || "Due for reminder",
        reminderSending: root?.dataset.profileReminderSending || "Sending reminder…",
        reminderFailed: root?.dataset.profileReminderFailed || "Failed to send reminder",
        reminderSuccess: root?.dataset.profileReminderSuccess || "Reminder sent",
    };
}

function customerInitials(name) {
    const parts = String(name || "")
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    if (!parts.length) {
        return "?";
    }

    if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase();
    }

    return `${parts[0][0] || ""}${parts[parts.length - 1][0] || ""}`.toUpperCase();
}

function statusClass(status) {
    const key = String(status || "")
        .trim()
        .toLowerCase()
        .replace(/\s+/g, "_");

    if (["pending", "in_progress", "completed", "canceled", "cancelled"].includes(key)) {
        return key === "cancelled" ? "canceled" : key;
    }

    return "pending";
}

function sectionHeading(label, count, { icon = "fa-calendar", modifier = "" } = {}) {
    const countValue = Number.isFinite(count) ? Math.max(0, count) : 0;
    const modifierClass = modifier
        ? ` tr-crm-customer-profile__section-head--${escapeHtml(modifier)}`
        : "";

    return `
        <h5 class="tr-crm-customer-profile__section-head${modifierClass}">
            <span class="tr-crm-customer-profile__section-label">
                <i class="fa ${escapeHtml(icon)}" aria-hidden="true"></i>
                <span>${escapeHtml(label)}</span>
            </span>
            <span class="tr-crm-customer-profile__section-count" aria-label="${escapeHtml(String(countValue))}">${escapeHtml(String(countValue))}</span>
        </h5>`;
}

function renderBookingList(items, emptyLabel, { showReminders = false } = {}) {
    if (!Array.isArray(items) || items.length === 0) {
        return `<p class="tr-crm-customer-profile__empty">${escapeHtml(emptyLabel)}</p>`;
    }

    const labels = getProfileLabels();

    return `
        <ul class="tr-crm-customer-profile__bookings">
            ${items.map((item) => {
                const status = statusClass(item.status);
                const reminderBadge = item.reminder_sent
                    ? `<span class="tr-crm-customer-profile__reminder-badge tr-crm-customer-profile__reminder-badge--sent">${escapeHtml(labels.reminderSent)}</span>`
                    : (item.reminder_due
                        ? `<span class="tr-crm-customer-profile__reminder-badge tr-crm-customer-profile__reminder-badge--due">${escapeHtml(labels.reminderDue)}</span>`
                        : "");
                const reminderBtn = showReminders && item.can_send_reminder
                    ? `<button
                        type="button"
                        class="tr-crm-customer-profile__reminder-btn"
                        data-send-reminder
                        data-booking-id="${escapeHtml(String(item.id))}"
                        data-resend="${item.reminder_sent ? "1" : "0"}"
                    >${escapeHtml(item.reminder_sent ? labels.resendReminder : labels.sendReminder)}</button>`
                    : "";

                return `
                <li
                    class="tr-crm-customer-profile__booking"
                    data-open-booking
                    data-booking-id="${escapeHtml(String(item.id))}"
                    title="${escapeHtml(labels.openBooking)}"
                >
                    <div class="tr-crm-customer-profile__booking-top">
                        <strong class="tr-crm-customer-profile__booking-title">${escapeHtml(item.treatment_name || "—")}</strong>
                        ${item.status_label
                            ? `<span class="tr-crm-customer-profile__status tr-crm-customer-profile__status--${escapeHtml(status)}">${escapeHtml(item.status_label)}</span>`
                            : ""}
                    </div>
                    <div class="tr-crm-customer-profile__booking-schedule">
                        <i class="fa fa-calendar-o" aria-hidden="true"></i>
                        <span>${escapeHtml(item.appointment_date || "—")} · ${escapeHtml(item.appointment_time || "—")}</span>
                    </div>
                    <div class="tr-crm-customer-profile__booking-foot">
                        <div class="tr-crm-customer-profile__booking-meta-left">
                            ${item.beautician_name
                                ? `<span class="tr-crm-customer-profile__booking-staff"><i class="fa fa-user" aria-hidden="true"></i> ${escapeHtml(item.beautician_name)}</span>`
                                : ""}
                            ${reminderBadge}
                        </div>
                        <div class="tr-crm-customer-profile__booking-meta-right">
                            ${item.total_formatted
                                ? `<span class="tr-crm-customer-profile__booking-price">${escapeHtml(item.total_formatted)}</span>`
                                : ""}
                            ${reminderBtn}
                        </div>
                    </div>
                </li>`;
            }).join("")}
        </ul>
    `;
}

function renderProfile(profile, labels) {
    const insights = [];
    const name = profile.customer_name || "—";
    const phoneHref = profile.customer_phone
        ? String(profile.customer_phone).replace(/[^\d+]/g, "")
        : "";

    if (profile.customer_history_label) {
        insights.push(`<span class="tr-crm-customer-profile__insight">${escapeHtml(profile.customer_history_label)}</span>`);
    }

    if (profile.purchase_count_label) {
        insights.push(`<span class="tr-crm-customer-profile__insight tr-crm-customer-profile__insight--purchases">${escapeHtml(profile.purchase_count_label)}</span>`);
    } else if (Number(profile.purchase_count) > 0) {
        insights.push(`<span class="tr-crm-customer-profile__insight tr-crm-customer-profile__insight--purchases">${escapeHtml(String(profile.purchase_count))}</span>`);
    }

    if (profile.loyalty_tier_name) {
        insights.push(`<span class="tr-crm-customer-profile__insight tr-crm-customer-profile__insight--loyalty"><i class="fa fa-star" aria-hidden="true"></i> ${escapeHtml(profile.loyalty_tier_name)}</span>`);
    }

    const upcoming = Array.isArray(profile.upcoming_bookings) ? profile.upcoming_bookings : [];
    const reminders = Array.isArray(profile.reminder_bookings) ? profile.reminder_bookings : [];
    const visits = Array.isArray(profile.visit_history) ? profile.visit_history : [];

    return `
        <div class="tr-crm-customer-profile__hero">
            <div class="tr-crm-customer-profile__hero-row">
                ${profile.customer_avatar_url
                    ? `<div class="tr-crm-customer-profile__avatar tr-crm-customer-profile__avatar--photo" aria-hidden="true"><img src="${escapeHtml(profile.customer_avatar_url)}" alt="" loading="lazy" decoding="async"></div>`
                    : `<div class="tr-crm-customer-profile__avatar" aria-hidden="true">${escapeHtml(customerInitials(name))}</div>`
                }
                <div class="tr-crm-customer-profile__hero-copy">
                    ${insights.length ? `<div class="tr-crm-customer-profile__insights">${insights.join("")}</div>` : ""}
                    <div class="tr-crm-customer-profile__contact">
                        ${profile.customer_phone
                            ? `<a class="tr-crm-customer-profile__contact-row" href="tel:${escapeHtml(phoneHref)}"><i class="fa fa-phone" aria-hidden="true"></i><span>${escapeHtml(profile.customer_phone)}</span></a>`
                            : ""}
                        ${profile.customer_email
                            ? `<a class="tr-crm-customer-profile__contact-row" href="mailto:${escapeHtml(profile.customer_email)}"><i class="fa fa-envelope-o" aria-hidden="true"></i><span>${escapeHtml(profile.customer_email)}</span></a>`
                            : ""}
                    </div>
                    ${profile.user_admin_url
                        ? `<a href="${escapeHtml(profile.user_admin_url)}" class="tr-crm-customer-profile__user-link" target="_blank" rel="noopener noreferrer"><i class="fa fa-external-link" aria-hidden="true"></i> ${escapeHtml(labels.viewUser)}</a>`
                        : ""}
                </div>
            </div>
        </div>

        <section class="tr-crm-customer-profile__section tr-crm-customer-profile__section--upcoming">
            ${sectionHeading(labels.upcoming, upcoming.length, { icon: "fa-calendar", modifier: "upcoming" })}
            ${renderBookingList(upcoming, labels.noUpcoming)}
        </section>

        <section class="tr-crm-customer-profile__section tr-crm-customer-profile__section--reminders">
            ${sectionHeading(labels.reminders, reminders.length, { icon: "fa-bell", modifier: "reminders" })}
            ${renderBookingList(reminders, labels.noUpcoming, { showReminders: true })}
        </section>

        <section class="tr-crm-customer-profile__section tr-crm-customer-profile__section--visits">
            ${sectionHeading(labels.visits, visits.length, { icon: "fa-history", modifier: "visits" })}
            ${renderBookingList(visits, labels.noVisits)}
        </section>
    `;
}

function setProfileLoading(isLoading) {
    const root = getProfileRoot();
    const loading = document.getElementById("tr-crm-customer-profile-loading");
    const content = document.getElementById("tr-crm-customer-profile-content");
    const body = document.getElementById("tr-crm-customer-profile-body");

    if (loading) {
        loading.hidden = !isLoading;
        loading.setAttribute("aria-hidden", isLoading ? "false" : "true");
    }

    if (content) {
        content.hidden = isLoading;
        content.setAttribute("aria-hidden", isLoading ? "true" : "false");
    }

    if (body) {
        body.setAttribute("aria-busy", isLoading ? "true" : "false");
    }

    root?.classList.toggle("tr-crm-customer-profile--loading", isLoading);
}

function openCustomerProfileDrawer() {
    const root = getProfileRoot();

    if (!root) {
        return;
    }

    // Blur any focused control inside appointment details before hiding it.
    // Otherwise aria-hidden on a still-focused Profile button can block the handoff.
    const preview = document.getElementById("tr-calendar-event-preview");
    const active = document.activeElement;

    if (active instanceof HTMLElement && preview?.contains(active)) {
        active.blur();
    }

    // Profile is opened from appointment details — dismiss that overlay so
    // CRM does not stack underneath (preview z-index is higher).
    closeCalendarEventPreview({ restoreFocus: false });

    root.hidden = false;
    root.setAttribute("aria-hidden", "false");
    document.body.classList.add("tr-crm-customer-profile-open");
    root.querySelector(".tr-crm-customer-profile__close")?.focus();
}

function closeCustomerProfileDrawer() {
    const root = getProfileRoot();

    if (!root) {
        return;
    }

    root.hidden = true;
    root.setAttribute("aria-hidden", "true");
    document.body.classList.remove("tr-crm-customer-profile-open");
    root.classList.remove("tr-crm-customer-profile--loading");

    const title = document.getElementById("tr-crm-customer-profile-title");
    const content = document.getElementById("tr-crm-customer-profile-content");

    if (title) {
        title.textContent = getProfileLabels().title;
    }

    if (content) {
        content.innerHTML = "";
        content.hidden = false;
    }

    setProfileLoading(false);
}

async function loadCustomerProfile({ bookingId = null, phone = null } = {}) {
    const root = getProfileRoot();
    const labels = getProfileLabels();
    const content = document.getElementById("tr-crm-customer-profile-content");
    const title = document.getElementById("tr-crm-customer-profile-title");

    if (!root?.dataset.profileUrl || !content) {
        return;
    }

    openCustomerProfileDrawer();

    lastProfileQuery = {
        bookingId: bookingId ? String(bookingId) : null,
        phone: phone || null,
    };

    if (title) {
        title.textContent = labels.title;
    }

    content.innerHTML = "";
    setProfileLoading(true);

    try {
        const params = new URLSearchParams();

        if (bookingId) {
            params.set("booking_id", String(bookingId));
        } else if (phone) {
            params.set("phone", phone);
        }

        const response = await axios.get(`${root.dataset.profileUrl}?${params.toString()}`);
        const profile = response.data?.profile;

        if (!profile) {
            throw new Error(labels.failed);
        }

        if (title) {
            title.textContent = profile.customer_name || labels.title;
        }

        content.innerHTML = renderProfile(profile, labels);
    } catch (error) {
        content.innerHTML = `<p class="tr-crm-customer-profile__error">${escapeHtml(error.response?.data?.message || labels.failed)}</p>`;
    } finally {
        setProfileLoading(false);
    }
}

async function sendReminderFromProfile(button) {
    const root = getProfileRoot();
    const labels = getProfileLabels();
    const bookingId = button.dataset.bookingId;
    const resend = button.dataset.resend === "1";
    const urlTemplate = root?.dataset.reminderUrlTemplate;

    if (!bookingId || !urlTemplate) {
        return;
    }

    const originalHtml = button.innerHTML;

    button.disabled = true;
    button.innerHTML = `<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> ${escapeHtml(labels.reminderSending)}`;

    try {
        const response = await axios.post(
            urlTemplate.replace("__ID__", bookingId),
            { resend }
        );

        if (response.data?.booking) {
            upsertBooking(response.data.booking);
            document.dispatchEvent(new CustomEvent("tr-crm-booking-updated", {
                detail: response.data.booking,
            }));
        }

        window.notify?.success?.(response.data?.message || labels.reminderSuccess) || alert(response.data?.message || labels.reminderSuccess);

        await loadCustomerProfile(lastProfileQuery);
    } catch (error) {
        const message = error.response?.data?.message || labels.reminderFailed;

        window.notify?.error?.(message) || alert(message);
        button.disabled = false;
        button.innerHTML = originalHtml;
    }
}

let customerProfileDrawerReady = false;

export function initCustomerProfileDrawer() {
    const root = getProfileRoot();

    if (!root || customerProfileDrawerReady) {
        return;
    }

    customerProfileDrawerReady = true;

    document.addEventListener("click", (event) => {
        const openTrigger = event.target.closest("[data-customer-profile]");

        if (openTrigger) {
            event.preventDefault();
            event.stopPropagation();

            loadCustomerProfile({
                bookingId: openTrigger.dataset.bookingId || openTrigger.dataset.customerProfile || null,
                phone: openTrigger.dataset.customerProfilePhone || null,
            });

            return;
        }

        const closeTrigger = event.target.closest("[data-close-customer-profile]");

        if (closeTrigger) {
            event.preventDefault();
            closeCustomerProfileDrawer();

            return;
        }

        const reminderButton = event.target.closest("[data-send-reminder]");

        if (reminderButton) {
            event.preventDefault();
            event.stopPropagation();
            sendReminderFromProfile(reminderButton);

            return;
        }

        const bookingRow = event.target.closest("[data-open-booking]");

        if (bookingRow?.dataset.bookingId && root.contains(bookingRow)) {
            event.preventDefault();
            event.stopPropagation();
            openBookingPreviewById(bookingRow.dataset.bookingId);
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !root.hidden) {
            closeCustomerProfileDrawer();
        }
    });

    document.addEventListener("tr-crm-close-customer-profile", () => {
        closeCustomerProfileDrawer();
    });
}

export { loadCustomerProfile, sendReminderFromProfile };
