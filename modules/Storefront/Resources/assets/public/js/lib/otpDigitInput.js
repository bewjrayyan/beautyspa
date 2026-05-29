/**
 * Six-box OTP input (Alpine) + optional vanilla initializer.
 */

function findParentWithModel(el, modelKey) {
    let node = el.parentElement;

    while (node) {
        if (node._x_dataStack?.length) {
            for (let i = node._x_dataStack.length - 1; i >= 0; i--) {
                const data = node._x_dataStack[i];

                if (data && Object.prototype.hasOwnProperty.call(data, modelKey)) {
                    return data;
                }
            }
        }

        node = node.parentElement;
    }

    return null;
}

export function registerOtpDigitInput(Alpine) {
    Alpine.data("otpDigitInput", (config = {}) => ({
        length: config.length || 6,
        modelKey: config.model || "otp",
        cells: [],
        cellIndexes: [],
        activeIndex: null,

        init() {
            this.cellIndexes = Array.from({ length: this.length }, (_, i) => i);
            this.resetCells();

            const parent = findParentWithModel(this.$el, this.modelKey);

            if (parent) {
                this.$watch(() => parent[this.modelKey], (value) => {
                    if (!value) {
                        this.resetCells();
                    } else if (String(value).length <= this.length) {
                        this.fillCells(String(value));
                    }
                });

                if (typeof parent.step !== "undefined") {
                    this.$watch(() => parent.step, (step) => {
                        if (step === "otp") {
                            this.resetCells();
                            this.$nextTick(() => this.focusCell(0));
                        }
                    });
                }
            }

            this.$watch("cells", () => this.updateModel());
        },

        resetCells() {
            this.cells = Array.from({ length: this.length }, () => "");
        },

        fillCells(value) {
            const digits = String(value).replace(/\D/g, "").slice(0, this.length);

            this.cells = Array.from({ length: this.length }, (_, i) => digits[i] || "");
        },

        updateModel() {
            const code = this.cells.join("").replace(/\D/g, "").slice(0, this.length);
            const parent = findParentWithModel(this.$el, this.modelKey);

            if (parent) {
                parent[this.modelKey] = code;
            }
        },

        onCellInput(index, event) {
            let value = event.target.value.replace(/\D/g, "");

            if (value.length > 1) {
                value = value.slice(-1);
            }

            this.cells[index] = value;
            event.target.value = value;
            this.updateModel();

            if (value && index < this.length - 1) {
                this.focusCell(index + 1);
            }
        },

        onCellFocus(index, event) {
            this.activeIndex = index;
            event.target.select();
        },

        onCellBlur() {
            this.activeIndex = null;
        },

        onCellKeydown(index, event) {
            if (event.key === "Backspace" && !this.cells[index] && index > 0) {
                event.preventDefault();
                this.focusCell(index - 1);
            }

            if (event.key === "ArrowLeft" && index > 0) {
                event.preventDefault();
                this.focusCell(index - 1);
            }

            if (event.key === "ArrowRight" && index < this.length - 1) {
                event.preventDefault();
                this.focusCell(index + 1);
            }
        },

        onPaste(event) {
            event.preventDefault();

            const pasted = (event.clipboardData?.getData("text") || "")
                .replace(/\D/g, "")
                .slice(0, this.length);

            this.fillCells(pasted);
            this.updateModel();
            this.focusCell(Math.min(pasted.length, this.length - 1) || 0);
        },

        focusCell(index) {
            this.$nextTick(() => {
                const inputs = this.$el.querySelectorAll(".otp-digit-input__cell");

                inputs[index]?.focus();
                inputs[index]?.select();
            });
        },
    }));
}

/**
 * Vanilla OTP boxes (e.g. My Appointments page without Alpine).
 */
export function initOtpDigitInput(root, options = {}) {
    if (!root) {
        return;
    }

    const length = options.length || 6;
    const cells = root.querySelectorAll(".otp-digit-input__cell");
    const hidden = options.hiddenInputId
        ? document.getElementById(options.hiddenInputId)
        : null;

    const sync = () => {
        const code = Array.from(cells)
            .map((input) => input.value.replace(/\D/g, ""))
            .join("")
            .slice(0, length);

        if (hidden) {
            hidden.value = code;
        }

        options.onChange?.(code);
    };

    const focusCell = (index) => {
        cells[index]?.focus();
        cells[index]?.select();
    };

    const fill = (value) => {
        const digits = String(value).replace(/\D/g, "").slice(0, length);

        cells.forEach((input, i) => {
            input.value = digits[i] || "";
        });

        sync();
    };

    cells.forEach((input, index) => {
        input.addEventListener("input", () => {
            let value = input.value.replace(/\D/g, "");

            if (value.length > 1) {
                value = value.slice(-1);
            }

            input.value = value;
            sync();

            if (value && index < length - 1) {
                focusCell(index + 1);
            }
        });

        input.addEventListener("keydown", (event) => {
            if (event.key === "Backspace" && !input.value && index > 0) {
                event.preventDefault();
                focusCell(index - 1);
            }
        });

        input.addEventListener("paste", (event) => {
            event.preventDefault();
            const pasted = (event.clipboardData?.getData("text") || "")
                .replace(/\D/g, "")
                .slice(0, length);

            fill(pasted);
            focusCell(Math.min(pasted.length, length - 1) || 0);
        });

        input.addEventListener("focus", () => {
            cells.forEach((cell) => cell.classList.remove("is-active"));
            input.classList.add("is-active");
            input.select();
        });

        input.addEventListener("blur", () => {
            input.classList.remove("is-active");
        });

        input.addEventListener("input", () => {
            input.classList.toggle("is-filled", Boolean(input.value));
        });
    });

    return { fill, focus: focusCell, sync };
}
