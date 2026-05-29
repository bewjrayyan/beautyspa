import { initJobTitleSelectize } from "../../../../../Beautician/Resources/assets/admin/js/job-title-selectize.js";

document.addEventListener("DOMContentLoaded", () => {
    initJobTitleSelectize(document.querySelector('[name="job_title"]'));

    const copyBtn = document.getElementById("tr-ical-copy-btn");
    const input = document.getElementById("tr-ical-url");
    const actions = document.getElementById("tr-ical-actions");
    const addBtn = document.getElementById("tr-ical-add-btn");

    if (copyBtn && input) {
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
});
