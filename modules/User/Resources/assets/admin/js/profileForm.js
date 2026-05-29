import axios from "axios";
import flatpickr from "flatpickr";

function buildDateOfBirthOptions(input) {
    const options = {
        mode: "single",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        disableMobile: true,
        defaultDate: input.dataset.defaultDate || input.value || null,
    };

    if (input.dataset.maxDate) {
        options.maxDate = input.dataset.maxDate;
    }

    if (input.dataset.minDate) {
        options.minDate = input.dataset.minDate;
    }

    return options;
}

function initProfileDateOfBirth(form) {
    form.querySelectorAll(".profile-date-picker").forEach((input) => {
        if (input._flatpickr) {
            input._flatpickr.destroy();
        }

        flatpickr(input, buildDateOfBirthOptions(input));
    });
}

function syncDateOfBirthOnSubmit(form) {
    form.addEventListener("submit", () => {
        form.querySelectorAll(".profile-date-picker").forEach((input) => {
            const picker = input._flatpickr;

            if (!picker) {
                return;
            }

            if (picker.selectedDates.length > 0) {
                input.value = picker.formatDate(picker.selectedDates[0], "Y-m-d");
            } else {
                input.value = "";
            }
        });
    });
}

function initProfileAddressCountryState(form) {
    const countrySelect = form.querySelector("[data-profile-address-country]");

    if (!countrySelect) {
        return;
    }

    const addressRoot = form.querySelector("[data-admin-profile-address]") || form;
    const stateText = addressRoot.querySelector("[data-profile-address-state-text]");
    const stateSelect = addressRoot.querySelector("[data-profile-address-state-select]");
    const stateInputWrap = addressRoot.querySelector(
        ".admin-profile-address-state--input"
    );
    const stateSelectWrap = addressRoot.querySelector(
        ".admin-profile-address-state--select"
    );

    const loadStates = (countryCode) => {
        const oldState = stateText?.value || stateSelect?.value || "";

        axios
            .get(FleetCart.apiUrl(`/countries/${countryCode}/states`))
            .then(({ data }) => {
                stateInputWrap?.classList.add("hide");
                stateSelectWrap?.classList.add("hide");

                if (!data || Object.keys(data).length === 0) {
                    stateInputWrap?.classList.remove("hide");
                    stateSelectWrap?.classList.add("hide");

                    if (stateText) {
                        stateText.disabled = false;
                        stateText.name = "state";
                        stateText.value = oldState;
                    }

                    if (stateSelect) {
                        stateSelect.disabled = true;
                        stateSelect.removeAttribute("name");
                    }

                    return;
                }

                stateSelectWrap?.classList.remove("hide");
                stateInputWrap?.classList.add("hide");

                if (stateSelect) {
                    stateSelect.disabled = false;
                    stateSelect.setAttribute("name", "state");
                    stateSelect.innerHTML = Object.entries(data)
                        .map(
                            ([code, name]) =>
                                `<option value="${code}">${name}</option>`
                        )
                        .join("");
                    stateSelect.value = oldState;
                }

                if (stateText) {
                    stateText.disabled = true;
                    stateText.removeAttribute("name");
                }
            })
            .catch(() => {
                stateInputWrap?.classList.remove("hide");
                stateSelectWrap?.classList.add("hide");

                if (stateText) {
                    stateText.disabled = false;
                    stateText.setAttribute("name", "state");
                }

                if (stateSelect) {
                    stateSelect.disabled = true;
                    stateSelect.removeAttribute("name");
                }
            });
    };

    countrySelect.addEventListener("change", (event) => {
        loadStates(event.target.value);
    });

    if (countrySelect.value) {
        loadStates(countrySelect.value);
    }
}

function initProfilePhoto(form) {
    const root = form.querySelector("[data-admin-profile-photo]");

    if (!root) {
        return;
    }

    const input = root.querySelector("[data-admin-profile-photo-input]");
    const removeBtn = root.querySelector("[data-admin-profile-photo-remove]");
    const removeFlag = root.querySelector("[data-admin-profile-photo-remove-flag]");
    const preview = root.querySelector("[data-admin-profile-photo-preview]");
    const previewWrap = root.querySelector(".admin-profile-photo__preview");
    const removedHint = root.dataset.removedHint || "";

    if (input) {
        input.addEventListener("change", () => {
            const file = input.files?.[0];

            if (!file) {
                return;
            }

            if (removeFlag) {
                removeFlag.value = "0";
            }

            let img = preview;

            if (!img) {
                previewWrap.innerHTML = "";
                img = document.createElement("img");
                img.className = "admin-profile-photo__img";
                img.dataset.adminProfilePhotoPreview = "";
                img.alt = "";
                previewWrap.appendChild(img);
            }

            img.src = URL.createObjectURL(file);
        });
    }

    if (removeBtn && removeFlag && previewWrap) {
        removeBtn.addEventListener("click", () => {
            removeFlag.value = "1";

            if (input) {
                input.value = "";
            }

            previewWrap.innerHTML = removedHint
                ? `<span class="admin-profile-photo__removed">${removedHint}</span>`
                : "";
        });
    }
}

function initAdminAccountForm(form) {
    initProfileDateOfBirth(form);
    syncDateOfBirthOnSubmit(form);
    initProfileAddressCountryState(form);
    initProfilePhoto(form);
}

document.querySelectorAll("[data-admin-account-form]").forEach((form) => {
    initAdminAccountForm(form);
});
