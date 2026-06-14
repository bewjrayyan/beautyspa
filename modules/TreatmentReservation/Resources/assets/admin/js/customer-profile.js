import axios from "axios";
import { escapeHtml, upsertBooking } from "./kanban-helpers.js";

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
        sendReminder: root?.dataset.profileSendReminder || "Send reminder",
        resendReminder: root?.dataset.profileResendReminder || "Resend reminder",
        reminderSent: root?.dataset.profileReminderSent || "Reminder sent",
        reminderDue: root?.dataset.profileReminderDue || "Due for reminder",
        reminderSending: root?.dataset.profileReminderSending || "Sending reminder…",
        reminderFailed: root?.dataset.profileReminderFailed || "Failed to send reminder",
        reminderSuccess: root?.dataset.profileReminderSuccess || "Reminder sent",
    };
}

function renderBookingList(items, emptyLabel) {
    if (!Array.isArray(items) || items.length === 0) {
        return `<p class="tr-crm-customer-profile__empty">${escapeHtml(emptyLabel)}</p>`;
    }

    return `
        <ul class="tr-crm-customer-profile__bookings">
            ${items.map((item) => `
                <li class="tr-crm-customer-profile__booking">
                    <div class="tr-crm-customer-profile__booking-main">
                        <strong>${escapeHtml(item.treatment_name || "—")}</strong>
                        <span>${escapeHtml(item.appointment_date || "—")} · ${escapeHtml(item.appointment_time || "—")}</span>
                        ${item.beautician_name ? `<span>${escapeHtml(item.beautician_name)}</span>` : ""}
                    </div>
                    <div class="tr-crm-customer-profile__booking-meta">
                        ${item.total_formatted ? `<span>${escapeHtml(item.total_formatted)}</span>` : ""}
                        ${item.status_label ? `<span class="tr-crm-customer-profile__status">${escapeHtml(item.status_label)}</span>` : ""}
                        ${item.reminder_sent
                            ? `<span class="tr-crm-customer-profile__reminder-badge tr-crm-customer-profile__reminder-badge--sent">${escapeHtml(getProfileLabels().reminderSent)}</span>`
                            : (item.reminder_due
                                ? `<span class="tr-crm-customer-profile__reminder-badge tr-crm-customer-profile__reminder-badge--due">${escapeHtml(getProfileLabels().reminderDue)}</span>`
                                : "")}
                        ${item.can_send_reminder
                            ? `<button
                                type="button"
                                class="tr-crm-customer-profile__reminder-btn"
                                data-send-reminder
                                data-booking-id="${escapeHtml(String(item.id))}"
                                data-resend="${item.reminder_sent ? "1" : "0"}"
                            >${escapeHtml(item.reminder_sent ? getProfileLabels().resendReminder : getProfileLabels().sendReminder)}</button>`
                            : ""}
                    </div>
                </li>
            `).join("")}
        </ul>
    `;
}

function renderProfile(profile, labels) {
    const insights = [];

    if (profile.customer_history_label) {
        insights.push(`<span class="tr-crm-customer-profile__insight">${escapeHtml(profile.customer_history_label)}</span>`);
    }

    if (profile.loyalty_tier_name) {
        insights.push(`<span class="tr-crm-customer-profile__insight tr-crm-customer-profile__insight--loyalty"><i class="fa fa-star" aria-hidden="true"></i> ${escapeHtml(profile.loyalty_tier_name)}</span>`);
    }

    return `
        <div class="tr-crm-customer-profile__hero">
            <h4>${escapeHtml(profile.customer_name || "—")}</h4>
            ${insights.length ? `<div class="tr-crm-customer-profile__insights">${insights.join("")}</div>` : ""}
            <div class="tr-crm-customer-profile__contact">
                ${profile.customer_phone ? `<a href="tel:${escapeHtml(String(profile.customer_phone).replace(/[^\d+]/g, ""))}">${escapeHtml(profile.customer_phone)}</a>` : ""}
                ${profile.customer_email ? `<span>${escapeHtml(profile.customer_email)}</span>` : ""}
            </div>
            ${profile.user_admin_url
                ? `<a href="${escapeHtml(profile.user_admin_url)}" class="tr-crm-customer-profile__user-link" target="_blank" rel="noopener noreferrer">${escapeHtml(labels.viewUser)}</a>`
                : ""}
        </div>

        <section class="tr-crm-customer-profile__section">
            <h5>${escapeHtml(labels.upcoming)}</h5>
            ${renderBookingList(profile.upcoming_bookings, labels.noUpcoming)}
        </section>

        <section class="tr-crm-customer-profile__section">
            <h5>${escapeHtml(labels.reminders)}</h5>
            ${renderBookingList(profile.reminder_bookings, labels.noUpcoming)}
        </section>

        <section class="tr-crm-customer-profile__section">
            <h5>${escapeHtml(labels.visits)}</h5>
            ${renderBookingList(profile.visit_history, labels.noVisits)}
        </section>
    `;
}

function openCustomerProfileDrawer() {
    const root = getProfileRoot();

    if (!root) {
        return;
    }

    root.hidden = false;
    root.setAttribute("aria-hidden", "false");
    document.body.classList.add("tr-crm-customer-profile-open");
}

function closeCustomerProfileDrawer() {
    const root = getProfileRoot();

    if (!root) {
        return;
    }

    root.hidden = true;
    root.setAttribute("aria-hidden", "true");
    document.body.classList.remove("tr-crm-customer-profile-open");
}

async function loadCustomerProfile({ bookingId = null, phone = null } = {}) {
    const root = getProfileRoot();
    const labels = getProfileLabels();
    const loading = document.getElementById("tr-crm-customer-profile-loading");
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

    if (loading) {
        loading.hidden = false;
    }

    content.innerHTML = "";

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
        if (loading) {
            loading.hidden = true;
        }
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

export function initCustomerProfileDrawer() {
    const root = getProfileRoot();

    if (!root) {
        return;
    }

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
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !root.hidden) {
            closeCustomerProfileDrawer();
        }
    });
}

export { loadCustomerProfile, sendReminderFromProfile };
