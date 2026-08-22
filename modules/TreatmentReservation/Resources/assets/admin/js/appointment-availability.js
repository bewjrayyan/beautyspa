import { formatAppointmentTimeDisplay, normalizeAppointmentTime24 } from "./time-format.js";

import flatpickr from "flatpickr";

/**
 * Admin UI for Treatment + Branch appointment availability.
 */
(function () {
    const root = document.getElementById("tr-appointment-availability");
    if (!root) return;

    const boot = window.trAppointmentAvailabilityBoot || {};
    const csrf = root.dataset.csrf;
    let modalReturnFocus = null;

    function initializeWorkspaceTabs() {
        const tabs = Array.from(root.querySelectorAll("[data-tr-tab]"));
        const panels = tabs
            .map((tab) => document.getElementById(tab.dataset.trTab))
            .filter(Boolean);
        if (!tabs.length || tabs.length !== panels.length) return;

        const panelIds = panels.map((panel) => panel.id);
        const workspaceUrl = (panelId) =>
            `${window.location.pathname}${window.location.search}#${panelId}`;

        const activateTab = (panelId, updateHistory = false) => {
            const resolvedId = panelIds.includes(panelId) ? panelId : panelIds[0];

            tabs.forEach((tab) => {
                const active = tab.dataset.trTab === resolvedId;
                tab.classList.toggle("is-active", active);
                tab.setAttribute("aria-selected", active ? "true" : "false");
                tab.tabIndex = active ? 0 : -1;
            });

            panels.forEach((panel) => {
                panel.hidden = panel.id !== resolvedId;
            });

            if (updateHistory && window.location.hash !== `#${resolvedId}`) {
                window.history.pushState(null, "", workspaceUrl(resolvedId));
            }
        };

        tabs.forEach((tab, index) => {
            tab.addEventListener("click", () => activateTab(tab.dataset.trTab, true));
            tab.addEventListener("keydown", (event) => {
                let targetIndex = null;

                if (event.key === "ArrowRight") targetIndex = (index + 1) % tabs.length;
                if (event.key === "ArrowLeft") targetIndex = (index - 1 + tabs.length) % tabs.length;
                if (event.key === "Home") targetIndex = 0;
                if (event.key === "End") targetIndex = tabs.length - 1;
                if (targetIndex === null) return;

                event.preventDefault();
                tabs[targetIndex].focus();
                activateTab(tabs[targetIndex].dataset.trTab, true);
            });
        });

        const activateFromHash = () => activateTab(window.location.hash.replace(/^#/, ""));
        window.addEventListener("hashchange", activateFromHash);
        activateFromHash();

        if (!panelIds.includes(window.location.hash.replace(/^#/, ""))) {
            window.history.replaceState(null, "", workspaceUrl(panelIds[0]));
        }
    }

    initializeWorkspaceTabs();

    function initializeScopeSelects() {
        if (!window.jQuery?.fn?.selectize) return;

        [
            { id: "tr-branch-select", placeholder: root.dataset.labelSearchBranch || "Search branches…" },
            { id: "tr-treatment-select", placeholder: root.dataset.labelSearchTreatment || "Search treatments…" },
        ].forEach(({ id, placeholder }) => {
            const select = document.getElementById(id);
            if (!select || select.selectize) return;

            const label = root.querySelector(`label[for="${id}"]`)?.textContent.trim() || placeholder;
            const dropdownId = `${id}-results`;
            const instance = window.jQuery(select)
                .removeClass("form-control")
                .selectize({
                    allowEmptyOption: id === "tr-treatment-select",
                    closeAfterSelect: true,
                    create: false,
                    hideSelected: false,
                    persist: false,
                    placeholder,
                    selectOnTab: true,
                    render: {
                        item(item, escape) {
                            const text = String(item.text ?? "").trim();

                            return `<div class="tr-avail-search-select__item"><span>${escape(text)}</span></div>`;
                        },
                        option(item, escape) {
                            const text = String(item.text ?? "").trim();

                            return (
                                `<div class="tr-avail-search-select__option">` +
                                `<span>${escape(text)}</span>` +
                                `<i class="fa fa-check" aria-hidden="true"></i>` +
                                `</div>`
                            );
                        },
                    },
                    onInitialize() {
                        this.$wrapper.addClass("tr-avail-search-select");
                        this.$dropdown.attr("id", dropdownId);
                        this.$control.attr("aria-label", label);
                        this.$control_input.attr({
                            "aria-autocomplete": "list",
                            "aria-controls": dropdownId,
                            "aria-expanded": "false",
                            "aria-label": placeholder,
                            autocomplete: "off",
                            role: "combobox",
                        });
                        this._trReady = true;
                    },
                    onDropdownOpen() {
                        this.$control_input.attr("aria-expanded", "true");
                    },
                    onDropdownClose() {
                        this.$control_input.attr("aria-expanded", "false");
                    },
                    onChange() {
                        if (!this._trReady) return;
                        this.$input[0]?.form?.submit();
                    },
                })[0]?.selectize;

            instance?.$control_input.attr("placeholder", placeholder);
        });
    }

    initializeScopeSelects();

    function localDateString(date = new Date()) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");
        return `${year}-${month}-${day}`;
    }

    function setButtonBusy(button, busy) {
        if (!button) return;
        if (busy) {
            button.dataset.originalLabel = button.textContent.trim();
            button.textContent = root.dataset.labelSaving || "Saving…";
            button.classList.add("is-loading");
        } else if (button.dataset.originalLabel) {
            button.textContent = button.dataset.originalLabel;
            button.classList.remove("is-loading");
        }
        button.disabled = busy;
        button.setAttribute("aria-busy", busy ? "true" : "false");
    }

    function parseDays(el) {
        if (!el) return [];
        try {
            return JSON.parse(el.dataset.days || "[]");
        } catch (e) {
            return [];
        }
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    function escapeAttr(value) {
        return escapeHtml(value).replace(/'/g, "&#39;");
    }

    function normalizeTimeInput(value) {
        return normalizeAppointmentTime24(value);
    }

    function getDayTimes(row) {
        return Array.from(row.querySelectorAll(".tr-avail-day__chip"))
            .map((chip) => chip.dataset.time)
            .filter(Boolean);
    }

    function renderTimeChips(wrap, times, disabled) {
        wrap.innerHTML = "";
        times.forEach((time) => {
            const chip = document.createElement("span");
            chip.className = "tr-avail-day__chip";
            chip.dataset.time = time;
            chip.innerHTML =
                `<span>${escapeHtml(formatAppointmentTimeDisplay(time))}</span>` +
                (disabled
                    ? ""
                    : `<button type="button" aria-label="${escapeAttr(root.dataset.labelRemove || "Remove")}">&times;</button>`);
            if (!disabled) {
                chip.querySelector("button")?.addEventListener("click", () => chip.remove());
            }
            wrap.appendChild(chip);
        });
    }

    function initializeTimePicker(input) {
        if (!input || input._flatpickr) return;

        input.setAttribute("aria-label", root.dataset.labelSelectTime || "Select a start time");
        flatpickr(input, {
            allowInput: true,
            clickOpens: true,
            dateFormat: "g:i K",
            disableMobile: true,
            enableTime: true,
            minuteIncrement: 15,
            noCalendar: true,
            time_24hr: false,
            onReady(_dates, _dateString, instance) {
                const calendar = instance.calendarContainer;
                calendar.classList.add("tr-avail-timepicker");
                calendar.setAttribute("aria-label", root.dataset.labelTimePickerTitle || "Choose start time");

                const header = document.createElement("div");
                header.className = "tr-avail-timepicker__head";
                header.innerHTML =
                    '<i class="fa fa-clock-o" aria-hidden="true"></i>' +
                    `<strong>${escapeHtml(root.dataset.labelTimePickerTitle || "Choose start time")}</strong>`;
                calendar.insertBefore(header, calendar.firstChild);

                const quick = document.createElement("div");
                quick.className = "tr-avail-timepicker__quick";
                quick.innerHTML = `<span>${escapeHtml(root.dataset.labelQuickTimes || "Quick times")}</span>`;
                [["09:00", "9:00 AM"], ["12:00", "12:00 PM"], ["15:00", "3:00 PM"], ["18:00", "6:00 PM"]].forEach(([time, label]) => {
                    const button = document.createElement("button");
                    button.type = "button";
                    button.textContent = label;
                    button.setAttribute("aria-label", `${root.dataset.labelSelectTime || "Select time"} ${time}`);
                    button.addEventListener("click", () => {
                        instance.setDate(time, true, "H:i");
                        instance.close();
                        input.focus();
                    });
                    quick.appendChild(button);
                });
                calendar.appendChild(quick);
            },
        });
    }

    function renderDays(container, days) {
        if (!container) return;
        container.querySelectorAll(".tr-day-time-input").forEach((input) => input._flatpickr?.destroy());
        container.classList.add("tr-avail-days");
        container.innerHTML = "";
        days.forEach((day, index) => {
            const open = Boolean(day.is_open);
            const times = Array.isArray(day.times) ? day.times.slice() : [];
            const row = document.createElement("div");
            row.className = `tr-avail-day ${open ? "tr-avail-day--open" : "tr-avail-day--closed"}`;
            row.dataset.index = String(index);
            row.dataset.dayOfWeek = String(day.day_of_week ?? index);

            row.innerHTML =
                `<div class="tr-avail-day__top">` +
                `<p class="tr-avail-day__name">${escapeHtml(day.label || "")}</p>` +
                `<label class="tr-avail-day__switch ${open ? "is-open" : "is-closed"}">` +
                `<input type="checkbox" role="switch" class="tr-day-open" ` +
                `aria-label="${escapeAttr(`${day.label || ""} — ${root.dataset.labelOpen || "Open"} / ${root.dataset.labelClosed || "Closed"}`)}" ` +
                `${open ? "checked" : ""}>` +
                `<span class="tr-avail-day__open-label">${escapeHtml(root.dataset.labelOpen || "Open")}</span>` +
                `<span class="tr-avail-day__closed-label">${escapeHtml(root.dataset.labelClosed || "Closed")}</span>` +
                `</label></div>` +
                `<div class="tr-avail-day__times-wrap"></div>` +
                `<div class="tr-avail-day__add" ${open ? "" : "hidden"}>` +
                `<div class="tr-avail-time-field"><i class="fa fa-clock-o" aria-hidden="true"></i>` +
                `<input type="text" class="tr-day-time-input" inputmode="numeric" autocomplete="off" ` +
                `placeholder="${escapeAttr(root.dataset.labelSelectTime || "Select time")}"></div>` +
                `<button type="button" class="tr-day-add-time">${escapeHtml(root.dataset.labelAddTime || "Add")}</button>` +
                `</div>` +
                `<p class="tr-avail-day__hint" ${open ? "hidden" : ""}>${escapeHtml(root.dataset.labelClosedHint || "Closed — no booking slots")}</p>`;

            const openInput = row.querySelector(".tr-day-open");
            const switchEl = row.querySelector(".tr-avail-day__switch");
            const timesWrap = row.querySelector(".tr-avail-day__times-wrap");
            const addWrap = row.querySelector(".tr-avail-day__add");
            const hint = row.querySelector(".tr-avail-day__hint");
            const timeInput = row.querySelector(".tr-day-time-input");
            const addBtn = row.querySelector(".tr-day-add-time");

            initializeTimePicker(timeInput);

            renderTimeChips(timesWrap, times, !open);

            const syncOpenState = () => {
                const isOpen = Boolean(openInput.checked);
                row.classList.toggle("tr-avail-day--open", isOpen);
                row.classList.toggle("tr-avail-day--closed", !isOpen);
                switchEl.classList.toggle("is-open", isOpen);
                switchEl.classList.toggle("is-closed", !isOpen);
                addWrap.hidden = !isOpen;
                hint.hidden = isOpen;
                renderTimeChips(timesWrap, getDayTimes(row), !isOpen);
            };

            openInput.addEventListener("change", syncOpenState);

            addBtn.addEventListener("click", () => {
                const time = normalizeTimeInput(timeInput.value);
                if (!time) return;
                const current = getDayTimes(row);
                if (!current.includes(time)) {
                    current.push(time);
                    current.sort();
                    renderTimeChips(timesWrap, current, false);
                }
                timeInput._flatpickr?.clear();
                timeInput.value = "";
            });
            timeInput.addEventListener("keydown", (event) => {
                if (event.key !== "Enter") return;
                event.preventDefault();
                addBtn.click();
            });

            container.appendChild(row);
        });
    }

    function updateBranchOverview(days) {
        const normalizedDays = Array.isArray(days) ? days : [];
        const openCount = normalizedDays.filter((day) => Boolean(day.is_open)).length;
        const timeCount = normalizedDays.reduce(
            (total, day) => total + (Array.isArray(day.times) ? day.times.length : 0),
            0
        );
        const openCountEl = document.getElementById("tr-open-days-count");
        const timeCountEl = document.getElementById("tr-start-times-count");
        if (openCountEl) openCountEl.textContent = String(openCount);
        if (timeCountEl) timeCountEl.textContent = String(timeCount);
    }

    function collectDays(container) {
        return Array.from(container.querySelectorAll(".tr-avail-day")).map((row) => {
            const open = row.querySelector(".tr-day-open")?.checked;
            return {
                day_of_week: Number(row.dataset.dayOfWeek || 0),
                is_open: Boolean(open),
                times: open ? getDayTimes(row) : [],
            };
        });
    }


    function assertOpenDaysHaveTimes(days) {
        const bad = (days || []).find((day) => day.is_open && (!day.times || day.times.length === 0));
        if (!bad) return true;
        const msg = root.dataset.msgOpenDayNeedsTimes || root.dataset.msgError || "Open days need times";
        window.notify?.error?.(msg) || alert(msg);
        return false;
    }

    function confirmAllClosedSave(days) {
        const anyOpen = (days || []).some((day) => day.is_open);
        if (anyOpen) return true;
        const msg =
            root.dataset.msgConfirmAllClosed ||
            "All days are closed. Save anyway?";
        return window.confirm(msg);
    }

    async function postJson(url, body) {
        const response = await fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": csrf,
            },
            body: JSON.stringify(body),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.message || root.dataset.msgError || "Error");
        }
        return data;
    }

    const branchEl = document.getElementById("tr-branch-days");
    const treatmentEl = document.getElementById("tr-treatment-days");
    const initialBranchDays = parseDays(branchEl);
    renderDays(branchEl, initialBranchDays);
    updateBranchOverview(initialBranchDays);
    renderDays(treatmentEl, parseDays(treatmentEl));

    document.getElementById("tr-save-branch")?.addEventListener("click", async (event) => {
        const btn = event.currentTarget;
        const days = collectDays(branchEl);
        if (!assertOpenDaysHaveTimes(days)) return;
        if (!confirmAllClosedSave(days)) return;
        setButtonBusy(btn, true);
        try {
            const data = await postJson(root.dataset.branchUrl, {
                spa_branch_id: boot.spaBranchId,
                days,
            });
            window.notify?.success?.(data.message) || alert(data.message || root.dataset.msgSaved);
            if (data.days) {
                branchEl.dataset.days = JSON.stringify(data.days);
                renderDays(branchEl, data.days);
                updateBranchOverview(data.days);
                refreshWeeklyMapFromEditors();
            }
        } catch (e) {
            window.notify?.error?.(e.message) || alert(e.message);
        } finally {
            setButtonBusy(btn, false);
            btn.disabled = boot.spaBranchId < 1;
        }
    });

    document.getElementById("tr-save-treatment")?.addEventListener("click", async (event) => {
        if (!treatmentEl || !boot.productId) return;
        const btn = event.currentTarget;
        const days = collectDays(treatmentEl);
        if (!assertOpenDaysHaveTimes(days)) return;
        if (!confirmAllClosedSave(days)) return;
        setButtonBusy(btn, true);
        try {
            const data = await postJson(root.dataset.treatmentUrl, {
                product_id: boot.productId,
                spa_branch_id: boot.spaBranchId,
                duration_minutes: Number(document.getElementById("tr-duration")?.value || 0) || null,
                capacity_per_slot: Number(document.getElementById("tr-capacity")?.value || 1),
                allow_tba: Boolean(document.getElementById("tr-allow-tba")?.checked),
                is_bookable: Boolean(document.getElementById("tr-bookable")?.checked),
                days,
            });
            window.notify?.success?.(data.message) || alert(data.message || root.dataset.msgSaved);
            if (data.days) {
                treatmentEl.dataset.days = JSON.stringify(data.days);
                renderDays(treatmentEl, data.days);
                refreshWeeklyMapFromEditors();
            }
        } catch (e) {
            window.notify?.error?.(e.message) || alert(e.message);
        } finally {
            setButtonBusy(btn, false);
        }
    });

    const calRoot = document.getElementById("tr-avail-calendar");
    const modal = document.getElementById("tr-avail-override-modal");
    let calMonth = boot.calendarMonth || new Date().toISOString().slice(0, 7);
    let overridesByDate = {};

    function buildWeeklyMap(days) {
        const map = {};
        (days || []).forEach((day) => {
            map[Number(day.day_of_week)] = Boolean(day.is_open);
        });
        return map;
    }

    // Prefer treatment schedule when selected; else branch.
    let weeklyOpenByDow =
        boot.productId && treatmentEl
            ? buildWeeklyMap(parseDays(treatmentEl))
            : buildWeeklyMap(parseDays(branchEl));

    function refreshWeeklyMapFromEditors() {
        if (boot.productId && treatmentEl) {
            weeklyOpenByDow = buildWeeklyMap(collectDays(treatmentEl));
        } else {
            weeklyOpenByDow = buildWeeklyMap(collectDays(branchEl));
        }
        renderCalendar();
    }


    function loadOverridesMap() {
        overridesByDate = {};
        try {
            const list = JSON.parse(calRoot?.dataset.overrides || "[]");
            list.forEach((item) => {
                if (!item?.date) return;
                const existing = overridesByDate[item.date];
                // Prefer treatment-specific override over branch-wide for the same date.
                if (!existing || ((Number(item.product_id) || 0) > 0 && (Number(existing.product_id) || 0) === 0)) {
                    overridesByDate[item.date] = item;
                }
            });
        } catch (e) {
            overridesByDate = {};
        }
    }

    function weekdayLabels() {
        try {
            return JSON.parse(root.dataset.weekdayShort || "[]");
        } catch (e) {
            return ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
        }
    }

    function renderCalendar() {
        if (!calRoot) return;
        const [year, month] = calMonth.split("-").map(Number);
        const firstDay = new Date(year, month - 1, 1);
        const lastDay = new Date(year, month, 0);
        const startOffset = (firstDay.getDay() + 6) % 7;
        const daysInMonth = lastDay.getDate();
        const todayStr = localDateString();
        const labelEl = document.getElementById("tr-avail-cal-label");
        if (labelEl) {
            labelEl.textContent = firstDay.toLocaleDateString(undefined, {
                month: "long",
                year: "numeric",
            });
        }

        const heads = weekdayLabels()
            .map((d) => `<div class="tr-avail-cal__dow">${escapeHtml(d)}</div>`)
            .join("");

        const cells = [];
        for (let i = 0; i < startOffset; i++) {
            cells.push('<div class="tr-avail-cal__day tr-avail-cal__day--muted" aria-hidden="true"></div>');
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
            const ov = overridesByDate[dateStr];
            const dow = new Date(year, month - 1, day).getDay();
            const weeklyOpen = weeklyOpenByDow[dow] === true;
            const classes = [
                "tr-avail-cal__day",
                dateStr === todayStr ? "tr-avail-cal__day--today" : "",
                ov ? `tr-avail-cal__day--${ov.status}` : weeklyOpen ? "tr-avail-cal__day--weekly-open" : "tr-avail-cal__day--weekly-closed",
            ]
                .filter(Boolean)
                .join(" ");

            const timesPreview = ov?.times?.length
                ? `<span class="tr-avail-cal__times">${escapeHtml(ov.times.slice(0, 3).join(" · "))}</span>`
                : "";
            const statusLabel = ov
                ? ov.status === "open"
                    ? root.dataset.labelOpen
                    : ov.status === "closed"
                      ? root.dataset.labelClosed
                      : root.dataset.labelCustom
                : weeklyOpen
                  ? root.dataset.labelWeeklyOpen
                  : root.dataset.labelWeeklyClosed;
            const badge = `<span class="tr-avail-cal__badge">${escapeHtml(
                statusLabel || root.dataset.labelWeeklyClosed || root.dataset.labelClosed || "closed"
            )}</span>`;
            const scopeLabel = ov?.scope_label || "";
            const ariaLabel = `${dateStr}, ${statusLabel || ""}${scopeLabel ? `, ${scopeLabel}` : ""}`.trim();
            const title = scopeLabel
                ? `${root.dataset.labelClickDate || "Set override"} — ${scopeLabel}`
                : root.dataset.labelClickDate || "Set override";

            cells.push(
                `<button type="button" class="${classes}" data-override-date="${dateStr}" aria-label="${escapeAttr(ariaLabel)}" title="${escapeAttr(title)}">` +
                    `<span class="tr-avail-cal__num">${day}</span>` +
                    badge +
                    timesPreview +
                    `</button>`
            );
        }

        const grid = calRoot.querySelector(".tr-avail-cal__grid");
        if (grid) grid.innerHTML = heads + cells.join("");
    }


    function renderPreviewBody(html) {
        const body = modal?.querySelector("[data-ov-preview-body]");
        if (body) body.innerHTML = html;
    }

    function getOverrideModalTimes() {
        return Array.from(modal?.querySelectorAll("[data-ov-time-chip]") || [])
            .map((chip) => chip.dataset.time)
            .filter(Boolean)
            .sort();
    }

    function renderOverrideDraftPreview() {
        const status = currentModalStatus();
        if (status === "open") {
            renderPreviewBody(
                `<p class="tr-avail-preview__state tr-avail-preview__state--open"><i class="fa fa-refresh" aria-hidden="true"></i><span>${escapeHtml(root.dataset.labelPreviewOpenInherit || "The weekly schedule will be used for this date.")}</span></p>`
            );
            return;
        }
        if (status === "closed") {
            renderPreviewBody(
                `<p class="tr-avail-preview__state tr-avail-preview__state--closed"><i class="fa fa-ban" aria-hidden="true"></i><span>${escapeHtml(root.dataset.labelPreviewClosedResult || "No booking slots will be available on this date.")}</span></p>`
            );
            return;
        }

        const times = getOverrideModalTimes();
        if (times.length === 0) {
            renderPreviewBody(
                `<p class="tr-avail-preview__state tr-avail-preview__state--empty"><i class="fa fa-clock-o" aria-hidden="true"></i><span>${escapeHtml(root.dataset.labelPreviewCustomEmpty || "Add at least one custom start time.")}</span></p>`
            );
            return;
        }

        renderPreviewBody(
            `<div class="tr-avail-preview__chips">${times
                .map((time) => `<span class="tr-avail-preview__chip">${escapeHtml(time)}</span>`)
                .join("")}</div>`
        );
    }

    function renderOverrideTimeChips(times) {
        const wrap = modal?.querySelector("[data-ov-time-chips]");
        const hidden = modal?.querySelector("#tr-ov-times");
        if (!wrap) return;

        const normalizedTimes = [...new Set((times || []).map(normalizeTimeInput).filter(Boolean))].sort();
        wrap.innerHTML = "";
        normalizedTimes.forEach((time) => {
            const chip = document.createElement("span");
            chip.className = "tr-avail-day__chip";
            chip.dataset.ovTimeChip = "";
            chip.dataset.time = time;
            chip.innerHTML =
                `<span>${escapeHtml(formatAppointmentTimeDisplay(time))}</span>` +
                `<button type="button" aria-label="${escapeAttr(`${root.dataset.labelRemove || "Remove"} ${time}`)}">&times;</button>`;
            chip.querySelector("button")?.addEventListener("click", () => {
                chip.remove();
                if (hidden) hidden.value = getOverrideModalTimes().join(",");
                renderOverrideDraftPreview();
            });
            wrap.appendChild(chip);
        });
        if (hidden) hidden.value = normalizedTimes.join(",");
    }

    function addOverrideModalTime() {
        const input = modal?.querySelector("#tr-ov-time-input");
        const time = normalizeTimeInput(input?.value);
        if (!time) {
            input?.focus();
            return;
        }
        renderOverrideTimeChips([...getOverrideModalTimes(), time]);
        if (input?._flatpickr) input._flatpickr.clear();
        else if (input) input.value = "";
        renderOverrideDraftPreview();
        input?.focus();
    }

    function openOverrideModal(dateStr) {
        if (!modal || boot.spaBranchId < 1) return;
        const ov = overridesByDate[dateStr] || {};
        modalReturnFocus = document.activeElement;
        modal.hidden = false;
        document.body.classList.add("tr-avail-modal-open");
        const label = modal.querySelector("[data-ov-date-label]");
        if (label) {
            try {
                label.textContent = new Date(dateStr + "T00:00:00").toLocaleDateString(undefined, {
                    weekday: "long",
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                });
            } catch (e) {
                label.textContent = dateStr;
            }
        }
        modal.querySelector("#tr-ov-date").value = dateStr;
        const dow = new Date(dateStr + "T00:00:00").getDay();
        const weeklyOpen = weeklyOpenByDow[dow] === true;
        const status = ov.status || (weeklyOpen ? "open" : "closed");
        modal.querySelectorAll('input[name="tr-ov-status-pill"]').forEach((input) => {
            input.checked = input.value === status;
        });
        const statusSelect = modal.querySelector("#tr-ov-status");
        if (statusSelect) statusSelect.value = status;
        renderOverrideTimeChips(ov.times || []);
        const timeInput = modal.querySelector("#tr-ov-time-input");
        if (timeInput?._flatpickr) timeInput._flatpickr.clear();
        else if (timeInput) timeInput.value = "";
        modal.querySelector("#tr-ov-reason").value = ov.reason || "";
        syncOverrideTimesState();
        modal.querySelector("[data-ov-close]")?.focus();
    }

    function closeOverrideModal() {
        if (!modal) return;
        modal.hidden = true;
        document.body.classList.remove("tr-avail-modal-open");
        modalReturnFocus?.focus?.();
        modalReturnFocus = null;
    }

    function currentModalStatus() {
        const pill = modal?.querySelector('input[name="tr-ov-status-pill"]:checked')?.value;
        return pill || modal?.querySelector("#tr-ov-status")?.value || "custom";
    }

    function syncOverrideTimesState() {
        const status = currentModalStatus();
        const editor = modal?.querySelector("[data-ov-time-editor]");
        const timeInput = modal?.querySelector("#tr-ov-time-input");
        const addButton = modal?.querySelector("[data-ov-add-time]");
        const select = modal?.querySelector("#tr-ov-status");
        if (select) select.value = status;
        const custom = status === "custom";
        if (editor) editor.hidden = !custom;
        if (timeInput) timeInput.disabled = !custom;
        if (addButton) addButton.disabled = !custom;
        renderOverrideDraftPreview();
    }

    async function saveOverrideFromModal() {
        const date = modal.querySelector("#tr-ov-date")?.value;
        const status = currentModalStatus();
        const reason = modal.querySelector("#tr-ov-reason")?.value || null;
        const times = status === "custom" ? getOverrideModalTimes() : [];

        if (!date) return;
        if (status === "custom" && times.length === 0) {
            throw new Error(root.dataset.msgCustomTimesRequired || root.dataset.msgError || "Custom times required");
        }

        const data = await postJson(root.dataset.overrideUrl, {
            spa_branch_id: boot.spaBranchId,
            product_id: boot.productId || 0,
            override_date: date,
            status,
            reason,
            times,
        });
        window.notify?.success?.(data.message) || alert(data.message || root.dataset.msgSaved);
        window.location.reload();
    }

    document.getElementById("tr-save-override")?.addEventListener("click", async () => {
        const date = document.getElementById("tr-override-date")?.value;
        const status = document.getElementById("tr-override-status")?.value;
        const reason = document.getElementById("tr-override-reason")?.value || null;
        const timesRaw = document.getElementById("tr-override-times")?.value || "";
        const times = timesRaw
            .split(/[,\s]+/)
            .map((t) => t.trim())
            .filter(Boolean);

        if (!date) {
            alert(root.dataset.msgDateRequired || root.dataset.msgError);
            return;
        }

        try {
            const data = await postJson(root.dataset.overrideUrl, {
                spa_branch_id: boot.spaBranchId,
                product_id: boot.productId || 0,
                override_date: date,
                status,
                reason,
                times,
            });
            window.notify?.success?.(data.message) || alert(data.message || root.dataset.msgSaved);
            window.location.reload();
        } catch (e) {
            window.notify?.error?.(e.message) || alert(e.message);
        }
    });

    document.querySelectorAll(".tr-delete-override").forEach((btn) => {
        btn.addEventListener("click", async () => {
            const id = btn.dataset.id;
            const url =
                root.dataset.overrideDeleteUrl.replace("__ID__", id) +
                `?spa_branch_id=${encodeURIComponent(boot.spaBranchId)}`;
            try {
                const response = await fetch(url, {
                    method: "DELETE",
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrf,
                    },
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || root.dataset.msgError);
                const row = btn.closest("tr");
                const date = row?.dataset?.overrideDate || row?.querySelector("[data-override-date]")?.dataset?.overrideDate;
                row?.remove();
                if (date && overridesByDate[date]) {
                    delete overridesByDate[date];
                    renderCalendar();
                } else {
                    window.location.reload();
                    return;
                }
                window.notify?.success?.(data.message) || alert(data.message || root.dataset.msgSaved);
            } catch (e) {
                window.notify?.error?.(e.message) || alert(e.message);
            }
        });
    });

    if (calRoot) {
        loadOverridesMap();
        renderCalendar();

        document.getElementById("tr-avail-cal-prev")?.addEventListener("click", () => {
            const [y, m] = calMonth.split("-").map(Number);
            const d = new Date(y, m - 2, 1);
            calMonth = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`;
            renderCalendar();
        });
        document.getElementById("tr-avail-cal-next")?.addEventListener("click", () => {
            const [y, m] = calMonth.split("-").map(Number);
            const d = new Date(y, m, 1);
            calMonth = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`;
            renderCalendar();
        });
        document.getElementById("tr-avail-cal-today")?.addEventListener("click", () => {
            calMonth = localDateString().slice(0, 7);
            renderCalendar();
            calRoot.querySelector(".tr-avail-cal__day--today")?.focus();
        });

        calRoot.addEventListener("click", (e) => {
            const dayBtn = e.target.closest("[data-override-date]");
            if (!dayBtn) return;
            openOverrideModal(dayBtn.dataset.overrideDate);
        });
    }

    modal?.querySelectorAll('input[name="tr-ov-status-pill"]').forEach((input) => {
        input.addEventListener("change", syncOverrideTimesState);
    });
    modal?.querySelector("#tr-ov-status")?.addEventListener("change", syncOverrideTimesState);
    const overrideTimeInput = modal?.querySelector("#tr-ov-time-input");
    initializeTimePicker(overrideTimeInput);
    overrideTimeInput?.addEventListener("keydown", (event) => {
        if (event.key !== "Enter") return;
        event.preventDefault();
        addOverrideModalTime();
    });
    modal?.querySelector("[data-ov-add-time]")?.addEventListener("click", addOverrideModalTime);
    modal?.querySelectorAll("[data-ov-close]").forEach((button) => {
        button.addEventListener("click", closeOverrideModal);
    });
    modal?.querySelector("[data-ov-backdrop]")?.addEventListener("click", closeOverrideModal);
    modal?.querySelector("[data-ov-save]")?.addEventListener("click", async (event) => {
        const button = event.currentTarget;
        setButtonBusy(button, true);
        try {
            await saveOverrideFromModal();
        } catch (e) {
            window.notify?.error?.(e.message) || alert(e.message);
        } finally {
            setButtonBusy(button, false);
        }
    });
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && modal && !modal.hidden) closeOverrideModal();
    });
})();
