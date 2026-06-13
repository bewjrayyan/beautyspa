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
            .get(AestheticCart.apiUrl(`/countries/${countryCode}/states`))
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

function syncHeroAvatar(previewUrl, { removed = false } = {}) {
    const hero = document.querySelector("[data-admin-profile-hero-avatar]");

    if (!hero) {
        return;
    }

    const accent = hero.dataset.accent || "#2584f0";
    const initial = hero.dataset.initial || "?";

    if (removed || !previewUrl) {
        hero.className = "admin-profile-hero__avatar admin-profile-hero__avatar--initial";
        hero.style.backgroundColor = accent;
        hero.textContent = initial;

        return;
    }

    hero.className = "admin-profile-hero__avatar admin-profile-hero__avatar--photo";
    hero.style.backgroundColor = "";

    let img = hero.querySelector("[data-admin-profile-hero-avatar-img]");

    if (!img) {
        hero.innerHTML = "";
        img = document.createElement("img");
        img.dataset.adminProfileHeroAvatarImg = "";
        img.alt = "";
        hero.appendChild(img);
    }

    img.src = previewUrl;
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
    const initialPreviewHtml = previewWrap?.innerHTML || "";

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

            const previewUrl = URL.createObjectURL(file);
            img.src = previewUrl;
            syncHeroAvatar(previewUrl);
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
                : initialPreviewHtml;

            syncHeroAvatar(null, { removed: true });
        });
    }
}

function initCreateHeroPreview(form) {
    const nameEl = document.querySelector("[data-admin-profile-hero-name]");
    const emailEl = document.querySelector("[data-admin-profile-hero-email]");
    const avatarEl = document.querySelector("[data-admin-profile-hero-avatar]");

    if (!nameEl || !emailEl) {
        return;
    }

    const firstNameInput = form.querySelector('[name="first_name"]');
    const lastNameInput = form.querySelector('[name="last_name"]');
    const emailInput = form.querySelector('[name="email"]');
    const isCreateHero = Boolean(nameEl.dataset.placeholder);

    const updatePreview = () => {
        const firstName = firstNameInput?.value?.trim() || "";
        const lastName = lastNameInput?.value?.trim() || "";
        const fullName = `${firstName} ${lastName}`.trim();
        const email = emailInput?.value?.trim() || "";

        if (isCreateHero) {
            nameEl.textContent = fullName || nameEl.dataset.placeholder || "";
            emailEl.textContent = email || emailEl.dataset.placeholder || "";
        } else {
            if (fullName) {
                nameEl.textContent = fullName;
            }

            if (email) {
                emailEl.textContent = email;
            }
        }

        if (avatarEl && avatarEl.classList.contains("admin-profile-hero__avatar--initial")) {
            const initial = firstName ? firstName.charAt(0).toUpperCase() : avatarEl.dataset.initial || "?";
            avatarEl.textContent = initial;
            avatarEl.dataset.initial = initial;
        }
    };

    [firstNameInput, lastNameInput, emailInput].forEach((input) => {
        input?.addEventListener("input", updatePreview);
    });

    updatePreview();
}

function initEditHeroStatus(form) {
    const statusEl = document.querySelector("[data-admin-profile-hero-status]");
    const statusLabel = document.querySelector("[data-admin-profile-hero-status-label]");
    const statusIcon = document.querySelector("[data-admin-profile-hero-status-icon]");
    const activatedInput = form.querySelector('[name="activated"]');

    if (!statusEl || !activatedInput || activatedInput.disabled) {
        return;
    }

    const updateStatus = () => {
        const isActive = activatedInput.checked;

        statusEl.classList.toggle("admin-profile-hero__status--active", isActive);
        statusEl.classList.toggle("admin-profile-hero__status--inactive", !isActive);

        if (statusLabel) {
            statusLabel.textContent = isActive
                ? statusEl.dataset.labelActive || "Active"
                : statusEl.dataset.labelInactive || "Inactive";
        }

        if (statusIcon) {
            statusIcon.className = `fa ${isActive ? "fa-check-circle" : "fa-pause-circle"}`;
        }
    };

    activatedInput.addEventListener("change", updateStatus);
}

function randomIndex(max) {
    const array = new Uint32Array(1);
    crypto.getRandomValues(array);

    return array[0] % max;
}

function shuffleString(value) {
    const chars = value.split("");

    for (let i = chars.length - 1; i > 0; i -= 1) {
        const j = randomIndex(i + 1);
        [chars[i], chars[j]] = [chars[j], chars[i]];
    }

    return chars.join("");
}

function generateSecurePassword(length = 14) {
    const upper = "ABCDEFGHJKLMNPQRSTUVWXYZ";
    const lower = "abcdefghijkmnopqrstuvwxyz";
    const numbers = "23456789";
    const special = "!@#$%&*";
    const all = upper + lower + numbers + special;

    let password = "";
    password += upper[randomIndex(upper.length)];
    password += lower[randomIndex(lower.length)];
    password += numbers[randomIndex(numbers.length)];
    password += special[randomIndex(special.length)];

    while (password.length < length) {
        password += all[randomIndex(all.length)];
    }

    return shuffleString(password);
}

function analyzePasswordStrength(password) {
    const checks = {
        length: password.length >= 6,
        length8: password.length >= 8,
        letter: /[a-zA-Z]/.test(password),
        number: /\d/.test(password),
        mixed: /[a-z]/.test(password) && /[A-Z]/.test(password),
    };

    let score = 0;

    if (checks.length) {
        score += 20;
    }

    if (checks.length8) {
        score += 15;
    }

    if (password.length >= 12) {
        score += 10;
    }

    if (checks.letter) {
        score += 15;
    }

    if (checks.number) {
        score += 15;
    }

    if (checks.mixed) {
        score += 15;
    }

    if (/[^a-zA-Z0-9]/.test(password)) {
        score += 10;
    }

    let level = "empty";

    if (password.length > 0) {
        if (score < 35) {
            level = "weak";
        } else if (score < 55) {
            level = "fair";
        } else if (score < 80) {
            level = "good";
        } else {
            level = "strong";
        }
    }

    return { checks, score: Math.min(score, 100), level };
}

function initPasswordPanel(form) {
    const root = form.querySelector("[data-admin-password-panel]");

    if (!root) {
        return;
    }

    const passwordInput = root.querySelector("[data-admin-password-input]");
    const confirmInput = root.querySelector("[data-admin-password-confirm]");
    const generateBtn = root.querySelector("[data-admin-password-generate]");
    const strengthRoot = root.querySelector("[data-admin-password-strength]");
    const strengthFill = root.querySelector("[data-admin-password-strength-fill]");
    const strengthLabel = root.querySelector("[data-admin-password-strength-label]");
    const matchEl = root.querySelector("[data-admin-password-match]");
    const generatedHint = root.querySelector("[data-admin-password-generated-hint]");
    const generatedHintText = generatedHint?.querySelector("span");
    const checkItems = root.querySelectorAll("[data-admin-password-check]");
    const strengthLabels = {
        empty: root.dataset.strengthEmpty || "",
        weak: root.dataset.strengthWeak || "",
        fair: root.dataset.strengthFair || "",
        good: root.dataset.strengthGood || "",
        strong: root.dataset.strengthStrong || "",
    };

    const setCheckState = (key, passed) => {
        const item = root.querySelector(`[data-admin-password-check="${key}"]`);

        if (!item) {
            return;
        }

        item.classList.toggle("is-met", passed);

        const pendingIcon = item.querySelector("[data-icon-pending]");
        const doneIcon = item.querySelector("[data-icon-done]");

        pendingIcon?.classList.toggle("hide", passed);
        doneIcon?.classList.toggle("hide", !passed);
    };

    const updateStrength = () => {
        const password = passwordInput?.value || "";
        const { checks, score, level } = analyzePasswordStrength(password);

        checkItems.forEach((item) => {
            const key = item.dataset.adminPasswordCheck;

            if (key && Object.prototype.hasOwnProperty.call(checks, key)) {
                setCheckState(key, checks[key]);
            }
        });

        if (!strengthRoot || !strengthFill || !strengthLabel) {
            return;
        }

        if (!password) {
            strengthRoot.hidden = true;
            strengthFill.style.width = "0%";
            strengthLabel.textContent = strengthLabels.empty;

            return;
        }

        strengthRoot.hidden = false;
        strengthFill.style.width = `${score}%`;
        strengthLabel.textContent = strengthLabels[level] || strengthLabels.empty;

        strengthRoot.dataset.level = level;
    };

    const updateMatch = () => {
        if (!matchEl || !passwordInput || !confirmInput) {
            return;
        }

        const password = passwordInput.value;
        const confirm = confirmInput.value;

        matchEl.classList.remove("is-match", "is-mismatch", "is-empty");

        if (!confirm) {
            matchEl.textContent = root.dataset.matchEmpty || "";
            matchEl.classList.add("is-empty");

            return;
        }

        if (password === confirm) {
            matchEl.textContent = root.dataset.matchMatch || "";
            matchEl.classList.add("is-match");

            return;
        }

        matchEl.textContent = root.dataset.matchMismatch || "";
        matchEl.classList.add("is-mismatch");
    };

    const bindToggle = (button) => {
        const targetId = button.dataset.target;
        const input = root.querySelector(`#${CSS.escape(targetId)}`);

        if (!input) {
            return;
        }

        button.addEventListener("click", () => {
            const isVisible = input.type === "text";

            input.type = isVisible ? "password" : "text";
            button.setAttribute("aria-pressed", String(!isVisible));
            button.setAttribute(
                "aria-label",
                isVisible
                    ? root.dataset.showPassword || "Show password"
                    : root.dataset.hidePassword || "Hide password"
            );

            button.querySelector("[data-icon-show]")?.classList.toggle("hide", !isVisible);
            button.querySelector("[data-icon-hide]")?.classList.toggle("hide", isVisible);
        });
    };

    root.querySelectorAll("[data-admin-password-toggle]").forEach(bindToggle);

    const refresh = () => {
        updateStrength();
        updateMatch();
    };

    passwordInput?.addEventListener("input", () => {
        if (generatedHint) {
            generatedHint.classList.add("hide");
        }

        refresh();
    });

    confirmInput?.addEventListener("input", updateMatch);

    generateBtn?.addEventListener("click", () => {
        const password = generateSecurePassword();

        if (!passwordInput || !confirmInput) {
            return;
        }

        passwordInput.value = password;
        confirmInput.value = password;
        passwordInput.type = "text";
        confirmInput.type = "text";

        root.querySelectorAll("[data-admin-password-toggle]").forEach((button) => {
            button.setAttribute("aria-pressed", "true");
            button.setAttribute("aria-label", root.dataset.hidePassword || "Hide password");
            button.querySelector("[data-icon-show]")?.classList.add("hide");
            button.querySelector("[data-icon-hide]")?.classList.remove("hide");
        });

        if (generatedHint && generatedHintText) {
            generatedHintText.textContent = root.dataset.generatedHint || "";
            generatedHint.classList.remove("hide");
        }

        refresh();
        passwordInput.focus();
    });

    refresh();
}

function initAdminAccountForm(form) {
    initProfileDateOfBirth(form);
    syncDateOfBirthOnSubmit(form);
    initProfileAddressCountryState(form);
    initProfilePhoto(form);
    initCreateHeroPreview(form);
    initEditHeroStatus(form);
    initPasswordPanel(form);
}

document.querySelectorAll("[data-admin-account-form]").forEach((form) => {
    initAdminAccountForm(form);
});
