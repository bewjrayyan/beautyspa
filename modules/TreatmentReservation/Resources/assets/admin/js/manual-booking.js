import flatpickr from "flatpickr";
import {
    formatPhoneE164,
    getPhoneInputE164,
    initModernPhoneInputs,
} from "../../../../../Storefront/Resources/assets/public/js/lib/modernPhoneInput.js";
import { initBeauticianPickers, resetBeauticianPicker, setBeauticianPickerValue } from "./beautician-picker.js";
import { initManualBookingProducts } from "./manual-booking-products.js";

function readProductCatalog(form) {
    const script = form.querySelector(".tr-manual-booking-product-catalog-data");

    if (!script) {
        return [];
    }

    try {
        return JSON.parse(script.textContent || "[]");
    } catch (error) {
        return [];
    }
}

function buildAppointmentDateOptions(input, onDateChange) {
    const options = {
        mode: "single",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d M Y",
        disableMobile: true,
        animate: true,
        minDate: input.dataset.minDate || "today",
        defaultDate: input.value || null,
        appendTo: document.body,
        onReady: (_selectedDates, _dateStr, instance) => {
            instance.calendarContainer.classList.add("tr-manual-booking-datepicker-calendar");
        },
        onOpen: (_selectedDates, _dateStr, instance) => {
            instance.config.positionElement = instance.altInput || instance.input;
        },
        onChange: (_selectedDates, dateStr) => {
            input.value = dateStr;
            onDateChange?.();
        },
    };

    if (input.dataset.maxDate) {
        options.maxDate = input.dataset.maxDate;
    }

    return options;
}

function initAppointmentDatePicker(input, onDateChange) {
    if (!input || input._flatpickr) {
        return input?._flatpickr || null;
    }

    return flatpickr(input, buildAppointmentDateOptions(input, onDateChange));
}

function setAppointmentDate(input, dateStr = "") {
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

function clearAppointmentDate(input) {
    setAppointmentDate(input, "");
}

function setPhoneInputValue(input, phone = "") {
    if (!input) {
        return;
    }

    const e164 = formatPhoneE164(phone);

    if (input._iti) {
        input._iti.setNumber(e164);
        input.dataset.fullNumber = input._iti.getNumber() || e164;
    } else {
        input.value = e164;
        input.dataset.fullNumber = e164;
    }
}

function clearPhoneInput(input) {
    setPhoneInputValue(input, "");
}

function resolvePhoneLookupQuery(input) {
    const raw = getPhoneInputE164(input) || input?.value || "";

    return raw.replace(/\D/g, "");
}

function escapeLookupHtml(value = "") {
    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

function debounce(fn, delay = 350) {
    let timer = null;

    return (...args) => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => fn(...args), delay);
    };
}

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
    const beauticianPicker = form.querySelector(".tr-beautician-picker");
    const beauticianField = form.querySelector('[name="beautician_id"]');
    const defaultBeauticianId = modal.dataset.defaultBeauticianId || "";
    const dateInput = form.querySelector('[name="appointment_date"]');
    const appointmentDatePicker = initAppointmentDatePicker(dateInput, () => loadSlots());
    const timeInput = form.querySelector('[name="appointment_time"]');
    const bookingIdInput = form.querySelector(".tr-manual-booking-id");
    const slotsRoot = form.querySelector(".tr-manual-booking-slots");
    const errorBox = form.querySelector(".tr-manual-booking-error");
    const submitBtn = form.querySelector('[type="submit"]');
    const titleEl = modal.querySelector(".modal-title");
    const phoneInput = form.querySelector('[name="customer_phone"]');
    const firstNameInput = form.querySelector('[name="customer_first_name"]');
    const lastNameInput = form.querySelector('[name="customer_last_name"]');
    const emailInput = form.querySelector('[name="customer_email"]');
    const lookupRoot = form.querySelector(".tr-manual-booking-customer-lookup");
    const lookupList = lookupRoot?.querySelector(".tr-manual-booking-customer-lookup__list");
    const slotsUrl = modal.dataset.slotsUrl;
    const storeUrl = modal.dataset.storeUrl;
    const customersUrl = modal.dataset.customersUrl || "";
    const updateUrlTemplate = modal.dataset.updateUrlTemplate || "";
    const productCatalog = readProductCatalog(form);
    const productPicker = initManualBookingProducts(form, productCatalog);
    let slotsRequestId = 0;
    let lookupRequestId = 0;
    let suppressCustomerLookup = false;

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
        if (portalMode && fixedBeauticianId) {
            return fixedBeauticianId;
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

        if (!portalMode || !fixedBeauticianId) {
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
        suppressCustomerLookup = true;

        if (firstNameInput) {
            firstNameInput.value = customer.customer_first_name || "";
        }

        if (lastNameInput) {
            lastNameInput.value = customer.customer_last_name || "";
        }

        if (phoneInput) {
            setPhoneInputValue(phoneInput, customer.customer_phone || "");
        }

        if (emailInput) {
            emailInput.value = customer.customer_email || "";
        }

        hideCustomerLookup();

        window.setTimeout(() => {
            suppressCustomerLookup = false;
        }, 0);
    };

    const formatCustomerLookupMeta = (customer) => {
        const parts = [];

        if (customer.customer_phone) {
            parts.push(customer.customer_phone);
        }

        if (customer.customer_email) {
            parts.push(customer.customer_email);
        }

        return parts.join(" · ");
    };

    const renderCustomerLookup = (customers) => {
        if (!lookupRoot || !lookupList) {
            return;
        }

        if (!Array.isArray(customers) || customers.length === 0) {
            lookupList.innerHTML = `<li class="tr-manual-booking-customer-lookup__empty">${escapeLookupHtml(modal.dataset.customerLookupEmpty || "No matching customers found.")}</li>`;
            lookupRoot.hidden = false;

            return;
        }

        lookupList.innerHTML = customers
            .map((customer) => {
                const fullName = `${customer.customer_first_name || ""} ${customer.customer_last_name || ""}`.trim();
                const meta = formatCustomerLookupMeta(customer);

                return `
                    <li>
                        <button type="button" class="tr-manual-booking-customer-lookup__item" data-customer='${JSON.stringify(customer).replace(/'/g, "&#39;")}'>
                            <strong>${escapeLookupHtml(fullName)}</strong>
                            ${meta ? `<span>${escapeLookupHtml(meta)}</span>` : ""}
                        </button>
                    </li>
                `;
            })
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

            return [];
        }

        const requestId = ++lookupRequestId;

        try {
            const response = await axios.get(customersUrl, { params: { q: query } });

            if (requestId !== lookupRequestId) {
                return [];
            }

            const customers = response.data?.customers || [];

            if (customers.length === 1) {
                applyCustomer(customers[0]);

                return customers;
            }

            renderCustomerLookup(customers);

            return customers;
        } catch (error) {
            if (requestId !== lookupRequestId) {
                return [];
            }

            hideCustomerLookup();

            return [];
        }
    };

    const resolveCustomerLookupQuery = (fieldName) => {
        if (fieldName === "customer_phone") {
            return resolvePhoneLookupQuery(phoneInput);
        }

        if (fieldName === "customer_email") {
            return (emailInput?.value || "").trim();
        }

        if (fieldName === "customer_first_name") {
            return (firstNameInput?.value || "").trim();
        }

        if (fieldName === "customer_last_name") {
            return (lastNameInput?.value || "").trim();
        }

        return "";
    };

    const triggerCustomerLookup = (fieldName) => {
        if (suppressCustomerLookup) {
            return;
        }

        const query = resolveCustomerLookupQuery(fieldName);

        if (query.length < 3) {
            hideCustomerLookup();

            return;
        }

        searchCustomers(query);
    };

    const debouncedCustomerLookup = debounce((fieldName) => {
        triggerCustomerLookup(fieldName);
    });

    const resetForm = () => {
        suppressCustomerLookup = true;
        form.reset();
        setCreateMode();

        if (beauticianPicker) {
            resetBeauticianPicker(
                beauticianPicker,
                portalMode && defaultBeauticianId ? defaultBeauticianId : ""
            );
        } else if (portalMode && beauticianField && fixedBeauticianId) {
            beauticianField.value = fixedBeauticianId;
        }

        clearAppointmentDate(dateInput);
        clearPhoneInput(phoneInput);
        productPicker?.reset();
        clearSelectedSlot();
        setError("");
        hideCustomerLookup();
        renderSlotsMessage(modal.dataset.selectSchedule || "Select beautician and date first");
        window.setTimeout(() => {
            suppressCustomerLookup = false;
        }, 0);
    };

    const fillFormForEdit = (booking) => {
        suppressCustomerLookup = true;
        setEditMode(booking.id);

        if (beauticianPicker && booking.beautician_id) {
            setBeauticianPickerValue(beauticianPicker, String(booking.beautician_id));
        } else if (beauticianField && booking.beautician_id) {
            beauticianField.value = String(booking.beautician_id);
        }

        if (dateInput && booking.appointment_date_value) {
            setAppointmentDate(dateInput, booking.appointment_date_value);
        }

        const notesInput = form.querySelector('[name="notes"]');

        if (firstNameInput) {
            firstNameInput.value = booking.customer_first_name || "";
        }

        if (lastNameInput) {
            lastNameInput.value = booking.customer_last_name || "";
        }

        if (phoneInput) {
            setPhoneInputValue(phoneInput, booking.customer_phone || "");
        }

        if (emailInput) {
            emailInput.value = booking.customer_email || "";
        }

        productPicker?.fillFromBooking(booking);

        if (notesInput) {
            notesInput.value = booking.notes || "";
        }

        if (timeInput && booking.appointment_time) {
            timeInput.value = booking.appointment_time;
        }

        setError("");
        hideCustomerLookup();
        loadSlots(booking.appointment_time || "");
        window.setTimeout(() => {
            suppressCustomerLookup = false;
        }, 0);
    };

    const bindCustomerLookupField = (input, fieldName) => {
        if (!input) {
            return;
        }

        input.addEventListener("input", () => debouncedCustomerLookup(fieldName));
        input.addEventListener("blur", () => {
            window.setTimeout(hideCustomerLookup, 150);
        });
    };

    beauticianField?.addEventListener("change", () => loadSlots());

    if (!appointmentDatePicker) {
        dateInput?.addEventListener("change", () => loadSlots());
    }

    bindCustomerLookupField(firstNameInput, "customer_first_name");
    bindCustomerLookupField(lastNameInput, "customer_last_name");
    bindCustomerLookupField(emailInput, "customer_email");
    bindCustomerLookupField(phoneInput, "customer_phone");
    phoneInput?.addEventListener("countrychange", () => debouncedCustomerLookup("customer_phone"));

    modal.addEventListener("hidden.bs.modal", resetForm);

    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        setError("");

        if (!timeInput?.value) {
            setError(modal.dataset.slotRequired || "Please select a time slot");

            return;
        }

        if (phoneInput?._iti && !phoneInput._iti.isValidNumber()) {
            setError(modal.dataset.invalidPhone || "Please enter a valid phone number.");

            return;
        }

        if (phoneInput?._iti) {
            phoneInput.value = getPhoneInputE164(phoneInput) || formatPhoneE164(phoneInput.value);
        }

        const productError = productPicker?.validate() || "";

        if (productError) {
            setError(productError);

            return;
        }

        const formData = new FormData(form);
        productPicker?.appendToFormData(formData);
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
                formData.append("_method", "PUT");
                response = await axios.post(updateUrl, formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                });
            } else {
                response = await axios.post(storeUrl, formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                });
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

    initBeauticianPickers(form);
    initModernPhoneInputs(form);
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
