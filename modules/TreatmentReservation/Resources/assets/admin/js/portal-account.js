import {
    initDateOfBirthPickers,
    syncDateOfBirthPickersOnSubmit,
} from "../../../../../Storefront/Resources/assets/public/js/lib/dateOfBirthPicker.js";

function initPortalDateOfBirthPickers() {
    const page = document.querySelector(".tr-portal-profile-page");

    if (!page) {
        return;
    }

    initDateOfBirthPickers(page, {
        selector: ".portal-date-picker",
        calendarClass: "bp-portal-datepicker-calendar",
    });

    const profileForm = page.querySelector("form.bp-account-form");

    syncDateOfBirthPickersOnSubmit(profileForm, ".portal-date-picker");
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
