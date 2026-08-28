import flatpickr from "flatpickr";
import {
    coerceFlatpickrDate,
    mergeFlatpickrLocale,
    parseLocalYmdDate,
} from "../../../../../Storefront/Resources/assets/public/js/lib/flatpickrLocale.js";

function parsePortalLocalDate(value) {
    return parseLocalYmdDate(value);
}

function resolveFlatpickrYear(dateValue) {
    if (!dateValue) {
        return null;
    }

    if (dateValue instanceof Date) {
        return dateValue.getFullYear();
    }

    const parsed = parsePortalLocalDate(dateValue) ?? new Date(dateValue);

    return Number.isNaN(parsed.getTime()) ? null : parsed.getFullYear();
}

function portalYearBounds(instance) {
    const maxYear = resolveFlatpickrYear(instance.config.maxDate) ?? new Date().getFullYear();
    const minYear = resolveFlatpickrYear(instance.config.minDate) ?? (maxYear - 120);

    return {
        minYear: Math.min(minYear, maxYear),
        maxYear,
    };
}

function syncPortalYearSelect(instance) {
    const select = instance.calendarContainer?.querySelector(".bp-portal-year-select");

    if (!select) {
        return;
    }

    const year = String(instance.currentYear);

    if (select.value !== year) {
        select.value = year;
    }
}

function applyPortalYearChange(instance, year) {
    if (Number.isNaN(year)) {
        return;
    }

    const selected = instance.selectedDates[0];
    const month = selected ? selected.getMonth() : instance.currentMonth;
    const day = selected
        ? Math.min(selected.getDate(), new Date(year, month + 1, 0).getDate())
        : 1;

    instance.changeYear(year);

    if (selected) {
        instance.setDate(new Date(year, month, day, 12, 0, 0), false);
    }

    syncPortalYearSelect(instance);
}

function installPortalYearSelect(instance) {
    const monthNav = instance.calendarContainer?.querySelector(".flatpickr-current-month");

    if (!monthNav) {
        return;
    }

    const yearWrapper = monthNav.querySelector(".numInputWrapper");
    const yearInput = instance.currentYearElement || yearWrapper?.querySelector("input.cur-year");

    if (!yearWrapper || !yearInput) {
        return;
    }

    let select = yearWrapper.querySelector(".bp-portal-year-select");
    const { minYear, maxYear } = portalYearBounds(instance);

    if (!select) {
        yearWrapper.classList.add("bp-portal-year-wrap");
        yearInput.classList.add("bp-portal-year-input");
        yearInput.setAttribute("aria-hidden", "true");
        yearInput.tabIndex = -1;

        select = document.createElement("select");
        select.className = "bp-portal-year-select";
        select.setAttribute("aria-label", "Year");
        select.addEventListener("change", () => {
            applyPortalYearChange(instance, parseInt(select.value, 10));
        });
        yearWrapper.appendChild(select);
    }

    const expectedOptions = maxYear - minYear + 1;

    if (select.options.length !== expectedOptions) {
        select.replaceChildren();

        for (let year = maxYear; year >= minYear; year -= 1) {
            const option = document.createElement("option");
            option.value = String(year);
            option.textContent = String(year);
            select.appendChild(option);
        }
    }

    syncPortalYearSelect(instance);
}

function buildPortalDateOfBirthOptions(input) {
    const defaultDate = parsePortalLocalDate(input.dataset.defaultDate || input.value);

    const options = mergeFlatpickrLocale({
        mode: "single",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d M Y",
        disableMobile: true,
        animate: true,
        monthSelectorType: "dropdown",
        defaultDate: defaultDate || undefined,
        appendTo: document.body,
        parseDate: (dateStr) => parsePortalLocalDate(dateStr) ?? undefined,
        onReady: (_selectedDates, _dateStr, instance) => {
            instance.calendarContainer.classList.add("bp-portal-datepicker-calendar");
            installPortalYearSelect(instance);

            if (defaultDate) {
                instance.jumpToDate(defaultDate, false);
            }
        },
        onOpen: (_selectedDates, _dateStr, instance) => {
            instance.config.positionElement = instance.altInput || instance.input;
            installPortalYearSelect(instance);

            const selected = instance.selectedDates[0];

            if (selected) {
                instance.jumpToDate(selected, false);
            }
        },
        onMonthChange: (_selectedDates, _dateStr, instance) => {
            syncPortalYearSelect(instance);
        },
        onYearChange: (_selectedDates, _dateStr, instance) => {
            syncPortalYearSelect(instance);
        },
    });

    if (input.dataset.maxDate) {
        options.maxDate = coerceFlatpickrDate(input.dataset.maxDate);
    }

    if (input.dataset.minDate) {
        options.minDate = coerceFlatpickrDate(input.dataset.minDate);
    }

    return options;
}

function initPortalDateOfBirthPickers() {
    const page = document.querySelector(".tr-portal-profile-page");

    if (!page) {
        return;
    }

    page.querySelectorAll(".portal-date-picker").forEach((input) => {
        if (input._flatpickr) {
            return;
        }

        flatpickr(input, buildPortalDateOfBirthOptions(input));
    });

    const profileForm = page.querySelector("form.bp-account-form");

    if (!profileForm || profileForm.dataset.portalDobSync === "1") {
        return;
    }

    profileForm.dataset.portalDobSync = "1";
    profileForm.addEventListener("submit", () => {
        profileForm.querySelectorAll(".portal-date-picker").forEach((input) => {
            const picker = input._flatpickr;

            if (!picker) {
                return;
            }

            input.value = picker.selectedDates.length > 0
                ? picker.formatDate(picker.selectedDates[0], "Y-m-d")
                : "";
        });
    });
}

function initPortalSectionNav() {
    const page = document.querySelector(".tr-portal-profile-page--account");

    if (!page) {
        return;
    }

    const nav = page.querySelector("[data-portal-section-nav]");

    if (!nav) {
        return;
    }

    const links = Array.from(nav.querySelectorAll("[data-section-target]"));
    const sections = links
        .map((link) => document.getElementById(link.dataset.sectionTarget || ""))
        .filter(Boolean);

    if (!sections.length) {
        return;
    }

    const setActive = (id) => {
        links.forEach((link) => {
            link.classList.toggle("is-active", link.dataset.sectionTarget === id);
        });
    };

    links.forEach((link) => {
        link.addEventListener("click", (event) => {
            const targetId = link.dataset.sectionTarget;

            if (!targetId) {
                return;
            }

            const section = document.getElementById(targetId);

            if (!section) {
                return;
            }

            event.preventDefault();
            section.scrollIntoView({ behavior: "smooth", block: "start" });
            setActive(targetId);
        });
    });

    if (!("IntersectionObserver" in window)) {
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            const visible = entries
                .filter((entry) => entry.isIntersecting)
                .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

            if (visible?.target?.id) {
                setActive(visible.target.id);
            }
        },
        {
            root: null,
            rootMargin: "-20% 0px -55% 0px",
            threshold: [0.15, 0.35, 0.55],
        },
    );

    sections.forEach((section) => observer.observe(section));
}

function initIcalCopyButton() {
    const copyBtn = document.getElementById("tr-ical-copy-btn");
    const input = document.getElementById("tr-ical-url");

    if (!copyBtn || !input) {
        return;
    }

    const defaultLabel = copyBtn.innerHTML;

    copyBtn.addEventListener("click", async () => {
        try {
            await navigator.clipboard.writeText(input.value);

            copyBtn.classList.add("is-copied");
            copyBtn.innerHTML = `<i class="fa fa-check"></i> ${copyBtn.dataset.copySuccess || "Copied"}`;

            window.setTimeout(() => {
                copyBtn.classList.remove("is-copied");
                copyBtn.innerHTML = defaultLabel;
            }, 2000);
        } catch (error) {
            input.select();
            document.execCommand("copy");
        }
    });
}

function initIcalAddButton() {
    const actions = document.getElementById("tr-ical-actions");
    const addBtn = document.getElementById("tr-ical-add-btn");

    if (!actions || !addBtn) {
        return;
    }

    const webcalUrl = actions.dataset.webcalUrl || addBtn.getAttribute("href") || "";
    const googleUrl = actions.dataset.googleUrl || "";
    const userAgent = navigator.userAgent || "";

    addBtn.addEventListener("click", (event) => {
        if (/Android/i.test(userAgent) && googleUrl) {
            event.preventDefault();
            window.open(googleUrl, "_blank", "noopener,noreferrer");

            return;
        }

        if (/Windows/i.test(userAgent) && actions.dataset.outlookUrl) {
            event.preventDefault();
            window.open(actions.dataset.outlookUrl, "_blank", "noopener,noreferrer");

            return;
        }

        if (!/iPhone|iPad|iPod|Mac/i.test(userAgent) && webcalUrl) {
            event.preventDefault();
            window.open(webcalUrl, "_blank");
        }
    });
}

document.addEventListener("DOMContentLoaded", () => {
    initPortalDateOfBirthPickers();
    initPortalSectionNav();
    initIcalCopyButton();
    initIcalAddButton();
});
