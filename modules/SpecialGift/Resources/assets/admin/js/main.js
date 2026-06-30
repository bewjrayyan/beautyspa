document.addEventListener("DOMContentLoaded", () => {
    initContentPreview();
    initDesignForm();
    initPlaceholderChips();
});

function initContentPreview() {
    const form = document.getElementById("gift-voucher-content-form");

    if (!form) {
        return;
    }

    const inputs = form.querySelectorAll("[data-preview-input]");
    const previewNodes = document.querySelectorAll("[data-preview]");

    const resolveValue = (input) => {
        const value = input.value.trim();

        return value || input.dataset.previewDefault || "";
    };

    const syncPreview = () => {
        inputs.forEach((input) => {
            const key = input.dataset.previewInput;
            const target = document.querySelector(`[data-preview="${key}"]`);

            if (!target) {
                return;
            }

            const value = resolveValue(input);
            target.textContent = value;

            if (target.tagName === "BUTTON") {
                target.textContent = value;
            }
        });
    };

    inputs.forEach((input) => {
        input.addEventListener("input", syncPreview);
        input.addEventListener("change", syncPreview);
    });

    syncPreview();
}

function initDesignForm() {
    const form = document.getElementById("gift-voucher-design-form");

    if (!form) {
        return;
    }

    const presetSelect = document.getElementById("specialgift-page-preset");
    const colorSourceSelect = document.getElementById("specialgift-page-color-source");
    const customColorField = document.getElementById("specialgift-custom-color-field");
    const themeColorNote = document.getElementById("specialgift-theme-color-note");
    const customColorInput = form.querySelector('[name="specialgift_page_accent_color"]');
    const customEffectsPanel = document.getElementById("specialgift-custom-effects-panel");
    const presetNote = document.getElementById("specialgift-design-preset-note");
    const previewCanvas = document.getElementById("gift-voucher-design-preview-canvas");
    const gradientInput = form.querySelector('[name="specialgift_page_gradient_enabled"]');
    const bokehInput = form.querySelector('[name="specialgift_page_bokeh_enabled"]');
    const sparklesInput = form.querySelector('[name="specialgift_page_sparkles_enabled"]');

    const presetValues = {
        aesthetic: { gradient: true, bokeh: true, sparkles: true },
        minimal: { gradient: true, bokeh: false, sparkles: false },
        classic: { gradient: true, bokeh: true, sparkles: false },
        custom: null,
    };

    const resolveAccentColor = () => {
        const storeColor = previewCanvas?.dataset.storeColor || "#f274ac";

        if (colorSourceSelect?.value === "custom") {
            return customColorInput?.value || storeColor;
        }

        return storeColor;
    };

    const syncColorSourceUi = () => {
        const isCustom = colorSourceSelect?.value === "custom";

        customColorField?.classList.toggle("hide", !isCustom);
        themeColorNote?.classList.toggle("hide", isCustom);
    };

    const syncPresetUi = () => {
        const isCustom = presetSelect?.value === "custom";

        customEffectsPanel?.classList.toggle("hide", !isCustom);
        presetNote?.classList.toggle("hide", isCustom);
    };

    const applyPresetToInputs = () => {
        const preset = presetSelect?.value;

        if (!preset || preset === "custom" || !presetValues[preset]) {
            return;
        }

        const values = presetValues[preset];

        if (gradientInput) {
            gradientInput.checked = values.gradient;
        }

        if (bokehInput) {
            bokehInput.checked = values.bokeh;
        }

        if (sparklesInput) {
            sparklesInput.checked = values.sparkles;
        }
    };

    const syncPreview = () => {
        if (!previewCanvas) {
            return;
        }

        const accent = resolveAccentColor();
        const gradientEnabled = gradientInput?.checked ?? true;
        const bokehEnabled = bokehInput?.checked ?? true;
        const sparklesEnabled = sparklesInput?.checked ?? true;

        previewCanvas.style.setProperty("--gv-preview-accent", accent);
        previewCanvas.classList.toggle("gv-design-preview__canvas--no-gradient", !gradientEnabled);

        previewCanvas.querySelectorAll(".gv-design-preview__orb").forEach((orb) => {
            orb.style.display = bokehEnabled ? "" : "none";
        });

        previewCanvas.querySelectorAll(".gv-design-preview__sparkle").forEach((sparkle) => {
            sparkle.style.display = sparklesEnabled ? "" : "none";
        });

        previewCanvas.querySelectorAll(".gv-design-preview__mock-card--form button").forEach((button) => {
            button.style.background = accent;
            button.style.borderColor = accent;
        });
    };

    presetSelect?.addEventListener("change", () => {
        applyPresetToInputs();
        syncPresetUi();
        syncPreview();
    });

    colorSourceSelect?.addEventListener("change", () => {
        syncColorSourceUi();
        syncPreview();
    });

    customColorInput?.addEventListener("input", syncPreview);
    gradientInput?.addEventListener("change", syncPreview);
    bokehInput?.addEventListener("change", syncPreview);
    sparklesInput?.addEventListener("change", syncPreview);

    syncColorSourceUi();
    syncPresetUi();
    syncPreview();
}

function initPlaceholderChips() {
    document.querySelectorAll(".gv-placeholder-chips").forEach((container) => {
        const targetSelector = container.dataset.target;
        const textarea = targetSelector ? document.querySelector(targetSelector) : null;

        if (!textarea) {
            return;
        }

        container.querySelectorAll(".gv-placeholder-chip").forEach((chip) => {
            chip.addEventListener("click", () => {
                const placeholder = chip.dataset.placeholder || chip.textContent.trim();
                const start = textarea.selectionStart ?? textarea.value.length;
                const end = textarea.selectionEnd ?? textarea.value.length;
                const before = textarea.value.slice(0, start);
                const after = textarea.value.slice(end);

                textarea.value = `${before}${placeholder}${after}`;
                textarea.focus();

                const cursor = start + placeholder.length;
                textarea.setSelectionRange(cursor, cursor);
                textarea.dispatchEvent(new Event("input", { bubbles: true }));
            });
        });
    });
}
