function escapeHtml(value) {
    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

function buildBeauticianSelectedMarkup(option) {
    const avatar = option.image
        ? `<img src="${escapeHtml(option.image)}" alt="${escapeHtml(option.name)}" class="tr-beautician-picker__avatar tr-beautician-picker__avatar--photo">`
        : `<span class="tr-beautician-picker__avatar" style="background-color:${escapeHtml(option.color)}">${escapeHtml(option.initial)}</span>`;

    const title = option.title
        ? `<span class="tr-beautician-picker__title">${escapeHtml(option.title)}</span>`
        : "";

    return `
        ${avatar}
        <span class="tr-beautician-picker__text">
            <span class="tr-beautician-picker__name">${escapeHtml(option.name)}</span>
            ${title}
        </span>
    `;
}

function readBeauticianOption(button) {
    const name = button.dataset.beauticianName || "";
    const title = button.dataset.beauticianTitle || "";
    const image = button.dataset.beauticianImage || "";
    const color = button.dataset.beauticianColor || "#6366f1";

    return {
        id: button.dataset.beauticianId || "",
        name,
        title,
        image,
        color,
        initial: name.charAt(0).toUpperCase() || "?",
    };
}

function closeBeauticianPicker(picker) {
    const card = picker.querySelector(".tr-beautician-picker__card");
    const options = picker.querySelector(".tr-beautician-picker__options");

    card?.classList.remove("is-open");
    card?.setAttribute("aria-expanded", "false");

    if (options) {
        options.hidden = true;
    }
}

function openBeauticianPicker(picker) {
    document.querySelectorAll(".tr-beautician-picker").forEach((other) => {
        if (other !== picker) {
            closeBeauticianPicker(other);
        }
    });

    const card = picker.querySelector(".tr-beautician-picker__card");
    const options = picker.querySelector(".tr-beautician-picker__options");

    card?.classList.add("is-open");
    card?.setAttribute("aria-expanded", "true");

    if (options) {
        options.hidden = false;
    }
}

function renderBeauticianSelection(picker, option) {
    const select = picker.querySelector(".tr-beautician-picker__native");
    const card = picker.querySelector(".tr-beautician-picker__card");
    const selected = picker.querySelector(".tr-beautician-picker__selected");
    const placeholder = picker.querySelector(".tr-beautician-picker__placeholder");

    if (select) {
        select.value = option?.id || "";
        select.dispatchEvent(new Event("change", { bubbles: true }));
    }

    picker.querySelectorAll(".tr-beautician-picker__option").forEach((button) => {
        const isActive = option && String(button.dataset.beauticianId) === String(option.id);
        button.classList.toggle("is-active", isActive);
        button.setAttribute("aria-selected", isActive ? "true" : "false");
    });

    if (!option || !selected || !card || !placeholder) {
        return;
    }

    selected.innerHTML = buildBeauticianSelectedMarkup(option);
    selected.hidden = false;
    placeholder.hidden = true;
    card.classList.remove("is-placeholder");
}

export function initBeauticianPickers(root = document) {
    root.querySelectorAll(".tr-beautician-picker").forEach((picker) => {
        if (picker.dataset.pickerBound === "1") {
            return;
        }

        picker.dataset.pickerBound = "1";

        const card = picker.querySelector(".tr-beautician-picker__card");
        const options = picker.querySelector(".tr-beautician-picker__options");
        const select = picker.querySelector(".tr-beautician-picker__native");

        card?.addEventListener("click", () => {
            if (card.classList.contains("is-open")) {
                closeBeauticianPicker(picker);
            } else {
                openBeauticianPicker(picker);
            }
        });

        options?.querySelectorAll(".tr-beautician-picker__option").forEach((button) => {
            button.addEventListener("click", () => {
                renderBeauticianSelection(picker, readBeauticianOption(button));
                closeBeauticianPicker(picker);
            });
        });

        if (select?.value) {
            const activeButton = options?.querySelector(
                `.tr-beautician-picker__option[data-beautician-id="${select.value}"]`
            );

            if (activeButton) {
                renderBeauticianSelection(picker, readBeauticianOption(activeButton));
            }
        }
    });

    if (!document.body.dataset.beauticianPickerOutsideBound) {
        document.body.dataset.beauticianPickerOutsideBound = "1";

        document.addEventListener("click", (event) => {
            if (event.target.closest(".tr-beautician-picker")) {
                return;
            }

            document.querySelectorAll(".tr-beautician-picker").forEach((picker) => {
                closeBeauticianPicker(picker);
            });
        });
    }
}

export function setBeauticianPickerValue(picker, beauticianId) {
    if (!picker) {
        return;
    }

    const button = picker.querySelector(
        `.tr-beautician-picker__option[data-beautician-id="${beauticianId}"]`
    );

    if (button) {
        renderBeauticianSelection(picker, readBeauticianOption(button));
    }
}

export function resetBeauticianPicker(picker, defaultBeauticianId = "") {
    if (!picker) {
        return;
    }

    const select = picker.querySelector(".tr-beautician-picker__native");
    const card = picker.querySelector(".tr-beautician-picker__card");
    const selected = picker.querySelector(".tr-beautician-picker__selected");
    const placeholder = picker.querySelector(".tr-beautician-picker__placeholder");
    const placeholderText = picker.dataset.placeholder || "Please select";

    closeBeauticianPicker(picker);

    if (defaultBeauticianId) {
        setBeauticianPickerValue(picker, defaultBeauticianId);

        return;
    }

    if (select) {
        select.value = "";
    }

    if (selected) {
        selected.innerHTML = "";
        selected.hidden = true;
    }

    if (placeholder) {
        placeholder.textContent = placeholderText;
        placeholder.hidden = false;
    }

    card?.classList.add("is-placeholder");

    picker.querySelectorAll(".tr-beautician-picker__option").forEach((button) => {
        button.classList.remove("is-active");
        button.setAttribute("aria-selected", "false");
    });
}
