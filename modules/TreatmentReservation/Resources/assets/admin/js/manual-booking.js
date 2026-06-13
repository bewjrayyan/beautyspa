function initManualBookingModal() {
    document.querySelectorAll(".tr-manual-booking-modal").forEach((modal) => {
        bindManualBookingModal(modal);
    });
}

function bindManualBookingModal(modal) {
    const form = modal.querySelector(".tr-manual-booking-form");

    if (!form || modal.dataset.manualBookingBound === "1") {
        return;
    }

    modal.dataset.manualBookingBound = "1";

    const portalMode = modal.dataset.portalMode === "1";
    const fixedBeauticianId = modal.dataset.fixedBeauticianId || "";
    const beauticianField = form.querySelector('[name="beautician_id"]');
    const dateInput = form.querySelector('[name="appointment_date"]');
    const timeInput = form.querySelector('[name="appointment_time"]');
    const bookingIdInput = form.querySelector(".tr-manual-booking-id");
    const slotsRoot = form.querySelector(".tr-manual-booking-slots");
    const errorBox = form.querySelector(".tr-manual-booking-error");
    const submitBtn = form.querySelector('[type="submit"]');
    const titleEl = modal.querySelector(".modal-title");
    const phoneInput = form.querySelector('[name="customer_phone"]');
    const lookupRoot = form.querySelector(".tr-manual-booking-customer-lookup");
    const lookupList = lookupRoot?.querySelector(".tr-manual-booking-customer-lookup__list");
    const slotsUrl = modal.dataset.slotsUrl;
    const storeUrl = modal.dataset.storeUrl;
    const customersUrl = modal.dataset.customersUrl || "";
    const updateUrlTemplate = modal.dataset.updateUrlTemplate || "";
    let slotsRequestId = 0;
    let lookupRequestId = 0;

    const setError = (message = "") => {
        if (!errorBox) {
            return;
        }

        if (!message) {
            errorBox.hidden = true;
            errorBox.textContent = "";

            return;
        }

        errorBox.hidden = false;
        errorBox.textContent = message;
    };

    const isEditMode = () => modal.dataset.editMode === "1";

    const setCreateMode = () => {
        modal.dataset.editMode = "0";

        if (bookingIdInput) {
            bookingIdInput.value = "";
        }

        if (titleEl) {
            titleEl.textContent = modal.dataset.createTitle || titleEl.dataset.defaultTitle || titleEl.textContent;
        }

        if (submitBtn) {
            submitBtn.textContent = modal.dataset.save || "Create appointment";
        }
    };

    const setEditMode = (bookingId) => {
        modal.dataset.editMode = "1";

        if (bookingIdInput) {
            bookingIdInput.value = String(bookingId);
        }

        if (titleEl) {
            if (!titleEl.dataset.defaultTitle) {
                titleEl.dataset.defaultTitle = titleEl.textContent;
            }

            titleEl.textContent = modal.dataset.editTitle || "Edit appointment";
        }

        if (submitBtn) {
            submitBtn.textContent = modal.dataset.editSave || "Save changes";
        }
    };

    const clearSelectedSlot = () => {
        if (timeInput) {
            timeInput.value = "";
        }

        slotsRoot?.querySelectorAll(".tr-manual-booking-slot.is-selected").forEach((button) => {
            button.classList.remove("is-selected");
        });
    };

    const renderSlotsMessage = (message) => {
        if (!slotsRoot) {
            return;
        }

        slotsRoot.dataset.empty = "true";
        slotsRoot.innerHTML = `<p class="tr-manual-booking-slots__message">${message}</p>`;
        clearSelectedSlot();
    };

    const selectSlot = (slot) => {
        if (!slotsRoot || !timeInput) {
            return;
        }

        slotsRoot.querySelectorAll(".tr-manual-booking-slot.is-selected").forEach((selected) => {
            selected.classList.remove("is-selected");
        });

        const button = slotsRoot.querySelector(`.tr-manual-booking-slot[data-slot="${slot}"]`);

        if (button) {
            button.classList.add("is-selected");
        }

        timeInput.value = slot;
        setError("");
    };

    const renderSlots = (slots, selectedSlot = "") => {
        if (!slotsRoot) {
            return;
        }

        if (!Array.isArray(slots) || slots.length === 0) {
            renderSlotsMessage(modal.dataset.noSlots || "No slots available");

            return;
        }

        slotsRoot.dataset.empty = "false";
        slotsRoot.innerHTML = slots
            .map(
                (slot) =>
                    `<button type="button" class="tr-manual-booking-slot${selectedSlot === slot ? " is-selected" : ""}" data-slot="${slot}">${slot}</button>`
            )
            .join("");

        slotsRoot.querySelectorAll(".tr-manual-booking-slot").forEach((button) => {
            button.addEventListener("click", () => {
                selectSlot(button.dataset.slot || "");
            });
        });

        if (selectedSlot) {
            timeInput.value = selectedSlot;
        }
    };

    const resolveBeauticianId = () => {
        if (portalMode) {
            return fixedBeauticianId || beauticianField?.value || "";
        }

        return beauticianField?.value || "";
    };

    const loadSlots = async (selectedSlot = timeInput?.value || "") => {
        const beauticianId = resolveBeauticianId();
        const date = dateInput?.value;

        if (!beauticianId || !date) {
            renderSlotsMessage(modal.dataset.selectSchedule || "Select beautician and date first");

            return;
        }

        const requestId = ++slotsRequestId;

        renderSlotsMessage(modal.dataset.loadingSlots || "Loading slots…");

        const params = { date };

        if (!portalMode) {
            params.beautician_id = beauticianId;
        }

        if (isEditMode() && bookingIdInput?.value) {
            params.booking_id = bookingIdInput.value;
        }

        try {
            const response = await axios.get(slotsUrl, { params });

            if (requestId !== slotsRequestId) {
                return;
            }

            const slots = response.data?.slots || [];
            const slotToSelect =
                selectedSlot && slots.includes(selectedSlot) ? selectedSlot : selectedSlot && !slots.includes(selectedSlot)
                    ? selectedSlot
                    : selectedSlot;

            renderSlots(slots, slotToSelect);

            if (selectedSlot && !slots.includes(selectedSlot)) {
                selectSlot(selectedSlot);
            }
        } catch (error) {
            if (requestId !== slotsRequestId) {
                return;
            }

            const message =
                error?.response?.data?.message ||
                modal.dataset.noSlots ||
                "Unable to load available slots";

            renderSlotsMessage(message);
        }
    };

    const hideCustomerLookup = () => {
        if (!lookupRoot) {
            return;
        }

        lookupRoot.hidden = true;

        if (lookupList) {
            lookupList.innerHTML = "";
        }
    };

    const applyCustomer = (customer) => {
        const firstNameInput = form.querySelector('[name="customer_first_name"]');
        const lastNameInput = form.querySelector('[name="customer_last_name"]');
        const emailInput = form.querySelector('[name="customer_email"]');

        if (firstNameInput) {
            firstNameInput.value = customer.customer_first_name || "";
        }

        if (lastNameInput) {
            lastNameInput.value = customer.customer_last_name || "";
        }

        if (phoneInput) {
            phoneInput.value = customer.customer_phone || "";
        }

        if (emailInput) {
            emailInput.value = customer.customer_email || "";
        }

        hideCustomerLookup();
    };

    const renderCustomerLookup = (customers) => {
        if (!lookupRoot || !lookupList) {
            return;
        }

        if (!Array.isArray(customers) || customers.length === 0) {
            lookupList.innerHTML = `<li class="tr-manual-booking-customer-lookup__empty">${modal.dataset.customerLookupEmpty || "No matching customers found."}</li>`;
            lookupRoot.hidden = false;

            return;
        }

        lookupList.innerHTML = customers
            .map(
                (customer) => `
                    <li>
                        <button type="button" class="tr-manual-booking-customer-lookup__item" data-customer='${JSON.stringify(customer).replace(/'/g, "&#39;")}'>
                            <strong>${customer.customer_first_name || ""} ${customer.customer_last_name || ""}</strong>
                            <span>${customer.customer_phone || ""}</span>
                        </button>
                    </li>
                `
            )
            .join("");

        lookupRoot.hidden = false;

        lookupList.querySelectorAll(".tr-manual-booking-customer-lookup__item").forEach((button) => {
            button.addEventListener("click", () => {
                try {
                    applyCustomer(JSON.parse(button.dataset.customer || "{}"));
                } catch (error) {
                    // ignore invalid payload
                }
            });
        });
    };

    const searchCustomers = async (query) => {
        if (!customersUrl || query.length < 3) {
            hideCustomerLookup();

            return;
        }

        const requestId = ++lookupRequestId;

        try {
            const response = await axios.get(customersUrl, { params: { q: query } });

            if (requestId !== lookupRequestId) {
                return;
            }

            renderCustomerLookup(response.data?.customers || []);
        } catch (error) {
            if (requestId !== lookupRequestId) {
                return;
            }

            hideCustomerLookup();
        }
    };

    const resetForm = () => {
        form.reset();
        setCreateMode();

        if (portalMode && beauticianField) {
            beauticianField.value = fixedBeauticianId;
        }

        clearSelectedSlot();
        setError("");
        hideCustomerLookup();
        renderSlotsMessage(modal.dataset.selectSchedule || "Select beautician and date first");
    };

    const fillFormForEdit = (booking) => {
        setEditMode(booking.id);

        if (beauticianField && booking.beautician_id) {
            beauticianField.value = String(booking.beautician_id);
        }

        if (dateInput && booking.appointment_date_value) {
            dateInput.value = booking.appointment_date_value;
        }

        const firstNameInput = form.querySelector('[name="customer_first_name"]');
        const lastNameInput = form.querySelector('[name="customer_last_name"]');
        const emailInput = form.querySelector('[name="customer_email"]');
        const productInput = form.querySelector('[name="product_id"]');
        const notesInput = form.querySelector('[name="notes"]');

        if (firstNameInput) {
            firstNameInput.value = booking.customer_first_name || "";
        }

        if (lastNameInput) {
            lastNameInput.value = booking.customer_last_name || "";
        }

        if (phoneInput) {
            phoneInput.value = booking.customer_phone || "";
        }

        if (emailInput) {
            emailInput.value = booking.customer_email || "";
        }

        if (productInput && booking.product_id) {
            productInput.value = String(booking.product_id);
        }

        if (notesInput) {
            notesInput.value = booking.notes || "";
        }

        if (timeInput && booking.appointment_time) {
            timeInput.value = booking.appointment_time;
        }

        setError("");
        hideCustomerLookup();
        loadSlots(booking.appointment_time || "");
    };

    beauticianField?.addEventListener("change", () => loadSlots());
    dateInput?.addEventListener("change", () => loadSlots());

    phoneInput?.addEventListener("input", (event) => {
        searchCustomers(event.target.value.trim());
    });

    phoneInput?.addEventListener("blur", () => {
        window.setTimeout(hideCustomerLookup, 150);
    });

    modal.addEventListener("hidden.bs.modal", resetForm);

    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        setError("");

        if (!timeInput?.value) {
            setError(modal.dataset.slotRequired || "Please select a time slot");

            return;
        }

        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());
        const editing = isEditMode();
        const bookingId = bookingIdInput?.value || "";

        submitBtn.disabled = true;
        submitBtn.textContent = editing
            ? modal.dataset.editSaving || "Saving…"
            : modal.dataset.saving || "Creating…";

        try {
            let response;

            if (editing && updateUrlTemplate && bookingId) {
                const updateUrl = updateUrlTemplate.replace("__ID__", bookingId);
                response = await axios.put(updateUrl, payload);
            } else {
                response = await axios.post(storeUrl, payload);
            }

            const redirect = response.data?.booking?.redirect;

            if (redirect) {
                window.location.href = redirect;

                return;
            }

            const message = response.data?.message;

            if (message) {
                window.notify?.success?.(message);
            }

            window.location.reload();
        } catch (error) {
            const responseData = error?.response?.data;

            if (responseData?.errors) {
                const firstError = Object.values(responseData.errors).flat()[0];
                setError(firstError || responseData.message || "Validation failed");
            } else {
                setError(responseData?.message || "Failed to save appointment");
            }
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = editing
                ? modal.dataset.editSave || "Save changes"
                : modal.dataset.save || "Create appointment";
        }
    });

    modal.openManualBookingEditor = (booking) => {
        fillFormForEdit(booking);

        if (window.jQuery) {
            window.jQuery(modal).modal("show");
        } else {
            modal.classList.add("show");
            modal.style.display = "block";
        }
    };
}

function openManualBookingEditor(booking, modalSelector = "") {
    const selector =
        modalSelector ||
        (document.getElementById("tr-portal-manual-booking-modal") ? "#tr-portal-manual-booking-modal" : "#tr-manual-booking-modal");
    const modal = document.querySelector(selector);

    if (!modal || typeof modal.openManualBookingEditor !== "function") {
        return;
    }

    modal.openManualBookingEditor(booking);
}

document.addEventListener("DOMContentLoaded", () => {
    initManualBookingModal();
});

export { initManualBookingModal, openManualBookingEditor };
